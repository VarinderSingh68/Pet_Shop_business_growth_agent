<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\App;
use App\Core\Database;
use App\Core\Request;

final class MaintenanceMiddleware
{
    public function handle(Request $request, callable $next): mixed
    {
        $row = Database::instance()->selectOne(
            "SELECT `value` FROM settings WHERE `key` = 'maintenance_mode' LIMIT 1",
        );

        $enabled = $row !== null && $row['value'] === '1';

        if ($enabled && !App::auth()->hasRole('owner', 'developer')) {
            http_response_code(503);
            header('Retry-After: 3600');
            echo view('errors/503');
            exit;
        }

        return $next();
    }
}
