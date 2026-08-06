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
use App\Services\OrderWorkflowService;
use DateTimeImmutable;
use DomainException;
use PDO;

final class OrderController extends Controller
{
    private Order $orders;
    private Menu $menus;
    private User $users;
    private Notification $notifications;
    private MailService $mailer;
    private OrderWorkflowService $workflow;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->orders = new Order($pdo);
        $this->menus = new Menu($pdo);
        $this->users = new User($pdo);
        $this->notifications = new Notification($pdo);
        $this->mailer = new MailService();
        $this->workflow = new OrderWorkflowService(
            $this->orders,
            $this->notifications,
            $this->mailer
        );
    }

    /** @return array<string, string> */
    public static function statuses(): array
    {
        return OrderWorkflowService::statuses();
    }

    /** @return array<string, list<string>> */
    public static function transitions(): array
    {
        return OrderWorkflowService::transitions();
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
                    $form['delivery_city'],
                    (float) str_replace(',', '.', $form['delivery_distance_km'])
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
        $statuses = self::statuses();
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
            'delivery_distance_km' => (string) $order['delivery_distance_km'],
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
                    $form['delivery_city'],
                    (float) str_replace(',', '.', $form['delivery_distance_km'])
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
        $statuses = self::statuses();
        $transitions = self::transitions();
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
            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                $errors[] = 'Le formulaire a expiré, merci de réessayer.';
            }

            if ($errors === []) {
                $errors = $this->workflow->changeStatus(
                    $orderId,
                    $newStatus,
                    (int) $employee['id'],
                    $contactMethod,
                    $reason
                );
            }

            if ($errors === []) {
                $this->redirect('employee-orders', ['updated' => 1]);
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
            'delivery_distance_km' => '0',
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
            'delivery_distance_km' => trim(
                (string) ($_POST['delivery_distance_km'] ?? '')
            ),
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

        $distance = str_replace(',', '.', $form['delivery_distance_km']);
        if (
            !is_numeric($distance)
            || (float) $distance < 0
            || (float) $distance > 1000
        ) {
            $errors[] = 'La distance de livraison doit être comprise entre 0 et 1 000 km.';
        } elseif (
            !OrderPricing::isBordeaux($form['delivery_city'])
            && (float) $distance <= 0
        ) {
            $errors[] = 'Indiquez la distance depuis Bordeaux pour une livraison hors Bordeaux.';
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
            'delivery_distance_km' => OrderPricing::isBordeaux(
                $form['delivery_city']
            ) ? 0.0 : (float) str_replace(',', '.', $form['delivery_distance_km']),
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
            || !is_numeric(str_replace(',', '.', $form['delivery_distance_km']))
            || (float) str_replace(',', '.', $form['delivery_distance_km']) < 0
            || (float) str_replace(',', '.', $form['delivery_distance_km']) > 1000
            || (
                !OrderPricing::isBordeaux($form['delivery_city'])
                && (float) str_replace(',', '.', $form['delivery_distance_km']) <= 0
            )
        ) {
            return null;
        }

        return OrderPricing::calculate(
            $menu,
            (int) $form['people_count'],
            $form['delivery_city'],
            (float) str_replace(',', '.', $form['delivery_distance_km'])
        );
    }

}
