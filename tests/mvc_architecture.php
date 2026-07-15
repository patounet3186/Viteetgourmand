<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);
$failures = [];

$classes = [
    App\Core\Controller::class,
    App\Core\HttpException::class,
    App\Controllers\AdminController::class,
    App\Controllers\AuthController::class,
    App\Controllers\ContactController::class,
    App\Controllers\DishController::class,
    App\Controllers\HomeController::class,
    App\Controllers\MenuController::class,
    App\Controllers\OrderController::class,
    App\Controllers\ReviewController::class,
    App\Models\ContactMessage::class,
    App\Models\Dish::class,
    App\Models\Menu::class,
    App\Models\Order::class,
    App\Models\Review::class,
    App\Models\User::class,
];

foreach ($classes as $class) {
    if (!class_exists($class)) {
        $failures[] = 'Autoload failed for ' . $class;
    }
}

$rules = [
    [
        'directory' => $root . '/app/Views',
        'pattern' => '/\$_(?:GET|POST|SESSION|SERVER)|getDatabase\s*\(|->(?:prepare|query)\s*\(|\bheader\s*\(|require_once/',
        'message' => 'HTTP or data access found in view',
    ],
    [
        'directory' => $root . '/app/Controllers',
        'pattern' => '/\b(?:SELECT|INSERT\s+INTO|UPDATE\s+[a-z_]|DELETE\s+FROM)\b/i',
        'message' => 'SQL found in controller',
    ],
    [
        'directory' => $root . '/app/Models',
        'pattern' => '/\$_(?:GET|POST|SESSION|SERVER)|\bheader\s*\(/',
        'message' => 'HTTP access found in model',
    ],
];

foreach ($rules as $rule) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($rule['directory'], FilesystemIterator::SKIP_DOTS)
    );

    foreach ($files as $file) {
        if (!$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $content = file_get_contents($file->getPathname());

        if ($content !== false && preg_match($rule['pattern'], $content) === 1) {
            $relativePath = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
            $failures[] = $rule['message'] . ': ' . $relativePath;
        }
    }
}

if ($failures !== []) {
    foreach ($failures as $failure) {
        fwrite(STDERR, $failure . PHP_EOL);
    }

    exit(1);
}

echo 'MVC architecture checks passed.' . PHP_EOL;
