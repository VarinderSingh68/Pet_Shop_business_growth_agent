<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Referral extends Model
{
    protected static string $table = 'referrals';

    public static function generateCode(string $name): string
    {
        $base = strtoupper(preg_replace('/[^A-Z]/', '', strtoupper(substr($name, 0, 4))) ?: 'PET');
        return $base . strtoupper(bin2hex(random_bytes(3)));
    }

    /** @return array<int, array<string, mixed>> */
    public static function forReferrer(int $referrerUserId): array
    {
        return static::db()->select(
            'SELECT rf.*, u.name AS referred_name FROM referrals rf
             JOIN users u ON u.id = rf.referred_user_id
             WHERE rf.referrer_user_id = :uid ORDER BY rf.created_at DESC',
            ['uid' => $referrerUserId],
        );
    }
}
