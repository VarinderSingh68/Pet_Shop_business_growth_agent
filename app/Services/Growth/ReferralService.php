<?php

declare(strict_types=1);

namespace App\Services\Growth;

use App\Core\Database;
use App\Models\Referral;
use App\Models\User;

/**
 * Two-sided referral reward, released only once the referred customer
 * places their first order (not just on signup, to keep the reward tied to
 * real revenue). Two fraud checks: self-referral (same phone number as the
 * referrer) rejected at signup, and repeat-address abuse (referred
 * customer's delivery address matches one the referrer has used) rejected
 * when the reward would otherwise be released.
 */
final class ReferralService
{
    private const REWARD_PAISE = 15000; // ₹150 each side

    public function getOrCreateCode(int $userId): string
    {
        $db = Database::instance();
        $user = $db->selectOne('SELECT referral_code, name FROM users WHERE id = :id', ['id' => $userId]);

        if ($user['referral_code'] !== null) {
            return $user['referral_code'];
        }

        do {
            $code = Referral::generateCode((string) $user['name']);
            $exists = $db->selectOne('SELECT 1 AS x FROM users WHERE referral_code = :code', ['code' => $code]);
        } while ($exists !== null);

        $db->update('users', ['referral_code' => $code], 'id = :id', ['id' => $userId]);

        return $code;
    }

    /**
     * Called at signup when a `?ref=CODE` was present. Records the pending
     * referral; the self-referral check (same phone as referrer) blocks it
     * immediately, everything else waits for the first-order fraud check.
     */
    public function recordSignup(string $code, int $newUserId): void
    {
        $db = Database::instance();
        $referrer = $db->selectOne('SELECT id, phone FROM users WHERE referral_code = :code', ['code' => strtoupper(trim($code))]);

        if ($referrer === null || (int) $referrer['id'] === $newUserId) {
            return;
        }

        $newUser = User::find($newUserId);
        $status = 'pending';
        $fraudReason = null;

        if ($newUser['phone'] !== null && $newUser['phone'] === $referrer['phone']) {
            $status = 'fraud_flagged';
            $fraudReason = 'Referred account shares a phone number with the referrer (likely self-referral).';
        }

        $db->insert('referrals', [
            'referrer_user_id' => $referrer['id'],
            'referred_user_id' => $newUserId,
            'status' => $status,
            'fraud_reason' => $fraudReason,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Releases the two-sided reward once the referred customer's first
     * order is placed — checked here rather than at signup so the reward
     * only pays out against real revenue.
     */
    public function tryReleaseReward(int $referredUserId, array $order): void
    {
        $db = Database::instance();
        $referral = $db->selectOne(
            "SELECT * FROM referrals WHERE referred_user_id = :uid AND status = 'pending'",
            ['uid' => $referredUserId],
        );

        if ($referral === null) {
            return;
        }

        $priorOrders = (int) ($db->selectOne(
            "SELECT COUNT(*) c FROM orders WHERE user_id = :uid AND status != 'cancelled' AND id != :oid",
            ['uid' => $referredUserId, 'oid' => $order['id']],
        )['c'] ?? 0);

        if ($priorOrders > 0) {
            return; // reward only triggers on the referred customer's first order
        }

        $addressReused = $db->selectOne(
            "SELECT 1 AS x FROM orders WHERE user_id = :referrer AND shipping_line1 = :line1 AND shipping_postal_code = :postal LIMIT 1",
            ['referrer' => $referral['referrer_user_id'], 'line1' => $order['shipping_line1'], 'postal' => $order['shipping_postal_code']],
        ) !== null;

        if ($addressReused) {
            $db->update('referrals', [
                'status' => 'fraud_flagged',
                'fraud_reason' => "Referred customer's delivery address matches one the referrer has used — likely the same household re-using a code.",
                'updated_at' => now(),
            ], 'id = :id', ['id' => $referral['id']]);
            return;
        }

        $loyalty = new LoyaltyService();
        $rewardPoints = (int) (self::REWARD_PAISE / 100);
        $db->insert('loyalty_points', [
            'user_id' => $referral['referrer_user_id'], 'points' => $rewardPoints, 'type' => 'earned',
            'reference_type' => 'referral', 'reference_id' => $referral['id'], 'created_at' => now(),
        ]);
        $db->insert('loyalty_points', [
            'user_id' => $referredUserId, 'points' => $rewardPoints, 'type' => 'earned',
            'reference_type' => 'referral', 'reference_id' => $referral['id'], 'created_at' => now(),
        ]);

        $db->update('referrals', [
            'status' => 'rewarded',
            'reward_paise' => self::REWARD_PAISE,
            'completed_at' => now(),
            'updated_at' => now(),
        ], 'id = :id', ['id' => $referral['id']]);
    }
}
