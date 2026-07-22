<section class="section auth-section">
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
                <label for="register_first_name" class="form-label">Prénom</label>
                <input type="text" id="register_first_name" name="first_name"
                    class="form-control" autocomplete="given-name"
                    minlength="2" maxlength="100"
                    value="<?= htmlspecialchars($form['first_name']) ?>" required>
            </div>

            <div class="col-md-6">
                <label for="register_last_name" class="form-label">Nom</label>
                <input type="text" id="register_last_name" name="last_name"
                    class="form-control" autocomplete="family-name"
                    minlength="2" maxlength="100"
                    value="<?= htmlspecialchars($form['last_name']) ?>" required>
            </div>

            <div class="col-md-6">
                <label for="register_email" class="form-label">Adresse e-mail</label>
                <input type="email" id="register_email" name="email"
                    class="form-control" autocomplete="email"
                    maxlength="180"
                    value="<?= htmlspecialchars($form['email']) ?>" required>
            </div>

            <div class="col-md-6">
                <label for="register_phone" class="form-label">Téléphone</label>
                <input type="tel" id="register_phone" name="phone"
                    class="form-control" autocomplete="tel"
                    maxlength="30"
                    value="<?= htmlspecialchars($form['phone']) ?>">
            </div>

            <div class="col-md-12">
                <label for="register_address" class="form-label">Adresse</label>
                <input type="text" id="register_address" name="address"
                    class="form-control" autocomplete="street-address"
                    maxlength="255"
                    value="<?= htmlspecialchars($form['address']) ?>">
            </div>

            <div class="col-md-4">
                <label for="register_postal_code" class="form-label">Code postal</label>
                <input type="text" id="register_postal_code" name="postal_code"
                    class="form-control" autocomplete="postal-code"
                    maxlength="20"
                    value="<?= htmlspecialchars($form['postal_code']) ?>">
            </div>

            <div class="col-md-8">
                <label for="register_city" class="form-label">Ville</label>
                <input type="text" id="register_city" name="city"
                    class="form-control" autocomplete="address-level2"
                    maxlength="100"
                    value="<?= htmlspecialchars($form['city']) ?>">
            </div>

            <div class="col-md-12">
                <label for="register_password" class="form-label">Mot de passe</label>
                <input type="password" id="register_password" name="password"
                    class="form-control" autocomplete="new-password"
                    aria-describedby="password_help" required>
                <div id="password_help" class="form-text">
                    10 caractères minimum avec majuscule, minuscule, chiffre
                    et caractère spécial.
                </div>
            </div>

            <div class="col-12">
                <div class="form-check">
                    <input type="checkbox" id="terms_accepted" name="terms_accepted"
                        class="form-check-input" value="1"
                        <?= $termsAccepted ? 'checked' : '' ?> required>
                    <label for="terms_accepted" class="form-check-label">
                        J’accepte les
                        <a href="?page=terms" target="_blank">conditions générales</a>
                        et la
                        <a href="?page=privacy" target="_blank">politique de confidentialité</a>.
                    </label>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-app mt-4">Créer mon compte</button>
    </form>
</section>
