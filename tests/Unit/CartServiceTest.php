<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\CartService;
use PHPUnit\Framework\TestCase;

final class CartServiceTest extends TestCase
{
    private CartService $cart;

    protected function setUp(): void
    {
        $this->cart = new CartService();
    }

    public function testSubtotalSumsPriceTimesQuantity(): void
    {
        $items = [
            ['price_paise' => 10000, 'quantity' => 2],
            ['price_paise' => 2500, 'quantity' => 3],
        ];

        $this->assertSame(27500, $this->cart->subtotal($items));
    }

    public function testDiscountForReturnsZeroWithoutCoupon(): void
    {
        $this->assertSame(0, $this->cart->discountFor(null, 10000));
    }

    public function testDiscountForFixedCouponIsCappedAtSubtotal(): void
    {
        $coupon = ['type' => 'fixed', 'value' => 50000, 'max_discount_paise' => null];

        $this->assertSame(10000, $this->cart->discountFor($coupon, 10000));
    }

    public function testDiscountForPercentCouponComputesShareOfSubtotal(): void
    {
        $coupon = ['type' => 'percent', 'value' => 10, 'max_discount_paise' => null];

        $this->assertSame(1000, $this->cart->discountFor($coupon, 10000));
    }

    public function testDiscountForPercentCouponRespectsMaxDiscountCap(): void
    {
        $coupon = ['type' => 'percent', 'value' => 50, 'max_discount_paise' => 2000];

        $this->assertSame(2000, $this->cart->discountFor($coupon, 10000));
    }
}
