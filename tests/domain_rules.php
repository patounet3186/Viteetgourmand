<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Controllers\OrderController;
use App\Core\Url;
use App\Services\DishManagementService;
use App\Services\MenuFilterService;
use App\Services\MenuManagementService;
use App\Services\OrderPricing;
use App\Services\OrderWorkflowService;
use App\Services\PasswordPolicy;
use App\Services\UserRegistrationService;

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

$discountPricing = OrderPricing::calculate($menu, 9, 'Mérignac', 8.0);
$assertSame(270.0, $discountPricing['menu_price'], 'Prix proportionnel');
$assertSame(9.72, $discountPricing['delivery_price'], 'Livraison hors Bordeaux');
$assertSame(27.0, $discountPricing['discount_amount'], 'Réduction de 10 %');
$assertSame(252.72, $discountPricing['total_price'], 'Total avec réduction');

$transitions = OrderWorkflowService::transitions();
$assertSame(
    ['accepte', 'annulee'],
    $transitions['nouvelle'],
    'Transitions depuis une nouvelle commande'
);
$assertSame([], $transitions['terminee'], 'Une commande terminée reste finale');
$assertSame([], $transitions['annulee'], 'Une commande annulée reste finale');

$filters = MenuFilterService::normalize([
    'min_price' => '40',
    'max_price' => '150.50',
    'theme' => 'Noël',
    'diet' => 'végétarien',
    'people' => '8',
]);
$assertSame(40.0, $filters['min_price'], 'Prix minimum du filtre');
$assertSame(150.5, $filters['max_price'], 'Prix maximum du filtre');
$assertSame('Noël', $filters['theme'], 'Thème du filtre');
$assertSame('végétarien', $filters['diet'], 'Régime du filtre');
$assertSame(8, $filters['people'], 'Nombre de personnes du filtre');

$availableDishes = [
    ['id' => 1, 'category' => 'entree'],
    ['id' => 2, 'category' => 'plat'],
    ['id' => 3, 'category' => 'dessert'],
];
$menuForm = MenuManagementService::normalize([
    'title' => '  Menu de saison  ',
    'description' => 'Une composition gourmande préparée avec des produits frais.',
    'theme' => 'Printemps',
    'diet' => 'classique',
    'min_people' => '4',
    'base_price' => '120,50',
    'conditions_text' => 'À commander au moins quarante-huit heures à l’avance.',
    'stock' => '8',
    'image_urls' => 'public/images/menu-classique.webp',
    'dish_ids' => ['1', '2', '3'],
    'is_active' => '1',
]);
$assertSame('Menu de saison', $menuForm['title'], 'Normalisation du titre du menu');
$assertSame([], MenuManagementService::validate($menuForm, $availableDishes), 'Menu valide');
$menuForm['dish_ids'] = [1, 2];
$assertSame(
    true,
    MenuManagementService::validate($menuForm, $availableDishes) !== [],
    'Un menu sans dessert est refusé'
);
$menuForm['dish_ids'] = [1, 2, 3];
$menuForm['image_urls'] = 'public/images/../../config/secret.webp';
$assertSame(
    true,
    MenuManagementService::validate($menuForm, $availableDishes) !== [],
    'Un chemin d’image avec remontée de dossier est refusé'
);

$assertSame(true, PasswordPolicy::isStrong('MotDePasse@2026'), 'Mot de passe fort');
$assertSame(false, PasswordPolicy::isStrong('motdepasse'), 'Mot de passe faible');
$assertSame(
    false,
    PasswordPolicy::isStrong('A1@' . str_repeat('a', 70)),
    'Mot de passe trop long'
);

$dish = DishManagementService::normalize([
    'name' => '  Tarte aux pommes  ',
    'category' => 'dessert',
    'description' => '  Une tarte maison aux pommes de saison.  ',
    'allergens' => '  gluten, œufs  ',
]);
$assertSame('Tarte aux pommes', $dish['name'], 'Normalisation du nom du plat');
$assertSame([], DishManagementService::validate($dish), 'Plat valide');
$dish['category'] = 'boisson';
$assertSame(
    true,
    DishManagementService::validate($dish) !== [],
    'Catégorie de plat refusée'
);

$registrationForm = [
    'first_name' => 'Julie',
    'last_name' => 'Martin',
    'email' => 'julie@example.test',
    'phone' => '06 12 34 56 78',
    'address' => '12 rue des Fleurs',
    'postal_code' => '33000',
    'city' => 'Bordeaux',
];
$assertSame(
    [],
    UserRegistrationService::validate(
        $registrationForm,
        'MotDePasse@2026',
        true
    ),
    'Inscription complète'
);
$registrationForm['phone'] = '';
$assertSame(
    true,
    UserRegistrationService::validate(
        $registrationForm,
        'MotDePasse@2026',
        true
    ) !== [],
    'Téléphone obligatoire à l’inscription'
);

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
