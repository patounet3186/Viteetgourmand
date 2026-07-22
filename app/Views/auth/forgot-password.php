<section class="section auth-section">
    <h1>Mot de passe oublié</h1>

    <?php if ($requestSent): ?>
        <div class="alert alert-success" role="status">
            Si un compte actif correspond à cette adresse, un lien valable une
            heure vient d’être envoyé.
        </div>
        <a href="?page=login" class="btn btn-outline-secondary">
            Retour à la connexion
        </a>
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

        <form method="post" class="card p-4 mt-4">
            <?= csrf_field() ?>
            <label for="reset_email" class="form-label">Adresse e-mail</label>
            <input type="email" id="reset_email" name="email"
                class="form-control" autocomplete="email"
                value="<?= htmlspecialchars($email) ?>" required>
            <button type="submit" class="btn btn-primary align-self-start mt-3">
                Envoyer le lien
            </button>
        </form>
    <?php endif; ?>
</section>
