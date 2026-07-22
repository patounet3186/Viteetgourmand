<section class="section">
    <h1>Modifier la commande n°<?= (int) $order['id'] ?></h1>
    <p>
        Le menu <strong><?= htmlspecialchars($menu['title']) ?></strong>
        ne peut pas être remplacé. Les autres informations restent modifiables
        tant que la commande n’est pas acceptée.
    </p>

    <?php if ($errors !== []): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post"
        action="?page=order-edit&amp;id=<?= (int) $order['id'] ?>"
        class="card p-4 js-order-form">
        <?= csrf_field() ?>

        <?php $orderFormPrefix = 'edit_order'; ?>
        <?php require __DIR__ . '/_form-fields.php'; ?>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
                Enregistrer les modifications
            </button>
            <a href="?page=order-show&amp;id=<?= (int) $order['id'] ?>"
                class="btn btn-outline-secondary">
                Annuler
            </a>
        </div>
    </form>
</section>
