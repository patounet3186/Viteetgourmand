<?php

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../Services/reviews.php';

if (!isset($_SESSION['user'])) {
    header('Location: ?page=login');
    exit;
}

$user = $_SESSION['user'];
$pdo = getDatabase();

$orderId = (int) ($_GET['order_id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT
        orders.id,
        orders.menu_id,
        orders.status,
        menus.title AS menu_title
    FROM orders
    INNER JOIN menus ON menus.id = orders.menu_id
    WHERE orders.id = :order_id
    AND orders.user_id = :user_id
");

$stmt->execute([
    'order_id' => $orderId,
    'user_id' => $user['id'],
]);

$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    echo '<section class="section"><h1>Commande non trouvée</h1></section>';
    return;
}

if (!in_array($order['status'], ['livre', 'terminee'], true)) {
    http_response_code(403);
    echo '<section class="section"><h1>Avis indisponible</h1><p>Vous pourrez laisser un avis après la livraison.</p></section>';
    return;
}

$existingReview = findReviewByOrderId($orderId);
$errors = [];
$rating = 5;
$comment = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $existingReview === null) {
    $rating = (int) ($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $errors[] = 'La note doit être comprise entre 1 et 5.';
    }

    if (mb_strlen($comment) < 10) {
        $errors[] = 'Le commentaire doit contenir au moins 10 caractères.';
    }

    if (empty($errors)) {
        addReview([
            'order_id' => $order['id'],
            'user_id' => $user['id'],
            'user_name' => $user['first_name'] . ' ' . $user['last_name'],
            'menu_id' => $order['menu_id'],
            'menu_title' => $order['menu_title'],
            'rating' => $rating,
            'comment' => $comment,
        ]);

        header('Location: ?page=account&review=created');
        exit;
    }
}
?>

<section class="section">
    <h1>Déposer un avis</h1>

    <div class="card p-4">
        <p>Commande #<?= (int) $order['id'] ?> - <?= htmlspecialchars($order['menu_title']) ?></p>

        <?php if ($existingReview !== null): ?>
            <div class="alert alert-info">Vous avez déjà dépose un avis pour cette commande.</div>
            <a href="?page=account" class="btn btn-primary">Retour a mon espace</a>
        <?php else: ?>
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>

            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Note</label>
                    <select name="rating" class="form-select" required>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?= $i ?>" <?= $rating === $i ? 'selected' : '' ?>>
                                <?= $i ?>/5
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Commentaire</label>
                    <textarea name="comment" class="form-control" rows="5" required><?= htmlspecialchars($comment) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Envoyer l'avis</button>
                <a href="?page=account" class="btn btn-outline-secondary">Annuler</a>
            </form>
        <?php endif; ?>
    </div>
</section>