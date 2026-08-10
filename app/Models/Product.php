<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Product extends Model
{
    protected static string $table = 'products';
    protected static bool $softDeletes = true;

    public static function findBySlug(string $slug): ?array
    {
        return static::firstWhere(['slug' => $slug, 'status' => 'active']);
    }

    /** @return array<int, array<string, mixed>> */
    public static function variants(int $productId): array
    {
        return static::db()->select(
            'SELECT * FROM product_variants WHERE product_id = :pid AND deleted_at IS NULL ORDER BY is_default DESC, price_paise ASC',
            ['pid' => $productId],
        );
    }

    /** @return array<int, array<string, mixed>> */
    public static function images(int $productId): array
    {
        return static::db()->select(
            'SELECT * FROM product_images WHERE product_id = :pid ORDER BY sort_order ASC, id ASC',
            ['pid' => $productId],
        );
    }

    /** @return array<int, array<string, mixed>> */
    public static function approvedReviews(int $productId, int $limit = 20): array
    {
        return static::db()->select(
            'SELECT r.*, u.name AS reviewer_name
             FROM reviews r
             LEFT JOIN users u ON u.id = r.user_id
             WHERE r.product_id = :pid AND r.status = "approved"
             ORDER BY r.created_at DESC
             LIMIT ' . max(1, $limit),
            ['pid' => $productId],
        );
    }

    /**
     * Same category, excluding the current product — the simplest useful
     * "related products" rule until the co-purchase matrix exists (Growth Agent phase).
     * @return array<int, array<string, mixed>>
     */
    public static function relatedTo(array $product, int $limit = 4): array
    {
        return static::db()->select(
            'SELECT p.*, (SELECT MIN(price_paise) FROM product_variants v WHERE v.product_id = p.id AND v.deleted_at IS NULL) AS min_price_paise
             FROM products p
             WHERE p.category_id = :cat AND p.id != :id AND p.status = "active" AND p.deleted_at IS NULL
             ORDER BY p.avg_rating DESC, p.review_count DESC
             LIMIT ' . max(1, $limit),
            ['cat' => $product['category_id'], 'id' => $product['id']],
        );
    }

    public static function recalculateRating(int $productId): void
    {
        $row = static::db()->selectOne(
            'SELECT COUNT(*) AS c, COALESCE(AVG(rating), 0) AS avg_r FROM reviews WHERE product_id = :pid AND status = "approved"',
            ['pid' => $productId],
        );

        static::db()->update(
            'products',
            ['avg_rating' => round((float) ($row['avg_r'] ?? 0), 2), 'review_count' => (int) ($row['c'] ?? 0)],
            'id = :id',
            ['id' => $productId],
        );
    }
}
