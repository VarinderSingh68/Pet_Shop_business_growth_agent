<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    private const SESSION_KEY = '_auth_user_id';

    private ?array $user = null;
    private bool $resolved = false;

    public function attempt(string $email, string $password): bool
    {
        $user = $this->validateCredentials($email, $password);

        if ($user === null) {
            return false;
        }

        $this->login($user);

        return true;
    }

    /**
     * Checks email/password/active-status without establishing a session —
     * used by flows (like the admin 2FA challenge) that need to confirm
     * credentials before deciding whether a second factor is required.
     *
     * @return array<string, mixed>|null
     */
    public function validateCredentials(string $email, string $password): ?array
    {
        $user = User::findByEmail($email);

        if ($user === null || !User::verifyPassword($user, $password)) {
            return null;
        }

        if ((int) $user['is_active'] === 0) {
            return null;
        }

        return $user;
    }

    public function login(array $user): void
    {
        Session::regenerate();
        Session::put(self::SESSION_KEY, (int) $user['id']);
        $this->user = $user;
        $this->resolved = true;
    }

    public function logout(): void
    {
        Session::forget(self::SESSION_KEY);
        Session::regenerate();
        $this->user = null;
        $this->resolved = true;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function id(): ?int
    {
        return $this->user()['id'] ?? null;
    }

    public function user(): ?array
    {
        if ($this->resolved) {
            return $this->user;
        }

        $this->resolved = true;
        $id = Session::get(self::SESSION_KEY);

        if ($id === null) {
            return null;
        }

        $this->user = User::find((int) $id);

        return $this->user;
    }

    public function hasRole(string ...$roleSlugs): bool
    {
        $user = $this->user();
        if ($user === null) {
            return false;
        }

        $row = Database::instance()->selectOne(
            'SELECT slug FROM roles WHERE id = :id',
            ['id' => $user['role_id']],
        );

        return $row !== null && in_array($row['slug'], $roleSlugs, true);
    }

    public function can(string $permissionSlug): bool
    {
        $user = $this->user();
        if ($user === null) {
            return false;
        }

        return User::hasPermission((int) $user['id'], $permissionSlug);
    }
}
