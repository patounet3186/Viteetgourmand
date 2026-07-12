<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?> - Vite & Gourmand</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/ECF-2026/public/css/style.css">
</head>

<body>
  <header class="site-header">
    <nav class="navbar navbar-expand-lg bg-white w-100">
      <div class="container">
        <?php
        $isBackOffice = isset($_SESSION['user'])
            && in_array($_SESSION['user']['role'], ['employee', 'admin'], true);
        ?>

        <?php if ($isBackOffice): ?>
          <span class="navbar-brand fw-bold text-success mb-0">
            Vite & Gourmand
          </span>
        <?php else: ?>
          <a class="navbar-brand fw-bold text-success" href="?page=home">
            Vite & Gourmand
          </a>
        <?php endif; ?>
        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#mainNavbar"
          aria-controls="mainNavbar"
          aria-expanded="false"
          aria-label="Ouvrir le menu de navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
          <ul class="navbar-nav ms-auto">
            <?php if (!isset($_SESSION['user']) || $_SESSION['user']['role'] === 'user'): ?>
              <li class="nav-item"><a class="nav-link" href="?page=home">Accueil</a></li>
              <li class="nav-item"><a class="nav-link" href="?page=menus">Menus</a></li>
              <li class="nav-item">
                  <a class="nav-link" href="?page=contact">Contact</a>
              </li>
            <?php endif; ?>
          <?php if (isset($_SESSION['user'])): ?>

            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="?page=admin-dashboard">Admin</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="?page=admin-users">Accès</a>
                </li>
            <?php endif; ?>

            <?php if (in_array($_SESSION['user']['role'], ['employee', 'admin'], true)): ?>
              <li class="nav-item">
                  <a class="nav-link" href="?page=employee-orders">Commandes</a>
              </li>

              <li class="nav-item">
                  <a class="nav-link" href="?page=employee-menus">Gestion des menus</a>
              </li>
            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link" href="?page=account">Mon espace</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="?page=logout">Déconnexion</a>
            </li>

          <?php else: ?>

            <li class="nav-item">
                <a class="nav-link" href="?page=register">Inscription</a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="?page=login">Connexion</a>
            </li>

          <?php endif; ?>
          </ul>
        </div>
      </div>
    </nav>
  </header>

  <main>
    <?php require $view; ?>
  </main>

  <footer class="site-footer">
    <p>Horaires :<br> Mardi au Dimanche,<br>
        11h - 15h 17h - 23h</p>
    <a href="#">Mentions légales</a>
    <a href="#">CGV</a>
  </footer>
  <script src="/ECF-2026/public/js/menu-filters.js"></script>
  <script src="/ECF-2026/public/js/app.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>