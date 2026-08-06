<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Dish;
use App\Models\Menu;
use App\Services\DishManagementService;
use App\Services\MenuFilterService;
use App\Services\MenuManagementService;
use InvalidArgumentException;
use PDO;

final class MenuController extends Controller
{
    private Menu $menus;
    private Dish $dishes;
    private MenuManagementService $management;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->menus = new Menu($pdo);
        $this->dishes = new Dish($pdo);
        $this->management = new MenuManagementService($this->menus);
    }

    public function index(): array
    {
        $menus = $this->menus->allActive();

        return $this->render('menus/index', 'Nos menus', compact('menus'));
    }

    public function filter(): never
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json(['error' => 'Méthode non autorisée.'], 405);
        }

        try {
            $menuIds = (new MenuFilterService($this->menus))->search($_GET);
            $this->json([
                'menu_ids' => $menuIds,
                'count' => count($menuIds),
            ]);
        } catch (InvalidArgumentException $exception) {
            $this->json(['error' => $exception->getMessage()], 422);
        }
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
        $categoryLabels = DishManagementService::categoryLabels();

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
        $categoryLabels = DishManagementService::categoryLabels();
        $menuErrors = [];
        $formMenu = MenuManagementService::fromRecord(
            $menu,
            $this->menus->imageUrls($menuId),
            $this->menus->dishIds($menuId)
        );

        if (
            $_SERVER['REQUEST_METHOD'] === 'POST'
            && ($_POST['action'] ?? '') === 'update_menu'
        ) {
            $formMenu = MenuManagementService::normalize($_POST);
            $menuErrors = MenuManagementService::validate($formMenu, $dishes);

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                array_unshift(
                    $menuErrors,
                    'Le formulaire a expiré, merci de réessayer.'
                );
            }

            if ($menuErrors === []) {
                $this->management->update($menuId, $formMenu);
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
        $categoryLabels = DishManagementService::categoryLabels();
        $menuErrors = [];
        $formMenu = MenuManagementService::emptyForm();
        $action = (string) ($_POST['action'] ?? '');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create_menu') {
            $formMenu = MenuManagementService::normalize($_POST);
            $menuErrors = MenuManagementService::validate($formMenu, $dishes);

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                array_unshift(
                    $menuErrors,
                    'Le formulaire a expiré, merci de réessayer.'
                );
            }

            if ($menuErrors === []) {
                $this->management->create($formMenu);
                $this->redirect('employee-menus', ['created' => 1]);
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update_menu_state') {
            $menuId = (int) ($_POST['menu_id'] ?? 0);
            $stock = trim((string) ($_POST['stock'] ?? ''));
            $isActive = (string) ($_POST['is_active'] ?? '');

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                $menuErrors[] = 'Le formulaire a expiré, merci de réessayer.';
            } else {
                try {
                    $this->management->updateState($menuId, $stock, $isActive);
                    $this->redirect('employee-menus', ['updated' => 1]);
                } catch (InvalidArgumentException $exception) {
                    $menuErrors[] = $exception->getMessage();
                }
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'archive_menu') {
            $menuId = (int) ($_POST['menu_id'] ?? 0);

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                $menuErrors[] = 'Le formulaire a expiré, merci de réessayer.';
            } else {
                try {
                    $this->management->archive($menuId);
                    $this->redirect('employee-menus', ['archived' => 1]);
                } catch (InvalidArgumentException $exception) {
                    $menuErrors[] = $exception->getMessage();
                }
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

}
