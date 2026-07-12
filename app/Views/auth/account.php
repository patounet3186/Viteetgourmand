<?php

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../Services/reviews.php';

if (!isset($_SESSION['user'])) {
    header('Location: ?page=login');
    exit;
}

$user = $_SESSION['user'];
$pdo = getDatabase();

$profileErrors = [];

$profileStmt = $pdo->prepare(
    'SELECT first_name, last_name, email, phone, address, postal_code, city
     FROM users
     WHERE id = :id AND is_active = 1'
);
$profileStmt->execute(['id' => $user['id']]);
$profile = $profileStmt->fetch();

if (!$profile) {
    session_unset();
    session_destroy();
    header('Location: ?page=login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['form_action'] ?? '') === 'update_profile') {
    $profile = [
        'first_name' => trim((string) ($_POST['first_name'] ?? '')),
        'last_name' => trim((string) ($_POST['last_name'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'phone' => trim((string) ($_POST['phone'] ?? '')),
        'address' => trim((string) ($_POST['address'] ?? '')),
        'postal_code' => trim((string) ($_POST['postal_code'] ?? '')),
        'city' => trim((string) ($_POST['city'] ?? '')),
    ];

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $profileErrors[] = 'Le formulaire a expiré, merci de réessayer.';
    }

    if (mb_strlen($profile['first_name']) < 2 || mb_strlen($profile['first_name']) > 100) {
        $profileErrors[] = 'Le prénom doit contenir entre 2 et 100 caractères.';
    }

    if (mb_strlen($profile['last_name']) < 2 || mb_strlen($profile['last_name']) > 100) {
        $profileErrors[] = 'Le nom doit contenir entre 2 et 100 caractères.';
    }

    if (!filter_var($profile['email'], FILTER_VALIDATE_EMAIL)) {
        $profileErrors[] = 'L’adresse e-mail est invalide.';
    }

    if (mb_strlen($profile['phone']) > 30
        || ($profile['phone'] !== '' && !preg_match('/^[0-9+().\s-]+$/', $profile['phone']))) {
        $profileErrors[] = 'Le numéro de téléphone est invalide.';
    }

    if (mb_strlen($profile['address']) > 255
        || mb_strlen($profile['postal_code']) > 20
        || mb_strlen($profile['city']) > 100) {
        $profileErrors[] = 'Une information est trop longue.';
    }

    if (empty($profileErrors)) {
        $emailCheck = $pdo->prepare(
            'SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1'
        );
        $emailCheck->execute(['email' => $profile['email'], 'id' => $user['id']]);

        if ($emailCheck->fetch()) {
            $profileErrors[] = 'Cette adresse e-mail est déjà utilisée.';
        }
    }

    if (empty($profileErrors)) {
        $update = $pdo->prepare(
            'UPDATE users
             SET first_name = :first_name, last_name = :last_name, email = :email,
                 phone = :phone, address = :address, postal_code = :postal_code, city = :city
             WHERE id = :id'
        );

        $update->execute([...$profile, 'id' => $user['id']]);

        $_SESSION['user']['first_name'] = $profile['first_name'];
        $_SESSION['user']['last_name'] = $profile['last_name'];
        $_SESSION['user']['email'] = $profile['email'];

        header('Location: ?page=account&profile=updated');
        exit;
    }
}

$statuses = [
    'nouvelle' => 'Nouvelle',
    'accepte' => 'Acceptée',
    'en_preparation' => 'En préparation',
    'en_livraison' => 'En livraison',
    'livre' => 'Livrée',
    'attente_materiel' => 'Attente matériel',
    'terminee' => 'Terminée',
    'annulee' => 'Annulée',
];

$stmt = $pdo->prepare("
    SELECT
        orders.id,
        orders.event_date,
        orders.event_time,
        orders.people_count,
        orders.total_price,
        orders.status,
        orders.created_at,
        menus.title AS menu_title
    FROM orders
    INNER JOIN menus ON menus.id = orders.menu_id
    WHERE orders.user_id = :user_id
    ORDER BY orders.created_at DESC
");

$stmt->execute([
    'user_id' => $user['id'],
]);

$orders = $stmt->fetchAll();
?>

<section class="section">
    <h1>Mon espace</h1>

    <?php if (isset($_GET['review']) && $_GET['review'] === 'created'): ?>
        <div class="alert alert-success js-auto-hide">Votre avis a bien été envoyé.</div>
    <?php endif; ?>

    <?php if (isset($_GET['profile']) && $_GET['profile'] === 'updated'): ?>
        <div class="alert alert-success js-auto-hide">
            Vos informations ont bien été mises à jour.
        </div>
    <?php endif; ?>

    <div class="card p-4 mb-4">
        <p>Bienvenue <?= htmlspecialchars($user['first_name']) ?>.</p>
        <p>Rôle : <?= htmlspecialchars($user['role']) ?></p>

        <a href="?page=logout" class="btn btn-outline-danger">Se déconnecter</a>
    </div>

    <h2 class="h3">Mes informations</h2>

    <?php if (!empty($profileErrors)): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                <?php foreach ($profileErrors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="card p-4 mb-4">
        <input type="hidden" name="form_action" value="update_profile">
        <?= csrf_field() ?>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="first_name" class="form-label">Prénom</label>
                <input type="text" id="first_name" name="first_name" class="form-control"
                    value="<?= htmlspecialchars($profile['first_name']) ?>" required>
            </div>

            <div class="col-md-6">
                <label for="last_name" class="form-label">Nom</label>
                <input type="text" id="last_name" name="last_name" class="form-control"
                    value="<?= htmlspecialchars($profile['last_name']) ?>" required>
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label">Adresse e-mail</label>
                <input type="email" id="email" name="email" class="form-control"
                    value="<?= htmlspecialchars($profile['email']) ?>" required>
            </div>

            <div class="col-md-6">
                <label for="phone" class="form-label">Téléphone</label>
                <input type="tel" id="phone" name="phone" class="form-control"
                    value="<?= htmlspecialchars($profile['phone'] ?? '') ?>">
            </div>

            <div class="col-md-6">
                <label for="address" class="form-label">Adresse</label>
                <input type="text" id="address" name="address" class="form-control"
                    value="<?= htmlspecialchars($profile['address'] ?? '') ?>">
            </div>

            <div class="col-md-3">
                <label for="postal_code" class="form-label">Code postal</label>
                <input type="text" id="postal_code" name="postal_code" class="form-control"
                    value="<?= htmlspecialchars($profile['postal_code'] ?? '') ?>">
            </div>

            <div class="col-md-3">
                <label for="city" class="form-label">Ville</label>
                <input type="text" id="city" name="city" class="form-control"
                    value="<?= htmlspecialchars($profile['city'] ?? '') ?>">
            </div>
        </div>

        <button type="submit" class="btn-app">Enregistrer mes informations</button>
    </form>

    <h2>Mes commandes</h2>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info">Vous n'avez pas encore passé de commande.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Commande</th>
                        <th>Menu</th>
                        <th>Date événement</th>
                        <th>Personnes</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Avis</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <?php
                            $existingReview = findReviewByOrderId((int) $order['id']);
                            $canReview = in_array($order['status'], ['livre', 'terminee'], true);
                        ?>

                        <tr>
                            <td>#<?= (int) $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['menu_title']) ?></td>
                            <td><?= htmlspecialchars($order['event_date']) ?> <?= htmlspecialchars($order['event_time']) ?></td>
                            <td><?= (int) $order['people_count'] ?></td>
                            <td><?= number_format((float) $order['total_price'], 2, ',', ' ') ?> €</td>
                            <td><?= htmlspecialchars($statuses[$order['status']] ?? $order['status']) ?></td>
                            <td>
                                <?php if ($existingReview !== null): ?>
                                    <span class="badge text-bg-success">Avis envoyé</span>
                                <?php elseif ($canReview): ?>
                                    <a
                                        href="?page=review-create&order_id=<?= (int) $order['id'] ?>"
                                        class="btn btn-sm btn-primary"
                                    >
                                        Déposer un avis
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Après livraison</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>