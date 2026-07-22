<section class="section">
    <h1>Gestion des avis</h1>
    <p>Seuls les avis validés sont visibles sur la page d’accueil.</p>

    <?php if ($reviewUpdated): ?>
        <div class="alert alert-success js-auto-hide" role="status">
            L’avis a été modéré.
        </div>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($pendingReviews === []): ?>
        <div class="alert alert-success">Aucun avis en attente.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <caption class="visually-hidden">Avis en attente de modération</caption>
                <thead>
                    <tr>
                        <th scope="col">Client</th>
                        <th scope="col">Menu</th>
                        <th scope="col">Note</th>
                        <th scope="col">Commentaire</th>
                        <th scope="col">Date</th>
                        <th scope="col">Décision</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingReviews as $review): ?>
                        <tr>
                            <td><?= htmlspecialchars($review['user_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($review['menu_title'] ?? '') ?></td>
                            <td><?= (int) ($review['rating'] ?? 0) ?>/5</td>
                            <td class="table-cell-wrap">
                                <?= htmlspecialchars($review['comment'] ?? '') ?>
                            </td>
                            <td><?= htmlspecialchars($review['created_at'] ?? '') ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <form method="post">
                                        <input type="hidden" name="action"
                                            value="moderate_review">
                                        <input type="hidden" name="review_id"
                                            value="<?= htmlspecialchars($review['id'] ?? '') ?>">
                                        <input type="hidden" name="review_status"
                                            value="validated">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-success">
                                            Valider
                                        </button>
                                    </form>
                                    <form method="post">
                                        <input type="hidden" name="action"
                                            value="moderate_review">
                                        <input type="hidden" name="review_id"
                                            value="<?= htmlspecialchars($review['id'] ?? '') ?>">
                                        <input type="hidden" name="review_status"
                                            value="refused">
                                        <?= csrf_field() ?>
                                        <button type="submit"
                                            class="btn btn-sm btn-outline-danger">
                                            Refuser
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
