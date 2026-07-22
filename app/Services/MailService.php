<?php

declare(strict_types=1);

namespace App\Services;

final class MailService
{
    public function send(
        string $recipient,
        string $subject,
        string $message,
        ?string $replyTo = null
    ): bool
    {
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $configuredFrom = (string) (getenv('MAIL_FROM')
            ?: 'no-reply@vite-et-gourmand.fr');
        $from = filter_var($configuredFrom, FILTER_VALIDATE_EMAIL)
            ? $configuredFrom
            : 'no-reply@vite-et-gourmand.fr';
        $replyAddress = $replyTo !== null
            && filter_var($replyTo, FILTER_VALIDATE_EMAIL)
            ? $replyTo
            : $from;
        $safeSubject = trim((string) preg_replace('/[\r\n]+/', ' ', $subject));
        $headers = [
            'From: Vite & Gourmand <' . $from . '>',
            'Reply-To: ' . $replyAddress,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'X-Mailer: PHP/' . PHP_VERSION,
        ];

        $sent = @mail(
            $recipient,
            mb_encode_mimeheader($safeSubject, 'UTF-8'),
            $message,
            implode("\r\n", $headers)
        );

        if (!$sent) {
            error_log("E-mail non envoyé à {$recipient} : {$safeSubject}");
        }

        return $sent;
    }

    public function orderConfirmation(
        string $email,
        string $firstName,
        int $orderId,
        string $menuTitle,
        float $total
    ): bool {
        $message = "Bonjour {$firstName},\n\n"
            . "Votre commande n°{$orderId} pour le menu « {$menuTitle} » "
            . "a bien été enregistrée.\n"
            . 'Montant total : ' . number_format($total, 2, ',', ' ') . " €.\n\n"
            . "Vous pouvez suivre son état depuis votre espace client.\n\n"
            . "Vite & Gourmand";

        return $this->send($email, 'Confirmation de votre commande', $message);
    }

    public function orderStatus(
        string $email,
        string $firstName,
        int $orderId,
        string $statusLabel,
        ?string $extraMessage = null
    ): bool {
        $message = "Bonjour {$firstName},\n\n"
            . "Le statut de votre commande n°{$orderId} est maintenant : "
            . "{$statusLabel}.\n";

        if ($extraMessage !== null && $extraMessage !== '') {
            $message .= "\n{$extraMessage}\n";
        }

        $message .= "\nConnectez-vous à votre espace pour consulter le détail.\n\n"
            . "Vite & Gourmand";

        return $this->send($email, "Mise à jour de la commande n°{$orderId}", $message);
    }

    public function employeeAccount(string $email, string $firstName): bool
    {
        $message = "Bonjour {$firstName},\n\n"
            . "Un compte employé Vite & Gourmand a été créé pour cette adresse.\n"
            . "Pour des raisons de sécurité, le mot de passe n’est pas envoyé par e-mail. "
            . "Rapprochez-vous de l’administrateur pour l’obtenir.\n\n"
            . "Vite & Gourmand";

        return $this->send($email, 'Création de votre compte employé', $message);
    }

    public function passwordReset(string $email, string $firstName, string $url): bool
    {
        $message = "Bonjour {$firstName},\n\n"
            . "Une réinitialisation de votre mot de passe a été demandée.\n"
            . "Utilisez ce lien valable une heure :\n{$url}\n\n"
            . "Si vous n’êtes pas à l’origine de cette demande, ignorez ce message.\n\n"
            . "Vite & Gourmand";

        return $this->send($email, 'Réinitialisation de votre mot de passe', $message);
    }

    public function contactRequest(
        string $name,
        string $email,
        string $subject,
        string $message
    ): bool {
        $recipient = getenv('COMPANY_EMAIL') ?: 'contact@vite-et-gourmand.fr';
        $content = "Nouveau message depuis le site.\n\n"
            . "Nom : {$name}\n"
            . "E-mail : {$email}\n"
            . "Sujet : {$subject}\n\n"
            . $message;

        return $this->send(
            $recipient,
            'Contact : ' . $subject,
            $content,
            $email
        );
    }
}
