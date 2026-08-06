<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Notification;
use App\Models\Order;

final class OrderWorkflowService
{
    private const STATUSES = [
        'nouvelle' => 'Nouvelle',
        'accepte' => 'Acceptée',
        'en_preparation' => 'En préparation',
        'en_livraison' => 'En cours de livraison',
        'livre' => 'Livrée',
        'attente_materiel' => 'En attente du retour de matériel',
        'terminee' => 'Terminée',
        'annulee' => 'Annulée',
    ];

    private const TRANSITIONS = [
        'nouvelle' => ['accepte', 'annulee'],
        'accepte' => ['en_preparation', 'annulee'],
        'en_preparation' => ['en_livraison', 'annulee'],
        'en_livraison' => ['livre', 'annulee'],
        'livre' => ['attente_materiel', 'terminee'],
        'attente_materiel' => ['terminee'],
        'terminee' => [],
        'annulee' => [],
    ];

    public function __construct(
        private Order $orders,
        private Notification $notifications,
        private MailService $mailer
    ) {
    }

    /** @return array<string, string> */
    public static function statuses(): array
    {
        return self::STATUSES;
    }

    /** @return array<string, list<string>> */
    public static function transitions(): array
    {
        return self::TRANSITIONS;
    }

    /** @return list<string> */
    public function changeStatus(
        int $orderId,
        string $newStatus,
        int $employeeId,
        string $contactMethod,
        string $reason
    ): array {
        $errors = [];
        $order = $this->orders->findForManagement($orderId);

        if ($order === null) {
            return ['La commande sélectionnée est introuvable.'];
        }

        if (!in_array(
            $newStatus,
            self::TRANSITIONS[$order['status']] ?? [],
            true
        )) {
            $errors[] = 'Cette transition de statut n’est pas autorisée.';
        }

        if (
            $newStatus === 'annulee'
            && !in_array($contactMethod, ['telephone', 'email'], true)
        ) {
            $errors[] = 'Indiquez comment le client a été contacté.';
        }

        if (
            $newStatus === 'annulee'
            && (mb_strlen($reason) < 10 || mb_strlen($reason) > 500)
        ) {
            $errors[] = 'Le motif d’annulation doit contenir entre 10 et 500 caractères.';
        }

        if ($errors !== []) {
            return $errors;
        }

        $updatedOrder = $this->orders->updateStatus(
            $orderId,
            $newStatus,
            $employeeId,
            $newStatus === 'annulee' ? $contactMethod : null,
            $newStatus === 'annulee' ? $reason : null,
            (string) $order['status']
        );

        if ($updatedOrder === null) {
            return [
                'La commande vient d’être modifiée par un autre utilisateur. '
                . 'Rechargez la page avant de recommencer.',
            ];
        }

        $statusLabel = self::STATUSES[$newStatus];

        try {
            $this->notifications->create(
                (int) $updatedOrder['user_id'],
                $orderId,
                'order_status',
                "La commande n°{$orderId} est maintenant « {$statusLabel} ».",
                "order-show&id={$orderId}"
            );
        } catch (\Throwable $exception) {
            error_log(
                'Notification de commande non créée : ' . $exception->getMessage()
            );
        }

        $this->mailer->orderStatus(
            (string) $updatedOrder['email'],
            (string) $updatedOrder['first_name'],
            $orderId,
            $statusLabel,
            self::statusMessage($newStatus)
        );

        return [];
    }

    private static function statusMessage(string $status): ?string
    {
        return match ($status) {
            'attente_materiel' =>
                'Le matériel doit être restitué sous 10 jours ouvrés. '
                . 'Au-delà de ce délai, des frais de 600 € pourront être facturés '
                . 'conformément aux conditions générales de vente.',
            'terminee' =>
                'Vous pouvez maintenant déposer un avis depuis cette commande.',
            default => null,
        };
    }
}
