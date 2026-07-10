<?php

require_once __DIR__ . '/../../../config/database.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Le formulaire a expiré, merci de réessayer.';
    }

    if ($email === '' || $password === '') {
        $errors[] = 'Email et mot de passe obligatoires.';
    }

    if (empty($errors)) {
        $pdo = getDatabase();

        $stmt = $pdo->prepare("
            SELECT id, first_name, last_name, email, role, password_hash
            FROM users
            WHERE email = :email AND is_active = 1
        ");

        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Identifiants incorrects.';
        } else {
            session_regenerate_id(true);

            $_SESSION['user'] = [
                'id' => $user['id'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];

            header('Location: ?page=account');
            exit;
        }
    }
}
?>

<section class="section">
    <h1>Connexion</h1>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>

    <form method="post" class="card p-4 mt-4">
      <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Mot de passe</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn-app">Se connecter</button>
    </form>
</section>