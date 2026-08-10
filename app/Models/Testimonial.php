<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Testimonial extends Model
{
    protected static string $table = 'testimonials';

    /** @return array<int, array<string, mixed>> */
    public static function published(int $limit = 6): array
    {
        return static::where(['is_published' => 1], 'sort_order ASC, id DESC', $limit);
    }
}
