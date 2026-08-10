<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class ProductImage extends Model
{
    protected static string $table = 'product_images';
    protected static bool $timestamps = false;
}
