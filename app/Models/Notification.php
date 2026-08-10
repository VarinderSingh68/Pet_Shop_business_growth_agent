<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Notification extends Model
{
    protected static string $table = 'notifications';
    protected static bool $timestamps = false;

    public static function alreadySentFor(int $userId, string $type, string $relatedType, int $relatedId): bool
    {
        return static::db()->selectOne(
            "SELECT 1 AS x FROM notifications
             WHERE user_id = :uid AND type = :type AND related_type = :rt AND related_id = :rid AND status = 'sent'
             LIMIT 1",
            ['uid' => $userId, 'type' => $type, 'rt' => $relatedType, 'rid' => $relatedId],
        ) !== null;
    }

    public static function countSentSince(string $type, string $since): int
    {
        $row = static::db()->selectOne(
            "SELECT COUNT(*) c FROM notifications WHERE type = :type AND status = 'sent' AND sent_at >= :since",
            ['type' => $type, 'since' => $since],
        );
        return (int) ($row['c'] ?? 0);
    }
}
