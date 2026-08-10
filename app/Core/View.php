<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    private static ?string $layout = null;
    /** @var array<string, string> */
    private static array $sections = [];
    private static ?string $currentSection = null;

    public static function render(string $template, array $data = []): string
    {
        self::$layout = null;
        self::$sections = [];

        $content = self::renderFile($template, $data);

        if (self::$layout !== null) {
            // A template using start('content')/stop() already populated the
            // section explicitly; only fall back to the raw buffer otherwise.
            self::$sections['content'] ??= $content;
            $content = self::renderFile(self::$layout, $data);
        }

        return $content;
    }

    private static function renderFile(string $template, array $data): string
    {
        $path = base_path('app/Views/' . $template . '.php');

        if (!is_file($path)) {
            throw new \RuntimeException("View [{$template}] not found at {$path}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $path;
        return (string) ob_get_clean();
    }

    public static function extend(string $layout): void
    {
        self::$layout = $layout;
    }

    public static function start(string $name): void
    {
        self::$currentSection = $name;
        ob_start();
    }

    public static function stop(): void
    {
        if (self::$currentSection === null) {
            return;
        }
        self::$sections[self::$currentSection] = (string) ob_get_clean();
        self::$currentSection = null;
    }

    public static function section(string $name, string $default = ''): string
    {
        return self::$sections[$name] ?? $default;
    }

    public static function include(string $component, array $data = []): void
    {
        echo self::renderFile($component, $data);
    }
}
