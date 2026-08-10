<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Autocomplete with a pragmatic typo-tolerance layer: exact/prefix matches
 * first, then SOUNDEX matching against product names for single-word queries
 * that don't hit anything — catches "chiken" -> "chicken" without an external
 * search engine.
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

        $results = $db->select(
            "SELECT p.id, p.name, p.slug,
                    (SELECT MIN(price_paise) FROM product_variants v WHERE v.product_id = p.id AND v.deleted_at IS NULL) AS min_price_paise
             FROM products p
             WHERE p.status = 'active' AND p.deleted_at IS NULL
               AND (p.name LIKE ? OR MATCH(p.name, p.short_description, p.description) AGAINST (? IN NATURAL LANGUAGE MODE))
             ORDER BY (p.name LIKE ?) DESC, p.is_featured DESC, p.review_count DESC
             LIMIT ?",
            ['%' . $query . '%', $query, $query . '%', $limit],
        );

        if ($results !== [] || str_contains($query, ' ')) {
            return $results;
        }

        // Single word, nothing matched — compare SOUNDEX word-by-word (not
        // against the whole name, which produces a different code) for
        // basic typo tolerance, e.g. "chikken" still finds "Chicken ...".
        return $db->select(
            "SELECT id, name, slug, min_price_paise FROM (
                 SELECT DISTINCT p.id, p.name, p.slug, p.review_count,
                        (SELECT MIN(price_paise) FROM product_variants v WHERE v.product_id = p.id AND v.deleted_at IS NULL) AS min_price_paise
                 FROM products p
                 JOIN JSON_TABLE(
                     CONCAT('[\"', REPLACE(REPLACE(p.name, '\\\\\\\\', ''), ' ', '\",\"'), '\"]'),
                     '$[*]' COLUMNS (word VARCHAR(191) PATH '$')
                 ) AS words
                 WHERE p.status = 'active' AND p.deleted_at IS NULL
                   AND SOUNDEX(words.word) = SOUNDEX(?)
             ) t
             ORDER BY review_count DESC
             LIMIT ?",
            [$query, $limit],
        );
    }
}
