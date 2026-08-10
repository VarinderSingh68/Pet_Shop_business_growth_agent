<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Core\Database;
use App\Core\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Models\Setting;

final class CartService
{
    private const COOKIE_NAME = 'petshop_cart';
    private const DEFAULT_FREE_SHIPPING_THRESHOLD_PAISE = 99900;
    private const DEFAULT_FLAT_SHIPPING_PAISE = 7900;
    private const DEFAULT_TAX_RATE_PERCENT = 5.0;

    /** @return array<string, mixed> */
    public function current(Request $request): array
    {
        $db = Database::instance();
        $userId = App::auth()->id();

        if ($userId !== null) {
            $cart = $db->selectOne('SELECT * FROM carts WHERE user_id = :uid ORDER BY id DESC LIMIT 1', ['uid' => $userId]);
            if ($cart !== null) {
                return $cart;
            }
            $id = Cart::create(['user_id' => $userId]);
            return Cart::find($id);
        }

        $token = $_COOKIE[self::COOKIE_NAME] ?? null;
        if (is_string($token) && $token !== '') {
            $cart = $db->selectOne('SELECT * FROM carts WHERE session_token = :t LIMIT 1', ['t' => $token]);
            if ($cart !== null) {
                return $cart;
            }
        }

        $token = bin2hex(random_bytes(24));
        setcookie(self::COOKIE_NAME, $token, [
            'expires' => time() + 60 * 60 * 24 * 30,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => (bool) config('session.secure'),
        ]);

        $id = Cart::create(['user_id' => null, 'session_token' => $token]);
        return Cart::find($id);
    }

    public function mergeGuestCartIntoUser(Request $request, int $userId): void
    {
        $token = $_COOKIE[self::COOKIE_NAME] ?? null;
        if (!is_string($token) || $token === '') {
            return;
        }

        $db = Database::instance();
        $guestCart = $db->selectOne('SELECT * FROM carts WHERE session_token = :t LIMIT 1', ['t' => $token]);
        if ($guestCart === null) {
            return;
        }

        $userCart = $db->selectOne('SELECT * FROM carts WHERE user_id = :uid ORDER BY id DESC LIMIT 1', ['uid' => $userId]);
        $userCartId = $userCart !== null ? (int) $userCart['id'] : Cart::create(['user_id' => $userId]);

        foreach (Cart::items((int) $guestCart['id']) as $item) {
            $this->addItem($userCartId, (int) $item['product_id'], (int) $item['variant_id'], (int) $item['quantity']);
        }

        Database::instance()->delete('carts', 'id = :id', ['id' => $guestCart['id']]);
        setcookie(self::COOKIE_NAME, '', ['expires' => time() - 3600, 'path' => '/']);
    }

    public function addItem(int $cartId, int $productId, int $variantId, int $quantity): void
    {
        $db = Database::instance();
        $existing = $db->selectOne(
            'SELECT * FROM cart_items WHERE cart_id = :cid AND variant_id = :vid',
            ['cid' => $cartId, 'vid' => $variantId],
        );

        if ($existing !== null) {
            CartItem::updateWhere((int) $existing['id'], ['quantity' => (int) $existing['quantity'] + $quantity]);
            return;
        }

        CartItem::create([
            'cart_id' => $cartId,
            'product_id' => $productId,
            'variant_id' => $variantId,
            'quantity' => $quantity,
        ]);
    }

    public function updateItemQuantity(int $cartId, int $cartItemId, int $quantity): void
    {
        $db = Database::instance();
        $item = $db->selectOne('SELECT * FROM cart_items WHERE id = :id AND cart_id = :cid', ['id' => $cartItemId, 'cid' => $cartId]);
        if ($item === null) {
            return;
        }

        if ($quantity <= 0) {
            $db->delete('cart_items', 'id = :id', ['id' => $cartItemId]);
            return;
        }

        CartItem::updateWhere($cartItemId, ['quantity' => $quantity]);
    }

    public function removeItem(int $cartId, int $cartItemId): void
    {
        Database::instance()->delete('cart_items', 'id = :id AND cart_id = :cid', ['id' => $cartItemId, 'cid' => $cartId]);
    }

    public function applyCoupon(array $cart, string $code, ?int $userId): array
    {
        $coupon = Coupon::findByCode($code);
        $items = Cart::items((int) $cart['id']);
        $subtotal = $this->subtotal($items);

        $error = $this->couponError($coupon, $subtotal, $userId);
        if ($error !== null) {
            return ['ok' => false, 'error' => $error];
        }

        Cart::updateWhere((int) $cart['id'], ['coupon_id' => $coupon['id']]);

        return ['ok' => true];
    }

    public function removeCoupon(array $cart): void
    {
        Cart::updateWhere((int) $cart['id'], ['coupon_id' => null]);
    }

