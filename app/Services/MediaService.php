<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Handles image uploads: validates the real MIME type (not the client-sent
 * one), re-encodes through GD to strip any embedded payload/EXIF, and stores
 * outside the web root. Files are only ever reachable via MediaController,
 * which re-checks the path before streaming.
 */
final class MediaService
{
    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    private const MAX_BYTES = 8 * 1024 * 1024;
    private const MAX_DIMENSION = 2400;

    /**
     * @param array{tmp_name: string, size: int, error: int} $file
     * @throws \RuntimeException on any validation failure
     */
    public function storeImage(array $file, string $subdir = 'products'): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload failed. Please try again.');
        }

        if ((int) $file['size'] > self::MAX_BYTES) {
            throw new \RuntimeException('Image is too large (max 8MB).');
        }

        $realMime = (string) mime_content_type($file['tmp_name']);
        if (!isset(self::ALLOWED[$realMime])) {
            throw new \RuntimeException('Only JPEG, PNG, or WebP images are allowed.');
        }

        $image = match ($realMime) {
            'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
            'image/png' => @imagecreatefrompng($file['tmp_name']),
            'image/webp' => @imagecreatefromwebp($file['tmp_name']),
        };

        if ($image === false) {
            throw new \RuntimeException('That file isn\'t a valid image.');
        }

        $width = imagesx($image);
        $height = imagesy($image);

        if ($width > self::MAX_DIMENSION || $height > self::MAX_DIMENSION) {
            $scale = min(self::MAX_DIMENSION / $width, self::MAX_DIMENSION / $height);
            $newWidth = (int) round($width * $scale);
            $newHeight = (int) round($height * $scale);

            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            $image = $resized;
        }

        $dir = storage_path('uploads/' . trim($subdir, '/'));
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $filename = bin2hex(random_bytes(16)) . '.jpg';
        $fullPath = $dir . '/' . $filename;

        // Re-encoded to JPEG regardless of source format: this is what
        // actually strips any polyglot payload or malformed metadata.
        // (No imagedestroy() call: GdImage resources are garbage-collected
        // automatically since PHP 8.0 — the function is now a deprecated no-op.)
        imagejpeg($image, $fullPath, 87);

        return trim($subdir, '/') . '/' . $filename;
    }

    public function delete(string $relativePath): void
    {
        $full = storage_path('uploads/' . ltrim($relativePath, '/'));
        if (is_file($full)) {
            unlink($full);
        }
    }
}
