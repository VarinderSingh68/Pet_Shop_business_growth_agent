<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class Migrator
{
    private PDO $pdo;

    public function __construct(private readonly string $migrationsPath)
    {
        $this->pdo = Database::pdo();
        $this->ensureMigrationsTable();
    }

    private function ensureMigrationsTable(): void
    {
        $this->pdo->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(191) NOT NULL,
                batch INT UNSIGNED NOT NULL,
                run_at DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);
    }

    /** @return array<int, string> */
    private function allMigrationFiles(): array
    {
        $files = glob($this->migrationsPath . '/*.php') ?: [];
        sort($files);
        return $files;
    }

    /** @return array<int, string> */
    private function ranMigrations(): array
    {
        $rows = $this->pdo->query('SELECT migration FROM migrations ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
        return $rows === false ? [] : $rows;
    }

    private function nextBatch(): int
    {
        $row = $this->pdo->query('SELECT MAX(batch) AS b FROM migrations')->fetch();
        return ((int) ($row['b'] ?? 0)) + 1;
    }

    public function status(): array
    {
        $ran = $this->ranMigrations();
        $output = [];

        foreach ($this->allMigrationFiles() as $file) {
            $name = basename($file, '.php');
            $output[] = ['migration' => $name, 'ran' => in_array($name, $ran, true)];
        }

        return $output;
    }

    public function up(): array
    {
        $ran = $this->ranMigrations();
        $batch = $this->nextBatch();
        $applied = [];

        foreach ($this->allMigrationFiles() as $file) {
            $name = basename($file, '.php');
            if (in_array($name, $ran, true)) {
                continue;
            }

            $migration = require $file;
            try {
                // Not wrapped in a transaction: MySQL DDL (CREATE/ALTER TABLE)
                // triggers an implicit commit, which would break an explicit one.
                $migration->up($this->pdo);
                $stmt = $this->pdo->prepare('INSERT INTO migrations (migration, batch, run_at) VALUES (?, ?, ?)');
                $stmt->execute([$name, $batch, now()]);
                $applied[] = $name;
            } catch (\Throwable $e) {
                throw new \RuntimeException("Migration [{$name}] failed: " . $e->getMessage(), previous: $e);
            }
        }

        return $applied;
    }

    public function down(): array
    {
        $row = $this->pdo->query('SELECT MAX(batch) AS b FROM migrations')->fetch();
        $batch = (int) ($row['b'] ?? 0);

        if ($batch === 0) {
            return [];
        }

        $stmt = $this->pdo->prepare('SELECT migration FROM migrations WHERE batch = ? ORDER BY id DESC');
        $stmt->execute([$batch]);
        $names = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $rolledBack = [];
        foreach ($names as $name) {
            $file = $this->migrationsPath . '/' . $name . '.php';
            if (!is_file($file)) {
                continue;
            }

            $migration = require $file;
            try {
                $migration->down($this->pdo);
                $del = $this->pdo->prepare('DELETE FROM migrations WHERE migration = ?');
                $del->execute([$name]);
                $rolledBack[] = $name;
            } catch (\Throwable $e) {
                throw new \RuntimeException("Rollback of [{$name}] failed: " . $e->getMessage(), previous: $e);
            }
        }

        return $rolledBack;
    }

    public function fresh(): array
    {
        $tables = $this->pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($tables as $table) {
            $this->pdo->exec("DROP TABLE IF EXISTS `{$table}`");
        }
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

        $this->ensureMigrationsTable();

        return $this->up();
    }
}
