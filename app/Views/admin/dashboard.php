<?php

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../Services/reviews.php';

if (!isset($_SESSION['user'])) {
    header('Location: ?page=login');
    exit;
}

if ($_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo '<section class="section"><h1>Accès refusé</h1></section>';
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_action'])) {
    $reviewId = $_POST['review_id'] ?? '';
    $reviewAction = $_POST['review_action'] ?? '';

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        header('Location: ?page=admin-dashboard&csrf=1');
        exit;
    }

    if ($reviewId !== '' && in_array($reviewAction, ['validated', 'refused'], true)) {
        updateReviewStatus($reviewId, $reviewAction);
        header('Location: ?page=admin-dashboard&review_updated=1');
        exit;
    }
}

$reviews = getReviews();
$pendingReviews = getReviewsByStatus('pending');

$totalReviews = count($reviews);
$pendingReviewsCount = count($pendingReviews);
$ratingSum = 0;

foreach ($reviews as $review) {
    $ratingSum += (int) ($review['rating'] ?? 0);
}

$averageRating = $totalReviews > 0 ? $ratingSum / $totalReviews : null;
?>

<section class="section">
    <h1>Tableau de bord administrateur</h1>
    <?php if (isset($_GET['review_updated'])): ?>
        <div class="alert alert-success js-auto-hide">Avis mis à jour.</div>
    <?php endif; ?>

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

        <div class="col-md-6">
            <div class="card p-4">
                <h2 class="h5">Avis en attente</h2>
                <p class="display-6"><?= $pendingReviewsCount ?></p>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card p-4">
                <h2 class="h5">Note moyenne</h2>
                <p class="display-6">
                    <?= $averageRating === null ? '-' : number_format($averageRating, 1, ',', ' ') . '/5' ?>
                </p>
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
    <h2 class="mt-5">Avis clients à valider</h2>

<?php if (empty($pendingReviews)): ?>
    <div class="alert alert-success">Aucun avis en attente.</div>
<?php else: ?>
    <div class="table-responsive mt-3">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Menu</th>
                    <th>Note</th>
                    <th>Commentaire</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingReviews as $review): ?>
                    <tr>
                        <td><?= htmlspecialchars($review['user_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($review['menu_title'] ?? '') ?></td>
                        <td><?= (int) ($review['rating'] ?? 0) ?>/5</td>
                        <td><?= htmlspecialchars($review['comment'] ?? '') ?></td>
                        <td><?= htmlspecialchars($review['created_at'] ?? '') ?></td>
                        <td>
                            <form method="post" class="d-inline">
                              <?= csrf_field() ?>
                                <input type="hidden" name="review_id" value="<?= htmlspecialchars($review['id'] ?? '') ?>">
                                <button type="submit" name="review_action" value="validated" class="btn btn-sm btn-success">
                                    Valider
                                </button>
                            </form>

                            <form method="post" class="d-inline">
                              <?= csrf_field() ?>
                                <input type="hidden" name="review_id" value="<?= htmlspecialchars($review['id'] ?? '') ?>">
                                <button type="submit" name="review_action" value="refused" class="btn btn-sm btn-outline-danger">
                                    Refuser
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

</section>