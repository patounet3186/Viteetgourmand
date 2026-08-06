<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Models\Menu;
use App\Models\Order;
use App\Services\DishManagementService;
use App\Services\OrderPricing;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$testDatabaseDsn = getenv('TEST_DATABASE_DSN');
if (!is_string($testDatabaseDsn) || $testDatabaseDsn === '') {
    require_once dirname(__DIR__) . '/config/database.php';
}

function verify(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function cleanStaleIntegrationData(\PDO $pdo): void
{
    $pdo->exec(
        "DELETE orders
         FROM orders
         INNER JOIN users ON users.id = orders.user_id
         WHERE users.email LIKE '%@example.test'"
    );
    $pdo->exec("DELETE FROM menus WHERE title LIKE 'Menu test %'");
    $pdo->exec("DELETE FROM dishes WHERE name LIKE 'Plat test %'");
    $pdo->exec("DELETE FROM users WHERE email LIKE '%@example.test'");
}

$pdo = is_string($testDatabaseDsn) && $testDatabaseDsn !== ''
    ? new PDO(
        $testDatabaseDsn,
        (string) (getenv('TEST_DATABASE_USER') ?: ''),
        (string) (getenv('TEST_DATABASE_PASSWORD') ?: ''),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    )
    : getDatabase();
$menuId = null;
$orderId = null;
$dishIds = [];
$userIds = [];
$token = bin2hex(random_bytes(6));
$exitCode = 0;

cleanStaleIntegrationData($pdo);

try {
    $insertUser = $pdo->prepare(
        'INSERT INTO users
            (role, first_name, last_name, email, password_hash, is_active)
         VALUES
            (:role, :first_name, :last_name, :email, :password_hash, 1)'
    );

    foreach (['user' => 'Client', 'employee' => 'Employé'] as $role => $firstName) {
        $insertUser->execute([
            'role' => $role,
            'first_name' => $firstName,
            'last_name' => 'Test intégration',
            'email' => "{$role}.{$token}@example.test",
            'password_hash' => password_hash(bin2hex(random_bytes(12)), PASSWORD_DEFAULT),
        ]);
        $userIds[$role] = (int) $pdo->lastInsertId();
    }

    $dishModel = new Dish($pdo);
    foreach (['entree', 'plat', 'dessert'] as $category) {
        $dishModel->create([
            'name' => "Plat test {$category} {$token}",
            'category' => $category,
            'description' => "Description temporaire du {$category} de test.",
            'allergens' => '',
        ]);
        $dishIds[] = (int) $pdo->lastInsertId();
    }

    verify(
        count($dishIds) === 3,
        'Les trois plats temporaires n’ont pas été créés.'
    );

    $menuModel = new Menu($pdo);
    $menuData = [
        'title' => "Menu test {$token}",
        'description' => 'Menu temporaire utilisé par le test d’intégration.',
        'theme' => 'Test',
        'diet' => 'classique',
        'min_people' => 4,
        'base_price' => 120.00,
        'conditions_text' => 'Commande de test, supprimée automatiquement.',
        'stock' => 3,
        'image_url' => 'images/menu-classique.webp',
        'is_active' => 1,
    ];
    $imageUrls = [
        'images/menu-classique.webp',
        'images/menu-noel.webp',
    ];
    $menuId = $menuModel->create($menuData, $dishIds, $imageUrls);

    verify($menuModel->dishIds($menuId) === $dishIds, 'Composition du menu incorrecte.');
    verify($menuModel->imageUrls($menuId) === $imageUrls, 'Galerie du menu incorrecte.');

    $dishManagement = new DishManagementService($dishModel);
    try {
        $dishManagement->delete($dishIds[0]);
        throw new RuntimeException('Un plat utilisé par un menu a été supprimé.');
    } catch (DomainException) {
        verify(
            $dishModel->find($dishIds[0]) !== null,
            'Le plat protégé doit rester enregistré.'
        );
    }

    $menuData['title'] = "Menu test modifié {$token}";
    $menuModel->update($menuId, $menuData, $dishIds, $imageUrls);
    verify(
        ($menuModel->findForManagement($menuId)['title'] ?? '') === $menuData['title'],
        'La modification du menu n’a pas été enregistrée.'
    );

    $pricing = OrderPricing::calculate($menuData, 9, 'Mérignac', 8.0);
    verify($pricing['discount_amount'] > 0, 'La remise de 10 % devait être appliquée.');
    verify($pricing['delivery_price'] === 9.72, 'Les frais de livraison incluent la distance.');

    $orderModel = new Order($pdo);
    $orderId = $orderModel->create([
        'user_id' => $userIds['user'],
        'menu_id' => $menuId,
        'event_date' => date('Y-m-d', strtotime('+15 days')),
        'event_time' => '12:30:00',
        'delivery_address' => '1 rue du Test',
        'delivery_city' => 'Mérignac',
        'delivery_distance_km' => 8.0,
        'people_count' => 9,
        ...$pricing,
    ]);

    $stockAfterOrder = (int) $pdo
        ->query("SELECT stock FROM menus WHERE id = {$menuId}")
        ->fetchColumn();
    verify($stockAfterOrder === 2, 'Le stock devait diminuer après la commande.');
    verify(count($orderModel->history($orderId)) === 1, 'Le statut initial est absent.');

    $pricingAfterEdit = OrderPricing::calculate($menuData, 10, 'Bordeaux', 0.0);
    $updated = $orderModel->updateByUser(
        $orderId,
        $userIds['user'],
        [
            'event_date' => date('Y-m-d', strtotime('+16 days')),
            'event_time' => '13:00:00',
            'delivery_address' => '2 rue du Test',
            'delivery_city' => 'Bordeaux',
            'delivery_distance_km' => 0.0,
            'people_count' => 10,
            ...$pricingAfterEdit,
        ]
    );
    verify($updated, 'La commande nouvelle devait être modifiable par son client.');

    $cancelledOrder = $orderModel->updateStatus(
        $orderId,
        'annulee',
        $userIds['employee'],
        'telephone',
        'Client contacté par téléphone pour le test d’intégration.',
        'nouvelle'
    );
    verify(
        ($cancelledOrder['status'] ?? '') === 'annulee',
        'La commande devait être annulée.'
    );
    verify(count($orderModel->history($orderId)) === 3, 'L’historique est incomplet.');

    $staleUpdate = $orderModel->updateStatus(
        $orderId,
        'terminee',
        $userIds['employee'],
        null,
        null,
        'nouvelle'
    );
    verify(
        $staleUpdate === null,
        'Une mise à jour fondée sur un ancien statut devait être refusée.'
    );
    verify(
        count($orderModel->history($orderId)) === 3,
        'Une transition concurrente refusée ne doit pas modifier l’historique.'
    );

    $restoredStock = (int) $pdo
        ->query("SELECT stock FROM menus WHERE id = {$menuId}")
        ->fetchColumn();
    verify($restoredStock === 3, 'Le stock devait être restauré après l’annulation.');

    $menuModel->archive($menuId);
    $archivedMenu = $menuModel->findForManagement($menuId);
    verify(
        (int) ($archivedMenu['is_active'] ?? 1) === 0
        && (int) ($archivedMenu['stock'] ?? -1) === 0,
        'L’archivage du menu est incorrect.'
    );

    echo "Database integration checks passed.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "Database integration check failed: {$exception->getMessage()}\n");
    $exitCode = 1;
} finally {
    if ($orderId !== null) {
        $deleteOrder = $pdo->prepare('DELETE FROM orders WHERE id = :id');
        $deleteOrder->execute(['id' => $orderId]);
    }

    if ($menuId !== null) {
        $deleteMenu = $pdo->prepare('DELETE FROM menus WHERE id = :id');
        $deleteMenu->execute(['id' => $menuId]);
    }

    if ($dishIds !== []) {
        $placeholders = implode(',', array_fill(0, count($dishIds), '?'));
        $deleteDishes = $pdo->prepare("DELETE FROM dishes WHERE id IN ({$placeholders})");
        $deleteDishes->execute($dishIds);
    }

    if ($userIds !== []) {
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $deleteUsers = $pdo->prepare("DELETE FROM users WHERE id IN ({$placeholders})");
        $deleteUsers->execute(array_values($userIds));
    }
}

exit($exitCode);
