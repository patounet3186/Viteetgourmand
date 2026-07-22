<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Menu;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use App\Services\MailService;
use App\Services\OrderPricing;
use DateTimeImmutable;
use DomainException;
use PDO;

final class OrderController extends Controller
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

    private Order $orders;
    private Menu $menus;
    private User $users;
    private Notification $notifications;
    private MailService $mailer;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->orders = new Order($pdo);
        $this->menus = new Menu($pdo);
        $this->users = new User($pdo);
        $this->notifications = new Notification($pdo);
        $this->mailer = new MailService();
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

    public function create(): array
    {
        $user = $this->requireRole(['user']);
        $userId = (int) $user['id'];
        $menuId = (int) ($_GET['menu_id'] ?? $_POST['menu_id'] ?? 0);
        $menu = $this->menus->findActive($menuId);

        if ($menu === null) {
            $this->abort(404, 'Menu introuvable');
        }

        if ((int) $menu['stock'] < 1) {
            $this->abort(
                409,
                'Menu indisponible',
                'Ce menu ne peut plus être commandé actuellement.'
            );
        }

        $profile = $this->users->findActiveProfile($userId);
        if ($profile === null) {
            session_unset();
            session_regenerate_id(true);
            $this->redirect('login');
        }

        $errors = [];
        $form = $this->emptyOrderForm($profile);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $form = $this->orderInput();
            $errors = $this->validateOrder($form, $menu);

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                array_unshift($errors, 'Le formulaire a expiré, merci de réessayer.');
            }

            if ($errors === []) {
                $pricing = OrderPricing::calculate(
                    $menu,
                    (int) $form['people_count'],
                    $form['delivery_city']
                );

                try {
                    $orderId = $this->orders->create([
                        'user_id' => $userId,
                        'menu_id' => (int) $menu['id'],
                        ...$this->orderData($form, $pricing),
                    ]);

                    $this->mailer->orderConfirmation(
                        (string) $profile['email'],
                        (string) $profile['first_name'],
                        $orderId,
                        (string) $menu['title'],
                        $pricing['total_price']
                    );
                    $this->redirect('order-show', [
                        'id' => $orderId,
                        'created' => 1,
                    ]);
                } catch (DomainException $exception) {
                    $errors[] = $exception->getMessage();
                }
            }
        }

        $pricing = $this->previewPricing($form, $menu);

        return $this->render('orders/create', 'Commander', compact(
            'menu',
            'profile',
            'errors',
            'form',
            'pricing'
        ));
    }

    public function show(): array
    {
        $user = $this->requireRole(['user']);
        $orderId = (int) ($_GET['id'] ?? 0);
        $order = $this->orders->findDetailedForUser($orderId, (int) $user['id']);

        if ($order === null) {
            $this->abort(404, 'Commande introuvable');
        }

        $history = $this->orders->history($orderId);
        $statuses = self::STATUSES;
        try {
            $this->notifications->markOrderRead((int) $user['id'], $orderId);
        } catch (\Throwable $exception) {
            error_log(
                'Notification de commande non lue : ' . $exception->getMessage()
            );
        }
        $orderCreated = ($_GET['created'] ?? '') === '1';
        $orderUpdated = ($_GET['updated'] ?? '') === '1';
        $orderCanceled = ($_GET['canceled'] ?? '') === '1';

        return $this->render('orders/show', 'Détail de la commande', compact(
            'order',
            'history',
            'statuses',
            'orderCreated',
            'orderUpdated',
            'orderCanceled'
        ));
    }

    public function edit(): array
    {
        $user = $this->requireRole(['user']);
        $userId = (int) $user['id'];
        $orderId = (int) ($_GET['id'] ?? 0);
        $order = $this->orders->findDetailedForUser($orderId, $userId);

        if ($order === null) {
            $this->abort(404, 'Commande introuvable');
        }

        if ($order['status'] !== 'nouvelle') {
            $this->abort(
                403,
                'Modification impossible',
                'Une commande acceptée ne peut plus être modifiée.'
            );
        }

        $menu = $this->menus->findForManagement((int) $order['menu_id']);
        if ($menu === null) {
            $this->abort(404, 'Menu introuvable');
        }

        $errors = [];
        $form = [
            'event_date' => (string) $order['event_date'],
            'event_time' => substr((string) $order['event_time'], 0, 5),
            'delivery_address' => (string) $order['delivery_address'],
            'delivery_city' => (string) $order['delivery_city'],
            'people_count' => (string) $order['people_count'],
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $form = $this->orderInput();
            $errors = $this->validateOrder($form, $menu);

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                array_unshift($errors, 'Le formulaire a expiré, merci de réessayer.');
            }

            if ($errors === []) {
                $pricing = OrderPricing::calculate(
                    $menu,
                    (int) $form['people_count'],
                    $form['delivery_city']
                );
                $updated = $this->orders->updateByUser(
                    $orderId,
                    $userId,
                    $this->orderData($form, $pricing)
                );

                if (!$updated) {
                    $errors[] = 'La commande vient d’être acceptée et ne peut plus être modifiée.';
                } else {
                    $this->redirect('order-show', [
                        'id' => $orderId,
                        'updated' => 1,
                    ]);
                }
            }
        }

        $pricing = $this->previewPricing($form, $menu);

        return $this->render('orders/edit', 'Modifier la commande', compact(
            'order',
            'menu',
            'errors',
            'form',
            'pricing'
        ));
    }

    public function cancel(): never
    {
        $user = $this->requireRole(['user']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->abort(405, 'Méthode non autorisée');
        }

        $orderId = (int) ($_POST['order_id'] ?? 0);
        if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
            $this->abort(
                403,
                'Formulaire expiré',
                'Rechargez la page avant de recommencer.'
            );
        }

        if (!$this->orders->cancelByUser($orderId, (int) $user['id'])) {
            $this->abort(
                409,
                'Annulation impossible',
                'Cette commande a déjà été prise en charge.'
            );
        }

        $this->redirect('order-show', ['id' => $orderId, 'canceled' => 1]);
    }

    public function manage(): array
    {
        $employee = $this->requireRole(['employee', 'admin']);
        $statuses = self::STATUSES;
        $transitions = self::TRANSITIONS;
        $selectedStatus = (string) ($_GET['status'] ?? '');
        $customerSearch = trim((string) ($_GET['customer'] ?? ''));
        $errors = [];

        if ($selectedStatus !== '' && !array_key_exists($selectedStatus, $statuses)) {
            $selectedStatus = '';
        }

        if (
            $_SERVER['REQUEST_METHOD'] === 'POST'
            && ($_POST['action'] ?? '') === 'update_order_status'
        ) {
            $orderId = (int) ($_POST['order_id'] ?? 0);
            $newStatus = (string) ($_POST['status'] ?? '');
            $contactMethod = trim((string) ($_POST['contact_method'] ?? ''));
            $reason = trim((string) ($_POST['cancellation_reason'] ?? ''));
            $order = $this->orders->findForManagement($orderId);

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                $errors[] = 'Le formulaire a expiré, merci de réessayer.';
            }

            if ($order === null) {
                $errors[] = 'La commande sélectionnée est introuvable.';
            } elseif (
                !in_array(
                    $newStatus,
                    self::TRANSITIONS[$order['status']] ?? [],
                    true
                )
            ) {
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

            if ($errors === [] && $order !== null) {
                $updatedOrder = $this->orders->updateStatus(
                    $orderId,
                    $newStatus,
                    (int) $employee['id'],
                    $newStatus === 'annulee' ? $contactMethod : null,
                    $newStatus === 'annulee' ? $reason : null,
                    (string) $order['status']
                );

                if ($updatedOrder === null) {
                    $errors[] =
                        'La commande vient d’être modifiée par un autre utilisateur. '
                        . 'Rechargez la page avant de recommencer.';
                } else {
                    $statusLabel = $statuses[$newStatus];
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
                            'Notification de commande non créée : '
                            . $exception->getMessage()
                        );
                    }
                    $this->mailer->orderStatus(
                        (string) $updatedOrder['email'],
                        (string) $updatedOrder['first_name'],
                        $orderId,
                        $statusLabel,
                        $this->statusMessage($newStatus)
                    );
                    $this->redirect('employee-orders', ['updated' => 1]);
                }
            }
        }

        $orders = $this->orders->allByFilters(
            $selectedStatus === '' ? null : $selectedStatus,
            $customerSearch
        );
        $orderUpdated = ($_GET['updated'] ?? '') === '1';

        return $this->render('employee/orders', 'Gestion des commandes', compact(
            'statuses',
            'transitions',
            'selectedStatus',
            'customerSearch',
            'orders',
            'errors',
            'orderUpdated'
        ));
    }

    /** @param array<string, mixed> $profile */
    private function emptyOrderForm(array $profile): array
    {
        return [
            'event_date' => '',
            'event_time' => '',
            'delivery_address' => (string) ($profile['address'] ?? ''),
            'delivery_city' => (string) ($profile['city'] ?? ''),
            'people_count' => '',
        ];
    }

    /** @return array<string, string> */
    private function orderInput(): array
    {
        return [
            'event_date' => trim((string) ($_POST['event_date'] ?? '')),
            'event_time' => trim((string) ($_POST['event_time'] ?? '')),
            'delivery_address' => trim((string) ($_POST['delivery_address'] ?? '')),
            'delivery_city' => trim((string) ($_POST['delivery_city'] ?? '')),
            'people_count' => trim((string) ($_POST['people_count'] ?? '')),
        ];
    }

    /**
     * @param array<string, string> $form
     * @param array<string, mixed> $menu
     * @return list<string>
     */
    private function validateOrder(array $form, array $menu): array
    {
        $errors = [];
        $eventDateTime = DateTimeImmutable::createFromFormat(
            '!Y-m-d H:i',
            $form['event_date'] . ' ' . $form['event_time']
        );

        if (
            $eventDateTime === false
            || $eventDateTime->format('Y-m-d H:i')
                !== $form['event_date'] . ' ' . $form['event_time']
            || $eventDateTime <= new DateTimeImmutable()
        ) {
            $errors[] = 'La date et l’heure de prestation doivent être futures.';
        }

        if (
            mb_strlen($form['delivery_address']) < 5
            || mb_strlen($form['delivery_address']) > 255
        ) {
            $errors[] = 'L’adresse de livraison doit contenir entre 5 et 255 caractères.';
        }

        if (
            mb_strlen($form['delivery_city']) < 2
            || mb_strlen($form['delivery_city']) > 100
        ) {
            $errors[] = 'La ville de livraison doit contenir entre 2 et 100 caractères.';
        }

        if (
            !ctype_digit($form['people_count'])
            || (int) $form['people_count'] < (int) $menu['min_people']
            || (int) $form['people_count'] > 1000
        ) {
            $errors[] =
                'Le nombre de personnes doit respecter le minimum du menu '
                . 'et ne pas dépasser 1 000.';
        }

        return $errors;
    }

    /**
     * @param array<string, string> $form
     * @param array{menu_price: float, delivery_price: float, discount_amount: float, total_price: float} $pricing
     * @return array<string, mixed>
     */
    private function orderData(array $form, array $pricing): array
    {
        return [
            'event_date' => $form['event_date'],
            'event_time' => $form['event_time'],
            'delivery_address' => $form['delivery_address'],
            'delivery_city' => $form['delivery_city'],
            'people_count' => (int) $form['people_count'],
            ...$pricing,
        ];
    }

    /**
     * @param array<string, string> $form
     * @param array<string, mixed> $menu
     * @return array<string, float>|null
     */
    private function previewPricing(array $form, array $menu): ?array
    {
        if (
            !ctype_digit($form['people_count'])
            || (int) $form['people_count'] < (int) $menu['min_people']
            || (int) $form['people_count'] > 1000
            || $form['delivery_city'] === ''
        ) {
            return null;
        }

        return OrderPricing::calculate(
            $menu,
            (int) $form['people_count'],
            $form['delivery_city']
        );
    }

    private function statusMessage(string $status): ?string
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
