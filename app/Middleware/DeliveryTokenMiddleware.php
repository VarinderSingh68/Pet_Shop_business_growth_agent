<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Cache;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

/**
 * Same bearer-token mechanics as ApiTokenMiddleware (shared `api_tokens`
 * table, per-token rate limit, X-RateLimit-* headers), but additionally
 * requires the token's owner to hold the 'delivery' role, and hands the
 * resolved rider's user id to the controller via the request attribute
 * bag rather than a shared static — see DeliveryController.
 */
final class DeliveryTokenMiddleware
{
    public function handle(Request $request, callable $next): mixed
    {
        $token = $request->bearerToken();
        if ($token === null) {
            Response::json(['message' => 'Missing bearer token. Pass Authorization: Bearer <token>.'], 401);
        }

        $hash = hash('sha256', $token);
        $row = Database::instance()->selectOne(
            'SELECT t.id, t.rate_limit_per_minute, u.id AS user_id, u.name AS user_name, r.slug AS role_slug
             FROM api_tokens t
             JOIN users u ON u.id = t.user_id
             JOIN roles r ON r.id = u.role_id
             WHERE t.token_hash = :hash AND t.revoked_at IS NULL
               AND u.deleted_at IS NULL AND u.is_active = 1',
            ['hash' => $hash],
        );

        if ($row === null || $row['role_slug'] !== 'delivery') {
            Response::json(['message' => 'Invalid or revoked delivery token.'], 401);
        }

        $limitKey = 'delivery_token_rate:' . $row['id'];
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

        $request->setAttribute('delivery_partner_id', (int) $row['user_id']);
        $request->setAttribute('delivery_partner_name', $row['user_name']);

        return $next();
    }
}
