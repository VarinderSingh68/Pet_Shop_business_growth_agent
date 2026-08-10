<?php

declare(strict_types=1);

/**
 * Router for PHP's built-in dev server only (php -S ... public/router.php).
 * On real hosting, public/.htaccess does this same job via mod_rewrite —
 * this file is never used there.
 */

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$file = __DIR__ . $path;

if ($path !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';
