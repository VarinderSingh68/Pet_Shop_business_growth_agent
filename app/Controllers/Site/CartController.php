<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\App;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Cart;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\Growth\UpsellService;

final class CartController extends Controller
{
    public function __construct(
        private readonly CartService $carts = new CartService(),
        private readonly UpsellService $upsell = new UpsellService(),
    ) {
    }

    public function index(Request $request): void
    {
        $cart = $this->carts->current($request);
        $items = Cart::items((int) $cart['id']);
        $totals = $this->carts->totals($cart);
        $crossSell = $this->upsell->forCart(array_column($items, 'product_id'));

        $this->view('site/cart/index', [
            'title' => 'Your cart',
            'cart' => $cart,
            'items' => $items,
            'totals' => $totals,
            'crossSell' => $crossSell,
        ]);
    }

    public function add(Request $request): void
    {
        $cart = $this->carts->current($request);
        $productId = (int) $request->input('product_id');
        $variantId = (int) $request->input('variant_id');
        $qty = max(1, (int) $request->input('qty', 1));

        $variant = ProductVariant::find($variantId);
        if ($variant === null || (int) $variant['product_id'] !== $productId) {
            if ($request->wantsJson()) {
                \App\Core\Response::json(['message' => "That item couldn't be added — it may no longer be available."], 422);
            }
            flash('error', "That item couldn't be added — it may no longer be available.");
            back();
        }

        $this->carts->addItem((int) $cart['id'], $productId, $variantId, $qty);

        if ($request->wantsJson()) {
            \App\Core\Response::json([
                'message' => 'Added to cart.',
                'cartCount' => Cart::itemCount((int) $cart['id']),
            ]);
        }

        flash('success', 'Added to cart.');
        $this->redirect('/cart');
    }

    public function update(Request $request): void
    {
        $cart = $this->carts->current($request);
        $itemId = (int) $request->input('item_id');
        $qty = (int) $request->input('qty', 1);

        $this->carts->updateItemQuantity((int) $cart['id'], $itemId, $qty);
        $this->redirect('/cart');
    }

    public function remove(Request $request): void
    {
        $cart = $this->carts->current($request);
        $itemId = (int) $request->input('item_id');

        $this->carts->removeItem((int) $cart['id'], $itemId);
        flash('success', 'Item removed.');
        $this->redirect('/cart');
    }

    public function applyCoupon(Request $request): void
    {
        $cart = $this->carts->current($request);
        $code = trim((string) $request->input('code', ''));

        $result = $this->carts->applyCoupon($cart, $code, App::auth()->id());

        if (!$result['ok']) {
            flash('error', $result['error']);
        } else {
            flash('success', 'Coupon applied.');
        }

        $this->redirect('/cart');
    }

    public function removeCoupon(Request $request): void
    {
        $cart = $this->carts->current($request);
        $this->carts->removeCoupon($cart);
        $this->redirect('/cart');
    }

    /**
     * One-click reorder from a replenishment reminder email. GET (not POST)
     * so it works as a plain link — safe because it only ever adds an item
     * to the clicking visitor's own cart, nothing more sensitive than that.
     */
    public function quickReorder(Request $request, string $variantId): void
    {
        $variant = ProductVariant::find((int) $variantId);
        if ($variant === null) {
            flash('error', 'That product is no longer available.');
            $this->redirect('/shop');
        }

        $cart = $this->carts->current($request);
        $this->carts->addItem((int) $cart['id'], (int) $variant['product_id'], (int) $variantId, 1);

        flash('success', 'Added to your cart.');
        $this->redirect('/cart');
    }
}
