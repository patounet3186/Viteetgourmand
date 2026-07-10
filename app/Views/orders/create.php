<?php

require_once __DIR__ . '/../../../config/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: ?page=login');
    exit;
}

$pdo = getDatabase();
$user = $_SESSION['user'];
$menuId = (int) ($_GET['menu_id'] ?? $_POST['menu_id'] ?? 0);
$errors = [];
$success = null;

$stmt = $pdo->prepare('SELECT * FROM menus WHERE id = :id AND is_active = 1');
$stmt->execute(['id' => $menuId]);
$menu = $stmt->fetch();

if (!$menu) {
    echo '<section class="section"><h1>Menu introuvable</h1></section>';
    return;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventDate = $_POST['event_date'] ?? '';
    $eventTime = $_POST['event_time'] ?? '';
    $deliveryAddress = trim($_POST['delivery_address'] ?? '');
    $deliveryCity = trim($_POST['delivery_city'] ?? '');
    $peopleCount = (int) ($_POST['people_count'] ?? 0);

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Le formulaire a expiré, merci de réessayer.';
    }

    if ($eventDate === '' || $eventTime === '' || $deliveryAddress === '' || $deliveryCity === '') {
        $errors[] = 'Tous les champs de prestation sont obligatoires.';
    }

    if ($peopleCount < (int) $menu['min_people']) {
        $errors[] = 'Le nombre de personnes doit respecter le minimum du menu.';
    }

    if (empty($errors)) {
        $basePrice = (float) $menu['base_price'];
        $minPeople = (int) $menu['min_people'];

        $menuPrice = $basePrice * ($peopleCount / $minPeople);

        $discount = 0;
        if ($peopleCount >= $minPeople + 5) {
            $discount = $menuPrice * 0.10;
        }

        $deliveryPrice = strtolower($deliveryCity) === 'bordeaux' ? 0 : 5;
        $total = $menuPrice + $deliveryPrice - $discount;

        $insert = $pdo->prepare("
            INSERT INTO orders
            (user_id, menu_id, event_date, event_time, delivery_address, delivery_city, people_count, menu_price, delivery_price, discount_amount, total_price)
            VALUES
            (:user_id, :menu_id, :event_date, :event_time, :delivery_address, :delivery_city, :people_count, :menu_price, :delivery_price, :discount_amount, :total_price)
        ");

        $insert->execute([
            'user_id' => $user['id'],
            'menu_id' => $menu['id'],
            'event_date' => $eventDate,
            'event_time' => $eventTime,
            'delivery_address' => $deliveryAddress,
            'delivery_city' => $deliveryCity,
            'people_count' => $peopleCount,
            'menu_price' => $menuPrice,
            'delivery_price' => $deliveryPrice,
            'discount_amount' => $discount,
            'total_price' => $total,
        ]);

        $success = 'Commande enregistrée avec succès.';
    }
}
?>

<section class="section">
    <h1>Commander : <?= htmlspecialchars($menu['title']) ?></h1>

    <div class="alert alert-warning">
        <strong>Conditions :</strong> <?= htmlspecialchars($menu['conditions_text']) ?>
    </div>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>

    <?php if ($success): ?>
        <div class="alert alert-success" js-auto-hide><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" class="card p-4 mt-4">
      <?= csrf_field() ?>
        <input type="hidden" name="menu_id" value="<?= (int) $menu['id'] ?>">

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Date de prestation</label>
                <input type="date" name="event_date" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Heure souhaitée</label>
                <input type="time" name="event_time" class="form-control" required>
            </div>

            <div class="col-md-8">
                <label class="form-label">Adresse de livraison</label>
                <input type="text" name="delivery_address" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Ville</label>
                <input type="text" name="delivery_city" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Nombre de personnes</label>
                <input type="number" name="people_count" class="form-control" min="<?= (int) $menu['min_people'] ?>" required>
            </div>
        </div>

        <p class="mt-4">
            Prix de base : <?= number_format((float) $menu['base_price'], 2, ',', ' ') ?> €
            pour <?= (int) $menu['min_people'] ?> personnes minimum.
        </p>

        <button type="submit" class="btn-app mt-3">Valider la commande</button>
    </form>
</section>