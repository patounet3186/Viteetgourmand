<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Dish;
use App\Models\Menu;
use PDO;

final class MenuController extends Controller
{
    private const DIETS = ['classique', 'végétarien', 'végan'];
    private const MAX_IMAGES = 6;

    private Menu $menus;
    private Dish $dishes;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->menus = new Menu($pdo);
        $this->dishes = new Dish($pdo);
    }

    public function index(): array
    {
        $menus = $this->menus->allActive();

        return $this->render('menus/index', 'Nos menus', compact('menus'));
    }

    public function show(): array
    {
        $menuId = (int) ($_GET['id'] ?? 0);
        $menu = $this->menus->findActive($menuId);

        if ($menu === null) {
            $this->abort(404, 'Menu introuvable');
        }

        $dishes = $this->dishes->forMenu((int) $menu['id']);
        $images = $this->menus->imageUrls((int) $menu['id']);
        $categoryLabels = DishController::categoryLabels();

        return $this->render('menus/show', 'Détail du menu', compact(
            'menu',
            'dishes',
            'images',
            'categoryLabels'
        ));
    }

    public function edit(): array
    {
        $this->requireRole(['employee', 'admin']);

        $menuId = (int) ($_GET['id'] ?? 0);
        $menu = $this->menus->findForManagement($menuId);

        if ($menu === null) {
            $this->abort(404, 'Menu introuvable');
        }

        $dishes = $this->dishes->all();
        $categoryLabels = DishController::categoryLabels();
        $menuErrors = [];
        $formMenu = [
            'title' => (string) $menu['title'],
            'description' => (string) $menu['description'],
            'theme' => (string) $menu['theme'],
            'diet' => (string) $menu['diet'],
            'min_people' => (string) $menu['min_people'],
            'base_price' => (string) $menu['base_price'],
            'conditions_text' => (string) $menu['conditions_text'],
            'stock' => (string) $menu['stock'],
            'image_urls' => implode(PHP_EOL, $this->menus->imageUrls($menuId)),
            'dish_ids' => $this->menus->dishIds($menuId),
            'is_active' => (int) $menu['is_active'],
        ];

        if (
            $_SERVER['REQUEST_METHOD'] === 'POST'
            && ($_POST['action'] ?? '') === 'update_menu'
        ) {
            $formMenu = $this->menuInput();
            $menuErrors = $this->validateMenu($formMenu, $dishes);

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                array_unshift(
                    $menuErrors,
                    'Le formulaire a expiré, merci de réessayer.'
                );
            }

            if ($menuErrors === []) {
                $imageUrls = $this->parseImageUrls($formMenu['image_urls']);
                $this->menus->update(
                    $menuId,
                    $this->menuData($formMenu, $imageUrls),
                    $formMenu['dish_ids'],
                    $imageUrls
                );
                $this->redirect('employee-menus', ['updated' => 1]);
            }
        }

        return $this->render(
            'employee/menu-edit',
            'Modifier un menu',
            compact(
                'menu',
                'menuErrors',
                'formMenu',
                'dishes',
                'categoryLabels'
            )
        );
    }

    public function manage(): array
    {
        $this->requireRole(['employee', 'admin']);

        $dishes = $this->dishes->all();
        $categoryLabels = DishController::categoryLabels();
        $menuErrors = [];
        $formMenu = $this->emptyMenuForm();
        $action = (string) ($_POST['action'] ?? '');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create_menu') {
            $formMenu = $this->menuInput();
            $menuErrors = $this->validateMenu($formMenu, $dishes);

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                array_unshift(
                    $menuErrors,
                    'Le formulaire a expiré, merci de réessayer.'
                );
            }

            if ($menuErrors === []) {
                $imageUrls = $this->parseImageUrls($formMenu['image_urls']);
                $this->menus->create(
                    $this->menuData($formMenu, $imageUrls),
                    $formMenu['dish_ids'],
                    $imageUrls
                );
                $this->redirect('employee-menus', ['created' => 1]);
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update_menu_state') {
            $menuId = (int) ($_POST['menu_id'] ?? 0);
            $stock = trim((string) ($_POST['stock'] ?? ''));
            $isActive = (string) ($_POST['is_active'] ?? '');

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                $menuErrors[] = 'Le formulaire a expiré, merci de réessayer.';
            } elseif (
                $menuId <= 0
                || !ctype_digit($stock)
                || !in_array($isActive, ['0', '1'], true)
            ) {
                $menuErrors[] = 'Les informations du menu sont invalides.';
            } else {
                $this->menus->updateState($menuId, (int) $stock, $isActive === '1');
                $this->redirect('employee-menus', ['updated' => 1]);
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'archive_menu') {
            $menuId = (int) ($_POST['menu_id'] ?? 0);

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                $menuErrors[] = 'Le formulaire a expiré, merci de réessayer.';
            } elseif ($menuId <= 0 || $this->menus->findForManagement($menuId) === null) {
                $menuErrors[] = 'Le menu sélectionné est introuvable.';
            } else {
                $this->menus->archive($menuId);
                $this->redirect('employee-menus', ['archived' => 1]);
            }
        }

        $menus = $this->menus->allForManagement();
        $menuCreated = ($_GET['created'] ?? '') === '1';
        $menuUpdated = ($_GET['updated'] ?? '') === '1';
        $menuArchived = ($_GET['archived'] ?? '') === '1';

        return $this->render('employee/menus', 'Gestion des menus', compact(
            'menuErrors',
            'formMenu',
            'dishes',
            'categoryLabels',
            'menus',
            'menuCreated',
            'menuUpdated',
            'menuArchived'
        ));
    }

    /** @return array<string, mixed> */
    private function emptyMenuForm(): array
    {
        return [
            'title' => '',
            'description' => '',
            'theme' => '',
            'diet' => 'classique',
            'min_people' => '',
            'base_price' => '',
            'conditions_text' => '',
            'stock' => '0',
            'image_urls' => '',
            'dish_ids' => [],
            'is_active' => 1,
        ];
    }

    /** @return array<string, mixed> */
    private function menuInput(): array
    {
        $postedDishIds = is_array($_POST['dish_ids'] ?? null)
            ? $_POST['dish_ids']
            : [];
        $dishIds = array_values(array_unique(array_filter(
            array_map('intval', $postedDishIds),
            static fn (int $dishId): bool => $dishId > 0
        )));

        return [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'theme' => trim((string) ($_POST['theme'] ?? '')),
            'diet' => (string) ($_POST['diet'] ?? ''),
            'min_people' => trim((string) ($_POST['min_people'] ?? '')),
            'base_price' => trim((string) ($_POST['base_price'] ?? '')),
            'conditions_text' => trim((string) ($_POST['conditions_text'] ?? '')),
            'stock' => trim((string) ($_POST['stock'] ?? '')),
            'image_urls' => trim((string) ($_POST['image_urls'] ?? '')),
            'dish_ids' => $dishIds,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
    }

    /**
     * @param array<string, mixed> $menu
     * @param list<array<string, mixed>> $availableDishes
     * @return list<string>
     */
    private function validateMenu(array $menu, array $availableDishes): array
    {
        $errors = [];

        if (mb_strlen($menu['title']) < 3 || mb_strlen($menu['title']) > 150) {
            $errors[] = 'Le titre doit contenir entre 3 et 150 caractères.';
        }

        if (
            mb_strlen($menu['description']) < 10
            || mb_strlen($menu['description']) > 2000
        ) {
            $errors[] = 'La description doit contenir entre 10 et 2 000 caractères.';
        }

        if (mb_strlen($menu['theme']) < 2 || mb_strlen($menu['theme']) > 80) {
            $errors[] = 'Le thème doit contenir entre 2 et 80 caractères.';
        }

        if (!in_array($menu['diet'], self::DIETS, true)) {
            $errors[] = 'Le régime sélectionné est invalide.';
        }

        if (!ctype_digit($menu['min_people']) || (int) $menu['min_people'] < 1) {
            $errors[] = 'Le nombre minimum de personnes est invalide.';
        }

        $price = str_replace(',', '.', $menu['base_price']);
        if (!is_numeric($price) || (float) $price <= 0) {
            $errors[] = 'Le prix de base est invalide.';
        }

        if (!ctype_digit($menu['stock'])) {
            $errors[] = 'Le stock est invalide.';
        }

        if (
            mb_strlen($menu['conditions_text']) < 10
            || mb_strlen($menu['conditions_text']) > 2000
        ) {
            $errors[] = 'Les conditions doivent contenir entre 10 et 2 000 caractères.';
        }

        $imageUrls = $this->parseImageUrls($menu['image_urls']);
        if ($imageUrls === []) {
            $errors[] = 'Ajoutez au moins une image au menu.';
        } elseif (count($imageUrls) > self::MAX_IMAGES) {
            $errors[] = 'Un menu peut contenir au maximum 6 images.';
        }

        foreach ($imageUrls as $imageUrl) {
            if (!$this->validImageUrl($imageUrl)) {
                $errors[] = "Le chemin d’image « {$imageUrl} » est invalide.";
            }
        }

        $availableById = [];
        foreach ($availableDishes as $dish) {
            $availableById[(int) $dish['id']] = (string) $dish['category'];
        }

        $selectedCategories = [];
        foreach ($menu['dish_ids'] as $dishId) {
            if (!isset($availableById[$dishId])) {
                $errors[] = 'Un plat sélectionné est introuvable.';
                break;
            }
            $selectedCategories[] = $availableById[$dishId];
        }

        foreach (DishController::categoryLabels() as $category => $label) {
            if (!in_array($category, $selectedCategories, true)) {
                $errors[] = "Sélectionnez au moins un plat dans la catégorie « {$label} ».";
            }
        }

        return array_values(array_unique($errors));
    }

    /**
     * @param array<string, mixed> $menu
     * @param list<string> $imageUrls
     * @return array<string, mixed>
     */
    private function menuData(array $menu, array $imageUrls): array
    {
        return [
            'title' => $menu['title'],
            'description' => $menu['description'],
            'theme' => $menu['theme'],
            'diet' => $menu['diet'],
            'min_people' => (int) $menu['min_people'],
            'base_price' => (float) str_replace(',', '.', $menu['base_price']),
            'conditions_text' => $menu['conditions_text'],
            'stock' => (int) $menu['stock'],
            'image_url' => $imageUrls[0] ?? null,
            'is_active' => (int) $menu['is_active'],
        ];
    }

    /** @return list<string> */
    private function parseImageUrls(string $value): array
    {
        $lines = preg_split('/\R/', $value) ?: [];

        return array_values(array_unique(array_filter(array_map(
            static fn (string $line): string => trim($line),
            $lines
        ))));
    }

    private function validImageUrl(string $imageUrl): bool
    {
        if (mb_strlen($imageUrl) > 255) {
            return false;
        }

        if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            return in_array(
                strtolower((string) parse_url($imageUrl, PHP_URL_SCHEME)),
                ['http', 'https'],
                true
            );
        }

        return preg_match(
            '#^/?(?:ECF-2026/)?(?:public/)?images/[A-Za-z0-9._/-]+\.(?:webp|png|jpe?g)$#i',
            $imageUrl
        ) === 1;
    }
}
