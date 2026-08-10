<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\App;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Address;
use App\Models\Cart;
use App\Services\CartService;
use App\Services\Growth\LoyaltyService;
use App\Services\Growth\UpsellService;
use App\Services\OrderService;
use App\Services\PaymentService;

final class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $carts = new CartService(),
        private readonly OrderService $orders = new OrderService(),
        private readonly PaymentService $payments = new PaymentService(),
        private readonly LoyaltyService $loyalty = new LoyaltyService(),
        private readonly UpsellService $upsell = new UpsellService(),
    ) {
    }

    public function show(Request $request): void
    {
        $cart = $this->carts->current($request);
        $items = Cart::items((int) $cart['id']);

        if ($items === []) {
            flash('error', 'Your cart is empty.');
            $this->redirect('/cart');
        }

        $totals = $this->carts->totals($cart);
        $user = App::auth()->user();
        $addresses = $user !== null ? Address::forUser((int) $user['id']) : [];
        $loyaltyBalance = $user !== null ? $this->loyalty->balance((int) $user['id']) : 0;

        $this->view('site/checkout/index', [
            'title' => 'Checkout',
            'cart' => $cart,
            'items' => $items,
            'totals' => $totals,
            'user' => $user,
            'addresses' => $addresses,
            'razorpayConfigured' => $this->payments->isRazorpayConfigured(),
            'loyaltyBalance' => $loyaltyBalance,
        ]);
    }

    public function place(Request $request): void
    {
        $cart = $this->carts->current($request);
        $user = App::auth()->user();

        $rules = [
            'full_name' => 'required|max:150',
            'phone' => 'required|max:20',
            'line1' => 'required|max:200',
            'city' => 'required|max:100',
            'state' => 'required|max:100',
            'postal_code' => 'required|max:12',
            'payment_method' => 'required|in:razorpay,cod',
        ];
        if ($user === null) {
            $rules['guest_email'] = 'required|email|max:191';
        }

        $data = $request->only([
            'full_name', 'phone', 'line1', 'line2', 'city', 'state', 'postal_code',
            'payment_method', 'guest_email', 'notes', 'redeem_points',
        ]);

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            flash('error', $validator->firstError() ?? 'Please check the delivery details and try again.');
            back();
        }

        $issues = $this->orders->stockIssues($cart);
        if ($issues !== []) {
            flash('error', implode(' ', $issues));
            $this->redirect('/cart');
        }

        $loyaltyPointsToRedeem = ($user !== null && !empty($data['redeem_points']))
            ? $this->loyalty->balance((int) $user['id'])
            : 0;

        try {
            $order = $this->orders->createFromCart(
                $cart,
                $data,
                (string) $data['payment_method'],
                $user['id'] ?? null,
                $user === null ? (string) $data['guest_email'] : null,
                (string) $data['phone'],
                $loyaltyPointsToRedeem,
            );
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            $this->redirect('/cart');
        }

        if ($order['payment_method'] === 'cod') {
            $this->payments->recordCod($order);
            $this->redirect('/checkout/confirmation/' . $order['order_number']);
        }

        // Razorpay: land on a bridge page that opens Checkout.js using the order we just created.
        $this->redirect('/checkout/pay/' . $order['order_number']);
    }

    public function pay(Request $request, string $orderNumber): void
    {
        $order = \App\Models\Order::findByNumber($orderNumber);
        if ($order === null) {
            abort(404);
        }

        if (!$this->payments->isRazorpayConfigured()) {
            flash('error', 'Online payment isn\'t available right now. Please choose cash on delivery.');
            $this->redirect('/cart');
        }

        $rzp = $this->payments->createRazorpayOrder($order);

        $this->view('site/checkout/pay', [
            'title' => 'Complete payment',
            'order' => $order,
            'rzp' => $rzp,
        ]);
    }

    public function confirmation(Request $request, string $orderNumber): void
    {
        $order = \App\Models\Order::findByNumber($orderNumber);
        if ($order === null) {
            abort(404);
        }

        $items = \App\Models\Order::items((int) $order['id']);
        $crossSell = $this->upsellFor($items);

        $this->view('site/orders/confirmation', [
            'title' => 'Order confirmed',
            'order' => $order,
            'items' => $items,
            'crossSell' => $crossSell,
        ]);
    }

    private function upsellFor(array $orderItems): array
    {
        $productIds = array_filter(array_column($orderItems, 'product_id'));
        if ($productIds === []) {
            return [];
        }
        return $this->upsell->forCart($productIds);
    }
}
