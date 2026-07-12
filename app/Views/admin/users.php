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
    'user' => 'Client',
    'employee' => 'Employé',
    'admin' => 'Administrateur',
];

$errors = [];
$employeeForm = [
  'first_name' => '',
  'last_name' => '',
  'email' => '',
  'is_active' => '1',
];

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['action'] ?? '') === 'create_employee'
) {
    $employeeForm = [
      'first_name' => trim((string) ($_POST['first_name'] ?? '')),
      'last_name' => trim((string) ($_POST['last_name'] ?? '')),
      'email' => trim((string) ($_POST['email'] ?? '')),
      'is_active' => (string) ($_POST['is_active'] ?? '1'),
    ];

    $password = (string) ($_POST['password'] ?? '');

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Le formulaire a expiré, merci de réessayer.';
    }

    if (
        mb_strlen($employeeForm['first_name']) < 2
        || mb_strlen($employeeForm['first_name']) > 100
    ) {
        $errors[] = 'Le prénom doit contenir entre 2 et 100 caractères.';
    }

    if (
        mb_strlen($employeeForm['last_name']) < 2
        || mb_strlen($employeeForm['last_name']) > 100
    ) {
        $errors[] = 'Le nom doit contenir entre 2 et 100 caractères.';
    }

    if (
        !filter_var($employeeForm['email'], FILTER_VALIDATE_EMAIL)
        || mb_strlen($employeeForm['email']) > 180
    ) {
        $errors[] = 'L’adresse e-mail est invalide.';
    }

    if (!preg_match(
        '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{10,}$/',
        $password
    )) {
        $errors[] = 'Le mot de passe doit contenir au moins 10 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.';
    }

    if (!in_array($employeeForm['is_active'], ['0', '1'], true)) {
        $errors[] = 'Le statut du compte est invalide.';
    }

    if (empty($errors)) {
        $emailCheck = $pdo->prepare(
            'SELECT id FROM users WHERE email = :email LIMIT 1'
        );
        $emailCheck->execute(['email' => $employeeForm['email']]);

        if ($emailCheck->fetch()) {
            $errors[] = 'Un compte existe déjà avec cette adresse e-mail.';
        }
    }

    if (empty($errors)) {
        $insertEmployee = $pdo->prepare(
            "INSERT INTO users
                (role, first_name, last_name, email, password_hash, is_active)
             VALUES
                ('employee', :first_name, :last_name, :email, :password_hash, :is_active)"
        );

        $insertEmployee->execute([
            'first_name' => $employeeForm['first_name'],
            'last_name' => $employeeForm['last_name'],
            'email' => $employeeForm['email'],
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'is_active' => (int) $employeeForm['is_active'],
        ]);

        header('Location: ?page=admin-users&employee_created=1');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['action'] ?? '') === 'update_employee_status'
) {
  if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
    header('Location: ?page=admin-users&csrf=1');
    exit;
  }

  $userId = (int) ($_POST['user_id'] ?? 0);
  $isActive = (string) ($_POST['is_active'] ?? '');

  if ($userId <= 0) {
    $errors[] = 'Utilisateur invalide.';
  }

  if (!in_array($isActive, ['0', '1'], true)) {
    $errors[] = 'Statut invalide.';
  }

  if (empty($errors)) {
      $targetStmt = $pdo->prepare(
          'SELECT role FROM users WHERE id = :id'
      );
      $targetStmt->execute(['id' => $userId]);
      $targetUser = $targetStmt->fetch();
      if (!$targetUser) {
          $errors[] = 'Utilisateur introuvable.';
      } elseif ($targetUser['role'] !== 'employee') {
          $errors[] = 'Seul le statut d’un employé peut être modifié.';
      }
  }

  if (empty($errors)) {
      $updateStmt = $pdo->prepare(
          "UPDATE users
           SET is_active = :is_active
           WHERE id = :id AND role = 'employee'"
      );
      $updateStmt->execute([
          'is_active' => (int) $isActive,
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
    <h1>Gestion des accès employés</h1>
    <p class="text-muted">
        Cette page affiche uniquement les informations nécessaires à la gestion des rôles et des accès.
    </p>
    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success js-auto-hide">Utilisateur mis à jour.</div>
    <?php endif; ?>

    <?php if (isset($_GET['csrf'])): ?>
        <div class="alert alert-danger js-auto-hide">Le formulaire a expiré, merci de réessayer.</div>
    <?php endif; ?>
    <?php if (($_GET['employee_created'] ?? '') === '1'): ?>
        <div class="alert alert-success js-auto-hide">
            Le compte employé a bien été créé.
        </div>
    <?php endif; ?>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>
    <h2 class="h3 mt-4">Créer un employé</h2>

    <form method="post" class="card p-4 mb-4">
        <input type="hidden" name="action" value="create_employee">
        <?= csrf_field() ?>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="employee_first_name" class="form-label">Prénom</label>
                <input
                    type="text"
                    id="employee_first_name"
                    name="first_name"
                    class="form-control"
                    value="<?= htmlspecialchars($employeeForm['first_name']) ?>"
                    maxlength="100"
                    required
                >
            </div>

            <div class="col-md-6">
                <label for="employee_last_name" class="form-label">Nom</label>
                <input
                    type="text"
                    id="employee_last_name"
                    name="last_name"
                    class="form-control"
                    value="<?= htmlspecialchars($employeeForm['last_name']) ?>"
                    maxlength="100"
                    required
                >
            </div>

            <div class="col-md-6">
                <label for="employee_email" class="form-label">Adresse e-mail</label>
                <input
                    type="email"
                    id="employee_email"
                    name="email"
                    class="form-control"
                    value="<?= htmlspecialchars($employeeForm['email']) ?>"
                    maxlength="180"
                    required
                >
            </div>

            <div class="col-md-6">
                <label for="employee_password" class="form-label">Mot de passe temporaire</label>
                <input
                    type="password"
                    id="employee_password"
                    name="password"
                    class="form-control"
                    minlength="10"
                    autocomplete="new-password"
                    required
                >
            </div>

            <div class="col-md-6">
                <label for="employee_is_active" class="form-label">État du compte</label>
                <select
                    id="employee_is_active"
                    name="is_active"
                    class="form-select"
                    required
                >
                    <option value="1" <?= $employeeForm['is_active'] === '1' ? 'selected' : '' ?>>
                        Actif
                    </option>
                    <option value="0" <?= $employeeForm['is_active'] === '0' ? 'selected' : '' ?>>
                        Inactif
                    </option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn-app">Créer l’employé</button>
    </form>

    <h2 class="h3">Comptes existants</h2>

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
                            <?php if ($user['role'] === 'employee'): ?>
                                <form method="post" class="d-flex gap-2">
                                    <?= csrf_field() ?>

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="update_employee_status"
                                    >

                                    <input
                                        type="hidden"
                                        name="user_id"
                                        value="<?= (int) $user['id'] ?>"
                                    >

                                    <select
                                        name="is_active"
                                        class="form-select form-select-sm"
                                        aria-label="État du compte employé"
                                    >
                                        <option value="1" <?= (int) $user['is_active'] === 1 ? 'selected' : '' ?>>
                                            Actif
                                        </option>
                                        <option value="0" <?= (int) $user['is_active'] === 0 ? 'selected' : '' ?>>
                                            Inactif
                                        </option>
                                    </select>

                                    <button type="submit" class="btn btn-sm btn-primary">
                                        Mettre à jour
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">Consultation uniquement</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>