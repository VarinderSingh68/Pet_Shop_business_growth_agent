<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\Order;

final class OrderController extends Controller
{
    private const STATUSES = ['pending_payment', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];

    public function index(Request $request): void
    {
        $db = Database::instance();
        $status = (string) $request->query('status', '');
        $search = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 25;

        $where = ['o.deleted_at IS NULL'];
        $bindings = [];
        if ($status !== '') {
            $where[] = 'o.status = :status';
            $bindings['status'] = $status;
        }
        if ($search !== '') {
            $where[] = '(o.order_number LIKE :q OR o.guest_email LIKE :q OR o.shipping_full_name LIKE :q)';
            $bindings['q'] = '%' . $search . '%';
        }
        $whereSql = implode(' AND ', $where);

        $total = (int) ($db->selectOne("SELECT COUNT(*) c FROM orders o WHERE {$whereSql}", $bindings)['c'] ?? 0);

        $orders = $db->select(
            "SELECT o.*, u.name AS customer_name FROM orders o LEFT JOIN users u ON u.id = o.user_id
             WHERE {$whereSql} ORDER BY o.created_at DESC LIMIT {$perPage} OFFSET " . (($page - 1) * $perPage),
            $bindings,
        );

        $this->view('admin/orders/index', [
            'title' => 'Orders',
            'orders' => $orders,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'status' => $status,
            'search' => $search,
            'statuses' => self::STATUSES,
        ]);
    }

    public function show(Request $request, string $id): void
    {
        $order = Order::find((int) $id);
        if ($order === null) {
            abort(404);
        }

        $db = Database::instance();
        $customer = $order['user_id'] !== null ? $db->selectOne('SELECT * FROM users WHERE id = :id', ['id' => $order['user_id']]) : null;
        $payments = $db->select('SELECT * FROM payments WHERE order_id = :id ORDER BY id DESC', ['id' => $id]);
        $refunds = $db->select('SELECT * FROM refunds WHERE order_id = :id ORDER BY id DESC', ['id' => $id]);

        $this->view('admin/orders/show', [
            'title' => 'Order ' . $order['order_number'],
            'order' => $order,
            'customer' => $customer,
            'items' => Order::items((int) $id),
            'history' => Order::statusHistory((int) $id),
            'payments' => $payments,
            'refunds' => $refunds,
            'statuses' => self::STATUSES,
        ]);
    }

    public function updateStatus(Request $request, string $id): void
    {
        $status = (string) $request->input('status');
        $note = (string) $request->input('note', '');

        if (!in_array($status, self::STATUSES, true)) {
            flash('error', 'Invalid status.');
            back();
        }

        Order::updateWhere((int) $id, ['status' => $status]);
        Order::addStatusHistory((int) $id, $status, $note !== '' ? $note : null, App::auth()->id());

        flash('success', 'Order status updated.');
        $this->redirect('/admin/orders/' . $id);
    }

    public function addNote(Request $request, string $id): void
    {
        $note = (string) $request->input('internal_notes', '');
        Order::updateWhere((int) $id, ['internal_notes' => $note]);
        flash('success', 'Note saved.');
        $this->redirect('/admin/orders/' . $id);
    }

    public function refund(Request $request, string $id): void
    {
        $order = Order::find((int) $id);
        if ($order === null) {
            abort(404);
        }

        $amount = (int) round(((float) $request->input('amount', 0)) * 100);
        $reason = (string) $request->input('reason', '');

        if ($amount <= 0 || $amount > (int) $order['total_paise']) {
            flash('error', 'Enter a refund amount up to the order total.');
            $this->redirect('/admin/orders/' . $id);
        }

        $db = Database::instance();
        $payment = $db->selectOne('SELECT * FROM payments WHERE order_id = :id ORDER BY id DESC LIMIT 1', ['id' => $id]);

        $db->insert('refunds', [
            'order_id' => $id,
            'payment_id' => $payment !== null ? $payment['id'] : null,
            'amount_paise' => $amount,
            'reason' => $reason !== '' ? $reason : null,
            'status' => 'processed',
            'processed_by_user_id' => App::auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $newPaymentStatus = $amount >= (int) $order['total_paise'] ? 'refunded' : 'partially_refunded';
        Order::updateWhere((int) $id, ['payment_status' => $newPaymentStatus]);
        Order::addStatusHistory((int) $id, (string) $order['status'], 'Refund issued: ' . money($amount) . ($reason !== '' ? " ({$reason})" : ''), App::auth()->id());

        flash('success', 'Refund recorded.');
        $this->redirect('/admin/orders/' . $id);
    }

    public function invoice(Request $request, string $id): void
    {
        $order = Order::find((int) $id);
        if ($order === null) {
            abort(404);
        }

        $pdf = (new \App\Services\InvoiceService())->renderPdf($order);
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="invoice-' . $order['order_number'] . '.pdf"');
        echo $pdf;
        exit;
    }
}
