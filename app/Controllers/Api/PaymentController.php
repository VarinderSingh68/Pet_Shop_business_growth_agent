<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\Order;
use App\Services\PaymentService;

final class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments = new PaymentService())
    {
    }

    public function verify(Request $request): void
    {
        $orderNumber = (string) $request->input('order_number');
        $rzpOrderId = (string) $request->input('razorpay_order_id');
        $rzpPaymentId = (string) $request->input('razorpay_payment_id');
        $rzpSignature = (string) $request->input('razorpay_signature');

        $order = Order::findByNumber($orderNumber);
        if ($order === null) {
            $this->json(['ok' => false, 'message' => 'Order not found.'], 404);
        }

        $ok = $this->payments->verifyAndCapture((int) $order['id'], $rzpOrderId, $rzpPaymentId, $rzpSignature);

        if (!$ok) {
            $this->json(['ok' => false, 'message' => 'Payment could not be verified.'], 422);
        }

        $this->json(['ok' => true, 'redirect' => '/checkout/confirmation/' . $order['order_number']]);
    }

    /**
     * Razorpay server-to-server webhook. Not CSRF-protected (Razorpay can't
     * send our token) — protected instead by the HMAC signature header,
     * verified inside PaymentService::handleWebhook.
     */
    public function webhook(Request $request): void
    {
        $rawBody = file_get_contents('php://input') ?: '';
        $signature = $request->header('X-Razorpay-Signature') ?? '';
        $event = json_decode($rawBody, true);

        try {
            $this->payments->handleWebhook($rawBody, $signature);
            $this->logDelivery($rawBody, $event, true, 'success', null);
        } catch (\Throwable $e) {
            logger_channel('webhooks')->error('Razorpay webhook rejected', ['error' => $e->getMessage()]);
            $this->logDelivery($rawBody, $event, false, 'failed', $e->getMessage());
            $this->json(['ok' => false], 400);
        }

        $this->json(['ok' => true]);
    }

    private function logDelivery(string $rawBody, mixed $event, bool $signatureValid, string $outcome, ?string $error): void
    {
        Database::instance()->insert('webhook_deliveries', [
            'source' => 'razorpay',
            'event_type' => is_array($event) ? ($event['event'] ?? null) : null,
            'signature_valid' => $signatureValid ? 1 : 0,
            'payload' => $rawBody,
            'headers' => json_encode(['X-Razorpay-Signature' => request()->header('X-Razorpay-Signature')]),
            'outcome' => $outcome,
            'error' => $error,
            'created_at' => now(),
        ]);
    }
}
