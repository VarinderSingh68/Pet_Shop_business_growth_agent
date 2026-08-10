<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Coupon extends Model
{
    protected static string $table = 'coupons';

    public static function findByCode(string $code): ?array
    {
        return static::firstWhere(['code' => strtoupper(trim($code))]);
    }

    public static function customerRedemptionCount(int $couponId, int $userId): int
    {
        $row = static::db()->selectOne(
            'SELECT COUNT(*) AS c FROM coupon_redemptions WHERE coupon_id = :cid AND user_id = :uid',
            ['cid' => $couponId, 'uid' => $userId],
        );

        return (int) ($row['c'] ?? 0);
    }
}
