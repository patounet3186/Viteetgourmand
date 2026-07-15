<section class="section">
    <h1>Création de compte</h1>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>

    <?php if ($success): ?>
        <div class="alert alert-success js-auto-hide"><?= htmlspecialchars($success) ?></div>
        <a href="?page=login" class="btn-app">Se connecter</a>
    <?php endif; ?>

    <form method="post" class="card p-4 mt-4">
      <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Prénom</label>
                <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($form['first_name']) ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Nom</label>
                <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($form['last_name']) ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($form['email']) ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Téléphone</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($form['phone']) ?>">
            </div>

            <div class="col-md-12">
                <label class="form-label">Adresse</label>
                <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($form['address']) ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Code postal</label>
                <input type="text" name="postal_code" class="form-control" value="<?= htmlspecialchars($form['postal_code']) ?>">
            </div>

            <div class="col-md-8">
                <label class="form-label">Ville</label>
                <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($form['city']) ?>">
            </div>

            <div class="col-md-12">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="password" class="form-control" required>
            </div>
        </div>

        <button type="submit" class="btn-app mt-4">Créer mon compte</button>
    </form>
</section>
