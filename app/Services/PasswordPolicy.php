<?php

declare(strict_types=1);

namespace App\Services;

final class PasswordPolicy
{
    public static function isStrong(string $password): bool
    {
        if (strlen($password) > 72) {
            return false;
        }

        return preg_match(
            '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{10,}$/',
            $password
        ) === 1;
    }

    public static function errorMessage(): string
    {
        return 'Le mot de passe doit contenir entre 10 et 72 caractères, '
            . 'une majuscule, une minuscule, un chiffre et un caractère spécial.';
    }
}
