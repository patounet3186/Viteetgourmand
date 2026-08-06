<section class="section">
    <h1>Déposer un avis</h1>

    <div class="card p-4">
        <p>Commande #<?= (int) $order['id'] ?> - <?= htmlspecialchars($order['menu_title']) ?></p>

        <?php if ($existingReview !== null): ?>
            <div class="alert alert-info" role="status">
                Vous avez déjà déposé un avis pour cette commande.
            </div>
            <a href="?page=account" class="btn btn-primary">Retour à mon espace</a>
        <?php else: ?>
            <?php if ($errors !== []): ?>
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post">
              <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="review_rating" class="form-label">Note</label>
                    <select id="review_rating" name="rating" class="form-select" required>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?= $i ?>" <?= $rating === $i ? 'selected' : '' ?>>
                                <?= $i ?>/5
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="review_comment" class="form-label">Commentaire</label>
                    <textarea id="review_comment" name="comment" class="form-control"
                        rows="5" minlength="10" maxlength="2000"
                        required><?= htmlspecialchars($comment) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Envoyer l'avis</button>
                <a href="?page=account" class="btn btn-outline-secondary">Annuler</a>
            </form>
        <?php endif; ?>
    </div>
</section>
