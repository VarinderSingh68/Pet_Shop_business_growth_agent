<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class CartItem extends Model
{
    protected static string $table = 'cart_items';
}
