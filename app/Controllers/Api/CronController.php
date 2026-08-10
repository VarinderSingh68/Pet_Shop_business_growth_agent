<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\GrowthEngine;

final class CronController extends Controller
{
    /**
     * HTTP-triggered alternative to `php cron.php` for hosts where a native
     * scheduled/cron service isn't free (e.g. Render's free tier) — an
     * external scheduler (cron-job.org, GitHub Actions, etc.) hits this on a
     * timer instead. Guarded by CRON_SECRET since it has no session/role
     * auth to fall back on; blocked entirely if that secret isn't set.
     */
    public function run(Request $request): void
    {
        $secret = (string) config('cron_secret');
        $provided = (string) $request->query('secret', '');

        if ($secret === '' || !hash_equals($secret, $provided)) {
            abort(403, 'Forbidden');
        }

        $results = (new GrowthEngine())->runAll();

        Response::json([
            'ok' => true,
            'jobs_run' => count($results),
            'ran_at' => now(),
        ]);
    }
}
