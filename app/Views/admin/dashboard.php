<section class="section">
    <h1>Tableau de bord administrateur</h1>
    <?php if ($reviewUpdated): ?>
        <div class="alert alert-success js-auto-hide">Avis mis à jour.</div>
    <?php endif; ?>

    <?php if ($filterError !== null): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($filterError) ?>
        </div>
    <?php endif; ?>

    <?php if ($analyticsWarning !== null): ?>
        <div class="alert alert-warning" role="alert">
            <?= htmlspecialchars($analyticsWarning) ?>
        </div>
    <?php endif; ?>

    <?php if ($reviewWarning !== null): ?>
        <div class="alert alert-warning" role="alert">
            <?= htmlspecialchars($reviewWarning) ?>
        </div>
    <?php endif; ?>

    <form method="get" class="card p-4 mt-4">
        <input type="hidden" name="page" value="admin-dashboard">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="stats_menu" class="form-label">Menu</label>
                <select id="stats_menu" name="menu_id" class="form-select">
                    <option value="">Tous les menus</option>
                    <?php foreach ($menusForFilter as $menu): ?>
                        <option value="<?= (int) $menu['id'] ?>"
                            <?= $selectedMenuId === (int) $menu['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($menu['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="date_from" class="form-label">Du</label>
                <input type="date" id="date_from" name="date_from"
                    class="form-control" value="<?= htmlspecialchars($dateFrom) ?>">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label">Au</label>
                <input type="date" id="date_to" name="date_to"
                    class="form-control" value="<?= htmlspecialchars($dateTo) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Filtrer</button>
            </div>
        </div>
    </form>

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
    <p class="text-muted">
        Source des données : <?= htmlspecialchars($analyticsSource) ?>
    </p>

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
                        <td class="table-cell-wrap"><?= htmlspecialchars($stat['title']) ?></td>
                        <td><?= (int) $stat['orders_count'] ?></td>
                        <td><?= number_format((float) $stat['turnover'], 2, ',', ' ') ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="chart-container mt-4">
        <canvas id="ordersByMenuChart"
            aria-label="Graphique du nombre de commandes par menu"
            role="img"></canvas>
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
                        <td class="table-cell-wrap"><?= htmlspecialchars($review['comment'] ?? '') ?></td>
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

<?php
$chartLabels = array_map(
    static fn (array $stat): string => (string) $stat['title'],
    $stats
);
$chartOrders = array_map(
    static fn (array $stat): int => (int) $stat['orders_count'],
    $stats
);
?>
<script src="<?= htmlspecialchars(
    \App\Core\Url::asset('vendor/chartjs/chart.umd.min.js')
) ?>"></script>
<script>
const chartCanvas = document.getElementById('ordersByMenuChart');

if (chartCanvas && window.Chart) {
    new Chart(chartCanvas, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chartLabels, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>,
            datasets: [{
                label: 'Nombre de commandes',
                data: <?= json_encode($chartOrders, JSON_HEX_TAG) ?>,
                backgroundColor: '#b83232',
                borderColor: '#952727',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });
}
</script>
