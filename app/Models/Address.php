<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Address extends Model
{
    protected static string $table = 'addresses';
    protected static bool $softDeletes = true;

    /** @return array<int, array<string, mixed>> */
    public static function forUser(int $userId): array
    {
        return static::where(['user_id' => $userId], 'is_default DESC, id DESC');
    }
}
