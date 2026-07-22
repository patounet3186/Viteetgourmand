<section class="section">
    <h1>Modifier un menu</h1>
    <p>
        Modifiez les informations, la galerie et la composition de
        <strong><?= htmlspecialchars($menu['title']) ?></strong>.
    </p>

    <?php if ($menuErrors !== []): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                <?php foreach ($menuErrors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post"
        action="?page=employee-menu-edit&amp;id=<?= (int) $menu['id'] ?>"
        class="card p-4">
        <input type="hidden" name="action" value="update_menu">
        <?= csrf_field() ?>

        <?php $formIdPrefix = 'edit_menu'; ?>
        <?php require __DIR__ . '/_menu-form-fields.php'; ?>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
                Enregistrer les modifications
            </button>
            <a href="?page=employee-menus" class="btn btn-outline-secondary">
                Annuler
            </a>
        </div>
    </form>
</section>
