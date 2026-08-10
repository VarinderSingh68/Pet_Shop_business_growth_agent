<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Services\Growth\CopilotService;

final class DashboardController extends Controller
{
    public function __construct(private readonly CopilotService $copilot = new CopilotService())
    {
    }

    public function index(Request $request): void
    {
        $db = Database::instance();

        $revenueToday = $this->revenueSince($db, 'CURDATE()');
        $revenueWeek = $this->revenueSince($db, 'DATE_SUB(CURDATE(), INTERVAL 7 DAY)');
        $revenueMonth = $this->revenueSince($db, 'DATE_SUB(CURDATE(), INTERVAL 30 DAY)');
        $revenuePrevMonth = (int) ($db->selectOne(
            "SELECT COALESCE(SUM(total_paise),0) AS r FROM orders
             WHERE status NOT IN ('cancelled') AND placed_at >= DATE_SUB(CURDATE(), INTERVAL 60 DAY) AND placed_at < DATE_SUB(CURDATE(), INTERVAL 30 DAY)",
        )['r'] ?? 0);

        $ordersByStatus = $db->select(
            "SELECT status, COUNT(*) AS c FROM orders WHERE deleted_at IS NULL GROUP BY status",
        );

        $lowStock = $db->select(
            "SELECT v.id, v.label, v.sku, v.stock_quantity, p.id AS product_id, p.name AS product_name, p.slug AS product_slug
             FROM product_variants v JOIN products p ON p.id = v.product_id
             WHERE v.deleted_at IS NULL AND p.deleted_at IS NULL AND v.stock_quantity <= v.low_stock_threshold
             ORDER BY v.stock_quantity ASC LIMIT 8",
        );

        $todayAppointments = $db->select(
            "SELECT a.*, s.name AS service_name, sm.name AS staff_name, sl.start_at
             FROM appointments a
             JOIN services s ON s.id = a.service_id
             JOIN staff_members sm ON sm.id = a.staff_id
             JOIN service_slots sl ON sl.id = a.slot_id
             WHERE DATE(sl.start_at) = CURDATE() AND a.status IN ('booked','confirmed')
             ORDER BY sl.start_at ASC",
        );

        $newCustomers7d = (int) ($db->selectOne(
            "SELECT COUNT(*) c FROM users u JOIN roles r ON r.id = u.role_id
             WHERE r.slug = 'customer' AND u.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
        )['c'] ?? 0);

        $topProducts = $db->select(
            "SELECT p.name, p.slug, SUM(oi.quantity) AS units_sold, SUM(oi.line_total_paise) AS revenue_paise
             FROM order_items oi
             JOIN orders o ON o.id = oi.order_id
             JOIN products p ON p.id = oi.product_id
             WHERE o.status NOT IN ('cancelled') AND o.placed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY p.id, p.name, p.slug
             ORDER BY revenue_paise DESC
             LIMIT 5",
        );

        $recentActivity = $db->select(
            "SELECT a.*, u.name AS user_name FROM activity_logs a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.id DESC LIMIT 10",
        );

        $funnel = $this->conversionFunnel($db);
        $opportunities = $this->copilot->rankedOpportunities();
        $revenueSparkline = $this->dailyRevenueSeries($db, 14);

        $this->view('admin/dashboard', [
            'title' => 'Dashboard',
            'revenueToday' => $revenueToday,
            'revenueWeek' => $revenueWeek,
            'revenueMonth' => $revenueMonth,
            'revenueDeltaPercent' => $revenuePrevMonth > 0 ? round((($revenueMonth - $revenuePrevMonth) / $revenuePrevMonth) * 100, 1) : null,
            'ordersByStatus' => $ordersByStatus,
            'lowStock' => $lowStock,
            'todayAppointments' => $todayAppointments,
            'newCustomers7d' => $newCustomers7d,
            'topProducts' => $topProducts,
            'recentActivity' => $recentActivity,
            'funnel' => $funnel,
            'opportunities' => $opportunities,
            'revenueSparkline' => $revenueSparkline,
        ]);
    }

    /** @return array<int, int> daily revenue in paise, oldest to newest, zero-filled */
    private function dailyRevenueSeries(Database $db, int $days): array
    {
        $rows = $db->select(
            "SELECT DATE(placed_at) AS d, COALESCE(SUM(total_paise),0) AS r
             FROM orders WHERE status NOT IN ('cancelled') AND placed_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
             GROUP BY DATE(placed_at)",
            ['days' => $days - 1],
        );
        $byDate = array_column($rows, 'r', 'd');

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = (new \DateTimeImmutable("-{$i} days"))->format('Y-m-d');
            $series[] = (int) ($byDate[$date] ?? 0);
        }

        return $series;
    }

    private function revenueSince(Database $db, string $sqlDateExpr): int
    {
        return (int) ($db->selectOne(
            "SELECT COALESCE(SUM(total_paise),0) AS r FROM orders WHERE status NOT IN ('cancelled') AND placed_at >= {$sqlDateExpr}",
        )['r'] ?? 0);
    }

    /** @return array{visitors_proxy: int, carts: int, orders: int} */
    private function conversionFunnel(Database $db): array
    {
        $carts = (int) ($db->selectOne(
            "SELECT COUNT(DISTINCT cart_id) c FROM cart_items ci WHERE ci.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
        )['c'] ?? 0);

        $orders = (int) ($db->selectOne(
            "SELECT COUNT(*) c FROM orders WHERE placed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND status != 'cancelled'",
        )['c'] ?? 0);

        return ['carts' => $carts, 'orders' => $orders];
    }
}
