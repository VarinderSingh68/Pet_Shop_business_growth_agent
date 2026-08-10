<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Wishlist extends Model
{
    protected static string $table = 'wishlists';
    protected static bool $timestamps = false;

    /** @return array<int, array<string, mixed>> */
    public static function forUser(int $userId): array
    {
        return static::db()->select(
            'SELECT w.*, p.name, p.slug, p.pet_type, p.avg_rating, p.review_count,
                    (SELECT MIN(price_paise) FROM product_variants v WHERE v.product_id = p.id AND v.deleted_at IS NULL) AS min_price_paise,
                    (SELECT SUM(stock_quantity) FROM product_variants v WHERE v.product_id = p.id AND v.deleted_at IS NULL) AS total_stock
             FROM wishlists w
             JOIN products p ON p.id = w.product_id
             WHERE w.user_id = :uid
             ORDER BY w.created_at DESC',
            ['uid' => $userId],
        );
    }

    public static function has(int $userId, int $productId): bool
    {
        return static::firstWhere(['user_id' => $userId, 'product_id' => $productId]) !== null;
    }

    public static function toggle(int $userId, int $productId): bool
    {
        $existing = static::firstWhere(['user_id' => $userId, 'product_id' => $productId]);

        if ($existing !== null) {
            static::db()->delete('wishlists', 'id = :id', ['id' => $existing['id']]);
            return false;
        }

        static::db()->insert('wishlists', ['user_id' => $userId, 'product_id' => $productId, 'created_at' => now()]);
        return true;
    }
}
