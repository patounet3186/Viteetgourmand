<?php

require_once __DIR__ . '/../../../config/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: ?page=login');
    exit;
}

if ($_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo '<section class="section"><h1>Acces refuse</h1></section>';
    return;
}

$pdo = getDatabase();

$stmt = $pdo->query("
    SELECT
        menus.id,
        menus.title,
        COUNT(orders.id) AS orders_count,
        COALESCE(SUM(orders.total_price), 0) AS turnover
    FROM menus
    LEFT JOIN orders ON orders.menu_id = menus.id
    GROUP BY menus.id, menus.title
    ORDER BY orders_count DESC
");

$stats = $stmt->fetchAll();

$totalOrders = 0;
$totalTurnover = 0;

foreach ($stats as $stat) {
    $totalOrders += (int) $stat['orders_count'];
    $totalTurnover += (float) $stat['turnover'];
}
?>

<section class="section">
    <h1>Tableau de bord administrateur</h1>

    <div class="row g-4 my-4">
        <div class="col-md-6">
            <div class="card p-4">
                <h2 class="h5">Nombre total de commandes</h2>
                <p class="display-6"><?= $totalOrders ?></p>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card p-4">
                <h2 class="h5">Chiffre d'affaires total</h2>
                <p class="display-6"><?= number_format($totalTurnover, 2, ',', ' ') ?> €</p>
            </div>
        </div>
    </div>

    <h2>Statistiques par menu</h2>

    <div class="table-responsive mt-3">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Menu</th>
                    <th>Nombre de commandes</th>
                    <th>Chiffre d'affaires</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats as $stat): ?>
                    <tr>
                        <td><?= htmlspecialchars($stat['title']) ?></td>
                        <td><?= (int) $stat['orders_count'] ?></td>
                        <td><?= number_format((float) $stat['turnover'], 2, ',', ' ') ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="alert alert-info mt-4">
        Ces statistiques sont actuellement calculees depuis la base relationnelle.
        Une base NoSQL sera ajoutee pour repondre a la contrainte du sujet.
    </div>
</section>