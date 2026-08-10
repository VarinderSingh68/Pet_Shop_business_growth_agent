<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

/**
 * Read-only order export — for an external fulfillment/accounting
 * integration. Requires a Bearer API token (see /admin/dev/api-tokens).
 */
final class OrdersController extends Controller
{
    public function index(Request $request): void
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 25;

        $orders = Database::instance()->select(
            "SELECT id, order_number, status, payment_status, total_paise, currency, placed_at
             FROM orders WHERE deleted_at IS NULL
             ORDER BY id DESC
             LIMIT {$perPage} OFFSET " . (($page - 1) * $perPage),
        );

        $this->json(['data' => $orders, 'page' => $page, 'per_page' => $perPage]);
    }
}
