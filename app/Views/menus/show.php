<?php

require_once __DIR__ . '/../../../config/database.php';

$pdo = getDatabase();

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT *
    FROM menus
    WHERE id = :id AND is_active = 1
");

$stmt->execute(['id' => $id]);
$menu = $stmt->fetch();

if (!$menu) {
    echo '<section class="section"><h1>Menu introuvable</h1></section>';
    return;
}
?>

<section class="section">
  <a href="?page=menus" class="btn btn-outline-secondary mb-4">Retour aux menus</a>

  <div class="card">
    <div class="card-body">
      <h1><?= htmlspecialchars($menu['title']) ?></h1>

      <p><?= htmlspecialchars($menu['description']) ?></p>

      <ul>
        <li>Theme : <?= htmlspecialchars($menu['theme']) ?></li>
        <li>Regime : <?= htmlspecialchars($menu['diet']) ?></li>
        <li>Minimum : <?= (int) $menu['min_people'] ?> personnes</li>
        <li>Stock disponible : <?= (int) $menu['stock'] ?></li>
      </ul>

      <p class="price">
        <?= number_format((float) $menu['base_price'], 2, ',', ' ') ?> €
      </p>

      <div class="alert alert-warning">
        <strong>Conditions :</strong>
        <?= htmlspecialchars($menu['conditions_text']) ?>
      </div>

      <a href="#" class="btn">Commander ce menu</a>
    </div>
  </div>
</section>