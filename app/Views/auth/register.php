<?php

require_once __DIR__ . '/../../../config/database.php';

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $postalCode = trim($_POST['postal_code'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Le formulaire a expiré, merci de réessayer.';
    }

    if ($firstName === '' || $lastName === '' || $email === '' || $password === '') {
        $errors[] = 'Les champs obligatoires doivent être remplis.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Adresse email invalide.';
    }

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{10,}$/', $password)) {
        $errors[] = 'Le mot de passe doit contenir 10 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.';
    }

    if (empty($errors)) {
        $pdo = getDatabase();

        $check = $pdo->prepare('SELECT id FROM users WHERE email = :email');
        $check->execute(['email' => $email]);

        if ($check->fetch()) {
            $errors[] = 'Un compte existe déjà avec cet email.';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO users
                (role, first_name, last_name, email, phone, address, postal_code, city, password_hash)
                VALUES
                ('user', :first_name, :last_name, :email, :phone, :address, :postal_code, :city, :password_hash)
            ");

            $stmt->execute([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'postal_code' => $postalCode,
                'city' => $city,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);

            $success = 'Compte créé avec succès. Vous pouvez maintenant vous connecter.';
        }
    }
}
?>

<section class="section">
    <h1>Création de compte</h1>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <a href="?page=login" class="btn">Se connecter</a>
    <?php endif; ?>

    <form method="post" class="card p-4 mt-4">
      <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Prénom</label>
                <input type="text" name="first_name" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Nom</label>
                <input type="text" name="last_name" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Téléphone</label>
                <input type="text" name="phone" class="form-control">
            </div>

            <div class="col-md-12">
                <label class="form-label">Adresse</label>
                <input type="text" name="address" class="form-control">
            </div>

            <div class="col-md-4">
                <label class="form-label">Code postal</label>
                <input type="text" name="postal_code" class="form-control">
            </div>

            <div class="col-md-8">
                <label class="form-label">Ville</label>
                <input type="text" name="city" class="form-control">
            </div>

            <div class="col-md-12">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="password" class="form-control" required>
            </div>
        </div>

        <button type="submit" class="btn mt-4">Créer mon compte</button>
    </form>
</section>