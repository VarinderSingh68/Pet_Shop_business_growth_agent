<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Page extends Model
{
    protected static string $table = 'pages';

    public static function findBySlug(string $slug): ?array
    {
        return static::firstWhere(['slug' => $slug, 'is_published' => 1]);
    }
}
