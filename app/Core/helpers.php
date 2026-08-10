<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;

function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    if ($value === false || $value === null) {
        return $default;
    }

    return match (strtolower((string) $value)) {
        'true', '(true)' => true,
        'false', '(false)' => false,
        'null', '(null)' => null,
        'empty', '(empty)' => '',
        default => $value,
    };
}

function config(string $key, mixed $default = null): mixed
{
    static $config = null;

    if ($config === null) {
        $config = require dirname(__DIR__, 2) . '/config/config.php';
    }

    $segments = explode('.', $key);
    $value = $config;

    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

function base_path(string $path = ''): string
{
    return dirname(__DIR__, 2) . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

function storage_path(string $path = ''): string
{
    return base_path('storage' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
}

function public_path(string $path = ''): string
{
    return base_path('public' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
}

function asset(string $path): string
{
    return rtrim((string) env('APP_URL', ''), '/') . '/assets/' . ltrim($path, '/');
}

function media_url(?string $relativePath): ?string
{
    if ($relativePath === null || $relativePath === '') {
        return null;
    }
    return '/media/' . ltrim($relativePath, '/');
}

/**
 * The theme customiser lets the owner pick a primary accent from a curated
 * set rather than any arbitrary color — keeps the design system's contrast
 * ratios and "one loud color" rule intact instead of letting the palette
 * drift into something that clashes.
 */
function theme_accent_hex(): string
{
    return match (\App\Models\Setting::get('theme_accent', 'leash')) {
        'fern' => '#1f5f4a',
        'tennis' => '#b8860b', // darkened from the raw tennis yellow so paper-on-accent text stays AA
        default => '#e8492a',
    };
}

function url(string $path = ''): string
{
    return rtrim((string) env('APP_URL', ''), '/') . '/' . ltrim($path, '/');
}

function old(string $key, mixed $default = ''): mixed
{
    return Session::flashInput($key, $default);
}

function csrf_token(): string
{
    return Csrf::token();
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
}

function method_field(string $method): string
{
    return '<input type="hidden" name="_method" value="' . e($method) . '">';
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money(int $paise, ?string $symbol = null): string
{
    $symbol ??= (string) env('APP_CURRENCY_SYMBOL', '₹');
    return $symbol . number_format($paise / 100, 2);
}

function view(string $template, array $data = []): string
{
    return View::render($template, $data);
}

function auth(): Auth
{
    return App::auth();
}

function request(): Request
{
    return App::request();
}

function session(): Session
{
    return App::session();
}

function flash(string $type, string $message): void
{
    Session::flash($type, $message);
}

function redirect(string $path, int $status = 302): never
{
    header('Location: ' . url($path), true, $status);
    exit;
}

function back(): never
{
    $to = $_SERVER['HTTP_REFERER'] ?? '/';
    header('Location: ' . $to, true, 302);
    exit;
}

function abort(int $status, string $message = ''): never
{
    http_response_code($status);
    echo view("errors/{$status}", ['message' => $message]);
    exit;
}

function logger_channel(string $channel = 'app'): \App\Core\Logger
{
    return new \App\Core\Logger($channel);
}

function slugify(string $text): string
{
    $text = trim($text);
    $text = preg_replace('~[^\pL\d]+~u', '-', $text) ?? $text;
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text) ?: $text;
    $text = preg_replace('~[^-\w]+~', '', $text) ?? $text;
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text) ?? $text;

    return strtolower($text) ?: 'n-a';
}

function now(): string
{
    return (new DateTime('now', new DateTimeZone((string) env('APP_TIMEZONE', 'UTC'))))->format('Y-m-d H:i:s');
}

/**
 * Builds a query string from a filter set with overrides applied — used to
 * generate facet/pagination links that preserve the other active filters.
 *
 * @param array<string, mixed> $filters
 * @param array<string, mixed> $overrides
 */
function query_with(array $filters, array $overrides): string
{
    $merged = array_merge($filters, $overrides);
    $merged = array_filter($merged, static fn ($v) => $v !== null && $v !== '' && $v !== []);

    return '/shop?' . http_build_query($merged);
}

/**
 * Renders one of a small curated set of hand-drawn line icons, matching the
 * existing hand-rolled SVGs already used in the header (same stroke style:
 * 24x24 viewBox, currentColor stroke, round caps/joins, no fill).
 */
function icon(string $name, string $class = 'h-5 w-5'): string
{
    static $icons = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>',
        'box' => '<path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/>',
        'layers' => '<path d="M12 2l9 5-9 5-9-5 9-5z"/><path d="M3 12l9 5 9-5"/><path d="M3 17l9 5 9-5"/>',
        'cart' => '<path d="M3 3h2l.4 2M7 13h10l3-7H6.4M7 13L5.4 5M7 13l-1.7 4H17M9 21a1 1 0 100-2 1 1 0 000 2zM17 21a1 1 0 100-2 1 1 0 000 2z"/>',
        'users' => '<path d="M17 21v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'megaphone' => '<path d="M3 11v2a1 1 0 001 1h2l4 4V6l-4 4H4a1 1 0 00-1 1z"/><path d="M14 8a4 4 0 010 8"/><path d="M17 5a8 8 0 010 14"/>',
        'document' => '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h8M8 9h2"/>',
        'chart-bar' => '<path d="M4 20V10M12 20V4M20 20v-7"/>',
        'cog' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06A1.65 1.65 0 004.6 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06A1.65 1.65 0 009 4.6a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>',
        'terminal' => '<polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/>',
        'edit' => '<path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>',
        'trash' => '<polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>',
        'plus' => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        'check' => '<polyline points="20 6 9 17 4 12"/>',
        'x' => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
        'filter' => '<polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>',
        'download' => '<path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
        'upload' => '<path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>',
        'refresh' => '<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>',
        'eye' => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'star' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
        'tag' => '<path d="M20.59 13.41L11 3.83A2 2 0 009.59 3H4a1 1 0 00-1 1v5.59a2 2 0 00.59 1.41l9.58 9.59a2 2 0 002.82 0l4.6-4.6a2 2 0 000-2.82z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
        'truck' => '<rect x="1" y="6" width="15" height="12" rx="1"/><path d="M16 10h3l3 3v5h-6z"/><circle cx="6" cy="19.5" r="1.5"/><circle cx="17.5" cy="19.5" r="1.5"/>',
        'credit-card' => '<rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>',
        'shield-check' => '<path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4z"/><polyline points="9 12 11 14 15 10"/>',
        'alert-triangle' => '<path d="M12 2L1 21h22L12 2z"/><line x1="12" y1="9" x2="12" y2="14"/><circle cx="12" cy="17.3" r="0.9" fill="currentColor" stroke="none"/>',
        'info' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="11"/><circle cx="12" cy="7.7" r="0.9" fill="currentColor" stroke="none"/>',
        'bell' => '<path d="M18 8a6 6 0 00-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/>',
        'chevron-down' => '<polyline points="6 9 12 15 18 9"/>',
        'chevron-right' => '<polyline points="9 18 15 12 9 6"/>',
        'arrow-right' => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'mail' => '<rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="2 7 12 13 22 7"/>',
        'heart' => '<path d="M20.8 4.6a5.5 5.5 0 00-7.8 0L12 5.6l-1-1a5.5 5.5 0 00-7.8 7.8l1 1L12 21l7.8-7.8 1-1a5.5 5.5 0 000-7.8z"/>',
        'map-pin' => '<path d="M21 10c0 6-9 13-9 13s-9-7-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>',
        'gift' => '<rect x="2" y="8" width="20" height="4"/><rect x="4" y="12" width="16" height="9"/><path d="M12 8v13"/><path d="M12 8c-1.5-4-6-4-6-1s3 1 6 1zM12 8c1.5-4 6-4 6-1s-3 1-6 1z"/>',
        'life-buoy' => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/><line x1="4.93" y1="4.93" x2="9.17" y2="9.17"/><line x1="14.83" y1="14.83" x2="19.07" y2="19.07"/><line x1="14.83" y1="9.17" x2="19.07" y2="4.93"/><line x1="4.93" y1="19.07" x2="9.17" y2="14.83"/>',
        'paw' => '<circle cx="6" cy="9" r="2"/><circle cx="12" cy="6" r="2"/><circle cx="18" cy="9" r="2"/><path d="M12 12c-3 0-6 2.5-6 5.5S8.5 22 12 22s6-2 6-4.5S15 12 12 12z"/>',
        'feather' => '<path d="M20.24 12.24a6 6 0 00-8.49-8.49L5 10.5V19h8.5z"/><line x1="16" y1="8" x2="2" y2="22"/><line x1="17.5" y1="15" x2="9" y2="15"/>',
        'fish' => '<path d="M2 12s4-6 11-6c4 0 7 2.5 9 6-2 3.5-5 6-9 6-7 0-11-6-11-6z"/><circle cx="16" cy="10.3" r="0.8" fill="currentColor" stroke="none"/><path d="M2 12l-2-3.5M2 12l-2 3.5"/>',
    ];

    $inner = $icons[$name] ?? $icons['tag'];

    return sprintf(
        '<svg class="%s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%s</svg>',
        e($class),
        $inner
    );
}

/**
 * Renders a minimal inline sparkline from a series of numbers — used for
 * admin dashboard stat tiles. No charting library; the data set is always
 * small (a handful of days/weeks) so a hand-built polyline is enough.
 *
 * @param array<int, int|float> $values
 */
function sparkline(array $values, int $width = 120, int $height = 32, string $color = 'currentColor'): string
{
    $values = array_values($values);
    $count = count($values);

    if ($count < 2) {
        return '';
    }

    $min = min($values);
    $max = max($values);
    $range = ($max - $min) ?: 1;

    $points = [];
    foreach ($values as $i => $v) {
        $x = ($i / ($count - 1)) * $width;
        $y = $height - (($v - $min) / $range) * $height;
        $points[] = round($x, 1) . ',' . round($y, 1);
    }

    return sprintf(
        '<svg width="%d" height="%d" viewBox="0 0 %d %d" fill="none" aria-hidden="true"><polyline points="%s" stroke="%s" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        $width,
        $height,
        $width,
        $height,
        e(implode(' ', $points)),
        e($color)
    );
}
