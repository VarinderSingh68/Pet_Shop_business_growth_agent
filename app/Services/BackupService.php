<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * Dumps and restores via plain PDO rather than shelling out to mysqldump —
 * shared hosting frequently doesn't expose the binary or allow exec(), so a
 * pure-PHP dump is the only approach guaranteed to work everywhere this app
 * deploys.
 */
final class BackupService
{
    public function createBackup(): string
    {
        $pdo = Database::pdo();
        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

        $sql = "-- Happy Tails Pet Store backup\n-- Generated " . now() . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $createRow = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch();
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n{$createRow['Create Table']};\n\n";

            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
            foreach (array_chunk($rows, 200) as $chunk) {
                if ($chunk === []) {
                    continue;
                }
                $columns = array_keys($chunk[0]);
                $columnList = implode(', ', array_map(static fn (string $c) => "`{$c}`", $columns));
                $valueLines = [];

                foreach ($chunk as $row) {
                    $values = array_map(static function ($v) use ($pdo) {
                        return $v === null ? 'NULL' : $pdo->quote((string) $v);
                    }, $row);
                    $valueLines[] = '(' . implode(', ', $values) . ')';
                }

                $sql .= "INSERT INTO `{$table}` ({$columnList}) VALUES\n" . implode(",\n", $valueLines) . ";\n";
            }
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $filename = 'backup-' . date('Y-m-d-His') . '.sql';
        $dir = storage_path('backups');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents($dir . '/' . $filename, $sql);

        return $filename;
    }

    /** @return array<int, array{name: string, size: int, created_at: int}> */
    public function listBackups(): array
    {
        $dir = storage_path('backups');
        if (!is_dir($dir)) {
            return [];
        }

        $files = glob($dir . '/backup-*.sql') ?: [];
        $result = [];
        foreach ($files as $file) {
            $result[] = ['name' => basename($file), 'size' => filesize($file), 'created_at' => filemtime($file)];
        }

        usort($result, static fn (array $a, array $b) => $b['created_at'] <=> $a['created_at']);

        return $result;
    }

    public function restore(string $filename): void
    {
        $path = $this->safePath($filename);
        $sql = file_get_contents($path);
        if ($sql === false) {
            throw new \RuntimeException('Could not read backup file.');
        }

        Database::pdo()->exec($sql);
    }

    public function pathFor(string $filename): string
    {
        return $this->safePath($filename);
    }

    private function safePath(string $filename): string
    {
        $filename = basename($filename); // strip any path traversal attempt
        $path = storage_path('backups/' . $filename);

        if (!is_file($path) || !str_starts_with($filename, 'backup-') || !str_ends_with($filename, '.sql')) {
            throw new \RuntimeException('Backup file not found.');
        }

        return $path;
    }
}
