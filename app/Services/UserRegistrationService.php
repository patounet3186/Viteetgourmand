<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use PDOException;

final class UserRegistrationService
{
    public function __construct(
        private User $users,
        private MailService $mailer
    ) {
    }

    /**
     * @param array<string, string> $form
     * @return list<string>
     */
    public function register(array $form, string $password, bool $termsAccepted): array
    {
        $errors = self::validate($form, $password, $termsAccepted);

        if ($errors === [] && $this->users->emailExists($form['email'])) {
            $errors[] = 'Un compte existe déjà avec cet email.';
        }

        if ($errors !== []) {
            return $errors;
        }

        try {
            $this->users->createCustomer([
                ...$form,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                return ['Un compte existe déjà avec cet email.'];
            }

            throw $exception;
        }
        $this->mailer->customerWelcome($form['email'], $form['first_name']);

        return [];
    }

    /**
     * @param array<string, string> $form
     * @return list<string>
     */
    public static function validate(
        array $form,
        string $password,
        bool $termsAccepted
    ): array {
        $errors = [];
        $requiredFields = [
            'first_name',
            'last_name',
            'email',
            'phone',
            'address',
            'postal_code',
            'city',
        ];

        foreach ($requiredFields as $field) {
            if (($form[$field] ?? '') === '') {
                $errors[] = 'Toutes les coordonnées demandées doivent être renseignées.';
                break;
            }
        }

        if (
            mb_strlen($form['first_name'] ?? '') < 2
            || mb_strlen($form['first_name'] ?? '') > 100
            || mb_strlen($form['last_name'] ?? '') < 2
            || mb_strlen($form['last_name'] ?? '') > 100
        ) {
            $errors[] = 'Le prénom et le nom doivent contenir entre 2 et 100 caractères.';
        }

        if (
            !filter_var($form['email'] ?? '', FILTER_VALIDATE_EMAIL)
            || mb_strlen($form['email'] ?? '') > 180
        ) {
            $errors[] = 'L’adresse e-mail est invalide.';
        }

        if (
            mb_strlen($form['phone'] ?? '') > 30
            || preg_match('/^[0-9+().\s-]+$/', $form['phone'] ?? '') !== 1
        ) {
            $errors[] = 'Le numéro de téléphone est invalide.';
        }

        if (
            mb_strlen($form['address'] ?? '') < 5
            || mb_strlen($form['address'] ?? '') > 255
        ) {
            $errors[] = 'L’adresse doit contenir entre 5 et 255 caractères.';
        }

        if (
            mb_strlen($form['postal_code'] ?? '') < 3
            || mb_strlen($form['postal_code'] ?? '') > 20
            || preg_match('/^[0-9A-Za-z -]+$/', $form['postal_code'] ?? '') !== 1
        ) {
            $errors[] = 'Le code postal est invalide.';
        }

        if (
            mb_strlen($form['city'] ?? '') < 2
            || mb_strlen($form['city'] ?? '') > 100
        ) {
            $errors[] = 'La ville doit contenir entre 2 et 100 caractères.';
        }

        if (!PasswordPolicy::isStrong($password)) {
            $errors[] = PasswordPolicy::errorMessage();
        }

        if (!$termsAccepted) {
            $errors[] = 'Vous devez accepter les conditions générales et la politique de confidentialité.';
        }

        return array_values(array_unique($errors));
    }
}
