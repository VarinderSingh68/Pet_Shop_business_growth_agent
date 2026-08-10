<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\CronRun;

final class HealthCheckService
{
    /** @return array<int, array{label: string, status: string, detail: string}> */
    public function run(): array
    {
        $checks = [];

        $checks[] = [
            'label' => 'PHP version',
            'status' => version_compare(PHP_VERSION, '8.2.0', '>=') ? 'ok' : 'fail',
            'detail' => PHP_VERSION,
        ];

        foreach (['pdo_mysql', 'mbstring', 'gd', 'fileinfo', 'curl', 'openssl'] as $ext) {
            $checks[] = [
                'label' => "Extension: {$ext}",
                'status' => extension_loaded($ext) ? 'ok' : 'fail',
                'detail' => extension_loaded($ext) ? 'loaded' : 'missing',
            ];
        }

        try {
            Database::instance()->selectOne('SELECT 1');
            $checks[] = ['label' => 'Database connectivity', 'status' => 'ok', 'detail' => 'Connected'];
        } catch (\Throwable $e) {
            $checks[] = ['label' => 'Database connectivity', 'status' => 'fail', 'detail' => $e->getMessage()];
        }

        foreach (['storage/logs', 'storage/cache', 'storage/uploads', 'storage/backups'] as $dir) {
            $path = base_path($dir);
            $writable = is_dir($path) && is_writable($path);
            $checks[] = ['label' => "Writable: {$dir}", 'status' => $writable ? 'ok' : 'fail', 'detail' => $writable ? 'writable' : 'not writable or missing'];
        }

        $freeBytes = @disk_free_space(base_path());
        $freeGb = $freeBytes !== false ? round($freeBytes / 1073741824, 1) : null;
        $checks[] = [
            'label' => 'Disk space',
            'status' => $freeGb === null ? 'warn' : ($freeGb < 1 ? 'fail' : 'ok'),
            'detail' => $freeGb !== null ? "{$freeGb} GB free" : 'Unable to determine',
        ];

        $lastCron = CronRun::recent(1)[0] ?? null;
        if ($lastCron === null) {
            $checks[] = ['label' => 'Growth Agent cron', 'status' => 'warn', 'detail' => 'Never run — see README for the crontab entry.'];
        } else {
            $minutesAgo = (int) round((time() - strtotime((string) $lastCron['started_at'])) / 60);
            $checks[] = [
                'label' => 'Growth Agent cron',
                'status' => $minutesAgo > 30 ? 'warn' : 'ok',
                'detail' => "Last run {$minutesAgo} minute(s) ago" . ($lastCron['outcome'] === 'failed' ? ' (last run FAILED)' : ''),
            ];
        }

        return $checks;
    }
}
