<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Order;
use App\Models\Payment;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use Razorpay\Api\Utility;

final class PaymentService
{
    public function __construct(private readonly OrderService $orderService = new OrderService())
    {
    }

    public function isRazorpayConfigured(): bool
    {
        return config('payment.razorpay_key_id') !== '' && config('payment.razorpay_key_secret') !== '';
    }

    /**
     * Creates a Razorpay order for the given local order and records a
     * pending payment row. The frontend uses the returned razorpay order id
     * to open Razorpay Checkout.
     *
     * @return array{razorpay_key: string, razorpay_order_id: string, amount: int, currency: string}
     */
    public function createRazorpayOrder(array $order): array
    {
        $api = $this->client();

        $rzpOrder = $api->order->create([
            'amount' => (int) $order['total_paise'],
            'currency' => $order['currency'],
            'receipt' => $order['order_number'],
            'notes' => ['order_id' => $order['id'], 'order_number' => $order['order_number']],
        ]);

        Database::instance()->insert('payments', [
            'order_id' => $order['id'],
            'gateway' => 'razorpay',
            'gateway_order_id' => $rzpOrder['id'],
            'amount_paise' => $order['total_paise'],
            'currency' => $order['currency'],
            'status' => 'created',
            'idempotency_key' => 'rzp_order_' . $order['order_number'],
            'raw_payload' => json_encode($rzpOrder->toArray()),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'razorpay_key' => (string) config('payment.razorpay_key_id'),
            'razorpay_order_id' => (string) $rzpOrder['id'],
            'amount' => (int) $order['total_paise'],
            'currency' => (string) $order['currency'],
        ];
    }

    /**
     * Verifies the signature Razorpay Checkout returns to the browser after
     * payment, then marks the order paid. Idempotent: replays against an
     * already-captured payment are a no-op.
     */
    public function verifyAndCapture(int $orderId, string $razorpayOrderId, string $razorpayPaymentId, string $razorpaySignature): bool
    {
        $payment = Database::instance()->selectOne(
            'SELECT * FROM payments WHERE order_id = :oid AND gateway_order_id = :rzid',
            ['oid' => $orderId, 'rzid' => $razorpayOrderId],
        );

        if ($payment === null) {
            return false;
        }

        if ($payment['status'] === 'captured') {
            return true; // already processed — idempotent replay
        }

        try {
            (new Utility())->verifyPaymentSignature([
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $razorpaySignature,
            ]);
        } catch (SignatureVerificationError $e) {
            logger_channel('payments')->error('Razorpay signature verification failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }

        Payment::updateWhere((int) $payment['id'], [
            'gateway_payment_id' => $razorpayPaymentId,
            'status' => 'captured',
        ]);

        $this->orderService->markConfirmed($orderId, 'Payment captured via Razorpay');

        return true;
    }

    /**
     * Handles the Razorpay server-to-server webhook (payment.captured /
     * payment.failed). Signature-verified and idempotent against the same
     * payments row the checkout-flow verify path uses.
     */
    public function handleWebhook(string $rawBody, string $signature): void
    {
        $secret = (string) config('payment.razorpay_webhook_secret');
        if ($secret === '') {
            throw new \RuntimeException('Webhook secret not configured');
        }

        (new Utility())->verifyWebhookSignature($rawBody, $signature, $secret);

        $event = json_decode($rawBody, true);
        $eventType = $event['event'] ?? '';
        $entity = $event['payload']['payment']['entity'] ?? null;

        if ($entity === null) {
            return;
        }

        $rzpOrderId = $entity['order_id'] ?? null;
        if ($rzpOrderId === null) {
            return;
        }

        $payment = Database::instance()->selectOne(
            'SELECT * FROM payments WHERE gateway_order_id = :rzid',
            ['rzid' => $rzpOrderId],
        );

        if ($payment === null || $payment['status'] === 'captured') {
            return; // unknown or already-processed — idempotent no-op
        }

        if ($eventType === 'payment.captured') {
            Payment::updateWhere((int) $payment['id'], [
                'gateway_payment_id' => $entity['id'],
                'status' => 'captured',
                'raw_payload' => json_encode($event),
            ]);
            $this->orderService->markConfirmed((int) $payment['order_id'], 'Payment captured via Razorpay webhook');
        } elseif ($eventType === 'payment.failed') {
            Payment::updateWhere((int) $payment['id'], ['status' => 'failed', 'raw_payload' => json_encode($event)]);
            Order::addStatusHistory((int) $payment['order_id'], 'pending_payment', 'Payment failed (webhook)');
        }
    }

    public function recordCod(array $order): void
    {
        Database::instance()->insert('payments', [
            'order_id' => $order['id'],
            'gateway' => 'cod',
            'amount_paise' => $order['total_paise'],
            'currency' => $order['currency'],
            'status' => 'created',
            'idempotency_key' => 'cod_' . $order['order_number'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->orderService->markCodConfirmed((int) $order['id']);
    }

    private function client(): Api
    {
        if (!$this->isRazorpayConfigured()) {
            throw new \RuntimeException('Razorpay is not configured. Set RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET.');
        }

        return new Api((string) config('payment.razorpay_key_id'), (string) config('payment.razorpay_key_secret'));
    }
}