    private function couponError(?array $coupon, int $subtotalPaise, ?int $userId): ?string
    {
        if ($coupon === null) {
            return "That coupon code doesn't exist. Double check for typos.";
        }
        if (!$coupon['is_active']) {
            return 'This coupon is no longer active.';
        }
        if ($coupon['starts_at'] !== null && strtotime((string) $coupon['starts_at']) > time()) {
            return 'This coupon isn\'t active yet.';
        }
        if ($coupon['expires_at'] !== null && strtotime((string) $coupon['expires_at']) < time()) {
            return 'This coupon has expired.';
        }
        if ($coupon['min_order_paise'] !== null && $subtotalPaise < (int) $coupon['min_order_paise']) {
            return 'Add ' . money((int) $coupon['min_order_paise'] - $subtotalPaise) . ' more to use this coupon.';
        }
        if ($coupon['usage_limit'] !== null && (int) $coupon['usage_count'] >= (int) $coupon['usage_limit']) {
            return 'This coupon has reached its usage limit.';
        }
        if ($userId !== null && $coupon['usage_limit_per_customer'] !== null) {
            $used = Coupon::customerRedemptionCount((int) $coupon['id'], $userId);
            if ($used >= (int) $coupon['usage_limit_per_customer']) {
                return "You've already used this coupon the maximum number of times.";
            }
        }

        return null;
    }

    /** @param array<int, array<string, mixed>> $items */
    public function subtotal(array $items): int
    {
        return array_sum(array_map(
            static fn (array $i) => (int) $i['price_paise'] * (int) $i['quantity'],
            $items,
        ));
    }

    public function discountFor(?array $coupon, int $subtotalPaise): int
    {
        if ($coupon === null) {
            return 0;
        }

        if ($coupon['type'] === 'fixed') {
            return min((int) $coupon['value'], $subtotalPaise);
        }

        $discount = (int) round($subtotalPaise * ((int) $coupon['value'] / 100));
        if ($coupon['max_discount_paise'] !== null) {
            $discount = min($discount, (int) $coupon['max_discount_paise']);
        }

        return min($discount, $subtotalPaise);
    }

    public function shippingFor(int $taxableSubtotalPaise): int
    {
        return $taxableSubtotalPaise >= $this->freeShippingThresholdPaise() ? 0 : $this->flatShippingPaise();
    }

    public function freeShippingRemaining(int $taxableSubtotalPaise): int
    {
        return max(0, $this->freeShippingThresholdPaise() - $taxableSubtotalPaise);
    }

    public function taxRatePercent(): float
    {
        return (float) Setting::get('tax_rate_percent', self::DEFAULT_TAX_RATE_PERCENT);
    }

    private function freeShippingThresholdPaise(): int
    {
        return (int) round(((float) Setting::get('shipping_free_threshold', self::DEFAULT_FREE_SHIPPING_THRESHOLD_PAISE / 100)) * 100);
    }

    private function flatShippingPaise(): int
    {
        return (int) round(((float) Setting::get('shipping_flat_rate', self::DEFAULT_FLAT_SHIPPING_PAISE / 100)) * 100);
    }

    /**
     * @return array{subtotal: int, discount: int, shipping: int, tax: int, total: int, coupon: ?array, freeShippingRemaining: int}
     */
    public function totals(array $cart): array
    {
        $items = Cart::items((int) $cart['id']);
        $subtotal = $this->subtotal($items);

        $coupon = $cart['coupon_id'] !== null
            ? Database::instance()->selectOne('SELECT * FROM coupons WHERE id = :id', ['id' => $cart['coupon_id']])
            : null;

        $discount = $this->discountFor($coupon, $subtotal);
        $taxable = max(0, $subtotal - $discount);
        $shipping = $this->shippingFor($taxable);
        $tax = (int) round($taxable * ($this->taxRatePercent() / 100));
        $total = $taxable + $shipping + $tax;
        $freeShippingRemaining = $this->freeShippingRemaining($taxable);

        return compact('subtotal', 'discount', 'shipping', 'tax', 'total', 'coupon', 'freeShippingRemaining');
    }

    public function clear(int $cartId): void
    {
        Database::instance()->delete('cart_items', 'cart_id = :id', ['id' => $cartId]);
        Cart::updateWhere($cartId, ['coupon_id' => null]);
    }

    /** Validates every line still has enough stock; returns list of problem messages. */
    public function validateStock(array $cart): array
    {
        $problems = [];
        foreach (Cart::items((int) $cart['id']) as $item) {
            if ((int) $item['quantity'] > (int) $item['stock_quantity']) {
                $problems[] = $item['product_name'] . ' (' . $item['variant_label'] . ') only has ' . $item['stock_quantity'] . ' left in stock.';
            }
        }
        return $problems;
    }
}
