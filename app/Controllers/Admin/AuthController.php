<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Core\Totp;
use App\Models\User;

final class AuthController extends Controller
{
    private const TWO_FACTOR_PENDING_KEY = '_2fa_pending_user_id';

    public function showLogin(Request $request): void
    {
        $this->view('admin/auth/login', ['title' => 'Admin sign in']);
    }

    public function login(Request $request): void
    {
        $email = (string) $request->input('email', '');
        $password = (string) $request->input('password', '');

        $auth = App::auth();
        $user = $auth->validateCredentials($email, $password);

        if ($user === null || !$this->hasAdminAccess((int) $user['role_id'])) {
            flash('error', "Those details don't match a staff account.");
            Session::flashInputData(['email' => $email]);
            back();
        }

        if ((int) $user['two_factor_enabled'] === 1) {
            Session::put(self::TWO_FACTOR_PENDING_KEY, (int) $user['id']);
            $this->redirect('/admin/login/2fa');
        }

        $this->completeLogin($auth, $user, $request);
    }

    public function showTwoFactorChallenge(Request $request): void
    {
        if (Session::get(self::TWO_FACTOR_PENDING_KEY) === null) {
            $this->redirect('/admin/login');
        }

        $this->view('admin/auth/two-factor', ['title' => 'Verification code']);
    }

    public function verifyTwoFactor(Request $request): void
    {
        $userId = Session::get(self::TWO_FACTOR_PENDING_KEY);
        if ($userId === null) {
            $this->redirect('/admin/login');
        }

        $user = User::find((int) $userId);
        $code = (string) $request->input('code', '');

        if ($user === null || !Totp::verify((string) $user['two_factor_secret'], $code)) {
            flash('error', 'That code is incorrect or has expired.');
            back();
        }

        Session::forget(self::TWO_FACTOR_PENDING_KEY);
        $this->completeLogin(App::auth(), $user, $request);
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

    private function hasAdminAccess(int $roleId): bool
    {
        $role = Database::instance()->selectOne('SELECT slug FROM roles WHERE id = :id', ['id' => $roleId]);

        return $role !== null && in_array($role['slug'], ['owner', 'manager', 'staff', 'developer'], true);
    }

    /** @param array<string, mixed> $user */
    private function completeLogin(Auth $auth, array $user, Request $request): void
    {
        $auth->login($user);

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
}
