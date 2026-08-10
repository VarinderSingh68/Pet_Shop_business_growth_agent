<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\App;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Order;
use App\Services\InvoiceService;

final class OrderController extends Controller
{
    public function __construct(private readonly InvoiceService $invoices = new InvoiceService())
    {
    }

    public function track(Request $request, string $orderNumber): void
    {
        $order = Order::findByNumber($orderNumber);
        if ($order === null) {
            abort(404);
        }

        // Owners can view their own order; guests need the email they checked out with.
        $auth = App::auth();
        if ($order['user_id'] !== null) {
            if ($auth->id() !== (int) $order['user_id']) {
                abort(403, 'Sign in with the account that placed this order to track it.');
            }
        } else {
            $email = (string) $request->query('email', '');
            if (strtolower($email) !== strtolower((string) $order['guest_email'])) {
                $this->view('site/orders/track-lookup', ['title' => 'Track your order', 'orderNumber' => $orderNumber, 'error' => $email !== '' ? 'That email doesn\'t match this order.' : null]);
                return;
            }
        }

        $items = Order::items((int) $order['id']);
        $history = Order::statusHistory((int) $order['id']);

        $this->view('site/orders/track', [
            'title' => 'Order ' . $order['order_number'],
            'order' => $order,
            'items' => $items,
            'history' => $history,
            'invoiceUrl' => $this->invoices->signedUrl($order),
        ]);
    }

    public function invoice(Request $request, string $id): void
    {
        $orderId = (int) $id;
        $expires = (int) $request->query('expires', 0);
        $signature = (string) $request->query('sig', '');

        $auth = App::auth();
        $order = Order::find($orderId);
        if ($order === null) {
            abort(404);
        }

        $authorized = ($order['user_id'] !== null && $auth->id() === (int) $order['user_id'])
            || $this->invoices->verify($orderId, $expires, $signature);

        if (!$authorized) {
            abort(403, 'This invoice link is invalid or has expired.');
        }

        $pdf = $this->invoices->renderPdf($order);

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="invoice-' . $order['order_number'] . '.pdf"');
        echo $pdf;
        exit;
    }
}
