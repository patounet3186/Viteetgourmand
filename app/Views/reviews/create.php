<section class="section">
    <h1>Déposer un avis</h1>

    <div class="card p-4">
        <p>Commande #<?= (int) $order['id'] ?> - <?= htmlspecialchars($order['menu_title']) ?></p>

        <?php if ($existingReview !== null): ?>
            <div class="alert alert-info">Vous avez déjà déposé un avis pour cette commande.</div>
            <a href="?page=account" class="btn btn-primary">Retour à mon espace</a>
        <?php else: ?>
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>

            <form method="post">
              <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Note</label>
                    <select name="rating" class="form-select" required>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?= $i ?>" <?= $rating === $i ? 'selected' : '' ?>>
                                <?= $i ?>/5
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Commentaire</label>
                    <textarea name="comment" class="form-control" rows="5" required><?= htmlspecialchars($comment) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Envoyer l'avis</button>
                <a href="?page=account" class="btn btn-outline-secondary">Annuler</a>
            </form>
        <?php endif; ?>
    </div>
</section>
