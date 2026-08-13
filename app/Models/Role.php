<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Role extends Model
{
    protected static string $table = 'roles';

    /** @return array<int, int> permission_id list currently granted to this role */
    public static function permissionIds(int $roleId): array
    {
        $rows = Database::instance()->select(
            'SELECT permission_id FROM role_permission WHERE role_id = :id',
            ['id' => $roleId],
        );

        return array_map(static fn (array $r) => (int) $r['permission_id'], $rows);
    }

    /** @param array<int, int> $permissionIds */
    public static function syncPermissions(int $roleId, array $permissionIds): void
    {
        $db = Database::instance();
        $db->delete('role_permission', 'role_id = :id', ['id' => $roleId]);

        foreach (array_unique($permissionIds) as $permissionId) {
            $db->insert('role_permission', ['role_id' => $roleId, 'permission_id' => $permissionId]);
        }
    }
}
