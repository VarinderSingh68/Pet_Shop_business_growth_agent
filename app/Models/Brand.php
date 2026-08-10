<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Brand extends Model
{
    protected static string $table = 'brands';

    public static function findBySlug(string $slug): ?array
    {
        return static::firstWhere(['slug' => $slug]);
    }
}
