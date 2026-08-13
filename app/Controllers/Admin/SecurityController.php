<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Totp;
use App\Models\User;

final class SecurityController extends Controller
{
    public function index(Request $request): void
    {
        $user = auth()->user();

        $this->view('admin/security/index', [
            'title' => 'Security',
            'user' => $user,
            'provisioningUri' => $user['two_factor_enabled'] || $user['two_factor_secret'] === null
                ? null
                : Totp::provisioningUri((string) $user['two_factor_secret'], (string) $user['email']),
            'secretDisplay' => $user['two_factor_secret'] !== null
                ? Totp::formatSecretForDisplay((string) $user['two_factor_secret'])
                : null,
        ]);
    }

    public function setup(Request $request): void
    {
        $user = auth()->user();
        if ((int) $user['two_factor_enabled'] === 1) {
            $this->redirect('/admin/security');
        }

        User::updateWhere((int) $user['id'], ['two_factor_secret' => Totp::generateSecret()]);

        flash('success', 'Scan or enter the new key below, then confirm with a code to finish enabling it.');
        $this->redirect('/admin/security');
    }

    public function confirm(Request $request): void
    {
        $user = auth()->user();
        $code = (string) $request->input('code', '');

        if ($user['two_factor_secret'] === null || !Totp::verify((string) $user['two_factor_secret'], $code)) {
            flash('error', 'That code is incorrect or has expired. Try again.');
            back();
        }

        User::updateWhere((int) $user['id'], ['two_factor_enabled' => 1]);
        $this->logSecurityEvent((int) $user['id'], 'security.2fa_enabled', 'Enabled two-factor authentication', $request);

        flash('success', 'Two-factor authentication is now on for your account.');
        $this->redirect('/admin/security');
    }

    public function disable(Request $request): void
    {
        $user = auth()->user();
        $password = (string) $request->input('password', '');

        if (!User::verifyPassword($user, $password)) {
            flash('error', 'That password is incorrect.');
            back();
        }

        User::updateWhere((int) $user['id'], ['two_factor_enabled' => 0, 'two_factor_secret' => null]);
        $this->logSecurityEvent((int) $user['id'], 'security.2fa_disabled', 'Disabled two-factor authentication', $request);

        flash('success', 'Two-factor authentication turned off.');
        $this->redirect('/admin/security');
    }

    private function logSecurityEvent(int $userId, string $action, string $description, Request $request): void
    {
        Database::instance()->insert('activity_logs', [
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);
    }
}
