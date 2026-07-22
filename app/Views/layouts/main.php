<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?> - Vite & Gourmand</title>
  <link
    rel="stylesheet"
    href="<?= htmlspecialchars(\App\Core\Url::asset('vendor/bootstrap/css/bootstrap.min.css')) ?>"
  >
  <link rel="stylesheet" href="<?= htmlspecialchars(\App\Core\Url::asset('css/style.css')) ?>">
</head>

<body>
  <a class="skip-link" href="#main-content">Aller au contenu principal</a>
  <header class="site-header">
    <nav class="navbar navbar-expand-lg bg-white w-100">
      <div class="container">
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
            <?php if ($currentUser === null || ($currentUser['role'] ?? '') === 'user'): ?>
              <li class="nav-item"><a class="nav-link" href="?page=home">Accueil</a></li>
              <li class="nav-item"><a class="nav-link" href="?page=menus">Menus</a></li>
              <li class="nav-item">
                  <a class="nav-link" href="?page=contact">Contact</a>
              </li>
            <?php endif; ?>
          <?php if ($currentUser !== null): ?>

            <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="?page=admin-dashboard">Admin</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="?page=admin-users">Accès</a>
                </li>
            <?php endif; ?>

            <?php if (in_array($currentUser['role'] ?? '', ['employee', 'admin'], true)): ?>
              <li class="nav-item">
                  <a class="nav-link" href="?page=employee-orders">
                    Commandes
                    <?php if ($navigationBadges['orders'] > 0): ?>
                      <span class="badge text-bg-danger">
                        <?= $navigationBadges['orders'] ?>
                        <span class="visually-hidden">nouvelles commandes</span>
                      </span>
                    <?php endif; ?>
                  </a>
              </li>

              <li class="nav-item">
                  <a class="nav-link" href="?page=employee-menus">Gestion des menus</a>
              </li>

              <li class="nav-item">
                  <a class="nav-link" href="?page=employee-dishes">Gestion des plats</a>
              </li>

              <li class="nav-item">
                  <a class="nav-link" href="?page=employee-hours">Horaires</a>
              </li>

              <li class="nav-item">
                  <a class="nav-link" href="?page=employee-reviews">
                    Avis
                    <?php if ($navigationBadges['reviews'] > 0): ?>
                      <span class="badge text-bg-warning">
                        <?= $navigationBadges['reviews'] ?>
                        <span class="visually-hidden">avis en attente</span>
                      </span>
                    <?php endif; ?>
                  </a>
              </li>
            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link" href="?page=account">
                  Mon espace
                  <?php if ($navigationBadges['account'] > 0): ?>
                    <span class="badge text-bg-danger">
                      <?= $navigationBadges['account'] ?>
                      <span class="visually-hidden">changements de commande</span>
                    </span>
                  <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <form method="post" action="?page=logout" class="nav-logout-form">
                  <?= csrf_field() ?>
                  <button type="submit" class="nav-link nav-logout-button">
                    Déconnexion
                  </button>
                </form>
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

  <main id="main-content" tabindex="-1">
    <?php require $view; ?>
  </main>

  <footer class="site-footer">
    <div>
      <p class="fw-bold mb-2">Horaires</p>
      <ul class="footer-hours">
        <?php foreach ($businessHours as $day): ?>
          <li>
            <span><?= htmlspecialchars($day['day_label']) ?></span>
            <?php if ((int) $day['is_closed'] === 1): ?>
              <span>Fermé</span>
            <?php else: ?>
              <span>
                <?= htmlspecialchars(substr((string) $day['first_open'], 0, 5)) ?>
                - <?= htmlspecialchars(substr((string) $day['first_close'], 0, 5)) ?>
                <?php if (!empty($day['second_open']) && !empty($day['second_close'])): ?>
                  / <?= htmlspecialchars(substr((string) $day['second_open'], 0, 5)) ?>
                  - <?= htmlspecialchars(substr((string) $day['second_close'], 0, 5)) ?>
                <?php endif; ?>
              </span>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <nav aria-label="Informations légales" class="footer-links">
      <a href="?page=legal-notice">Mentions légales</a>
      <a href="?page=terms">Conditions générales de vente</a>
      <a href="?page=privacy">Confidentialité</a>
    </nav>
  </footer>
  <script src="<?= htmlspecialchars(\App\Core\Url::asset('js/menu-filters.js')) ?>"></script>
  <script src="<?= htmlspecialchars(\App\Core\Url::asset('js/app.js')) ?>"></script>
  <script
    src="<?= htmlspecialchars(\App\Core\Url::asset('vendor/bootstrap/js/bootstrap.bundle.min.js')) ?>"
  ></script>
</body>

</html>
