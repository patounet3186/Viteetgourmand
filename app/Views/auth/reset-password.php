<section class="section auth-section">
    <h1>Nouveau mot de passe</h1>

    <?php if (!$tokenValid): ?>
        <div class="alert alert-danger" role="alert">
            Ce lien est invalide, expiré ou a déjà été utilisé.
        </div>
        <a href="?page=forgot-password" class="btn btn-primary">
            Demander un nouveau lien
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
            <input type="hidden" name="token" value="<?= htmlspecialchars($rawToken) ?>">

            <div class="mb-3">
                <label for="new_password" class="form-label">Nouveau mot de passe</label>
                <input type="password" id="new_password" name="password"
                    class="form-control" autocomplete="new-password" required>
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">
                    Confirmation
                </label>
                <input type="password" id="password_confirmation"
                    name="password_confirmation" class="form-control"
                    autocomplete="new-password" required>
            </div>

            <p class="form-text">
                Utilisez au moins 10 caractères avec une majuscule, une
                minuscule, un chiffre et un caractère spécial.
            </p>

            <button type="submit" class="btn btn-primary align-self-start">
                Modifier mon mot de passe
            </button>
        </form>
    <?php endif; ?>
</section>
