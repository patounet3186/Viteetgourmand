<?php

require_once __DIR__ . '/../../../config/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: ?page=login');
    exit;
}

if (!in_array($_SESSION['user']['role'], ['employee', 'admin'], true)) {
    http_response_code(403);
    echo '<section class="section"><h1>Accès refusé</h1></section>';
    return;
}

$pdo = getDatabase();

$statuses = [
    'nouvelle' => 'Nouvelle',
    'accepte' => 'Acceptée',
    'en_preparation' => 'En préparation',
    'en_livraison' => 'En livraison',
    'livre' => 'Livrée',
    'attente_materiel' => 'Attente matériel',
    'terminee' => 'Terminée',
    'annulee' => 'Annulée',
];

$selectedStatus = $_GET['status'] ?? '';

if ($selectedStatus !== '' && !array_key_exists($selectedStatus, $statuses)) {
    $selectedStatus = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        header('Location: ?page=employee-orders&csrf=1');
        exit;
    }

    if ($orderId > 0 && array_key_exists($newStatus, $statuses)) {
        $stmt = $pdo->prepare("
            UPDATE orders
            SET status = :status
            WHERE id = :id
        ");

        $stmt->execute([
            'status' => $newStatus,
            'id' => $orderId,
        ]);

        header('Location: ?page=employee-orders&updated=1');
        exit;
    }
}

$sql = "
    SELECT
        orders.id,
        orders.event_date,
        orders.event_time,
        orders.people_count,
        orders.total_price,
        orders.status,
        orders.created_at,
        menus.title AS menu_title,
        users.first_name,
        users.last_name,
        users.email
    FROM orders
    INNER JOIN menus ON menus.id = orders.menu_id
    INNER JOIN users ON users.id = orders.user_id
";

$params = [];

if ($selectedStatus !== '') {
    $sql .= " WHERE orders.status = :status";
    $params['status'] = $selectedStatus;
}

$sql .= " ORDER BY orders.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<section class="section">
    <h1>Gestion des commandes</h1>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success js-auto-hide">Commande mise à jour.</div>
    <?php endif; ?>

    <form method="get" class="card p-4 mb-4">
        <input type="hidden" name="page" value="employee-orders">

        <label class="form-label">Filtrer par statut</label>
        <div class="row g-3">
            <div class="col-md-6">
                <select name="status" class="form-select">
                    <option value="">Tous les statuts</option>
                    <?php foreach ($statuses as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= $selectedStatus === $value ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <button type="submit" class="btn-app">Filtrer</button>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Menu</th>
                    <th>Date</th>
                    <th>Personnes</th>
                    <th>Total</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?><br>
                            <small><?= htmlspecialchars($order['email']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($order['menu_title']) ?></td>
                        <td><?= htmlspecialchars($order['event_date']) ?> <?= htmlspecialchars($order['event_time']) ?></td>
                        <td><?= (int) $order['people_count'] ?></td>
                        <td><?= number_format((float) $order['total_price'], 2, ',', ' ') ?> €</td>
                        <td><?= htmlspecialchars($statuses[$order['status']] ?? $order['status']) ?></td>
                        <td>
                            <form method="post" class="d-flex gap-2">
                              <?= csrf_field() ?>
                                <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">

                                <select name="status" class="form-select form-select-sm">
                                    <?php foreach ($statuses as $value => $label): ?>
                                        <option value="<?= htmlspecialchars($value) ?>" <?= $order['status'] === $value ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <button type="submit" class="btn btn-sm btn-primary">OK</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>