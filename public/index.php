<?php

declare(strict_types=1);

session_start();

ini_set('display_errors', '1');
error_reporting(E_ALL);

$page = $_GET['page'] ?? 'home';

$routes = [
    'home' => __DIR__ .'/../app/Views/pages/home.php',
    'menus' => __DIR__ . '/../app/Views/menus/index.php',
    'menu-show' => __DIR__ .'/../app/Views/menus/show.php',
];

$view = $routes[$page] ?? $routes['home'];
$title =  match ($page) {
  'menus' => 'Nos menu',
  'menu-show' => 'Detail de menu',
  default => 'Accueil',
};

require_once __DIR__ . '/../app/Views/layouts/main.php';