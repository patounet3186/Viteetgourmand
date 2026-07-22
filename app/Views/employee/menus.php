<section class="section">
    <h1>Gestion des menus</h1>
    <p>Créez les menus et gérez leur composition, leur stock et leur visibilité.</p>

    <?php if ($menuCreated): ?>
        <div class="alert alert-success js-auto-hide" role="status">
            Le menu a bien été ajouté.
        </div>
    <?php endif; ?>

    <?php if ($menuUpdated): ?>
        <div class="alert alert-success js-auto-hide" role="status">
            Le menu a bien été mis à jour.
        </div>
    <?php endif; ?>

    <?php if ($menuArchived): ?>
        <div class="alert alert-success js-auto-hide" role="status">
            Le menu a été archivé. Les anciennes commandes sont conservées.
        </div>
    <?php endif; ?>

    <?php if ($menuErrors !== []): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                <?php foreach ($menuErrors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <h2 class="h3 mt-5">Ajouter un menu</h2>

    <?php if ($dishes === []): ?>
        <div class="alert alert-warning">
            Créez d’abord au moins une entrée, un plat et un dessert dans
            <a href="?page=employee-dishes">la gestion des plats</a>.
        </div>
    <?php else: ?>
        <form method="post" class="card p-4 mb-5">
            <input type="hidden" name="action" value="create_menu">
            <?= csrf_field() ?>

            <?php $formIdPrefix = 'create_menu'; ?>
            <?php require __DIR__ . '/_menu-form-fields.php'; ?>

            <button type="submit" class="btn btn-primary align-self-start mt-4">
                Ajouter le menu
            </button>
        </form>
    <?php endif; ?>

    <h2 class="h3">Menus enregistrés</h2>

    <?php if ($menus === []): ?>
        <div class="alert alert-info">Aucun menu n’est enregistré.</div>
    <?php else: ?>
        <div class="table-responsive mt-3">
            <table class="table table-striped align-middle menu-management-table">
                <caption class="visually-hidden">Liste des menus enregistrés</caption>
                <thead>
                    <tr>
                        <th scope="col">Menu</th>
                        <th scope="col">Thème</th>
                        <th scope="col">Régime</th>
                        <th scope="col">Minimum</th>
                        <th scope="col">Prix</th>
                        <th scope="col">Plats</th>
                        <th scope="col">Stock et visibilité</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menus as $menu): ?>
                        <tr>
                            <td><?= htmlspecialchars($menu['title']) ?></td>
                            <td><?= htmlspecialchars($menu['theme']) ?></td>
                            <td><?= htmlspecialchars($menu['diet']) ?></td>
                            <td><?= (int) $menu['min_people'] ?> personnes</td>
                            <td><?= number_format((float) $menu['base_price'], 2, ',', ' ') ?> €</td>
                            <td><?= (int) $menu['dishes_count'] ?></td>
                            <td>
                                <form method="post"
                                    class="d-flex gap-2 align-items-center menu-management-form">
                                    <input type="hidden" name="action" value="update_menu_state">
                                    <input type="hidden" name="menu_id"
                                        value="<?= (int) $menu['id'] ?>">
                                    <?= csrf_field() ?>

                                    <input type="number" name="stock" min="0"
                                        class="form-control form-control-sm menu-stock-input"
                                        value="<?= (int) $menu['stock'] ?>"
                                        aria-label="Stock de <?= htmlspecialchars($menu['title']) ?>">

                                    <select name="is_active"
                                        class="form-select form-select-sm menu-status-select"
                                        aria-label="Visibilité de <?= htmlspecialchars($menu['title']) ?>">
                                        <option value="1"
                                            <?= (int) $menu['is_active'] === 1 ? 'selected' : '' ?>>
                                            Actif
                                        </option>
                                        <option value="0"
                                            <?= (int) $menu['is_active'] === 0 ? 'selected' : '' ?>>
                                            Inactif
                                        </option>
                                    </select>

                                    <button type="submit" class="btn btn-sm btn-primary text-nowrap">
                                        Mettre à jour
                                    </button>
                                </form>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="?page=employee-menu-edit&amp;id=<?= (int) $menu['id'] ?>"
                                        class="btn btn-sm btn-outline-primary">
                                        Modifier
                                    </a>
                                    <form method="post" class="js-confirm-form"
                                        data-confirm="Archiver ce menu ? Il ne sera plus commandable.">
                                        <input type="hidden" name="action" value="archive_menu">
                                        <input type="hidden" name="menu_id"
                                            value="<?= (int) $menu['id'] ?>">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Archiver
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
