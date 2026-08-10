<?php

declare(strict_types=1);

namespace App\Services\Growth;

use App\Core\Cache;
use App\Core\Database;

/**
 * "Frequently bought together" built from the real co-purchase matrix in
 * order_items — products that have shown up in the same order as the given
 * product, ranked by how often that's happened. Falls back to nothing
 * (callers fall back to category-based "related products") until there's
 * enough order history to be meaningful.
 */
final class UpsellService
{
    /** @return array<int, array<string, mixed>> */
    public function frequentlyBoughtWith(int $productId, int $limit = 4): array
    {
        return Cache::remember("upsell:fbt:{$productId}:{$limit}", 3600, function () use ($productId, $limit) {
            $db = Database::instance();

            return $db->select(
                "SELECT p.id, p.name, p.slug, p.pet_type,
                        (SELECT MIN(price_paise) FROM product_variants v WHERE v.product_id = p.id AND v.deleted_at IS NULL) AS min_price_paise,
                        COUNT(*) AS co_purchase_count
                 FROM order_items oi1
                 JOIN order_items oi2 ON oi2.order_id = oi1.order_id AND oi2.product_id != oi1.product_id
                 JOIN products p ON p.id = oi2.product_id
                 WHERE oi1.product_id = :pid AND p.status = 'active' AND p.deleted_at IS NULL
                 GROUP BY p.id, p.name, p.slug, p.pet_type
                 ORDER BY co_purchase_count DESC
                 LIMIT " . max(1, $limit),
                ['pid' => $productId],
            );
        });
    }

    /** @return array<int, array<string, mixed>> Cart-page cross-sell: bought with anything currently in the cart. */
    public function forCart(array $productIds, int $limit = 4): array
    {
        if ($productIds === []) {
            return [];
        }

        $db = Database::instance();
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $excludePlaceholders = implode(',', array_fill(0, count($productIds), '?'));

        return $db->select(
            "SELECT p.id, p.name, p.slug, p.pet_type,
                    (SELECT MIN(price_paise) FROM product_variants v WHERE v.product_id = p.id AND v.deleted_at IS NULL) AS min_price_paise,
                    COUNT(*) AS co_purchase_count
             FROM order_items oi1
             JOIN order_items oi2 ON oi2.order_id = oi1.order_id
             JOIN products p ON p.id = oi2.product_id
             WHERE oi1.product_id IN ({$placeholders})
               AND p.id NOT IN ({$excludePlaceholders})
               AND p.status = 'active' AND p.deleted_at IS NULL
             GROUP BY p.id, p.name, p.slug, p.pet_type
             ORDER BY co_purchase_count DESC
             LIMIT " . max(1, $limit),
            [...$productIds, ...$productIds],
        );
    }
}
