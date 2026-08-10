<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Simple file-based cache — fine for a single shared-hosting instance,
 * avoids requiring Redis/Memcached as an extra service.
 */
final class Cache
{
    public static function remember(string $key, int $ttlSeconds, callable $resolver): mixed
    {
        $cached = self::get($key);
        if ($cached !== null) {
            return $cached;
        }

        $value = $resolver();
        self::put($key, $value, $ttlSeconds);
        return $value;
    }

    public static function get(string $key): mixed
    {
        $path = self::path($key);
        if (!is_file($path)) {
            return null;
        }

        $payload = @unserialize((string) file_get_contents($path));
        if (!is_array($payload) || !isset($payload['expires'], $payload['value'])) {
            return null;
        }

        if ($payload['expires'] !== 0 && $payload['expires'] < time()) {
            @unlink($path);
            return null;
        }

        return $payload['value'];
    }

    public static function put(string $key, mixed $value, int $ttlSeconds = 3600): void
    {
        $dir = storage_path('cache');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $payload = [
            'expires' => $ttlSeconds === 0 ? 0 : time() + $ttlSeconds,
            'value' => $value,
        ];

        file_put_contents(self::path($key), serialize($payload), LOCK_EX);
    }

    public static function forget(string $key): void
    {
        $path = self::path($key);
        if (is_file($path)) {
            unlink($path);
        }
    }

    public static function flush(): void
    {
        foreach (glob(storage_path('cache') . '/*.cache') ?: [] as $file) {
            unlink($file);
        }
    }

    private static function path(string $key): string
    {
        return storage_path('cache') . '/' . hash('sha256', $key) . '.cache';
    }
}
