<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\App;
use App\Core\Request;

final class AdminMiddleware
{
    private const STAFF_ROLES = ['owner', 'manager', 'staff', 'developer'];

    public function handle(Request $request, callable $next): mixed
    {
        $auth = App::auth();

        if (!$auth->check()) {
            \App\Core\Session::put('_intended_url', $request->path());
            redirect('/admin/login');
        }

        if (!$auth->hasRole(...self::STAFF_ROLES)) {
            abort(403, "You don't have permission to access the admin panel.");
        }

        return $next();
    }
}
