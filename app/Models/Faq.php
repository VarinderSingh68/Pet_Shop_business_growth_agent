<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Faq extends Model
{
    protected static string $table = 'faqs';

    /** @return array<int, array<string, mixed>> */
    public static function published(): array
    {
        return static::where(['is_published' => 1], 'sort_order ASC, id ASC');
    }
}
