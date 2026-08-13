<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

final class ActivityController extends Controller
{
    public function index(Request $request): void
    {
        $db = Database::instance();
        $search = trim((string) $request->query('q', ''));
        $userId = (int) $request->query('user_id', 0);
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 30;

        $where = ['1=1'];
        $bindings = [];
        if ($search !== '') {
            $where[] = '(a.action LIKE :q OR a.description LIKE :q)';
            $bindings['q'] = '%' . $search . '%';
        }
        if ($userId > 0) {
            $where[] = 'a.user_id = :user_id';
            $bindings['user_id'] = $userId;
        }
        $whereSql = implode(' AND ', $where);

        $total = (int) ($db->selectOne("SELECT COUNT(*) c FROM activity_logs a WHERE {$whereSql}", $bindings)['c'] ?? 0);

        $entries = $db->select(
            "SELECT a.*, u.name AS user_name FROM activity_logs a LEFT JOIN users u ON u.id = a.user_id
             WHERE {$whereSql} ORDER BY a.id DESC LIMIT {$perPage} OFFSET " . (($page - 1) * $perPage),
            $bindings,
        );

        $this->view('admin/activity/index', [
            'title' => 'Activity log',
            'entries' => $entries,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'search' => $search,
            'userId' => $userId,
            'staffUsers' => $this->staffUsers(),
        ]);
    }

    /** @return array<int, array<string, mixed>> staff/admin accounts, for the filter dropdown */
    private function staffUsers(): array
    {
        return Database::instance()->select(
            "SELECT u.id, u.name FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE r.slug IN ('owner', 'manager', 'staff', 'developer') AND u.deleted_at IS NULL
             ORDER BY u.name ASC",
        );
    }
}
