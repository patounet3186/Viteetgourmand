<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\ContactController;
use App\Controllers\DishController;
use App\Controllers\HomeController;
use App\Controllers\HoursController;
use App\Controllers\MenuController;
use App\Controllers\OrderController;
use App\Controllers\PageController;
use App\Controllers\ReviewController;
use App\Core\Environment;
use App\Core\HttpException;
use App\Models\BusinessHour;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Review;

$isHttps = ($_SERVER['HTTPS'] ?? '') !== ''
    && ($_SERVER['HTTPS'] ?? '') !== 'off';
session_set_cookie_params([
    'httponly' => true,
    'secure' => $isHttps,
    'samesite' => 'Lax',
]);
session_start();
ob_start();

require_once __DIR__ . '/../vendor/autoload.php';
Environment::load(__DIR__ . '/../.env');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Services/csrf.php';

$host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
$isDevelopment = (getenv('APP_ENV') ?: '') === 'development'
    || str_starts_with($host, 'localhost')
    || str_starts_with($host, '127.0.0.1');
ini_set('display_errors', $isDevelopment ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header(
    "Content-Security-Policy: default-src 'self'; "
    . "img-src 'self' https: data:; "
    . "style-src 'self' 'unsafe-inline'; "
    . "script-src 'self' 'unsafe-inline'; "
    . "object-src 'none'; base-uri 'self'; "
    . "frame-ancestors 'self'; form-action 'self'"
);

if ($isHttps) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

$page = (string) ($_GET['page'] ?? 'home');

$routes = [
    'home' => [HomeController::class, 'index'],
    'menus' => [MenuController::class, 'index'],
    'menu-show' => [MenuController::class, 'show'],
    'register' => [AuthController::class, 'register'],
    'login' => [AuthController::class, 'login'],
    'forgot-password' => [AuthController::class, 'forgotPassword'],
    'reset-password' => [AuthController::class, 'resetPassword'],
    'account' => [AuthController::class, 'account'],
    'logout' => [AuthController::class, 'logout'],
    'order-create' => [OrderController::class, 'create'],
    'order-show' => [OrderController::class, 'show'],
    'order-edit' => [OrderController::class, 'edit'],
    'order-cancel' => [OrderController::class, 'cancel'],
    'employee-orders' => [OrderController::class, 'manage'],
    'employee-menus' => [MenuController::class, 'manage'],
    'employee-menu-edit' => [MenuController::class, 'edit'],
    'employee-dishes' => [DishController::class, 'index'],
    'employee-dish-edit' => [DishController::class, 'edit'],
    'employee-hours' => [HoursController::class, 'manage'],
    'admin-dashboard' => [AdminController::class, 'dashboard'],
    'review-create' => [ReviewController::class, 'create'],
    'employee-reviews' => [ReviewController::class, 'manage'],
    'admin-users' => [AdminController::class, 'users'],
    'contact' => [ContactController::class, 'index'],
    'legal-notice' => [PageController::class, 'legalNotice'],
    'terms' => [PageController::class, 'terms'],
    'privacy' => [PageController::class, 'privacy'],
];

try {
    if (!isset($routes[$page])) {
        throw new HttpException(
            404,
            'Page introuvable',
            'La page demandée n’existe pas.'
        );
    }

    [$controllerClass, $action] = $routes[$page];
    $pdo = getDatabase();
    $controller = new $controllerClass($pdo);
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
} catch (Throwable $exception) {
    error_log(
        sprintf(
            "%s: %s dans %s:%d",
            $exception::class,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        )
    );
    http_response_code(500);

    $response = [
        'view' => __DIR__ . '/../app/Views/errors/message.php',
        'title' => 'Erreur interne',
        'data' => [
            'heading' => 'Une erreur est survenue',
            'message' => $isDevelopment
                ? $exception->getMessage()
                : 'Le service est momentanément indisponible. Réessayez plus tard.',
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
try {
    $businessHours = isset($pdo)
        ? (new BusinessHour($pdo))->all()
        : BusinessHour::defaults();
} catch (Throwable $exception) {
    error_log('Horaires indisponibles : ' . $exception->getMessage());
    $businessHours = BusinessHour::defaults();
}
$navigationBadges = [
    'orders' => 0,
    'reviews' => 0,
    'account' => 0,
];

if ($currentUser !== null && isset($pdo)) {
    try {
        if (($currentUser['role'] ?? '') === 'user') {
            $navigationBadges['account'] = (new Notification($pdo))
                ->unreadCount((int) $currentUser['id']);
        }

        if (in_array($currentUser['role'] ?? '', ['employee', 'admin'], true)) {
            $navigationBadges['orders'] = (new Order($pdo))->countNew();
            $navigationBadges['reviews'] = (new Review())->countByStatus('pending');
        }
    } catch (Throwable) {
        // Navigation remains usable if an external service is temporarily unavailable.
    }
}

require_once __DIR__ . '/../app/Views/layouts/main.php';
