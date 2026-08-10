<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Pet extends Model
{
    protected static string $table = 'pets';
    protected static bool $softDeletes = true;

    /** @return array<int, array<string, mixed>> */
    public static function forUser(int $userId): array
    {
        return static::where(['user_id' => $userId], 'created_at DESC');
    }

    public static function ageLabel(?string $birthday): ?string
    {
        if ($birthday === null) {
            return null;
        }

        $birth = new \DateTimeImmutable($birthday);
        $now = new \DateTimeImmutable('now');
        $diff = $birth->diff($now);

        if ($diff->y >= 1) {
            return $diff->y . ' year' . ($diff->y === 1 ? '' : 's') . ' old';
        }

        return $diff->m . ' month' . ($diff->m === 1 ? '' : 's') . ' old';
    }
}
