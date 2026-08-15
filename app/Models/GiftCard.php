<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class GiftCard extends Model
{
    protected static string $table = 'gift_cards';

    public static function findByCode(string $code): ?array
    {
        return static::firstWhere(['code' => strtoupper(trim($code))]);
    }

    public static function generateCode(): string
    {
        do {
            $code = 'GC-' . strtoupper(bin2hex(random_bytes(4)));
        } while (static::findByCode($code) !== null);

        return $code;
    }

    /** @return array<int, array<string, mixed>> */
    public static function redemptions(int $giftCardId): array
    {
        return static::db()->select(
            'SELECT r.*, u.name AS redeemed_by_name FROM gift_card_redemptions r
             LEFT JOIN users u ON u.id = r.redeemed_by_user_id
             WHERE r.gift_card_id = :id ORDER BY r.created_at DESC',
            ['id' => $giftCardId],
        );
    }
}
