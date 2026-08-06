<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Dish;
use App\Services\DishManagementService;
use DomainException;
use InvalidArgumentException;
use PDO;

final class DishController extends Controller
{
    private Dish $dishes;
    private DishManagementService $management;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->dishes = new Dish($pdo);
        $this->management = new DishManagementService($this->dishes);
    }

    /** @return array<string, string> */
    public static function categoryLabels(): array
    {
        return DishManagementService::categoryLabels();
    }

    public function index(): array
    {
        $this->requireRole(['employee', 'admin']);

        $dishErrors = [];
        $formDish = DishManagementService::emptyForm();

        if (
            $_SERVER['REQUEST_METHOD'] === 'POST'
            && ($_POST['action'] ?? '') === 'create_dish'
        ) {
            $formDish = DishManagementService::normalize($_POST);
            $dishErrors = DishManagementService::validate($formDish);

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                array_unshift($dishErrors, 'Le formulaire a expiré, merci de réessayer.');
            }

            if (empty($dishErrors)) {
                $this->management->create($formDish);
                $this->redirect('employee-dishes', ['created' => 1]);
            }
        } elseif (
            $_SERVER['REQUEST_METHOD'] === 'POST'
            && ($_POST['action'] ?? '') === 'delete_dish'
        ) {
            $dishId = (int) ($_POST['dish_id'] ?? 0);

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                $dishErrors[] = 'Le formulaire a expiré, merci de réessayer.';
            } else {
                try {
                    $this->management->delete($dishId);
                    $this->redirect('employee-dishes', ['deleted' => 1]);
                } catch (InvalidArgumentException | DomainException $exception) {
                    $dishErrors[] = $exception->getMessage();
                }
            }
        }

        $dishes = $this->dishes->all();
        $categoryLabels = self::categoryLabels();
        $dishCreated = ($_GET['created'] ?? '') === '1';
        $dishUpdated = ($_GET['updated'] ?? '') === '1';
        $dishDeleted = ($_GET['deleted'] ?? '') === '1';

        return $this->render('employee/dishes', 'Gestion des plats', compact(
            'dishErrors',
            'formDish',
            'dishes',
            'categoryLabels',
            'dishCreated',
            'dishUpdated',
            'dishDeleted'
        ));
    }

    public function edit(): array
    {
        $this->requireRole(['employee', 'admin']);

        $dishId = (int) ($_GET['id'] ?? 0);
        $dish = $dishId > 0 ? $this->dishes->find($dishId) : null;

        if ($dish === null) {
            $this->abort(404, 'Plat introuvable');
        }

        $editErrors = [];
        $formDish = DishManagementService::fromRecord($dish);

        if (
            $_SERVER['REQUEST_METHOD'] === 'POST'
            && ($_POST['action'] ?? '') === 'update_dish'
        ) {
            $formDish = DishManagementService::normalize($_POST);
            $editErrors = DishManagementService::validate($formDish);

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                array_unshift($editErrors, 'Le formulaire a expiré, merci de réessayer.');
            }

            if (empty($editErrors)) {
                $this->management->update($dishId, $formDish);
                $this->redirect('employee-dishes', ['updated' => 1]);
            }
        }

        return $this->render('employee/dish-edit', 'Modifier un plat', compact(
            'dish',
            'dishId',
            'editErrors',
            'formDish'
        ));
    }

}
