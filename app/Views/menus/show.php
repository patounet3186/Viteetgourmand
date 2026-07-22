<?php
$resolvedImages = [];

foreach ($images as $imageUrl) {
    $resolvedUrl = $imageUrl;
    if (!str_starts_with($resolvedUrl, 'http')) {
        $resolvedUrl = \App\Core\Url::asset($resolvedUrl);
    }
    $resolvedImages[] = $resolvedUrl;
}
?>

<section class="section">
    <a href="?page=menus" class="btn btn-outline-dark mb-4">
        Retour aux menus
    </a>

    <div class="menu-detail-layout">
        <?php if ($resolvedImages !== []): ?>
            <div id="menuGallery" class="carousel slide menu-gallery">
                <div class="carousel-inner">
                    <?php foreach ($resolvedImages as $index => $imageUrl): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                            <img src="<?= htmlspecialchars($imageUrl) ?>"
                                class="d-block w-100 menu-gallery-image"
                                alt="<?= htmlspecialchars($menu['title']) ?> - image <?= $index + 1 ?>">
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (count($resolvedImages) > 1): ?>
                    <button class="carousel-control-prev" type="button"
                        data-bs-target="#menuGallery" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Image précédente</span>
                    </button>
                    <button class="carousel-control-next" type="button"
                        data-bs-target="#menuGallery" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Image suivante</span>
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="menu-detail-content">
            <h1><?= htmlspecialchars($menu['title']) ?></h1>
            <p class="lead"><?= htmlspecialchars($menu['description']) ?></p>

            <dl class="row menu-facts">
                <dt class="col-sm-4">Thème</dt>
                <dd class="col-sm-8"><?= htmlspecialchars($menu['theme']) ?></dd>

                <dt class="col-sm-4">Régime</dt>
                <dd class="col-sm-8"><?= htmlspecialchars($menu['diet']) ?></dd>

                <dt class="col-sm-4">Minimum</dt>
                <dd class="col-sm-8"><?= (int) $menu['min_people'] ?> personnes</dd>

                <dt class="col-sm-4">Disponibilité</dt>
                <dd class="col-sm-8">
                    <?= (int) $menu['stock'] > 0
                        ? (int) $menu['stock'] . ' commande(s)'
                        : 'Indisponible' ?>
                </dd>
            </dl>

            <p class="price">
                <?= number_format((float) $menu['base_price'], 2, ',', ' ') ?> €
                pour <?= (int) $menu['min_people'] ?> personnes
            </p>

            <div class="alert alert-warning">
                <strong>Conditions à connaître avant de commander :</strong>
                <?= htmlspecialchars($menu['conditions_text']) ?>
            </div>

            <?php if ((int) $menu['stock'] > 0): ?>
                <a href="?page=order-create&amp;menu_id=<?= (int) $menu['id'] ?>"
                    class="btn-app">
                    Commander ce menu
                </a>
            <?php else: ?>
                <button type="button" class="btn btn-secondary" disabled>
                    Menu actuellement indisponible
                </button>
            <?php endif; ?>
        </div>
    </div>

    <section class="mt-5" aria-labelledby="menu-composition-title">
        <h2 id="menu-composition-title">Composition du menu</h2>

        <?php if ($dishes === []): ?>
            <p>Aucun plat n’est encore associé à ce menu.</p>
        <?php else: ?>
            <div class="row g-3 mt-1">
                <?php foreach ($dishes as $dish): ?>
                    <div class="col-md-4">
                        <article class="card h-100">
                            <div class="card-body">
                                <p class="text-uppercase small fw-bold mb-2">
                                    <?= htmlspecialchars(
                                        $categoryLabels[$dish['category']] ?? 'Plat'
                                    ) ?>
                                </p>
                                <h3 class="h5"><?= htmlspecialchars($dish['name']) ?></h3>
                                <p><?= htmlspecialchars($dish['description'] ?: '') ?></p>
                                <p class="small mb-0">
                                    <strong>Allergènes :</strong>
                                    <?= htmlspecialchars(
                                        $dish['allergens'] ?: 'Aucun allergène renseigné'
                                    ) ?>
                                </p>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</section>
