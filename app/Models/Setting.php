<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Cache;
use App\Core\Database;

final class Setting
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting:{$key}", 300, static function () use ($key, $default) {
            $row = Database::instance()->selectOne('SELECT `value` FROM settings WHERE `key` = :key', ['key' => $key]);
            return $row !== null ? $row['value'] : $default;
        });
    }

    /** @return array<string, mixed> */
    public static function group(string $group): array
    {
        $rows = Database::instance()->select('SELECT `key`, `value` FROM settings WHERE `group` = :g', ['g' => $group]);
        $result = [];
        foreach ($rows as $row) {
            $result[$row['key']] = $row['value'];
        }
        return $result;
    }

    public static function set(string $key, mixed $value, string $group = 'general', string $type = 'string'): void
    {
        $db = Database::instance();
        $existing = $db->selectOne('SELECT id FROM settings WHERE `key` = :key', ['key' => $key]);

        if ($existing !== null) {
            $db->update('settings', ['value' => $value, 'updated_at' => now()], 'id = :id', ['id' => $existing['id']]);
        } else {
            $db->insert('settings', ['key' => $key, 'value' => $value, 'group' => $group, 'type' => $type, 'updated_at' => now()]);
        }

        Cache::forget("setting:{$key}");
    }
}
