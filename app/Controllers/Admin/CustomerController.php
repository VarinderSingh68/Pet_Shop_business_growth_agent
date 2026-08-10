<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Models\Address;
use App\Models\Order;
use App\Models\Pet;
use App\Models\User;

final class CustomerController extends Controller
{
    public function index(Request $request): void
    {
        $db = Database::instance();
        $search = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 30;

        $where = ["r.slug = 'customer'", 'u.deleted_at IS NULL'];
        $bindings = [];
        if ($search !== '') {
            $where[] = '(u.name LIKE :q OR u.email LIKE :q OR u.phone LIKE :q)';
            $bindings['q'] = '%' . $search . '%';
        }
        $whereSql = implode(' AND ', $where);

        $total = (int) ($db->selectOne(
            "SELECT COUNT(*) c FROM users u JOIN roles r ON r.id = u.role_id WHERE {$whereSql}",
            $bindings,
        )['c'] ?? 0);

        $customers = $db->select(
            "SELECT u.*,
                    (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id AND o.deleted_at IS NULL) AS order_count,
                    (SELECT COALESCE(SUM(total_paise),0) FROM orders o WHERE o.user_id = u.id AND o.deleted_at IS NULL AND o.status NOT IN ('cancelled')) AS lifetime_value_paise
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE {$whereSql}
             ORDER BY u.created_at DESC
             LIMIT {$perPage} OFFSET " . (($page - 1) * $perPage),
            $bindings,
        );

        $this->view('admin/customers/index', [
            'title' => 'Customers',
            'customers' => $customers,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'search' => $search,
        ]);
    }

    public function show(Request $request, string $id): void
    {
        $customer = User::find((int) $id);
        if ($customer === null) {
            abort(404);
        }

        $orders = Order::forUser((int) $id);
        $lifetimeValue = array_sum(array_map(
            static fn (array $o) => $o['status'] !== 'cancelled' ? (int) $o['total_paise'] : 0,
            $orders,
        ));

        $this->view('admin/customers/show', [
            'title' => $customer['name'],
            'customer' => $customer,
            'orders' => $orders,
            'pets' => Pet::forUser((int) $id),
            'addresses' => Address::forUser((int) $id),
            'lifetimeValue' => $lifetimeValue,
            'activity' => Database::instance()->select(
                'SELECT * FROM activity_logs WHERE user_id = :id ORDER BY id DESC LIMIT 20',
                ['id' => $id],
            ),
        ]);
    }

    /**
     * Logs into the storefront as this customer for support purposes.
     * Loudly audited: activity_logs entry plus a persistent banner (see
     * layouts/site.php) shown for the rest of the impersonated session.
     */
    public function impersonate(Request $request, string $id): void
    {
        $customer = User::find((int) $id);
        if ($customer === null) {
            abort(404);
        }

        $adminId = App::auth()->id();

        Database::instance()->insert('activity_logs', [
            'user_id' => $adminId,
            'action' => 'customer.impersonate',
            'subject_type' => 'user',
            'subject_id' => $id,
            'description' => 'Started impersonating ' . $customer['name'] . ' (' . $customer['email'] . ')',
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        Session::put('_impersonating_admin_id', $adminId);
        App::auth()->login($customer);

        $this->redirect('/account');
    }

    public function stopImpersonate(Request $request): void
    {
        $adminId = Session::get('_impersonating_admin_id');
        if ($adminId === null) {
            $this->redirect('/');
        }

        $admin = User::find((int) $adminId);
        Session::forget('_impersonating_admin_id');

        if ($admin !== null) {
            Database::instance()->insert('activity_logs', [
                'user_id' => $admin['id'],
                'action' => 'customer.impersonate.end',
                'description' => 'Stopped impersonating a customer',
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);
            App::auth()->login($admin);
        }

        $this->redirect('/admin/customers');
    }
}
