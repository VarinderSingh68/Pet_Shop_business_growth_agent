<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class User extends Model
{
    protected static string $table = 'users';
    protected static bool $softDeletes = true;

    public static function findByEmail(string $email): ?array
    {
        return static::firstWhere(['email' => strtolower(trim($email))]);
    }

    public static function findByGoogleId(string $googleId): ?array
    {
        return static::firstWhere(['google_id' => $googleId]);
    }

    public static function verifyPassword(array $user, string $password): bool
    {
        // Google-only accounts have no password_hash until they set one —
        // password_verify() requires a string, so null must short-circuit here.
        if ($user['password_hash'] === null) {
            return false;
        }

        return password_verify($password, $user['password_hash']);
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    /** @return array<int, array<string, mixed>> */
    public static function withRole(string $roleSlug): array
    {
        $sql = 'SELECT u.* FROM users u
                JOIN roles r ON r.id = u.role_id
                WHERE r.slug = :slug AND u.deleted_at IS NULL
                ORDER BY u.id DESC';

        return static::db()->select($sql, ['slug' => $roleSlug]);
    }

    public static function hasPermission(int $userId, string $permissionSlug): bool
    {
        $sql = 'SELECT COUNT(*) AS c
                FROM users u
                JOIN role_permission rp ON rp.role_id = u.role_id
                JOIN permissions p ON p.id = rp.permission_id
                WHERE u.id = :uid AND p.slug = :slug';

        $row = static::db()->selectOne($sql, ['uid' => $userId, 'slug' => $permissionSlug]);

        return (int) ($row['c'] ?? 0) > 0;
    }
}
