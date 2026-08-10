<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Cache;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

final class ApiTokenMiddleware
{
    public function handle(Request $request, callable $next): mixed
    {
        $token = $request->bearerToken();
        if ($token === null) {
            Response::json(['message' => 'Missing bearer token. Pass Authorization: Bearer <token>.'], 401);
        }

        $hash = hash('sha256', $token);
        $row = Database::instance()->selectOne(
            'SELECT * FROM api_tokens WHERE token_hash = :hash AND revoked_at IS NULL',
            ['hash' => $hash],
        );

        if ($row === null) {
            Response::json(['message' => 'Invalid or revoked API token.'], 401);
        }

        $limitKey = 'api_token_rate:' . $row['id'];
        $window = Cache::get($limitKey) ?? ['count' => 0, 'started' => time()];
        if (time() - $window['started'] > 60) {
            $window = ['count' => 0, 'started' => time()];
        }
        $window['count']++;
        Cache::put($limitKey, $window, 60);

        header('X-RateLimit-Limit: ' . $row['rate_limit_per_minute']);
        header('X-RateLimit-Remaining: ' . max(0, (int) $row['rate_limit_per_minute'] - $window['count']));

        if ($window['count'] > (int) $row['rate_limit_per_minute']) {
            Response::json(['message' => 'Rate limit exceeded for this token.'], 429);
        }

        Database::instance()->update('api_tokens', ['last_used_at' => now()], 'id = :id', ['id' => $row['id']]);

        return $next();
    }
}
