<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class LoyaltyPoint extends Model
{
    protected static string $table = 'loyalty_points';
    protected static bool $timestamps = false;

    public static function balance(int $userId): int
    {
        $row = static::db()->selectOne(
            'SELECT COALESCE(SUM(points), 0) AS b FROM loyalty_points WHERE user_id = :uid',
            ['uid' => $userId],
        );
        return (int) ($row['b'] ?? 0);
    }

    public static function tierFor(int $lifetimePoints): string
    {
        return match (true) {
            $lifetimePoints >= 5000 => 'Gold',
            $lifetimePoints >= 1500 => 'Silver',
            default => 'Bronze',
        };
    }

    /** @return array<int, array<string, mixed>> */
    public static function ledgerFor(int $userId, int $limit = 20): array
    {
        return static::db()->select(
            'SELECT * FROM loyalty_points WHERE user_id = :uid ORDER BY id DESC LIMIT ' . max(1, $limit),
            ['uid' => $userId],
        );
    }
}
