<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Router;
use App\Models\CronRun;
use App\Services\ApiTokenService;
use App\Services\BackupService;
use App\Services\GrowthEngine;
use App\Services\HealthCheckService;

final class DeveloperController extends Controller
{
    public function index(Request $request): void
    {
        $db = Database::instance();

        $this->view('admin/dev/index', [
            'title' => 'Developer tools',
            'migrationCount' => (int) ($db->selectOne('SELECT COUNT(*) c FROM migrations')['c'] ?? 0),
            'lastCron' => CronRun::recent(1)[0] ?? null,
            'slowQueryCount' => (int) ($db->selectOne('SELECT COUNT(*) c FROM slow_queries WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)')['c'] ?? 0),
            'failedWebhookCount' => (int) ($db->selectOne("SELECT COUNT(*) c FROM webhook_deliveries WHERE outcome = 'failed'")['c'] ?? 0),
        ]);
    }

    // --- Migrations -------------------------------------------------------

    public function migrations(Request $request): void
    {
        $output = $this->runMigrateCommand('status');
        $this->view('admin/dev/migrations', ['title' => 'Migrations', 'output' => $output]);
    }

    public function runMigration(Request $request): void
    {
        $action = (string) $request->input('action', 'status');
        if (!in_array($action, ['up', 'down', 'fresh', 'fresh-seed'], true)) {
            abort(404);
        }

        $args = $action === 'fresh-seed' ? 'fresh --seed' : $action;
        $output = $this->runMigrateCommand($args);

        flash('success', 'Migration command finished — see output below.');
        $this->view('admin/dev/migrations', ['title' => 'Migrations', 'output' => $output]);
    }

    private function runMigrateCommand(string $args): string
    {
        $php = escapeshellarg(PHP_BINARY);
        $script = escapeshellarg(base_path('database/migrate.php'));
        $output = shell_exec("{$php} {$script} {$args} 2>&1");

        return $output ?? 'No output (command may have failed to start).';
    }

    // --- Logs -------------------------------------------------------------

    public function logs(Request $request): void
    {
        $files = glob(storage_path('logs') . '/*.log') ?: [];
        rsort($files);
        $files = array_map('basename', $files);

        $selected = (string) $request->query('file', $files[0] ?? '');
        $level = (string) $request->query('level', '');
        $search = (string) $request->query('q', '');

        $entries = [];
        if ($selected !== '' && in_array($selected, $files, true)) {
            $entries = $this->parseLogFile(storage_path('logs/' . $selected), $level, $search);
        }

        $this->view('admin/dev/logs', [
            'title' => 'Logs',
            'files' => $files,
            'selected' => $selected,
            'level' => $level,
            'search' => $search,
            'entries' => array_slice(array_reverse($entries), 0, 200),
        ]);
    }

    private function parseLogFile(string $path, string $level, string $search): array
    {
        if (!is_file($path)) {
            return [];
        }

        $entries = [];
        $lines = file($path, FILE_IGNORE_NEW_LINES) ?: [];

        foreach ($lines as $line) {
            if (!preg_match('/^\[(.*?)\] (\S+)\.(\w+): (.*)$/', $line, $m)) {
                continue;
            }
            [, $timestamp, $channel, $lineLevel, $rest] = $m;

            if ($level !== '' && strtoupper($level) !== $lineLevel) {
                continue;
            }
            if ($search !== '' && !str_contains(strtolower($line), strtolower($search))) {
                continue;
            }

            $jsonStart = strpos($rest, '{');
            $message = $jsonStart !== false ? trim(substr($rest, 0, $jsonStart)) : $rest;
            $context = $jsonStart !== false ? json_decode(substr($rest, $jsonStart), true) : null;

            $entries[] = [
                'timestamp' => $timestamp,
                'channel' => $channel,
                'level' => $lineLevel,
                'message' => $message,
                'context' => $context,
            ];
        }

        return $entries;
    }

    // --- Query profiler -----------------------------------------------

    public function queries(Request $request): void
    {
        $this->view('admin/dev/queries', [
            'title' => 'Query profiler',
            'queries' => Database::instance()->select('SELECT * FROM slow_queries ORDER BY created_at DESC LIMIT 100'),
        ]);
    }

    // --- Cron monitor -------------------------------------------------

    public function cron(Request $request): void
    {
        $this->view('admin/dev/cron', [
            'title' => 'Cron monitor',
            'runs' => CronRun::recent(50),
        ]);
    }

    public function runCron(Request $request): void
    {
        $results = (new GrowthEngine())->runAll();
        flash('success', 'Growth Agent run complete — ' . count($results) . ' job(s) executed.');
        $this->redirect('/admin/dev/cron');
    }

    // --- Mail log -------------------------------------------------------

    public function mail(Request $request): void
    {
        $this->view('admin/dev/mail', [
            'title' => 'Mail log',
            'logs' => Database::instance()->select('SELECT * FROM mail_logs ORDER BY id DESC LIMIT 100'),
        ]);
    }

    public function mailPreview(Request $request, string $id): void
    {
        $log = Database::instance()->selectOne('SELECT * FROM mail_logs WHERE id = :id', ['id' => $id]);
        if ($log === null) {
            abort(404);
        }

        header('Content-Type: text/html; charset=utf-8');
        header('Content-Security-Policy: default-src \'none\'; style-src \'unsafe-inline\';');
        echo $log['body_html'];
        exit;
    }

    // --- Webhooks -------------------------------------------------------

