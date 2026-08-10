<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\App;
use App\Core\Request;

final class GuestMiddleware
{
    public function handle(Request $request, callable $next): mixed
    {
        if (App::auth()->check()) {
            redirect('/account');
        }

        return $next();
    }
}
