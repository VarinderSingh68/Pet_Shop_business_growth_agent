<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Permission extends Model
{
    protected static string $table = 'permissions';

    /** Grouped by the `group` column, in a stable group order for consistent display. */
    public static function grouped(): array
    {
        $grouped = [];
        foreach (static::all('`group` ASC, name ASC') as $permission) {
            $grouped[$permission['group']][] = $permission;
        }

        return $grouped;
    }
}
