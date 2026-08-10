<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class ProductVariant extends Model
{
    protected static string $table = 'product_variants';
    protected static bool $softDeletes = true;

    public static function inStock(array $variant): bool
    {
        return (int) $variant['stock_quantity'] > 0;
    }

    public static function isLowStock(array $variant): bool
    {
        return (int) $variant['stock_quantity'] > 0
            && (int) $variant['stock_quantity'] <= (int) $variant['low_stock_threshold'];
    }
}