    public function webhooks(Request $request): void
    {
        $this->view('admin/dev/webhooks', [
            'title' => 'Webhooks',
            'deliveries' => Database::instance()->select('SELECT * FROM webhook_deliveries ORDER BY id DESC LIMIT 100'),
        ]);
    }

    /**
     * A true replay: recomputes a valid signature for the stored payload
     * using our own configured webhook secret (the same HMAC Razorpay would
     * have produced) and re-runs it through the real handler, which is
     * idempotent — so replaying an already-processed event is a safe no-op.
     */
    public function replayWebhook(Request $request, string $id): void
    {
        $delivery = Database::instance()->selectOne('SELECT * FROM webhook_deliveries WHERE id = :id', ['id' => $id]);
        if ($delivery === null) {
            abort(404);
        }

        $secret = (string) config('payment.razorpay_webhook_secret');
        if ($secret === '') {
            flash('error', 'Cannot replay — no webhook secret configured.');
            $this->redirect('/admin/dev/webhooks');
        }

        $signature = hash_hmac('sha256', $delivery['payload'], $secret);

        try {
            (new \App\Services\PaymentService())->handleWebhook($delivery['payload'], $signature);
            flash('success', 'Replayed successfully.');
        } catch (\Throwable $e) {
            flash('error', 'Replay failed: ' . $e->getMessage());
        }

        $this->redirect('/admin/dev/webhooks');
    }

    // --- API explorer ---------------------------------------------------

    public function apiExplorer(Request $request): void
    {
        $router = new Router();
        require base_path('routes/api.php');

        $this->view('admin/dev/api-explorer', [
            'title' => 'API explorer',
            'routes' => $router->routesUnder('/api/v1'),
        ]);
    }

    public function apiTokens(Request $request): void
    {
        $this->view('admin/dev/api-tokens', [
            'title' => 'API tokens',
            'tokens' => Database::instance()->select(
                'SELECT t.*, u.name AS user_name FROM api_tokens t JOIN users u ON u.id = t.user_id ORDER BY t.id DESC',
            ),
        ]);
    }

    public function storeApiToken(Request $request): void
    {
        $name = (string) $request->input('name', '');
        $rateLimit = max(1, (int) $request->input('rate_limit', 60));

        if ($name === '') {
            $this->redirect('/admin/dev/api-tokens');
        }

        $result = (new ApiTokenService())->create((int) App::auth()->id(), $name, $rateLimit);

        flash('success', 'Token created — copy it now, it will not be shown again: ' . $result['token']);
        $this->redirect('/admin/dev/api-tokens');
    }

    public function revokeApiToken(Request $request, string $id): void
    {
        (new ApiTokenService())->revoke((int) $id);
        flash('success', 'Token revoked.');
        $this->redirect('/admin/dev/api-tokens');
    }

    // --- Feature flags --------------------------------------------------

    public function featureFlags(Request $request): void
    {
        $this->view('admin/dev/feature-flags', [
            'title' => 'Feature flags',
            'flags' => Database::instance()->select('SELECT * FROM feature_flags ORDER BY `key`'),
        ]);
    }

    public function storeFeatureFlag(Request $request): void
    {
        $key = (string) $request->input('key', '');
        $description = (string) $request->input('description', '');

        if ($key !== '') {
            Database::instance()->insert('feature_flags', [
                'key' => $key, 'description' => $description !== '' ? $description : null,
                'is_enabled' => 0, 'updated_at' => now(),
            ]);
        }

        $this->redirect('/admin/dev/feature-flags');
    }

    public function toggleFeatureFlag(Request $request, string $id): void
    {
        $flag = Database::instance()->selectOne('SELECT is_enabled FROM feature_flags WHERE id = :id', ['id' => $id]);
        if ($flag !== null) {
            Database::instance()->update('feature_flags', ['is_enabled' => $flag['is_enabled'] ? 0 : 1, 'updated_at' => now()], 'id = :id', ['id' => $id]);
        }
        $this->redirect('/admin/dev/feature-flags');
    }

    // --- Backups ----------------------------------------------------------

    public function backups(Request $request): void
    {
        $this->view('admin/dev/backups', [
            'title' => 'Backups',
            'backups' => (new BackupService())->listBackups(),
        ]);
    }

    public function createBackup(Request $request): void
    {
        $filename = (new BackupService())->createBackup();
        flash('success', "Backup created: {$filename}");
        $this->redirect('/admin/dev/backups');
    }

    public function downloadBackup(Request $request, string $filename): void
    {
        try {
            $path = (new BackupService())->pathFor($filename);
        } catch (\RuntimeException) {
            abort(404);
        }

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        readfile($path);
        exit;
    }

    public function restoreBackup(Request $request, string $filename): void
    {
        try {
            (new BackupService())->restore($filename);
            flash('success', "Restored from {$filename}.");
        } catch (\Throwable $e) {
            flash('error', 'Restore failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/dev/backups');
    }

    // --- Health -----------------------------------------------------------

    public function health(Request $request): void
    {
        $this->view('admin/dev/health', [
            'title' => 'Health check',
            'checks' => (new HealthCheckService())->run(),
        ]);
    }

    // --- Demo data reset --------------------------------------------------

    public function resetDemoData(Request $request): void
    {
        if (!(bool) config('app.debug')) {
            abort(403, 'Demo data reset is only available with APP_DEBUG=true.');
        }

        $output = $this->runMigrateCommand('fresh --seed');
        flash('success', 'Demo data reset complete.');
        $this->view('admin/dev/migrations', ['title' => 'Migrations', 'output' => $output]);
    }
}
