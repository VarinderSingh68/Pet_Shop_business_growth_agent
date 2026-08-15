<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Cart extends Model
{
    protected static string $table = 'carts';

    /** @return array<int, array<string, mixed>> */
    public static function items(int $cartId): array
    {
        return static::db()->select(
            'SELECT ci.*, p.name AS product_name, p.slug AS product_slug, p.pet_type,
                    v.label AS variant_label, v.price_paise, v.compare_at_price_paise, v.stock_quantity, v.weight_grams,
                    (SELECT pi.path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.sort_order ASC, pi.id ASC LIMIT 1) AS image_path
             FROM cart_items ci
             JOIN products p ON p.id = ci.product_id
             JOIN product_variants v ON v.id = ci.variant_id
             WHERE ci.cart_id = :cart_id
             ORDER BY ci.id ASC',
            ['cart_id' => $cartId],
        );
    }

    public static function itemCount(int $cartId): int
    {
        $row = static::db()->selectOne(
            'SELECT COALESCE(SUM(quantity), 0) AS c FROM cart_items WHERE cart_id = :cart_id',
            ['cart_id' => $cartId],
        );

        return (int) ($row['c'] ?? 0);
    }
}
