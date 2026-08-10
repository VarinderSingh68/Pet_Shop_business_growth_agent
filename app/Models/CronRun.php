<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class CronRun extends Model
{
    protected static string $table = 'cron_runs';
    protected static bool $timestamps = false;

    public static function start(string $jobName): int
    {
        return static::create(['job_name' => $jobName, 'started_at' => now()]);
    }

    public static function finish(int $id, string $outcome, string $summary, ?string $error = null): void
    {
        $run = static::find($id);
        $startedAt = new \DateTimeImmutable((string) $run['started_at']);
        $durationMs = (int) round((microtime(true) - $startedAt->getTimestamp()) * 1000);

        static::updateWhere($id, [
            'finished_at' => now(),
            'duration_ms' => max(0, $durationMs),
            'outcome' => $outcome,
            'summary' => $summary,
            'error' => $error,
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    public static function recent(int $limit = 50): array
    {
        return static::db()->select('SELECT * FROM cron_runs ORDER BY id DESC LIMIT ' . max(1, $limit));
    }
}
