<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Banner extends Model
{
    protected static string $table = 'banners';

    /** @return array<int, array<string, mixed>> */
    public static function activeFor(string $page, string $displayType): array
    {
        return static::db()->select(
            "SELECT * FROM banners
             WHERE is_active = 1
               AND (target_page = 'all' OR target_page = :page)
               AND display_type = :type
               AND (starts_at IS NULL OR starts_at <= datetime('now'))
               AND (ends_at IS NULL OR ends_at >= datetime('now'))
             ORDER BY id DESC",
            ['page' => $page, 'type' => $displayType],
        );
    }
}
