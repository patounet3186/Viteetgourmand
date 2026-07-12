<?php

declare(strict_types=1);

session_start();
ob_start();
require_once __DIR__ . '/../app/Services/csrf.php';

ini_set('display_errors', '1');
error_reporting(E_ALL);

$page = $_GET['page'] ?? 'home';

$routes = [
    'home' => __DIR__ .'/../app/Views/pages/home.php',
    'menus' => __DIR__ . '/../app/Views/menus/index.php',
    'menu-show' => __DIR__ .'/../app/Views/menus/show.php',
    'register' => __DIR__ . '/../app/Views/auth/register.php',
    'login' => __DIR__ . '/../app/Views/auth/login.php',
    'account' => __DIR__ . '/../app/Views/auth/account.php',
    'logout' => __DIR__ . '/../app/Views/auth/logout.php',
    'order-create' => __DIR__ . '/../app/Views/orders/create.php',
    'employee-orders' => __DIR__ . '/../app/Views/employee/orders.php',
    'employee-menus' => __DIR__ . '/../app/Views/employee/menus.php',
    'admin-dashboard' => __DIR__ . '/../app/Views/admin/dashboard.php',
    'review-create' => __DIR__ . '/../app/Views/reviews/create.php',
    'admin-users' => __DIR__ . '/../app/Views/admin/users.php',
    'contact' => __DIR__ . '/../app/Views/pages/contact.php',
];

$view = $routes[$page] ?? $routes['home'];
$title =  match ($page) {
  'menus' => 'Nos menus',
  'menu-show' => 'Détail du menu',
  'order-create' => 'Commander',
  'register' => 'Inscription',
  'login' => 'Connexion',
  'account' => 'Mon espace',
  'logout' => 'Déconnexion',
  'employee-orders' => 'Gestion des commandes',
  'employee-menus' => 'Gestion des menus',
  'admin-dashboard' => 'Administration',
  'review-create' => 'Déposer un avis',
  'admin-users' => 'Gestion des rôles et des accès',
  'contact' => 'Contact',
  default => 'Accueil',
};

require_once __DIR__ . '/../app/Views/layouts/main.php';