<?php

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../Services/reviews.php';

if (!isset($_SESSION['user'])) {
    header('Location: ?page=login');
    exit;
}

$user = $_SESSION['user'];
$pdo = getDatabase();

$statuses = [
    'nouvelle' => 'Nouvelle',
    'accepte' => 'Acceptée',
    'en_preparation' => 'En préparation',
    'en_livraison' => 'En livraison',
    'livre' => 'Livre',
    'attente_materiel' => 'Attente matériel',
    'terminee' => 'Terminée',
    'annulee' => 'Annulée',
];

$stmt = $pdo->prepare("
    SELECT
        orders.id,
        orders.event_date,
        orders.event_time,
        orders.people_count,
        orders.total_price,
        orders.status,
        orders.created_at,
        menus.title AS menu_title
    FROM orders
    INNER JOIN menus ON menus.id = orders.menu_id
    WHERE orders.user_id = :user_id
    ORDER BY orders.created_at DESC
");

$stmt->execute([
    'user_id' => $user['id'],
]);

$orders = $stmt->fetchAll();
?>

<section class="section">
    <h1>Mon espace</h1>

    <?php if (isset($_GET['review']) && $_GET['review'] === 'created'): ?>
        <div class="alert alert-success js-auto-hide">Votre avis à bien été envoyé.</div>
    <?php endif; ?>

    <div class="card p-4 mb-4">
        <p>Bienvenue <?= htmlspecialchars($user['first_name']) ?>.</p>
        <p>Rôle : <?= htmlspecialchars($user['role']) ?></p>

        <a href="?page=logout" class="btn btn-outline-danger">Se déconnecter</a>
    </div>

    <h2>Mes commandes</h2>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info">Vous n'avez pas encore passé de commande.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Commande</th>
                        <th>Menu</th>
                        <th>Date événement</th>
                        <th>Personnes</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Avis</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <?php
                            $existingReview = findReviewByOrderId((int) $order['id']);
                            $canReview = in_array($order['status'], ['livre', 'terminee'], true);
                        ?>

                        <tr>
                            <td>#<?= (int) $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['menu_title']) ?></td>
                            <td><?= htmlspecialchars($order['event_date']) ?> <?= htmlspecialchars($order['event_time']) ?></td>
                            <td><?= (int) $order['people_count'] ?></td>
                            <td><?= number_format((float) $order['total_price'], 2, ',', ' ') ?> €</td>
                            <td><?= htmlspecialchars($statuses[$order['status']] ?? $order['status']) ?></td>
                            <td>
                                <?php if ($existingReview !== null): ?>
                                    <span class="badge text-bg-success">Avis envoyé</span>
                                <?php elseif ($canReview): ?>
                                    <a
                                        href="?page=review-create&order_id=<?= (int) $order['id'] ?>"
                                        class="btn btn-sm btn-primary"
                                    >
                                        Déposer un avis
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Après livraison</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>