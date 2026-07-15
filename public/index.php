<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\ContactController;
use App\Controllers\DishController;
use App\Controllers\HomeController;
use App\Controllers\MenuController;
use App\Controllers\OrderController;
use App\Controllers\ReviewController;
use App\Core\HttpException;

session_start();
ob_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Services/csrf.php';

ini_set('display_errors', '1');
error_reporting(E_ALL);

$page = (string) ($_GET['page'] ?? 'home');

$routes = [
    'home' => [HomeController::class, 'index'],
    'menus' => [MenuController::class, 'index'],
    'menu-show' => [MenuController::class, 'show'],
    'register' => [AuthController::class, 'register'],
    'login' => [AuthController::class, 'login'],
    'account' => [AuthController::class, 'account'],
    'logout' => [AuthController::class, 'logout'],
    'order-create' => [OrderController::class, 'create'],
    'employee-orders' => [OrderController::class, 'manage'],
    'employee-menus' => [MenuController::class, 'manage'],
    'employee-dishes' => [DishController::class, 'index'],
    'employee-dish-edit' => [DishController::class, 'edit'],
    'admin-dashboard' => [AdminController::class, 'dashboard'],
    'review-create' => [ReviewController::class, 'create'],
    'admin-users' => [AdminController::class, 'users'],
    'contact' => [ContactController::class, 'index'],
];

[$controllerClass, $action] = $routes[$page] ?? $routes['home'];

try {
    $controller = new $controllerClass(getDatabase());
    $response = $controller->$action();
} catch (HttpException $exception) {
    http_response_code($exception->getStatusCode());

    $response = [
        'view' => __DIR__ . '/../app/Views/errors/message.php',
        'title' => $exception->getPageTitle(),
        'data' => [
            'heading' => $exception->getPageTitle(),
            'message' => $exception->getMessage(),
        ],
    ];
}

$view = $response['view'];
$title = $response['title'];
extract($response['data'], EXTR_SKIP);
$currentUser = isset($_SESSION['user']) && is_array($_SESSION['user'])
    ? $_SESSION['user']
    : null;
$isBackOffice = $currentUser !== null
    && in_array($currentUser['role'] ?? '', ['employee', 'admin'], true);

require_once __DIR__ . '/../app/Views/layouts/main.php';
