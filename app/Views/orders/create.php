<section class="section">
    <h1>Commander : <?= htmlspecialchars($menu['title']) ?></h1>

    <div class="alert alert-warning">
        <strong>Conditions à connaître :</strong>
        <?= htmlspecialchars($menu['conditions_text']) ?>
    </div>

    <?php if ($errors !== []): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card p-4 mb-4">
        <h2 class="h5">Client</h2>
        <p class="mb-1">
            <?= htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']) ?>
        </p>
        <p class="mb-1"><?= htmlspecialchars($profile['email']) ?></p>
        <p class="mb-0">
            <?= htmlspecialchars($profile['phone'] ?: 'Téléphone non renseigné') ?>
        </p>
    </div>

    <form method="post"
        action="?page=order-create&amp;menu_id=<?= (int) $menu['id'] ?>"
        class="card p-4 js-order-form">
        <?= csrf_field() ?>
        <input type="hidden" name="menu_id" value="<?= (int) $menu['id'] ?>">

        <?php $orderFormPrefix = 'create_order'; ?>
        <?php require __DIR__ . '/_form-fields.php'; ?>

        <button type="submit" class="btn btn-primary align-self-start mt-4">
            Confirmer la commande
        </button>
    </form>
</section>
