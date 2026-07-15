<section class="section">
    <h1>Commander : <?= htmlspecialchars($menu['title']) ?></h1>

    <div class="alert alert-warning">
        <strong>Conditions :</strong> <?= htmlspecialchars($menu['conditions_text']) ?>
    </div>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>

    <?php if ($success): ?>
        <div class="alert alert-success js-auto-hide"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" class="card p-4 mt-4">
      <?= csrf_field() ?>
        <input type="hidden" name="menu_id" value="<?= (int) $menu['id'] ?>">

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Date de prestation</label>
                <input type="date" name="event_date" class="form-control" value="<?= htmlspecialchars($form['event_date']) ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Heure souhaitée</label>
                <input type="time" name="event_time" class="form-control" value="<?= htmlspecialchars($form['event_time']) ?>" required>
            </div>

            <div class="col-md-8">
                <label class="form-label">Adresse de livraison</label>
                <input type="text" name="delivery_address" class="form-control" value="<?= htmlspecialchars($form['delivery_address']) ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Ville</label>
                <input type="text" name="delivery_city" class="form-control" value="<?= htmlspecialchars($form['delivery_city']) ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Nombre de personnes</label>
                <input type="number" name="people_count" class="form-control" min="<?= (int) $menu['min_people'] ?>" value="<?= htmlspecialchars($form['people_count']) ?>" required>
            </div>
        </div>

        <p class="mt-4">
            Prix de base : <?= number_format((float) $menu['base_price'], 2, ',', ' ') ?> €
            pour <?= (int) $menu['min_people'] ?> personnes minimum.
        </p>

        <button type="submit" class="btn-app mt-3">Valider la commande</button>
    </form>
</section>
