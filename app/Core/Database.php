<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;

/**
 * Thin PDO wrapper. No query builder abstraction beyond small helpers —
 * models write their own SQL with bound parameters.
 */
final class Database
{
    private const SLOW_QUERY_THRESHOLD_MS = 50.0;

    private static ?self $instance = null;
    private PDO $pdo;
    /** @var array<int, array{sql: string, bindings: array, time: float}> */
    private array $queryLog = [];
    private bool $profiling = false;

    private function __construct()
    {
        $path = (string) config('db.path');
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $dsn = 'sqlite:' . $path;

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->pdo = new PDO($dsn, null, null, $options);

        // SQLite disables foreign-key enforcement per-connection by default.
        $this->pdo->exec('PRAGMA foreign_keys = ON');

        // WAL lets readers and writers proceed concurrently instead of
        // blocking on the default rollback-journal locking, and the busy
        // timeout makes a PHP process wait out a brief lock instead of
        // failing immediately — both matter once more than one request can
        // be in flight against the same file at once (multiple visitors in
        // production, or even the dev server handling overlapping requests
        // for a page plus its assets).
        $this->pdo->exec('PRAGMA journal_mode = WAL');
        $this->pdo->exec('PRAGMA busy_timeout = 5000');

        $this->profiling = (bool) config('app.debug');
    }

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public static function pdo(): PDO
    {
        return self::instance()->pdo;
    }

    /**
     * @param array<string|int, mixed> $bindings
     */
    public function query(string $sql, array $bindings = []): PDOStatement
    {
        $start = microtime(true);

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bindings);
        } catch (PDOException $e) {
            logger_channel('db')->error('Query failed', [
                'sql' => $sql,
                'bindings' => $bindings,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        $durationMs = round((microtime(true) - $start) * 1000, 2);

        if ($this->profiling) {
            $this->queryLog[] = ['sql' => $sql, 'bindings' => $bindings, 'time' => $durationMs];
        }

        // Logged directly via PDO (not $this->query()) to avoid recursively
        // profiling the INSERT itself; the table-name guard is redundant
        // belt-and-suspenders against that same recursion.
        if ($durationMs >= self::SLOW_QUERY_THRESHOLD_MS && !str_contains($sql, 'slow_queries')) {
            $this->logSlowQuery($sql, $bindings, $durationMs);
        }

        return $stmt;
    }

    /** @param array<string|int, mixed> $bindings */
    private function logSlowQuery(string $sql, array $bindings, float $durationMs): void
    {
        try {
            $insert = $this->pdo->prepare(
                'INSERT INTO slow_queries (sql_text, bindings, duration_ms, request_path, created_at) VALUES (?, ?, ?, ?, ?)',
            );
            $insert->execute([
                $sql,
                json_encode($bindings, JSON_PARTIAL_OUTPUT_ON_ERROR),
                $durationMs,
                $_SERVER['REQUEST_URI'] ?? (PHP_SAPI === 'cli' ? 'cli' : null),
                now(),
            ]);
        } catch (PDOException) {
            // Never let profiling break the actual request.
        }
    }

    /**
     * @param array<string|int, mixed> $bindings
     * @return array<int, array<string, mixed>>
     */
    public function select(string $sql, array $bindings = []): array
    {
        return $this->query($sql, $bindings)->fetchAll();
    }

    /**
     * @param array<string|int, mixed> $bindings
     * @return array<string, mixed>|null
     */
    public function selectOne(string $sql, array $bindings = []): ?array
    {
        $row = $this->query($sql, $bindings)->fetch();
        return $row === false ? null : $row;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(static fn (string $c) => ':' . $c, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', array_map(static fn (string $c) => "`{$c}`", $columns)),
            implode(', ', $placeholders),
        );

        $bindings = [];
        foreach ($data as $key => $value) {
            $bindings[':' . $key] = $value;
        }

        $this->query($sql, $bindings);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $whereBindings
     */
    public function update(string $table, array $data, string $where, array $whereBindings = []): int
    {
        $set = implode(', ', array_map(static fn (string $c) => "`{$c}` = :set_{$c}", array_keys($data)));

        $bindings = [];
        foreach ($data as $key => $value) {
            $bindings[':set_' . $key] = $value;
        }
        foreach ($whereBindings as $key => $value) {
            $bindings[$key] = $value;
        }

        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";

        return $this->query($sql, $bindings)->rowCount();
    }

    /**
     * @param array<string, mixed> $whereBindings
     */
    public function delete(string $table, string $where, array $whereBindings = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $whereBindings)->rowCount();
    }

    public function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->inTransaction() ? $this->pdo->rollBack() : false;
    }

    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * @return array<int, array{sql: string, bindings: array, time: float}>
     */
    public function getQueryLog(): array
    {
        return $this->queryLog;
    }
}
