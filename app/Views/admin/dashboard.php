<section class="section">
    <h1>Tableau de bord administrateur</h1>
    <?php if ($reviewUpdated): ?>
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
                        <td class="table-cell-wrap"><?= htmlspecialchars($stat['title']) ?></td>
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
