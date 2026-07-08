<?php

if (!isset($_SESSION['user'])) {
    header('Location: ?page=login');
    exit;
}

$user = $_SESSION['user'];
?>

<section class="section">
    <h1>Mon espace</h1>

    <div class="card p-4">
        <p>Bienvenue <?= htmlspecialchars($user['first_name']) ?>.</p>
        <p>Role : <?= htmlspecialchars($user['role']) ?></p>

        <a href="?page=logout" class="btn btn-outline-danger">Se deconnecter</a>
    </div>
</section>