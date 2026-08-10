<?php

declare(strict_types=1);

namespace App\Services\Growth;

use App\Core\Database;
use App\Models\Segment;

/**
 * Living segments, fully re-evaluated on every run — a customer who no
 * longer qualifies (e.g. redeemed their last coupon, started a subscription)
 * drops out automatically rather than sticking in a stale list.
 */
final class SegmentService
{
    private const DEFINITIONS = [
        'new' => ['name' => 'New', 'description' => 'Placed their first order in the last 30 days.'],
        'loyal' => ['name' => 'Loyal', 'description' => 'High RFM score — orders often and spends well.'],
        'at_risk' => ['name' => 'At Risk', 'description' => "Going quiet relative to their own ordering rhythm."],
        'lapsed' => ['name' => 'Lapsed', 'description' => "Well overdue for a reorder based on their own history."],
        'high_value' => ['name' => 'High Value', 'description' => 'Top spenders in the last 12 months.'],
        'puppy_owner' => ['name' => 'Puppy/Kitten Owner', 'description' => 'Has a dog or cat under 12 months old.'],
        'senior_pet_owner' => ['name' => 'Senior Pet Owner', 'description' => 'Has a dog or cat over 7 years old.'],
        'discount_hunter' => ['name' => 'Discount Hunter', 'description' => 'Rarely orders without a coupon.'],
        'subscription_candidate' => ['name' => 'Subscription Candidate', 'description' => "Buys food repeatedly but hasn't subscribed."],
    ];

    /** @return array<string, int> segment key => member count */
    public function evaluateAll(): array
    {
        $db = Database::instance();
        $counts = [];

        $resolvers = [
            'new' => $this->membersNew(...),
            'loyal' => $this->membersLoyal(...),
            'at_risk' => $this->membersAtRisk(...),
            'lapsed' => $this->membersLapsed(...),
            'high_value' => $this->membersHighValue(...),
            'puppy_owner' => $this->membersPuppyOwner(...),
            'senior_pet_owner' => $this->membersSeniorPetOwner(...),
            'discount_hunter' => $this->membersDiscountHunter(...),
            'subscription_candidate' => $this->membersSubscriptionCandidate(...),
        ];

        foreach (self::DEFINITIONS as $key => $meta) {
            $segmentId = $this->ensureSegment($db, $key, $meta);
            $memberIds = $resolvers[$key]($db);
            Segment::syncMembers($segmentId, $memberIds);
            $counts[$key] = count(array_unique($memberIds));
        }

        return $counts;
    }

    private function ensureSegment(Database $db, string $key, array $meta): int
    {
        $existing = Segment::findByKey($key);
        if ($existing !== null) {
            return (int) $existing['id'];
        }

        return $db->insert('segments', [
            'key' => $key,
            'name' => $meta['name'],
            'description' => $meta['description'],
            'is_dynamic' => 1,
            'member_count' => 0,
            'updated_at' => now(),
        ]);
    }

    /** @return array<int, int> */
    private function membersNew(Database $db): array
    {
        $rows = $db->select(
            "SELECT o.user_id FROM orders o
             WHERE o.user_id IS NOT NULL AND o.status != 'cancelled'
             GROUP BY o.user_id
             HAVING COUNT(*) = 1 AND MIN(o.placed_at) >= datetime('now', '-30 days')",
        );
        return array_map(static fn (array $r) => (int) $r['user_id'], $rows);
    }

    private function membersLoyal(Database $db): array
    {
        $rows = $db->select("SELECT user_id FROM customer_scores WHERE rfm_total >= 11");
        return array_map(static fn (array $r) => (int) $r['user_id'], $rows);
    }

    private function membersAtRisk(Database $db): array
    {
        $rows = $db->select("SELECT user_id FROM customer_scores WHERE churn_risk = 'medium'");
        return array_map(static fn (array $r) => (int) $r['user_id'], $rows);
    }

    private function membersLapsed(Database $db): array
    {
        $rows = $db->select("SELECT user_id FROM customer_scores WHERE churn_risk = 'high'");
        return array_map(static fn (array $r) => (int) $r['user_id'], $rows);
    }

    private function membersHighValue(Database $db): array
    {
        $rows = $db->select("SELECT user_id FROM customer_scores WHERE monetary_score = 5");
        return array_map(static fn (array $r) => (int) $r['user_id'], $rows);
    }

    private function membersPuppyOwner(Database $db): array
    {
        $rows = $db->select(
            "SELECT DISTINCT user_id FROM pets
             WHERE species IN ('dog','cat') AND deleted_at IS NULL
               AND birthday IS NOT NULL AND birthday >= date('now', '-12 months')",
        );
        return array_map(static fn (array $r) => (int) $r['user_id'], $rows);
    }

    private function membersSeniorPetOwner(Database $db): array
    {
        $rows = $db->select(
            "SELECT DISTINCT user_id FROM pets
             WHERE species IN ('dog','cat') AND deleted_at IS NULL
               AND birthday IS NOT NULL AND birthday <= date('now', '-7 years')",
        );
        return array_map(static fn (array $r) => (int) $r['user_id'], $rows);
    }

    private function membersDiscountHunter(Database $db): array
    {
        $rows = $db->select(
            "SELECT o.user_id
             FROM orders o
             WHERE o.user_id IS NOT NULL AND o.status != 'cancelled'
             GROUP BY o.user_id
             HAVING COUNT(*) >= 2 AND CAST(SUM(o.coupon_id IS NOT NULL) AS REAL) / COUNT(*) >= 0.5",
        );
        return array_map(static fn (array $r) => (int) $r['user_id'], $rows);
    }

    private function membersSubscriptionCandidate(Database $db): array
    {
        $rows = $db->select(
            "SELECT o.user_id
             FROM orders o
             JOIN order_items oi ON oi.order_id = o.id
             JOIN products p ON p.id = oi.product_id
             WHERE o.user_id IS NOT NULL AND o.status != 'cancelled' AND p.feeding_grams_per_day IS NOT NULL
             GROUP BY o.user_id
             HAVING COUNT(DISTINCT o.id) >= 2
                AND o.user_id NOT IN (SELECT user_id FROM subscriptions WHERE status = 'active')",
        );
        return array_map(static fn (array $r) => (int) $r['user_id'], $rows);
    }
}
