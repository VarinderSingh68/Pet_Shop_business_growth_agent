<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;

final class AuthController extends Controller
{
    public function showLogin(Request $request): void
    {
        $this->view('admin/auth/login', ['title' => 'Admin sign in']);
    }

    public function login(Request $request): void
    {
        $email = (string) $request->input('email', '');
        $password = (string) $request->input('password', '');

        $auth = App::auth();

        if (!$auth->attempt($email, $password)) {
            flash('error', 'Those details don\'t match a staff account.');
            Session::flashInputData(['email' => $email]);
            back();
        }

        if (!$auth->hasRole('owner', 'manager', 'staff', 'developer')) {
            $auth->logout();
            flash('error', 'This account doesn\'t have admin access.');
            back();
        }

        Database::instance()->insert('activity_logs', [
            'user_id' => $auth->id(),
            'action' => 'admin.login',
            'description' => 'Signed in to the admin panel',
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        $intended = Session::get('_intended_url', '/admin');
        Session::forget('_intended_url');
        $this->redirect(is_string($intended) ? $intended : '/admin');
    }

    public function logout(Request $request): void
    {
        Database::instance()->insert('activity_logs', [
            'user_id' => App::auth()->id(),
            'action' => 'admin.logout',
            'description' => 'Signed out of the admin panel',
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        App::auth()->logout();
        $this->redirect('/admin/login');
    }
}
