<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Enquiry extends Model
{
    protected static string $table = 'enquiries';

    /** @return array<int, array<string, mixed>> */
    public static function forUser(int $userId): array
    {
        return static::where(['user_id' => $userId], 'created_at DESC');
    }
}
