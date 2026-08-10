<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

/**
 * Read-only external catalog access — for a POS/marketplace sync integration.
 * Requires a Bearer API token (see /admin/dev/api-tokens).
 */
final class ProductsController extends Controller
{
    public function index(Request $request): void
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 25;

        $products = Database::instance()->select(
            "SELECT p.id, p.name, p.slug, p.pet_type, p.status,
                    (SELECT MIN(price_paise) FROM product_variants v WHERE v.product_id = p.id AND v.deleted_at IS NULL) AS min_price_paise,
                    (SELECT COALESCE(SUM(stock_quantity),0) FROM product_variants v WHERE v.product_id = p.id AND v.deleted_at IS NULL) AS total_stock
             FROM products p
             WHERE p.deleted_at IS NULL
             ORDER BY p.id
             LIMIT {$perPage} OFFSET " . (($page - 1) * $perPage),
        );

        $this->json(['data' => $products, 'page' => $page, 'per_page' => $perPage]);
    }
}
