<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\App;
use App\Core\Request;

final class AuthMiddleware
{
    public function handle(Request $request, callable $next): mixed
    {
        if (!App::auth()->check()) {
            if ($request->wantsJson()) {
                \App\Core\Response::json(['message' => 'Unauthenticated.'], 401);
            }

            \App\Core\Session::put('_intended_url', $request->path());
            redirect('/account/login');
        }

        return $next();
    }
}
