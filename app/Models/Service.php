<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Service extends Model
{
    protected static string $table = 'services';

    public static function findBySlug(string $slug): ?array
    {
        return static::firstWhere(['slug' => $slug, 'is_active' => 1]);
    }

    /** @return array<int, array<string, mixed>> */
    public static function allActive(): array
    {
        return static::where(['is_active' => 1], 'name ASC');
    }
}
