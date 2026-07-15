<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Dish;
use App\Models\Menu;
use PDO;

final class MenuController extends Controller
{
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
        $categoryLabels = DishController::categoryLabels();

        return $this->render('menus/show', 'Détail du menu', compact(
            'menu',
            'dishes',
            'categoryLabels'
        ));
    }

    public function manage(): array
    {
        $this->requireRole(['employee', 'admin']);

        $menuErrors = [];
        $formMenu = [
            'title' => '',
            'description' => '',
            'theme' => '',
            'diet' => 'classique',
            'min_people' => '',
            'base_price' => '',
            'conditions_text' => '',
            'stock' => '0',
            'image_url' => '',
            'is_active' => 1,
        ];
        $allowedDiets = ['classique', 'végétarien', 'végan'];
        $action = (string) ($_POST['action'] ?? '');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create_menu') {
            $formMenu = [
                'title' => trim((string) ($_POST['title'] ?? '')),
                'description' => trim((string) ($_POST['description'] ?? '')),
                'theme' => trim((string) ($_POST['theme'] ?? '')),
                'diet' => (string) ($_POST['diet'] ?? ''),
                'min_people' => trim((string) ($_POST['min_people'] ?? '')),
                'base_price' => trim((string) ($_POST['base_price'] ?? '')),
                'conditions_text' => trim((string) ($_POST['conditions_text'] ?? '')),
                'stock' => trim((string) ($_POST['stock'] ?? '')),
                'image_url' => trim((string) ($_POST['image_url'] ?? '')),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ];

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                $menuErrors[] = 'Le formulaire a expiré, merci de réessayer.';
            }

            if (mb_strlen($formMenu['title']) < 3 || mb_strlen($formMenu['title']) > 150) {
                $menuErrors[] = 'Le titre doit contenir entre 3 et 150 caractères.';
            }

            if (mb_strlen($formMenu['description']) < 10) {
                $menuErrors[] = 'La description doit contenir au moins 10 caractères.';
            }

            if ($formMenu['theme'] === '' || mb_strlen($formMenu['theme']) > 80) {
                $menuErrors[] = 'Le thème est invalide.';
            }

            if (!in_array($formMenu['diet'], $allowedDiets, true)) {
                $menuErrors[] = 'Le régime est invalide.';
            }

            if (!ctype_digit($formMenu['min_people']) || (int) $formMenu['min_people'] < 1) {
                $menuErrors[] = 'Le nombre minimum de personnes est invalide.';
            }

            $price = str_replace(',', '.', $formMenu['base_price']);
            if (!is_numeric($price) || (float) $price <= 0) {
                $menuErrors[] = 'Le prix est invalide.';
            }

            if (!ctype_digit($formMenu['stock'])) {
                $menuErrors[] = 'Le stock est invalide.';
            }

            if (mb_strlen($formMenu['conditions_text']) < 10) {
                $menuErrors[] = 'Les conditions doivent contenir au moins 10 caractères.';
            }

            if (mb_strlen($formMenu['image_url']) > 255) {
                $menuErrors[] = 'Le chemin de l’image est trop long.';
            }

            if (empty($menuErrors)) {
                $this->menus->create([
                    'title' => $formMenu['title'],
                    'description' => $formMenu['description'],
                    'theme' => $formMenu['theme'],
                    'diet' => $formMenu['diet'],
                    'min_people' => (int) $formMenu['min_people'],
                    'base_price' => (float) $price,
                    'conditions_text' => $formMenu['conditions_text'],
                    'stock' => (int) $formMenu['stock'],
                    'image_url' => $formMenu['image_url'] !== '' ? $formMenu['image_url'] : null,
                    'is_active' => $formMenu['is_active'],
                ]);
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
        }

        $menus = $this->menus->allForManagement();
        $menuCreated = ($_GET['created'] ?? '') === '1';
        $menuUpdated = ($_GET['updated'] ?? '') === '1';

        return $this->render('employee/menus', 'Gestion des menus', compact(
            'menuErrors',
            'formMenu',
            'menus',
            'menuCreated',
            'menuUpdated'
        ));
    }
}
