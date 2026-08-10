<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * The whole database is a single SQLite file, so backup/restore is just a
 * clean file copy — `VACUUM INTO` produces an atomic, consistent snapshot
 * even while the live connection is open (SQLite handles the locking).
 */
final class BackupService
{
    public function createBackup(): string
    {
        $filename = 'backup-' . date('Y-m-d-His') . '.sqlite';
        $dir = storage_path('backups');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $destPath = $dir . '/' . $filename;
        $pdo = Database::pdo();
        $pdo->exec('VACUUM INTO ' . $pdo->quote($destPath));

        return $filename;
    }

    /** @return array<int, array{name: string, size: int, created_at: int}> */
    public function listBackups(): array
    {
        $dir = storage_path('backups');
        if (!is_dir($dir)) {
            return [];
        }

        $files = glob($dir . '/backup-*.sqlite') ?: [];
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
        $dbPath = (string) config('db.path');

        // Each HTTP request gets a fresh Database singleton (PHP's static
        // state doesn't persist across requests under Apache/PHP-FPM), so
        // overwriting the live file here is safe for every request after
        // this one — nothing later in *this* request touches the database.
        if (!copy($path, $dbPath)) {
            throw new \RuntimeException('Could not restore backup file.');
        }
    }

    public function pathFor(string $filename): string
    {
        return $this->safePath($filename);
    }

    private function safePath(string $filename): string
    {
        $filename = basename($filename); // strip any path traversal attempt
        $path = storage_path('backups/' . $filename);

        if (!is_file($path) || !str_starts_with($filename, 'backup-') || !str_ends_with($filename, '.sqlite')) {
            throw new \RuntimeException('Backup file not found.');
        }

        return $path;
    }
}
