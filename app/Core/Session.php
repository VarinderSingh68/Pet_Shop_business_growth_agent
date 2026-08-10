<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        session_set_cookie_params([
            'lifetime' => (int) config('session.lifetime') * 60,
            'path' => '/',
            'domain' => '',
            'secure' => (bool) config('session.secure'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_name('petshop_session');
        session_start();
        self::$started = true;

        if (!isset($_SESSION['_started_at'])) {
            $_SESSION['_started_at'] = time();
        }

        self::ageFlashData();
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash_new'][$type][] = $message;
    }

    /** @return array<int, string> */
    public static function getFlash(string $type): array
    {
        return $_SESSION['_flash_old'][$type] ?? [];
    }

    public static function flashInputData(array $data): void
    {
        $_SESSION['_flash_input_new'] = $data;
    }

    public static function flashInput(string $key, mixed $default = ''): mixed
    {
        return $_SESSION['_flash_input_old'][$key] ?? $default;
    }

    private static function ageFlashData(): void
    {
        $_SESSION['_flash_old'] = $_SESSION['_flash_new'] ?? [];
        $_SESSION['_flash_new'] = [];

        $_SESSION['_flash_input_old'] = $_SESSION['_flash_input_new'] ?? [];
        $_SESSION['_flash_input_new'] = [];
    }
}
