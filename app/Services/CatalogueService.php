<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Category;

/**
 * Builds the faceted product listing query. Facet counts reflect the
 * currently-applied filters (not a full cross-facet count matrix) — a
 * pragmatic tradeoff for a catalogue this size, cheap to compute on every
 * request without a search-engine service.
 */
final class CatalogueService
{
    private const PER_PAGE = 12;

    /**
     * @param array<string, mixed> $filters
     * @return array{items: array<int, array<string, mixed>>, total: int, page: int, perPage: int, facets: array<string, mixed>}
     */
    public function search(array $filters): array
    {
        $db = Database::instance();

        $where = ["p.status = 'active'", 'p.deleted_at IS NULL'];
        $bindings = [];

        if (!empty($filters['category'])) {
            $category = Category::findBySlug((string) $filters['category']);
            if ($category !== null) {
                $ids = Category::withDescendants((int) $category['id']);
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $where[] = "p.category_id IN ({$placeholders})";
                array_push($bindings, ...$ids);
            }
        }

        if (!empty($filters['brand']) && is_array($filters['brand'])) {
            $placeholders = implode(',', array_fill(0, count($filters['brand']), '?'));
            $where[] = "b.slug IN ({$placeholders})";
            array_push($bindings, ...$filters['brand']);
        }

        if (!empty($filters['pet'])) {
            $where[] = 'p.pet_type = ?';
            $bindings[] = $filters['pet'];
        }

        if (!empty($filters['stage'])) {
            $where[] = 'p.life_stage = ?';
            $bindings[] = $filters['stage'];
        }

        $searchActive = !empty($filters['q']);
        if ($searchActive) {
            $like = '%' . $filters['q'] . '%';
            $where[] = '(p.name LIKE ? OR p.short_description LIKE ? OR p.description LIKE ?)';
            $bindings[] = $like;
            $bindings[] = $like;
            $bindings[] = $like;
        }

        $whereSql = implode(' AND ', $where);

        $having = [];
        $havingBindings = [];

        if (!empty($filters['price_min'])) {
            $having[] = 'min_price_paise >= ?';
            $havingBindings[] = (int) round(((float) $filters['price_min']) * 100);
        }
        if (!empty($filters['price_max'])) {
            $having[] = 'min_price_paise <= ?';
            $havingBindings[] = (int) round(((float) $filters['price_max']) * 100);
        }
        if (!empty($filters['rating'])) {
            $having[] = 'p.avg_rating >= ?';
            $havingBindings[] = (float) $filters['rating'];
        }
        if (!empty($filters['in_stock'])) {
            $having[] = 'total_stock > 0';
        }

        $havingSql = $having === [] ? '' : ('HAVING ' . implode(' AND ', $having));

        $sortMap = [
            'price_asc' => 'min_price_paise ASC',
            'price_desc' => 'min_price_paise DESC',
            'rating' => 'p.avg_rating DESC, p.review_count DESC',
            'newest' => 'p.created_at DESC',
            'relevance' => $searchActive ? '(p.name LIKE ?) DESC, p.is_featured DESC, p.review_count DESC' : 'p.is_featured DESC, p.created_at DESC',
        ];
        $sortKey = is_string($filters['sort'] ?? null) && isset($sortMap[$filters['sort']]) ? $filters['sort'] : 'newest';
        $orderSql = $sortMap[$sortKey];

        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = self::PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT p.*, b.name AS brand_name, b.slug AS brand_slug,
                       MIN(v.price_paise) AS min_price_paise,
                       MAX(v.compare_at_price_paise) AS compare_at_price_paise,
                       COALESCE(SUM(v.stock_quantity), 0) AS total_stock,
                       (SELECT pi.path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.sort_order ASC, pi.id ASC LIMIT 1) AS image_path
                FROM products p
                LEFT JOIN brands b ON b.id = p.brand_id
                JOIN product_variants v ON v.product_id = p.id AND v.deleted_at IS NULL
                WHERE {$whereSql}
                GROUP BY p.id
                {$havingSql}
                ORDER BY {$orderSql}
                LIMIT {$perPage} OFFSET {$offset}";

        // ORDER BY comes after WHERE/HAVING in the final SQL, so its bind
        // (only present for the relevance sort's prefix-match check) must
        // be appended last, not first.
        $orderBindings = ($sortKey === 'relevance' && $searchActive) ? [$filters['q'] . '%'] : [];
        $allBindings = [...$bindings, ...$havingBindings, ...$orderBindings];

        $items = $db->select($sql, $allBindings);

        $countSql = "SELECT COUNT(*) AS c FROM (
                        SELECT p.id, MIN(v.price_paise) AS min_price_paise, COALESCE(SUM(v.stock_quantity),0) AS total_stock
                        FROM products p
                        LEFT JOIN brands b ON b.id = p.brand_id
                        JOIN product_variants v ON v.product_id = p.id AND v.deleted_at IS NULL
                        WHERE {$whereSql}
                        GROUP BY p.id
                        {$havingSql}
                     ) t";
        $countBindings = [...$bindings, ...$havingBindings];
        $total = (int) ($db->selectOne($countSql, $countBindings)['c'] ?? 0);

        $facets = $this->facets($whereSql, $bindings);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'facets' => $facets,
        ];
    }

    /**
     * @param array<int, mixed> $bindings
     * @return array<string, mixed>
     */
    private function facets(string $whereSql, array $bindings): array
    {
        $db = Database::instance();

        $categories = $db->select(
            "SELECT c.id, c.name, c.slug, COUNT(DISTINCT p.id) AS product_count
             FROM products p JOIN categories c ON c.id = p.category_id
             LEFT JOIN brands b ON b.id = p.brand_id
             WHERE {$whereSql}
             GROUP BY c.id, c.name, c.slug
             ORDER BY c.sort_order, c.name",
            $bindings,
        );

        $brands = $db->select(
            "SELECT b.id, b.name, b.slug, COUNT(DISTINCT p.id) AS product_count
             FROM products p JOIN brands b ON b.id = p.brand_id
             WHERE {$whereSql}
             GROUP BY b.id, b.name, b.slug
             ORDER BY b.name",
            $bindings,
        );

        $petTypes = $db->select(
            "SELECT p.pet_type, COUNT(DISTINCT p.id) AS product_count
             FROM products p
             LEFT JOIN brands b ON b.id = p.brand_id
             WHERE {$whereSql}
             GROUP BY p.pet_type",
            $bindings,
        );

        return ['categories' => $categories, 'brands' => $brands, 'pet_types' => $petTypes];
    }
}
