<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Controllers\OrderController;
use App\Core\Url;
use App\Services\OrderPricing;

$failures = [];

$assertSame = static function (
    mixed $expected,
    mixed $actual,
    string $message
) use (&$failures): void {
    if ($expected !== $actual) {
        $failures[] = $message
            . ' (attendu : ' . var_export($expected, true)
            . ', obtenu : ' . var_export($actual, true) . ')';
    }
};

$menu = [
    'min_people' => 4,
    'base_price' => 120.0,
];

$basePricing = OrderPricing::calculate($menu, 4, 'Bordeaux');
$assertSame(120.0, $basePricing['menu_price'], 'Prix au minimum');
$assertSame(0.0, $basePricing['delivery_price'], 'Livraison à Bordeaux');
$assertSame(0.0, $basePricing['discount_amount'], 'Absence de réduction');
$assertSame(120.0, $basePricing['total_price'], 'Total au minimum');

$discountPricing = OrderPricing::calculate($menu, 9, 'Mérignac');
$assertSame(270.0, $discountPricing['menu_price'], 'Prix proportionnel');
$assertSame(5.0, $discountPricing['delivery_price'], 'Livraison hors Bordeaux');
$assertSame(27.0, $discountPricing['discount_amount'], 'Réduction de 10 %');
$assertSame(248.0, $discountPricing['total_price'], 'Total avec réduction');

$transitions = OrderController::transitions();
$assertSame(
    ['accepte', 'annulee'],
    $transitions['nouvelle'],
    'Transitions depuis une nouvelle commande'
);
$assertSame([], $transitions['terminee'], 'Une commande terminée reste finale');
$assertSame([], $transitions['annulee'], 'Une commande annulée reste finale');

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/ECF-2026/public/index.php';
$_SERVER['HTTPS'] = '';
putenv('APP_URL');
$assertSame(
    'http://localhost/ECF-2026/public/images/menu.webp',
    Url::asset('public/images/menu.webp'),
    'URL d’asset locale'
);

if ($failures !== []) {
    foreach ($failures as $failure) {
        fwrite(STDERR, $failure . PHP_EOL);
    }
    exit(1);
}

echo 'Domain rules checks passed.' . PHP_EOL;
