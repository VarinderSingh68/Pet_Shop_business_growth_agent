<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class OrderItem extends Model
{
    protected static string $table = 'order_items';
    protected static bool $timestamps = false;
}
