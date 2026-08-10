<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public static function token(): string
    {
        if (!Session::has('_csrf_token')) {
            Session::put('_csrf_token', bin2hex(random_bytes(32)));
        }

        return (string) Session::get('_csrf_token');
    }

    public static function verify(?string $token): bool
    {
        $stored = Session::get('_csrf_token');

        return is_string($stored) && is_string($token) && hash_equals($stored, $token);
    }
}
