<?php

declare(strict_types=1);

namespace App\Core;

abstract class Model
{
    protected static string $table = '';
    protected static bool $softDeletes = false;
    protected static bool $timestamps = true;

    public static function db(): Database
    {
        return Database::instance();
    }

    public static function table(): string
    {
        return static::$table;
    }

    /** @return array<string, mixed>|null */
    public static function find(int $id): ?array
    {
        $sql = 'SELECT * FROM ' . static::$table . ' WHERE id = :id';
        $sql .= static::$softDeletes ? ' AND deleted_at IS NULL' : '';

        return static::db()->selectOne($sql, ['id' => $id]);
    }

    /** @return array<int, array<string, mixed>> */
    public static function all(string $orderBy = 'id DESC'): array
    {
        $sql = 'SELECT * FROM ' . static::$table;
        $sql .= static::$softDeletes ? ' WHERE deleted_at IS NULL' : '';
        $sql .= ' ORDER BY ' . $orderBy;

        return static::db()->select($sql);
    }

    /**
     * @param array<string, mixed> $where column => value, combined with AND, exact match
     * @return array<int, array<string, mixed>>
     */
    public static function where(array $where, string $orderBy = 'id DESC', ?int $limit = null): array
    {
        [$clause, $bindings] = static::buildWhere($where);

        $sql = 'SELECT * FROM ' . static::$table . ' WHERE ' . $clause;
        $sql .= ' ORDER BY ' . $orderBy;
        if ($limit !== null) {
            $sql .= ' LIMIT ' . $limit;
        }

        return static::db()->select($sql, $bindings);
    }

    /** @param array<string, mixed> $where */
    public static function firstWhere(array $where): ?array
    {
        $rows = static::where($where, 'id DESC', 1);
        return $rows[0] ?? null;
    }

    /** @param array<string, mixed> $data */
    public static function create(array $data): int
    {
        if (static::$timestamps) {
            $data['created_at'] ??= now();
            $data['updated_at'] ??= now();
        }

        return static::db()->insert(static::$table, $data);
    }

    /** @param array<string, mixed> $data */
    public static function updateWhere(int $id, array $data): int
    {
        if (static::$timestamps) {
            $data['updated_at'] = now();
        }

        return static::db()->update(static::$table, $data, 'id = :where_id', ['where_id' => $id]);
    }

    public static function destroy(int $id): int
    {
        if (static::$softDeletes) {
            return static::db()->update(static::$table, ['deleted_at' => now()], 'id = :id', ['id' => $id]);
        }

        return static::db()->delete(static::$table, 'id = :id', ['id' => $id]);
    }

    public static function count(array $where = []): int
    {
        [$clause, $bindings] = static::buildWhere($where);
        $sql = 'SELECT COUNT(*) AS c FROM ' . static::$table;
        if ($clause !== '1=1' || static::$softDeletes) {
            $sql .= ' WHERE ' . $clause;
        }

        return (int) (static::db()->selectOne($sql, $bindings)['c'] ?? 0);
    }

    /**
     * @param array<string, mixed> $where
     * @return array{0: string, 1: array<string, mixed>}
     */
    protected static function buildWhere(array $where): array
    {
        $parts = [];
        $bindings = [];

        foreach ($where as $column => $value) {
            $key = 'w_' . str_replace('.', '_', $column);
            $parts[] = "`{$column}` = :{$key}";
            $bindings[$key] = $value;
        }

        if (static::$softDeletes) {
            $parts[] = 'deleted_at IS NULL';
        }

        return [$parts === [] ? '1=1' : implode(' AND ', $parts), $bindings];
    }
}
