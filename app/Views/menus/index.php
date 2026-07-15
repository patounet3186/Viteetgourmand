<section class="section">
  <h1>Nos menus</h1>
  <p>Découvrez les menus proposés par Vite & Gourmand.</p>
  <div class="card mt-4">
    <div class="card-body">
      <h2 class="h5">Filtrer les menus</h2>

      <div class="row g-3">
        <div class="col-md-3">
          <label for="filterMaxPrice" class="form-label">Prix maximum</label>
          <input type="number" id="filterMaxPrice" class="form-control" placeholder="Ex : 150">
        </div>

        <div class="col-md-3">
          <label for="filterTheme" class="form-label">Thème</label>
          <select id="filterTheme" class="form-select">
            <option value="">Tous</option>
            <option value="Noël">Noël</option>
            <option value="Pâques">Pâques</option>
            <option value="Classique">Classique</option>
          </select>
        </div>

        <div class="col-md-3">
          <label for="filterDiet" class="form-label">Régime</label>
          <select id="filterDiet" class="form-select">
            <option value="">Tous</option>
            <option value="classique">Classique</option>
            <option value="végétarien">Végétarien</option>
            <option value="végan">Végan</option>
          </select>
        </div>

        <div class="col-md-3">
          <label for="filterPeople" class="form-label">Nombre de personnes</label>
          <input type="number" id="filterPeople" class="form-control" placeholder="Ex : 4">
        </div>
      </div>
    </div>
  </div>
  <div class="row g-4 mt-4">
    <?php foreach ($menus as $menu): ?>
    <?php
      $imageUrl = $menu['image_url'] ?? '';
      if ($imageUrl !== '' && !str_starts_with($imageUrl, 'http')) {
          $imageUrl = '/ECF-2026/' . ltrim($imageUrl, '/');
      }
      $imageStyle = $imageUrl !== '' ? "--menu-image: url('" . htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') . "');" : '';
    ?>
    <div class="col-md-4">
      <article class="card h-100 menu-card" data-price="<?= (float) $menu['base_price'] ?>" data-theme="<?= htmlspecialchars($menu['theme']) ?>" data-diet="<?= htmlspecialchars($menu['diet']) ?>" data-people="<?= (int) $menu['min_people'] ?>">
        <div class="card-body <?= $imageUrl !== '' ? 'menu-card-body-image' : '' ?>" <?= $imageStyle !== '' ? 'style="' . $imageStyle . '"' : '' ?>>
          <h2 class="h4 card-title"><?= htmlspecialchars($menu['title']) ?></h2>
          <p class="card-text"><?= htmlspecialchars($menu['description']) ?></p>

          <p>Thème : <?= htmlspecialchars($menu['theme']) ?></p>
          <p>Régime : <?= htmlspecialchars($menu['diet']) ?></p>
          <p>Minimum : <?= (int) $menu['min_people'] ?> personnes</p>
          <p>Stock : <?= (int) $menu['stock'] ?></p>

          <p class="price">
            <?= number_format((float) $menu['base_price'], 2, ',', ' ') ?> €
          </p>

          <a class="btn-app" href="?page=menu-show&id=<?= (int) $menu['id'] ?>">Voir le détail</a>
        </div>
      </article>
    </div>
    <?php endforeach; ?>
  </div>
</section>
