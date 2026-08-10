<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;

final class CsrfMiddleware
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request, callable $next): mixed
    {
        if (in_array($request->method(), self::SAFE_METHODS, true)) {
            return $next();
        }

        $token = $request->input('_token') ?? $request->header('X-CSRF-Token');

        if (!Csrf::verify(is_string($token) ? $token : null)) {
            if ($request->wantsJson()) {
                Response::json(['message' => 'Invalid or expired security token. Please refresh and try again.'], 419);
            }

            flash('error', 'Your session expired. Please try again.');
            back();
        }

        return $next();
    }
}
