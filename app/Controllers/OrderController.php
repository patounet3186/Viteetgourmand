<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Menu;
use App\Models\Order;
use PDO;

final class OrderController extends Controller
{
    private const STATUSES = [
        'nouvelle' => 'Nouvelle',
        'accepte' => 'Acceptée',
        'en_preparation' => 'En préparation',
        'en_livraison' => 'En livraison',
        'livre' => 'Livrée',
        'attente_materiel' => 'Attente matériel',
        'terminee' => 'Terminée',
        'annulee' => 'Annulée',
    ];

    private Order $orders;
    private Menu $menus;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->orders = new Order($pdo);
        $this->menus = new Menu($pdo);
    }

    /** @return array<string, string> */
    public static function statuses(): array
    {
        return self::STATUSES;
    }

    public function create(): array
    {
        $user = $this->requireUser();
        $menuId = (int) ($_GET['menu_id'] ?? $_POST['menu_id'] ?? 0);
        $menu = $this->menus->findActive($menuId);

        if ($menu === null) {
            $this->abort(404, 'Menu introuvable');
        }

        $errors = [];
        $success = null;
        $form = [
            'event_date' => '',
            'event_time' => '',
            'delivery_address' => '',
            'delivery_city' => '',
            'people_count' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $form = [
                'event_date' => (string) ($_POST['event_date'] ?? ''),
                'event_time' => (string) ($_POST['event_time'] ?? ''),
                'delivery_address' => trim((string) ($_POST['delivery_address'] ?? '')),
                'delivery_city' => trim((string) ($_POST['delivery_city'] ?? '')),
                'people_count' => trim((string) ($_POST['people_count'] ?? '')),
            ];
            $peopleCount = (int) $form['people_count'];

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                $errors[] = 'Le formulaire a expiré, merci de réessayer.';
            }

            if (
                $form['event_date'] === ''
                || $form['event_time'] === ''
                || $form['delivery_address'] === ''
                || $form['delivery_city'] === ''
            ) {
                $errors[] = 'Tous les champs de prestation sont obligatoires.';
            }

            if ($peopleCount < (int) $menu['min_people']) {
                $errors[] = 'Le nombre de personnes doit respecter le minimum du menu.';
            }

            if (empty($errors)) {
                $basePrice = (float) $menu['base_price'];
                $minPeople = (int) $menu['min_people'];
                $menuPrice = $basePrice * ($peopleCount / $minPeople);
                $discount = $peopleCount >= $minPeople + 5 ? $menuPrice * 0.10 : 0;
                $deliveryPrice = strtolower($form['delivery_city']) === 'bordeaux' ? 0 : 5;
                $total = $menuPrice + $deliveryPrice - $discount;

                $this->orders->create([
                    'user_id' => (int) $user['id'],
                    'menu_id' => (int) $menu['id'],
                    'event_date' => $form['event_date'],
                    'event_time' => $form['event_time'],
                    'delivery_address' => $form['delivery_address'],
                    'delivery_city' => $form['delivery_city'],
                    'people_count' => $peopleCount,
                    'menu_price' => $menuPrice,
                    'delivery_price' => $deliveryPrice,
                    'discount_amount' => $discount,
                    'total_price' => $total,
                ]);

                $success = 'Commande enregistrée avec succès.';
            }
        }

        return $this->render('orders/create', 'Commander', compact(
            'menu',
            'errors',
            'success',
            'form'
        ));
    }

    public function manage(): array
    {
        $this->requireRole(['employee', 'admin']);

        $statuses = self::STATUSES;
        $selectedStatus = (string) ($_GET['status'] ?? '');

        if ($selectedStatus !== '' && !array_key_exists($selectedStatus, $statuses)) {
            $selectedStatus = '';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orderId = (int) ($_POST['order_id'] ?? 0);
            $newStatus = (string) ($_POST['status'] ?? '');

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                $this->redirect('employee-orders', ['csrf' => 1]);
            }

            if ($orderId > 0 && array_key_exists($newStatus, $statuses)) {
                $this->orders->updateStatus($orderId, $newStatus);
                $this->redirect('employee-orders', ['updated' => 1]);
            }
        }

        $orders = $this->orders->allByStatus(
            $selectedStatus === '' ? null : $selectedStatus
        );
        $orderUpdated = isset($_GET['updated']);

        return $this->render('employee/orders', 'Gestion des commandes', compact(
            'statuses',
            'selectedStatus',
            'orders',
            'orderUpdated'
        ));
    }
}
