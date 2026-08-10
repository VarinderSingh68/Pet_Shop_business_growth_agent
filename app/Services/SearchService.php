<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Autocomplete via LIKE matching against name/short_description/description
 * — plenty fast for a catalogue this size (dozens of products), no full-text
 * index required.
 */
final class SearchService
{
    /** @return array<int, array<string, mixed>> */
    public function suggest(string $query, int $limit = 8): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $db = Database::instance();
        $like = '%' . $query . '%';

        return $db->select(
            "SELECT p.id, p.name, p.slug,
                    (SELECT MIN(price_paise) FROM product_variants v WHERE v.product_id = p.id AND v.deleted_at IS NULL) AS min_price_paise
             FROM products p
             WHERE p.status = 'active' AND p.deleted_at IS NULL
               AND (p.name LIKE ? OR p.short_description LIKE ? OR p.description LIKE ?)
             ORDER BY (p.name LIKE ?) DESC, p.is_featured DESC, p.review_count DESC
             LIMIT ?",
            [$like, $like, $like, $query . '%', $limit],
        );
    }
}
