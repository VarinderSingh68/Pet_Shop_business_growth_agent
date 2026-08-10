<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Cache;
use App\Core\Request;
use App\Core\Response;

/**
 * Fixed-window rate limiter backed by the file cache. Route-declared as
 * middleware('throttle:5,1') = 5 attempts per 1 minute, keyed by IP + path.
 */
final class ThrottleMiddleware
{
    public function __construct(private readonly int $maxAttempts = 10, private readonly int $windowMinutes = 1)
    {
    }

    public function handle(Request $request, callable $next): mixed
    {
        $key = 'throttle:' . $request->ip() . ':' . $request->path();
        $window = Cache::get($key) ?? ['count' => 0, 'started' => time()];

        if (time() - $window['started'] > $this->windowMinutes * 60) {
            $window = ['count' => 0, 'started' => time()];
        }

        $window['count']++;

        if ($window['count'] > $this->maxAttempts) {
            $retryAfter = $this->windowMinutes * 60 - (time() - $window['started']);
            header('Retry-After: ' . max($retryAfter, 1));

            if ($request->wantsJson()) {
                Response::json(['message' => 'Too many requests. Please slow down and try again shortly.'], 429);
            }

            http_response_code(429);
            echo view('errors/429');
            exit;
        }

        Cache::put($key, $window, $this->windowMinutes * 60);

        return $next();
    }
}
