<?php

declare(strict_types=1);

namespace App\Core;

use Dotenv\Dotenv;
use Throwable;

final class App
{
    private static ?Auth $auth = null;
    private static ?Request $request = null;

    public static function bootstrap(bool $cli = false): void
    {
        $root = dirname(__DIR__, 2);

        if (is_file($root . '/.env')) {
            Dotenv::createImmutable($root)->load();
        }

        date_default_timezone_set((string) env('APP_TIMEZONE', 'Asia/Kolkata'));

        error_reporting(E_ALL);
        ini_set('display_errors', ((bool) env('APP_DEBUG', false)) ? '1' : '0');

        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);

        if (!$cli) {
            Session::start();
            self::securityHeaders();
            self::captureReferralCode();
        }
    }

    /**
     * A `?ref=CODE` on any page is remembered for 30 days so it's still
     * there by the time the visitor actually registers, without forcing
     * the referral link to point straight at the signup page.
     */
    private static function captureReferralCode(): void
    {
        $ref = $_GET['ref'] ?? null;
        if (is_string($ref) && $ref !== '' && preg_match('/^[A-Z0-9]{4,20}$/i', $ref) === 1) {
            setcookie('petshop_ref', strtoupper($ref), [
                'expires' => time() + 60 * 60 * 24 * 30,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure' => (bool) config('session.secure'),
            ]);
        }
    }

    public static function auth(): Auth
    {
        return self::$auth ??= new Auth();
    }

    public static function request(): Request
    {
        return self::$request ??= new Request();
    }

    public static function session(): Session
    {
        return new Session();
    }

    public static function handleException(Throwable $e): void
    {
        logger_channel('error')->critical($e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        if (PHP_SAPI === 'cli') {
            fwrite(STDERR, $e::class . ': ' . $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine() . "\n");
            exit(1);
        }

        http_response_code(500);

        if ((bool) config('app.debug')) {
            echo '<pre style="padding:2rem;font-family:monospace;white-space:pre-wrap;">'
                . e($e::class . ': ' . $e->getMessage() . "\n\n" . $e->getTraceAsString())
                . '</pre>';
            return;
        }

        echo view('errors/500');
    }

    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    private static function securityHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        header(
            "Content-Security-Policy: default-src 'self'; "
            . "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://checkout.razorpay.com; "
            . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
            . "font-src 'self' https://fonts.gstatic.com; "
            . "img-src 'self' data: blob: https://*.razorpay.com; "
            . "frame-src 'self' https://api.razorpay.com https://checkout.razorpay.com; "
            . "connect-src 'self' https://api.razorpay.com https://lumberjack.razorpay.com;"
        );

        if ((bool) config('session.secure')) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}
