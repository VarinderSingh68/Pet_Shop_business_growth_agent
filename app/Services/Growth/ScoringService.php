<?php

declare(strict_types=1);

namespace App\Services\Growth;

use App\Core\Database;
use App\Models\CustomerScore;

/**
 * Nightly RFM (Recency/Frequency/Monetary) scoring, plus a churn-risk flag
 * and predicted next-order date derived from each customer's OWN average
 * order interval — not a fixed "90 days since last order" rule, because a
 * customer who orders every 10 days going quiet for 20 is a very different
 * signal than one who orders every 60 days going quiet for 20.
 */
final class ScoringService
{
    public function scoreAll(): int
    {
        $db = Database::instance();

        $customers = $db->select(
            "SELECT u.id FROM users u JOIN roles r ON r.id = u.role_id
             WHERE r.slug = 'customer' AND u.deleted_at IS NULL",
        );

        $scored = 0;
        foreach ($customers as $customer) {
            if ($this->scoreOne($db, (int) $customer['id'])) {
                $scored++;
            }
        }

        return $scored;
    }

    private function scoreOne(Database $db, int $userId): bool
    {
        $orders = $db->select(
            "SELECT placed_at, total_paise FROM orders
             WHERE user_id = :uid AND status != 'cancelled' AND placed_at IS NOT NULL
             ORDER BY placed_at ASC",
            ['uid' => $userId],
        );

        if ($orders === []) {
            return false;
        }

        $dates = array_map(static fn (array $o) => new \DateTimeImmutable((string) $o['placed_at']), $orders);
        $now = new \DateTimeImmutable('now');
        $lastOrderDate = end($dates);
        $recencyDays = $now->diff($lastOrderDate)->days;

        $yearAgo = $now->modify('-365 days');
        $recentOrders = array_filter($orders, static fn (array $o) => new \DateTimeImmutable((string) $o['placed_at']) >= $yearAgo);
        $frequency = count($recentOrders);
        $monetary = array_sum(array_map(static fn (array $o) => (int) $o['total_paise'], $recentOrders));

        $avgIntervalDays = null;
        if (count($dates) >= 2) {
            $intervals = [];
            for ($i = 1; $i < count($dates); $i++) {
                $intervals[] = $dates[$i]->diff($dates[$i - 1])->days;
            }
            $avgIntervalDays = (int) round(array_sum($intervals) / count($intervals));
        }

        $predictedNextOrderDate = $avgIntervalDays !== null
            ? $lastOrderDate->modify("+{$avgIntervalDays} days")->format('Y-m-d')
            : null;

        $churnRisk = $this->churnRisk($recencyDays, $avgIntervalDays);

        CustomerScore::upsert($userId, [
            'recency_days' => $recencyDays,
            'frequency_count' => $frequency,
            'monetary_paise' => $monetary,
            'recency_score' => $this->recencyScore($recencyDays),
            'frequency_score' => $this->frequencyScore($frequency),
            'monetary_score' => $this->monetaryScore($monetary),
            'rfm_total' => $this->recencyScore($recencyDays) + $this->frequencyScore($frequency) + $this->monetaryScore($monetary),
            'avg_order_interval_days' => $avgIntervalDays,
            'predicted_next_order_date' => $predictedNextOrderDate,
            'churn_risk' => $churnRisk,
        ]);

        return true;
    }

    private function churnRisk(int $recencyDays, ?int $avgIntervalDays): string
    {
        if ($avgIntervalDays !== null && $avgIntervalDays > 0) {
            return match (true) {
                $recencyDays > $avgIntervalDays * 2 => 'high',
                $recencyDays > (int) ($avgIntervalDays * 1.3) => 'medium',
                default => 'low',
            };
        }

        return match (true) {
            $recencyDays > 120 => 'high',
            $recencyDays > 60 => 'medium',
            default => 'low',
        };
    }

    private function recencyScore(int $days): int
    {
        return match (true) {
            $days <= 7 => 5,
            $days <= 30 => 4,
            $days <= 60 => 3,
            $days <= 120 => 2,
            default => 1,
        };
    }

    private function frequencyScore(int $count): int
    {
        return match (true) {
            $count >= 8 => 5,
            $count >= 5 => 4,
            $count >= 3 => 3,
            $count >= 1 => 2,
            default => 1,
        };
    }

    private function monetaryScore(int $paise): int
    {
        return match (true) {
            $paise >= 1000000 => 5,
            $paise >= 500000 => 4,
            $paise >= 200000 => 3,
            $paise >= 50000 => 2,
            default => 1,
        };
    }
}
