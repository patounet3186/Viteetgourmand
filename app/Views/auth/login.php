<section class="section auth-section">
    <h1>Connexion</h1>

    <?php if ($passwordReset): ?>
        <div class="alert alert-success" role="status">
            Votre mot de passe a été modifié. Vous pouvez vous connecter.
        </div>
    <?php endif; ?>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>

    <form method="post" class="card p-4 mt-4">
      <?= csrf_field() ?>
        <div class="mb-3">
            <label for="login_email" class="form-label">Adresse e-mail</label>
            <input type="email" id="login_email" name="email" class="form-control"
                autocomplete="email" value="<?= htmlspecialchars($email) ?>" required>
        </div>

        <div class="mb-3">
            <label for="login_password" class="form-label">Mot de passe</label>
            <input type="password" id="login_password" name="password"
                class="form-control" autocomplete="current-password" required>
        </div>

        <button type="submit" class="btn-app">Se connecter</button>
        <a href="?page=forgot-password" class="d-inline-block mt-3">
            Mot de passe oublié ?
        </a>
    </form>
</section>
