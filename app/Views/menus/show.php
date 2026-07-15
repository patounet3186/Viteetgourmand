<?php
$imageUrl = $menu['image_url'] ?? '';
if ($imageUrl !== '' && !str_starts_with($imageUrl, 'http')) {
    $imageUrl = '/ECF-2026/' . ltrim($imageUrl, '/');
}

$imageStyle = $imageUrl !== '' ? "--menu-image: url('" . htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') . "');" : '';
?>

<section class="section">
  <a href="?page=menus" class="btn btn-outline-secondary mb-4">Retour aux menus</a>

  <div class="card">
    <div class="card-body <?= $imageUrl !== '' ? 'menu-detail-body-image' : '' ?>" <?= $imageStyle !== '' ? 'style="' . $imageStyle . '"' : '' ?>>
      <h1><?= htmlspecialchars($menu['title']) ?></h1>

      <p><?= htmlspecialchars($menu['description']) ?></p>

      <ul>
        <li>Thème : <?= htmlspecialchars($menu['theme']) ?></li>
        <li>Régime : <?= htmlspecialchars($menu['diet']) ?></li>
        <li>Minimum : <?= (int) $menu['min_people'] ?> personnes</li>
        <li>Stock disponible : <?= (int) $menu['stock'] ?></li>
      </ul>
<section class="mt-4" aria-labelledby="menu-composition-title">
    <h2 id="menu-composition-title" class="h4">Composition du menu</h2>

    <?php if (empty($dishes)): ?>
        <p>Aucun plat n’est encore associé à ce menu.</p>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($dishes as $dish): ?>
                <div class="col-md-4">
                    <article class="menu-dish h-100">
                        <h3 class="h5">
                            <?= htmlspecialchars(
                                $categoryLabels[$dish['category']] ?? 'Plat'
                            ) ?>
                            :
                            <?= htmlspecialchars($dish['name']) ?>
                        </h3>

                        <?php if (!empty($dish['description'])): ?>
                            <p><?= htmlspecialchars($dish['description']) ?></p>
                        <?php endif; ?>

                        <p class="small mb-0">
                            <strong>Allergènes :</strong>
                            <?= htmlspecialchars(
                                !empty($dish['allergens'])
                                    ? $dish['allergens']
                                    : 'Non renseignés'
                            ) ?>
                        </p>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

      <p class="price">
        <?= number_format((float) $menu['base_price'], 2, ',', ' ') ?> €
      </p>

      <div class="alert alert-warning">
        <strong>Conditions :</strong>
        <?= htmlspecialchars($menu['conditions_text']) ?>
      </div>

      <a href="?page=order-create&menu_id=<?= (int) $menu['id'] ?>" class="btn-app">Commander ce menu</a>
    </div>
  </div>
</section>
