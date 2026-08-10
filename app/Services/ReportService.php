<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Read-only reporting queries. Kept separate from the models they query
 * because reports aggregate across several tables and don't belong to any
 * single one of them.
 */
final class ReportService
{
    /** @return array<int, array{date: string, order_count: int, revenue_paise: int}> */
    public function salesByDay(string $from, string $to): array
    {
        return Database::instance()->select(
            "SELECT DATE(placed_at) AS date, COUNT(*) AS order_count, SUM(total_paise) AS revenue_paise
             FROM orders WHERE placed_at BETWEEN :from AND :to AND status != 'cancelled'
             GROUP BY DATE(placed_at) ORDER BY date DESC",
            ['from' => $from, 'to' => $to . ' 23:59:59'],
        );
    }

    /** @return array<int, array{product_name: string, units_sold: int, revenue_paise: int}> */
    public function salesByProduct(string $from, string $to): array
    {
        return Database::instance()->select(
            "SELECT p.name AS product_name, SUM(oi.quantity) AS units_sold, SUM(oi.line_total_paise) AS revenue_paise
             FROM order_items oi
             JOIN orders o ON o.id = oi.order_id
             JOIN products p ON p.id = oi.product_id
             WHERE o.placed_at BETWEEN :from AND :to AND o.status != 'cancelled'
             GROUP BY p.id, p.name ORDER BY revenue_paise DESC",
            ['from' => $from, 'to' => $to . ' 23:59:59'],
        );
    }

    /** @return array<int, array{category_name: string, units_sold: int, revenue_paise: int}> */
    public function salesByCategory(string $from, string $to): array
    {
        return Database::instance()->select(
            "SELECT c.name AS category_name, SUM(oi.quantity) AS units_sold, SUM(oi.line_total_paise) AS revenue_paise
             FROM order_items oi
             JOIN orders o ON o.id = oi.order_id
             JOIN products p ON p.id = oi.product_id
             JOIN categories c ON c.id = p.category_id
             WHERE o.placed_at BETWEEN :from AND :to AND o.status != 'cancelled'
             GROUP BY c.id, c.name ORDER BY revenue_paise DESC",
            ['from' => $from, 'to' => $to . ' 23:59:59'],
        );
    }

    /** @return array{subtotal_paise: int, tax_paise: int, shipping_paise: int, discount_paise: int, total_paise: int, order_count: int} */
    public function financialSummary(string $from, string $to): array
    {
        $row = Database::instance()->selectOne(
            "SELECT COALESCE(SUM(subtotal_paise),0) AS subtotal_paise, COALESCE(SUM(tax_paise),0) AS tax_paise,
                    COALESCE(SUM(shipping_paise),0) AS shipping_paise, COALESCE(SUM(discount_paise),0) AS discount_paise,
                    COALESCE(SUM(total_paise),0) AS total_paise, COUNT(*) AS order_count
             FROM orders WHERE placed_at BETWEEN :from AND :to AND status != 'cancelled'",
            ['from' => $from, 'to' => $to . ' 23:59:59'],
        );

        return [
            'subtotal_paise' => (int) $row['subtotal_paise'],
            'tax_paise' => (int) $row['tax_paise'],
            'shipping_paise' => (int) $row['shipping_paise'],
            'discount_paise' => (int) $row['discount_paise'],
            'total_paise' => (int) $row['total_paise'],
            'order_count' => (int) $row['order_count'],
        ];
    }

    /** @return array<int, array{code: string, redemptions: int, total_discount_paise: int}> */
    public function couponCost(string $from, string $to): array
    {
        return Database::instance()->select(
            "SELECT coupon_code_snapshot AS code, COUNT(*) AS redemptions, SUM(discount_paise) AS total_discount_paise
             FROM orders
             WHERE coupon_code_snapshot IS NOT NULL AND placed_at BETWEEN :from AND :to AND status != 'cancelled'
             GROUP BY coupon_code_snapshot ORDER BY total_discount_paise DESC",
            ['from' => $from, 'to' => $to . ' 23:59:59'],
        );
    }

    /** @return array<int, array{cohort_month: string, customer_count: int, returning_count: int}> */
    public function customerCohortsWithRetention(): array
    {
        $cohorts = Database::instance()->select(
            "SELECT DATE_FORMAT(MIN(o.placed_at), '%Y-%m') AS cohort_month, o.user_id,
                    COUNT(*) AS order_count
             FROM orders o
             WHERE o.user_id IS NOT NULL AND o.status != 'cancelled'
             GROUP BY o.user_id",
        );

        $byMonth = [];
        foreach ($cohorts as $c) {
            $month = $c['cohort_month'];
            $byMonth[$month]['customer_count'] = ($byMonth[$month]['customer_count'] ?? 0) + 1;
            $byMonth[$month]['returning_count'] = ($byMonth[$month]['returning_count'] ?? 0) + ((int) $c['order_count'] > 1 ? 1 : 0);
        }

        ksort($byMonth);

        $result = [];
        foreach ($byMonth as $month => $data) {
            $result[] = ['cohort_month' => $month, 'customer_count' => $data['customer_count'], 'returning_count' => $data['returning_count']];
        }

        return $result;
    }

    /** @return array<int, array{service_name: string, total_slots: int, booked_slots: int, utilization_percent: float}> */
    public function serviceUtilization(string $from, string $to): array
    {
        return Database::instance()->select(
            "SELECT s.name AS service_name,
                    COUNT(sl.id) AS total_slots,
                    SUM(sl.is_booked) AS booked_slots,
                    ROUND(SUM(sl.is_booked) / COUNT(sl.id) * 100, 1) AS utilization_percent
             FROM service_slots sl
             JOIN services s ON s.id = sl.service_id
             WHERE DATE(sl.start_at) BETWEEN :from AND :to
             GROUP BY s.id, s.name ORDER BY utilization_percent DESC",
            ['from' => $from, 'to' => $to],
        );
    }

    /** @return array<int, array{product_name: string, variant_label: string, units_sold_30d: int, current_stock: int, turnover_ratio: ?float}> */
    public function inventoryTurnover(): array
    {
        return Database::instance()->select(
            "SELECT p.name AS product_name, v.label AS variant_label, v.stock_quantity AS current_stock,
                    COALESCE((SELECT SUM(oi.quantity) FROM order_items oi JOIN orders o ON o.id = oi.order_id
                              WHERE oi.variant_id = v.id AND o.placed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND o.status != 'cancelled'), 0) AS units_sold_30d
             FROM product_variants v
             JOIN products p ON p.id = v.product_id
             WHERE v.deleted_at IS NULL AND p.deleted_at IS NULL
             ORDER BY units_sold_30d DESC
             LIMIT 100",
        );
    }

    public function toCsv(array $rows): string
    {
        if ($rows === []) {
            return '';
        }

        // $escape explicit: PHP 8.5 deprecates the implicit "\\" default.
        // Empty string opts into RFC 4180 quote-doubling instead of backslash-escaping.
        $out = fopen('php://temp', 'r+');
        fputcsv($out, array_keys($rows[0]), ',', '"', '');
        foreach ($rows as $row) {
            fputcsv($out, $row, ',', '"', '');
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return (string) $csv;
    }
}
