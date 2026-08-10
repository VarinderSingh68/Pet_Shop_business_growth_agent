<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Cart;
use App\Models\Order;
use App\Services\Growth\LoyaltyService;
use App\Services\Growth\ReferralService;

final class OrderService
{
    public function __construct(
        private readonly CartService $cartService = new CartService(),
        private readonly LoyaltyService $loyalty = new LoyaltyService(),
        private readonly ReferralService $referrals = new ReferralService(),
    ) {
    }

    public function stockIssues(array $cart): array
    {
        return $this->cartService->validateStock($cart);
    }

    /**
     * Creates the order from the current cart inside a single transaction:
     * re-reads variant prices/stock with a row lock (never trusts client
     * totals), decrements stock, snapshots line items, and clears the cart.
     *
     * @param array<string, mixed> $cart
     * @param array<string, mixed> $shipping
     * @throws \RuntimeException on stock/coupon problems, safe to show to the customer
     */
    public function createFromCart(
        array $cart,
        array $shipping,
        string $paymentMethod,
        ?int $userId,
        ?string $guestEmail,
        ?string $guestPhone,
        int $loyaltyPointsToRedeem = 0,
    ): array {
        $db = Database::instance();

        return $db->transaction(function (Database $db) use ($cart, $shipping, $paymentMethod, $userId, $guestEmail, $guestPhone, $loyaltyPointsToRedeem) {
            $items = $db->select(
                'SELECT ci.*, v.price_paise, v.compare_at_price_paise, v.stock_quantity, v.sku, v.label AS variant_label,
                        p.name AS product_name
                 FROM cart_items ci
                 JOIN product_variants v ON v.id = ci.variant_id
                 JOIN products p ON p.id = ci.product_id
                 WHERE ci.cart_id = :cid
                 FOR UPDATE',
                ['cid' => $cart['id']],
            );

            if ($items === []) {
                throw new \RuntimeException('Your cart is empty.');
            }

            foreach ($items as $item) {
                if ((int) $item['quantity'] > (int) $item['stock_quantity']) {
                    throw new \RuntimeException(
                        $item['product_name'] . ' (' . $item['variant_label'] . ') only has ' . $item['stock_quantity'] . ' left. Please update your cart.',
                    );
                }
            }

            $subtotal = $this->cartService->subtotal($items);

            $coupon = $cart['coupon_id'] !== null
                ? $db->selectOne('SELECT * FROM coupons WHERE id = :id FOR UPDATE', ['id' => $cart['coupon_id']])
                : null;
            $discount = $this->cartService->discountFor($coupon, $subtotal);

            $loyaltyRedeemedPoints = 0;
            if ($loyaltyPointsToRedeem > 0 && $userId !== null) {
                $available = min($loyaltyPointsToRedeem, $this->loyalty->balance($userId));
                $maxRedeemablePaise = max(0, $subtotal - $discount);
                $loyaltyRedeemedPoints = min($available, (int) floor($maxRedeemablePaise / 100));
                $discount += $loyaltyRedeemedPoints * 100;
            }

            $taxable = max(0, $subtotal - $discount);
            $shippingCost = $this->cartService->shippingFor($taxable);
            $tax = (int) round($taxable * ($this->cartService->taxRatePercent() / 100));
            $total = $taxable + $shippingCost + $tax;

            $orderId = $db->insert('orders', [
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $userId,
                'guest_email' => $userId === null ? $guestEmail : null,
                'guest_phone' => $userId === null ? $guestPhone : null,
                'status' => 'pending_payment',
                'subtotal_paise' => $subtotal,
                'discount_paise' => $discount,
                'shipping_paise' => $shippingCost,
                'tax_paise' => $tax,
                'total_paise' => $total,
                'coupon_id' => $coupon['id'] ?? null,
                'coupon_code_snapshot' => $coupon['code'] ?? null,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'shipping_full_name' => $shipping['full_name'],
                'shipping_phone' => $shipping['phone'],
                'shipping_line1' => $shipping['line1'],
                'shipping_line2' => $shipping['line2'] ?? null,
                'shipping_city' => $shipping['city'],
                'shipping_state' => $shipping['state'],
                'shipping_postal_code' => $shipping['postal_code'],
                'shipping_country' => $shipping['country'] ?? 'IN',
                'customer_notes' => $shipping['notes'] ?? null,
                'placed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($loyaltyRedeemedPoints > 0) {
                $db->insert('loyalty_points', [
                    'user_id' => $userId,
                    'points' => -$loyaltyRedeemedPoints,
                    'type' => 'redeemed',
                    'reference_type' => 'order',
                    'reference_id' => $orderId,
                    'created_at' => now(),
                ]);
            }

            foreach ($items as $item) {
                $lineTotal = (int) $item['price_paise'] * (int) $item['quantity'];

                $db->insert('order_items', [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'product_name_snapshot' => $item['product_name'],
                    'variant_label_snapshot' => $item['variant_label'],
                    'sku_snapshot' => $item['sku'],
                    'unit_price_paise' => $item['price_paise'],
                    'quantity' => $item['quantity'],
                    'line_total_paise' => $lineTotal,
                    'created_at' => now(),
                ]);

                $db->update(
                    'product_variants',
                    ['stock_quantity' => (int) $item['stock_quantity'] - (int) $item['quantity']],
                    'id = :id',
                    ['id' => $item['variant_id']],
                );

                $db->insert('inventory_movements', [
                    'variant_id' => $item['variant_id'],
                    'change_quantity' => -(int) $item['quantity'],
                    'reason' => 'order',
                    'reference_type' => 'order',
                    'reference_id' => $orderId,
                    'created_at' => now(),
                ]);
            }

            if ($coupon !== null) {
                $db->insert('coupon_redemptions', [
                    'coupon_id' => $coupon['id'],
                    'user_id' => $userId,
                    'order_id' => $orderId,
                    'discount_paise' => $discount,
                    'created_at' => now(),
                ]);
                $db->update('coupons', ['usage_count' => (int) $coupon['usage_count'] + 1], 'id = :id', ['id' => $coupon['id']]);
            }

            Order::addStatusHistory($orderId, 'pending_payment', 'Order placed');

            $this->cartService->clear((int) $cart['id']);

            return Order::find($orderId);
        });
    }

    public function markConfirmed(int $orderId, string $note): void
    {
        Order::updateWhere($orderId, ['status' => 'confirmed', 'payment_status' => 'paid']);
        Order::addStatusHistory($orderId, 'confirmed', $note);
        $this->onConfirmed($orderId);
    }

    public function markCodConfirmed(int $orderId): void
    {
        Order::updateWhere($orderId, ['status' => 'confirmed']);
        Order::addStatusHistory($orderId, 'confirmed', 'Cash on delivery order confirmed');
        $this->onConfirmed($orderId);
    }

    private function onConfirmed(int $orderId): void
    {
        $order = Order::find($orderId);
        if ($order === null || $order['user_id'] === null) {
            return; // guest checkout — no account to credit
        }

        $this->loyalty->awardForOrder((int) $order['user_id'], $orderId, (int) $order['total_paise']);
        $this->referrals->tryReleaseReward((int) $order['user_id'], $order);
    }
}
