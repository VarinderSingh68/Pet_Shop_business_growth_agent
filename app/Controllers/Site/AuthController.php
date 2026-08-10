<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\App;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Session;
use App\Core\Validator;
use App\Models\User;
use App\Services\CartService;
use App\Services\GoogleAuthService;
use App\Services\Growth\ReferralService;

final class AuthController extends Controller
{
    public function __construct(
        private readonly CartService $carts = new CartService(),
        private readonly ReferralService $referrals = new ReferralService(),
        private readonly GoogleAuthService $google = new GoogleAuthService(),
    ) {
    }

    public function showLogin(Request $request): void
    {
        $this->view('site/auth/login', [
            'title' => 'Sign in',
            'googleEnabled' => $this->google->isConfigured(),
        ]);
    }

    public function login(Request $request): void
    {
        $email = (string) $request->input('email', '');
        $password = (string) $request->input('password', '');

        if (!App::auth()->attempt($email, $password)) {
            flash('error', 'Those details don\'t match an account. Check your email and password and try again.');
            Session::flashInputData(['email' => $email]);
            back();
        }

        $this->afterLogin('login');
    }

    public function showRegister(Request $request): void
    {
        $this->view('site/auth/register', [
            'title' => 'Create your account',
            'googleEnabled' => $this->google->isConfigured(),
        ]);
    }

    public function register(Request $request): void
    {
        $data = $request->only(['name', 'email', 'phone', 'password']);

        $validator = Validator::make($data, [
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:191|unique:users,email',
            'phone' => 'max:20',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            flash('error', $validator->firstError() ?? 'Please check the form and try again.');
            Session::flashInputData($request->only(['name', 'email', 'phone']));
            back();
        }

        $customerRole = Database::instance()->selectOne("SELECT id FROM roles WHERE slug = 'customer'");
        if ($customerRole === null) {
            flash('error', 'Registration is temporarily unavailable. Please try again shortly.');
            back();
        }

        $userId = User::create([
            'role_id' => $customerRole['id'],
            'name' => (string) $data['name'],
            'email' => strtolower(trim((string) $data['email'])),
            'phone' => !empty($data['phone']) ? $data['phone'] : null,
            'password_hash' => User::hashPassword((string) $data['password']),
            'is_active' => 1,
        ]);

        App::auth()->login(User::find($userId));
        $this->afterLogin('register', isNewAccount: true);
    }

    public function logout(Request $request): void
    {
        $this->logActivity('logout');
        App::auth()->logout();
        $this->redirect('/');
    }

    /**
     * Redirects to Google's consent screen. A random `state` value is
     * stashed in the session and re-checked on callback — without it, an
     * attacker could trick a victim into completing an OAuth flow the
     * attacker initiated, linking the attacker's Google identity to the
     * victim's session (a login CSRF).
     */
    public function googleRedirect(Request $request): void
    {
        if (!$this->google->isConfigured()) {
            abort(404);
        }

        $state = bin2hex(random_bytes(16));
        Session::put('_google_oauth_state', $state);

        // Response::redirect(), not $this->redirect() — the latter runs the
        // target through url() and prepends our own APP_URL, which would
        // mangle this external Google URL into garbage.
        \App\Core\Response::redirect($this->google->redirectUrl($state));
    }

    public function googleCallback(Request $request): void
    {
        if (!$this->google->isConfigured()) {
            abort(404);
        }

        $state = (string) $request->query('state', '');
        $expectedState = Session::get('_google_oauth_state');
        Session::forget('_google_oauth_state');

        if ($state === '' || $expectedState === null || !hash_equals((string) $expectedState, $state)) {
            flash('error', 'That sign-in link expired. Please try again.');
            $this->redirect('/account/login');
        }

        $code = (string) $request->query('code', '');
        if ($code === '') {
            flash('error', 'Google sign-in was cancelled.');
            $this->redirect('/account/login');
        }

        try {
            $profile = $this->google->fetchProfile($code);
        } catch (\RuntimeException $e) {
            logger_channel('auth')->warning('Google sign-in failed', ['error' => $e->getMessage()]);
            flash('error', "Google sign-in didn't work. Please try again or use your email and password.");
            $this->redirect('/account/login');
        }

        $user = User::findByGoogleId($profile['google_id']);
        $isNewAccount = false;

        if ($user === null) {
            $user = User::findByEmail($profile['email']);

            if ($user !== null) {
                // Existing password-based account with a matching, Google-verified
                // email — link it rather than creating a duplicate account.
                User::updateWhere((int) $user['id'], ['google_id' => $profile['google_id']]);
                $user = User::find((int) $user['id']);
            } else {
                $customerRole = Database::instance()->selectOne("SELECT id FROM roles WHERE slug = 'customer'");
                $userId = User::create([
                    'role_id' => $customerRole['id'],
                    'name' => $profile['name'],
                    'email' => $profile['email'],
                    'google_id' => $profile['google_id'],
                    'password_hash' => null,
                    'is_active' => 1,
                    'email_verified_at' => now(),
                ]);
                $user = User::find($userId);
                $isNewAccount = true;
            }
        }

        if ((int) $user['is_active'] === 0) {
            flash('error', 'This account is inactive. Contact support for help.');
            $this->redirect('/account/login');
        }

        App::auth()->login($user);
        $this->afterLogin('login.google', $isNewAccount);
    }

    private function afterLogin(string $action, bool $isNewAccount = false): void
    {
        $userId = (int) App::auth()->id();
        $this->carts->mergeGuestCartIntoUser(request(), $userId);
        $this->logActivity($action);

        if ($isNewAccount) {
            $refCode = $_COOKIE['petshop_ref'] ?? null;
            if (is_string($refCode) && $refCode !== '') {
                $this->referrals->recordSignup($refCode, $userId);
            }
            $this->sendWelcomeEmail(App::auth()->user());
            flash('success', 'Welcome to Happy Tails! Your account is ready.');
        }

        $intended = Session::get('_intended_url', '/account');
        Session::forget('_intended_url');
        $this->redirect(is_string($intended) ? $intended : '/account');
    }

    /** @param array{name: string, email: string} $user */
    private function sendWelcomeEmail(array $user): void
    {
        $shopUrl = url('/shop');
        $accentColor = theme_accent_hex();
        $firstName = e(explode(' ', $user['name'])[0]);

        $html = <<<HTML
        <div style="font-family: Arial, Helvetica, sans-serif; max-width: 480px; margin: 0 auto; color: #12141c;">
            <h1 style="font-size: 22px; margin-bottom: 4px;">Welcome, {$firstName}! 🐾</h1>
            <p style="font-size: 15px; line-height: 1.6;">
                Your Happy Tails account is ready. Browse food, toys, grooming, and vet-care
                supplies, or book a service appointment whenever your pet needs one.
            </p>
            <p style="margin: 24px 0;">
                <a href="{$shopUrl}" style="background: {$accentColor}; color: #f6f7f2; text-decoration: none; font-weight: 600; padding: 12px 24px; display: inline-block;">
                    Start shopping
                </a>
            </p>
            <p style="font-size: 13px; color: #6b6f76;">
                Questions? Just reply to this email — we're happy to help.
            </p>
        </div>
        HTML;

        Mailer::send($user['email'], $user['name'], 'Welcome to Happy Tails!', $html);
    }

    private function logActivity(string $action): void
    {
        Database::instance()->insert('activity_logs', [
            'user_id' => App::auth()->id(),
            'action' => $action,
            'description' => ucfirst(str_replace('.', ' via ', $action)) . ' from storefront',
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
