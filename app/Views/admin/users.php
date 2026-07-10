<?php

require_once __DIR__ . '/../../../config/database.php';

if (!isset($_SESSION['user'])) {
  header('Location: ?page=login');
  exit;
}

if ($_SESSION['user']['role'] !== 'admin') {
  http_response_code(403);
  echo '<section class="section"><h1>Accès refusé</h1></section>';
  return;
}

$pdo = getDatabase();
$currentUserId = (int) $_SESSION['user']['id'];

$roles = [
    'user' => 'Utilisateur',
    'employee' => 'Employé',
    'admin' => 'Administrateur',
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
    header('Location: ?page=admin-users&csrf=1');
    exit;
  }

  $userId = (int) ($_POST['user_id'] ?? 0);
  $role = $_POST['role'] ?? '';
  $isActive = isset($_POST['is_active']) ? (int) $_POST['is_active'] : -1;

  if ($userId === $currentUserId) {
    $errors[] = 'Vous ne pouvez pas modifier votre propre compte administrateur.';
  }

  if ($userId <= 0) {
    $errors[] = 'Utilisateur invalide.';
  }

  if (!array_key_exists($role, $roles)) {
    $errors[] = 'Rôle invalide.';
  }

  if (!in_array($isActive, [0, 1], true)) {
    $errors[] = 'Statut invalide.';
  }

  if (empty($errors)) {
    $stmt = $pdo->prepare("
        UPDATE users
        SET role = :role, is_active = :is_active
        WHERE id = :id
    ");

    $stmt->execute([
      'role' => $role,
      'is_active' => $isActive,
      'id' => $userId,
    ]);

    header('Location: ?page=admin-users&updated=1');
    exit;
  }
}

$stmt = $pdo->query("
    SELECT id, role, first_name, last_name, email, is_active, created_at
    FROM users
    ORDER BY created_at DESC
");

$users = $stmt->fetchAll();
?>

<section class="section">
    <h1>Gestion des rôles et des accès</h1>
    <p class="text-muted">
        Cette page affiche uniquement les informations nécessaires à la gestion des rôles et des accès.
    </p>
    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success js-auto-hide">Utilisateur mis à jour.</div>
    <?php endif; ?>

    <?php if (isset($_GET['csrf'])): ?>
        <div class="alert alert-danger js-auto-hide">Le formulaire a expiré, merci de réessayer.</div>
    <?php endif; ?>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>État du compte</th>
                    <th>Créé le</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($users as $user): ?>
                    <?php $isCurrentUser = (int) $user['id'] === $currentUserId; ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                            <?php if ($isCurrentUser): ?>
                                <span class="badge text-bg-secondary">Compte actuel</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($roles[$user['role']] ?? $user['role']) ?></td>
                        <td>
                            <?= (int) $user['is_active'] === 1 ? 'Actif' : 'Inactif' ?>
                        </td>
                        <td><?= htmlspecialchars($user['created_at']) ?></td>
                        <td>
                            <?php if ($isCurrentUser): ?>
                                <span class="text-muted">Non modifiable</span>
                            <?php else: ?>
                                <form method="post" class="d-flex gap-2">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">

                                    <select name="role" class="form-select form-select-sm">
                                        <?php foreach ($roles as $value => $label): ?>
                                            <option value="<?= htmlspecialchars($value) ?>" <?= $user['role'] === $value ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <select name="is_active" class="form-select form-select-sm">
                                        <option value="1" <?= (int) $user['is_active'] === 1 ? 'selected' : '' ?>>Actif</option>
                                        <option value="0" <?= (int) $user['is_active'] === 0 ? 'selected' : '' ?>>Inactif</option>
                                    </select>

                                    <button type="submit" class="btn-app btn-sm">OK</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>