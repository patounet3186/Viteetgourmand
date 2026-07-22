<?php

declare(strict_types=1);

namespace App\Core;

final class Environment
{
    public static function load(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        $variables = parse_ini_file($path, false, INI_SCANNER_RAW);
        if ($variables === false) {
            throw new \RuntimeException('Le fichier d’environnement est invalide.');
        }

        foreach ($variables as $name => $value) {
            if (!is_string($name) || !is_string($value) || getenv($name) !== false) {
                continue;
            }

            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
        }
    }
}
