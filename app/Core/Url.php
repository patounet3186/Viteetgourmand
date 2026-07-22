<?php

declare(strict_types=1);

namespace App\Core;

final class Url
{
    public static function base(): string
    {
        $configuredUrl = trim((string) (getenv('APP_URL') ?: ''));
        if ($configuredUrl !== '') {
            return rtrim($configuredUrl, '/');
        }

        $https = ($_SERVER['HTTPS'] ?? '') !== ''
            && ($_SERVER['HTTPS'] ?? '') !== 'off';
        $scheme = $https ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptDirectory = str_replace(
            '\\',
            '/',
            dirname($_SERVER['SCRIPT_NAME'] ?? '/')
        );

        return $scheme . '://' . $host . rtrim($scriptDirectory, '/.');
    }

    /** @param array<string, scalar> $params */
    public static function page(string $page, array $params = []): string
    {
        return self::base() . '/?' . http_build_query(['page' => $page] + $params);
    }

    public static function asset(string $path): string
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');
        if (str_starts_with($normalizedPath, 'public/')) {
            $normalizedPath = substr($normalizedPath, 7);
        }

        return self::base() . '/' . $normalizedPath;
    }
}
