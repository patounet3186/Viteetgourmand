<?php

declare(strict_types=1);

session_start();
ob_start();

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
];

$view = $routes[$page] ?? $routes['home'];
$title =  match ($page) {
  'menus' => 'Nos menus',
  'menu-show' => 'Detail du menu',
  'order-create' => 'Commander',
  'register' => 'Inscription',
  'login' => 'Connexion',
  'account' => 'Mon espace',
  'logout' => 'Deconnexion',
  'employee-orders' => 'Gestion des commandes',
  default => 'Accueil',
};

require_once __DIR__ . '/../app/Views/layouts/main.php';