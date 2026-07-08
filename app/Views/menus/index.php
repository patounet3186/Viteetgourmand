<?php

require_once __DIR__ . '/../../../config/database.php';

$pdo = getDatabase();

$stmt = $pdo->query("
    SELECT id, title, description, theme, diet, min_people, base_price, stock
    FROM menus
    WHERE is_active = 1
    ORDER BY created_at DESC
");

$menus = $stmt->fetchAll();
?>

<section class="section">
  <h1>Nos menus</h1>
  <p>Decouvrez les menus proposes par Vite & Gourmand.</p>
  <div class="card mt-4">
    <div class="card-body">
      <h2 class="h5">Filtrer les menus</h2>

      <div class="row g-3">
        <div class="col-md-3">
          <label for="filterMaxPrice" class="form-label">Prix maximum</label>
          <input type="number" id="filterMaxPrice" class="form-control" placeholder="Ex : 150">
        </div>

        <div class="col-md-3">
          <label for="filterTheme" class="form-label">Theme</label>
          <select id="filterTheme" class="form-select">
            <option value="">Tous</option>
            <option value="Noel">Noel</option>
            <option value="Paques">Paques</option>
            <option value="Classique">Classique</option>
          </select>
        </div>

        <div class="col-md-3">
          <label for="filterDiet" class="form-label">Regime</label>
          <select id="filterDiet" class="form-select">
            <option value="">Tous</option>
            <option value="classique">Classique</option>
            <option value="vegetarien">Vegetarien</option>
            <option value="vegan">Vegan</option>
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
    <div class="col-md-4">
      <article class="card h-100 menu-card" data-price="<?= (float) $menu['base_price'] ?>" data-theme="<?= htmlspecialchars($menu['theme']) ?>" data-diet="<?= htmlspecialchars($menu['diet']) ?>" data-people="<?= (int) $menu['min_people'] ?>">
        <div class="card-body">
          <h2 class="h4 card-title"><?= htmlspecialchars($menu['title']) ?></h2>
          <p class="card-text"><?= htmlspecialchars($menu['description']) ?></p>

          <p>Theme : <?= htmlspecialchars($menu['theme']) ?></p>
          <p>Regime : <?= htmlspecialchars($menu['diet']) ?></p>
          <p>Minimum : <?= (int) $menu['min_people'] ?> personnes</p>
          <p>Stock : <?= (int) $menu['stock'] ?></p>

          <p class="price">
            <?= number_format((float) $menu['base_price'], 2, ',', ' ') ?> €
          </p>

          <a class="btn" href="?page=menu-show&id=<?= (int) $menu['id'] ?>">Voir le detail</a>
        </div>
      </article>
    </div>
    <?php endforeach; ?>
  </div>
</section>