<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Payment extends Model
{
    protected static string $table = 'payments';

    public static function findByIdempotencyKey(string $key): ?array
    {
        return static::firstWhere(['idempotency_key' => $key]);
    }
}
