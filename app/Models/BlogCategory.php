<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class BlogCategory extends Model
{
    protected static string $table = 'blog_categories';

    public static function findBySlug(string $slug): ?array
    {
        return static::firstWhere(['slug' => $slug]);
    }
}
