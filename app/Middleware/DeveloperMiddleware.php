<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\App;
use App\Core\Request;

final class DeveloperMiddleware
{
    public function handle(Request $request, callable $next): mixed
    {
        if (!(bool) config('developer_tools')) {
            abort(404);
        }

        $auth = App::auth();

        if (!$auth->check() || !$auth->hasRole('developer', 'owner')) {
            abort(403, 'Developer tools are restricted to the developer role.');
        }

        return $next();
    }
}
