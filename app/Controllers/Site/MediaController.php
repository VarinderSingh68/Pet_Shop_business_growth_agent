<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Core\Request;

final class MediaController extends Controller
{
    public function show(Request $request, string $subdir, string $filename): void
    {
        $safePath = str_replace(['..', "\0", '/', '\\'], '', $subdir) . '/' . str_replace(['..', "\0", '/', '\\'], '', $filename);
        $uploadsRoot = realpath(storage_path('uploads'));
        $fullPath = $uploadsRoot . '/' . $safePath;

        if ($uploadsRoot === false || !str_starts_with((string) realpath($fullPath), $uploadsRoot) || !is_file($fullPath)) {
            abort(404);
        }

        $mime = mime_content_type($fullPath) ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=31536000, immutable');
        readfile($fullPath);
        exit;
    }
}
