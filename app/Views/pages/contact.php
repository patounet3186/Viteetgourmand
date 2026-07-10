<?php

require_once __DIR__ . '/../../../config/database.php';

$pdo = getDatabase();

$errors = [];
$fullName = '';
$email = '';
$subject = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Le formulaire a expiré, merci de réessayer.';
    }

    if (mb_strlen($fullName) < 2 || mb_strlen($fullName) > 100) {
        $errors[] = 'Le nom doit contenir entre 2 et 100 caractères.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'L’adresse e-mail est invalide.';
    }

    if (mb_strlen($subject) < 3 || mb_strlen($subject) > 150) {
        $errors[] = 'Le sujet doit contenir entre 3 et 150 caractères.';
    }

    if (mb_strlen($message) < 10 || mb_strlen($message) > 3000) {
        $errors[] = 'Le message doit contenir entre 10 et 3 000 caractères.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            'INSERT INTO contact_messages (full_name, email, subject, message)
             VALUES (:full_name, :email, :subject, :message)'
        );

        $stmt->execute([
            'full_name' => $fullName,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
        ]);

        header('Location: ?page=contact&sent=1');
        exit;
    }
}
?>
<section class="section">
    <h1>Nous contacter</h1>
    <p>Une question sur un menu ou une commande ? Écrivez-nous.</p>

    <?php if (isset($_GET['sent']) && $_GET['sent'] === '1'): ?>
        <div class="alert alert-success js-auto-hide">
            Votre message a bien été envoyé.
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="card p-4 mt-4">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="full_name" class="form-label">Nom complet</label>
            <input
                type="text"
                id="full_name"
                name="full_name"
                class="form-control"
                value="<?= htmlspecialchars($fullName) ?>"
                maxlength="100"
                autocomplete="name"
                required
            >
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Adresse e-mail</label>
            <input
                type="email"
                id="email"
                name="email"
                class="form-control"
                value="<?= htmlspecialchars($email) ?>"
                maxlength="180"
                autocomplete="email"
                required
            >
        </div>

        <div class="mb-3">
            <label for="subject" class="form-label">Sujet</label>
            <input
                type="text"
                id="subject"
                name="subject"
                class="form-control"
                value="<?= htmlspecialchars($subject) ?>"
                maxlength="150"
                required
            >
        </div>

        <div class="mb-3">
            <label for="message" class="form-label">Message</label>
            <textarea
                id="message"
                name="message"
                class="form-control"
                rows="6"
                maxlength="3000"
                required
            ><?= htmlspecialchars($message) ?></textarea>
        </div>

        <button type="submit" class="btn-app">Envoyer le message</button>
    </form>
</section>