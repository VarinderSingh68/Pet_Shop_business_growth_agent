<?php

declare(strict_types=1);

namespace App\Services\Growth;

use App\Core\Database;
use App\Models\LoyaltyPoint;
use App\Models\Notification;

/**
 * 1 point per ₹10 spent, 1 point redeemable for ₹1 off at checkout. Points
 * expire 12 months after being earned; a warning notification goes out
 * 30 days before a batch expires. Simplification: expiry warnings and
 * actual expiry are tracked per earned batch rather than a full FIFO
 * ledger reconciliation against redemptions — accurate enough for a loyalty
 * program this size without the bookkeeping complexity of true FIFO.
 */
final class LoyaltyService
{
    private const POINTS_PER_RUPEE_SPENT = 0.1; // 1 point per ₹10
    private const PAISE_PER_POINT_REDEEMED = 100; // 1 point = ₹1

    public function pointsForOrder(int $totalPaise): int
    {
        return (int) floor(($totalPaise / 100) * self::POINTS_PER_RUPEE_SPENT);
    }

    public function awardForOrder(int $userId, int $orderId, int $totalPaise): void
    {
        $points = $this->pointsForOrder($totalPaise);
        if ($points <= 0) {
            return;
        }

        Database::instance()->insert('loyalty_points', [
            'user_id' => $userId,
            'points' => $points,
            'type' => 'earned',
            'reference_type' => 'order',
            'reference_id' => $orderId,
            'expires_at' => (new \DateTimeImmutable('+12 months'))->format('Y-m-d'),
            'created_at' => now(),
        ]);
    }

    public function balance(int $userId): int
    {
        return LoyaltyPoint::balance($userId);
    }

    public function tier(int $userId): string
    {
        $lifetime = (int) (Database::instance()->selectOne(
            "SELECT COALESCE(SUM(points), 0) AS s FROM loyalty_points WHERE user_id = :uid AND type = 'earned'",
            ['uid' => $userId],
        )['s'] ?? 0);

        return LoyaltyPoint::tierFor($lifetime);
    }

    /** @throws \RuntimeException if the customer doesn't have enough points */
    public function redeem(int $userId, int $points, ?int $orderId = null): int
    {
        if ($points <= 0) {
            return 0;
        }

        if ($this->balance($userId) < $points) {
            throw new \RuntimeException('Not enough loyalty points.');
        }

        Database::instance()->insert('loyalty_points', [
            'user_id' => $userId,
            'points' => -$points,
            'type' => 'redeemed',
            'reference_type' => $orderId !== null ? 'order' : null,
            'reference_id' => $orderId,
            'created_at' => now(),
        ]);

        return $points * self::PAISE_PER_POINT_REDEEMED;
    }

    /** Sends one expiry-warning notification per batch nearing its expiry date. */
    public function sendExpiryWarnings(): int
    {
        $db = Database::instance();

        $expiringSoon = $db->select(
            "SELECT lp.id, lp.user_id, lp.points, lp.expires_at, u.name, u.email FROM loyalty_points lp
             JOIN users u ON u.id = lp.user_id
             WHERE lp.type = 'earned' AND lp.expires_at IS NOT NULL
               AND lp.expires_at BETWEEN date('now') AND date('now', '+30 days')",
        );

        $sent = 0;
        foreach ($expiringSoon as $batch) {
            if (Notification::alreadySentFor((int) $batch['user_id'], 'loyalty_expiry_warning', 'loyalty_points', (int) $batch['id'])) {
                continue;
            }

            $db->insert('notifications', [
                'user_id' => $batch['user_id'],
                'type' => 'loyalty_expiry_warning',
                'channel' => 'email',
                'subject' => 'Your loyalty points are expiring soon',
                'body' => "{$batch['points']} points expire on " . date('d M Y', strtotime((string) $batch['expires_at'])) . '. Use them before they\'re gone!',
                'status' => 'sent',
                'related_type' => 'loyalty_points',
                'related_id' => $batch['id'],
                'sent_at' => now(),
                'created_at' => now(),
            ]);
            $sent++;
        }

        return $sent;
    }

    /** Expires any batch whose date has passed, offsetting it with a negative ledger entry. */
    public function expirePastDue(): int
    {
        $db = Database::instance();
        $expired = $db->select(
            "SELECT id, user_id, points FROM loyalty_points WHERE type = 'earned' AND expires_at < date('now')
             AND id NOT IN (SELECT reference_id FROM loyalty_points WHERE type = 'expired' AND reference_type = 'loyalty_points')",
        );

        foreach ($expired as $batch) {
            $db->insert('loyalty_points', [
                'user_id' => $batch['user_id'],
                'points' => -(int) $batch['points'],
                'type' => 'expired',
                'reference_type' => 'loyalty_points',
                'reference_id' => $batch['id'],
                'created_at' => now(),
            ]);
        }

        return count($expired);
    }
}
