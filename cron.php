<?php

declare(strict_types=1);

/**
 * Growth Agent worker. Intended to run every 15 minutes via cron, e.g.
 * (star)/15 (star) (star) (star) (star) php /path/to/petshop/cron.php >> /path/to/petshop/storage/logs/cron.log 2>&1
 * — see README.md for the literal crontab line.
 *
 * Every job is idempotent (re-running it doesn't double-send anything —
 * each checks notifications/segments/scores before acting) so a missed or
 * overlapping run is harmless.
 */

use App\Core\App;
use App\Services\GrowthEngine;

require __DIR__ . '/vendor/autoload.php';

App::bootstrap(cli: true);

$start = microtime(true);
echo '[' . now() . "] Growth Agent run starting\n";

$results = (new GrowthEngine())->runAll();

foreach ($results as $result) {
    $marker = $result['outcome'] === 'success' ? 'OK' : 'FAIL';
    echo "  [{$marker}] {$result['job']}: {$result['summary']}\n";
}

$elapsed = round(microtime(true) - $start, 2);
echo '[' . now() . "] Growth Agent run finished in {$elapsed}s\n";
