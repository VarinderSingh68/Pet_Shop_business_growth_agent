<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Subscription extends Model
{
    protected static string $table = 'subscriptions';

    /** @return array<int, array<string, mixed>> */
    public static function forUser(int $userId): array
    {
        return static::db()->select(
            'SELECT sub.*, p.name AS product_name, p.slug AS product_slug, v.label AS variant_label, v.price_paise
             FROM subscriptions sub
             JOIN products p ON p.id = sub.product_id
             JOIN product_variants v ON v.id = sub.variant_id
             WHERE sub.user_id = :uid
             ORDER BY sub.status = "active" DESC, sub.next_order_date ASC',
            ['uid' => $userId],
        );
    }

    /** @return array<int, array<string, mixed>> Subscriptions due today or earlier, still active */
    public static function due(): array
    {
        return static::db()->select(
            "SELECT * FROM subscriptions WHERE status = 'active' AND next_order_date <= date('now')",
        );
    }
}
