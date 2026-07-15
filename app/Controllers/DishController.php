<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Dish;
use PDO;

final class DishController extends Controller
{
    private const CATEGORIES = ['entree', 'plat', 'dessert'];

    private Dish $dishes;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->dishes = new Dish($pdo);
    }

    /** @return array<string, string> */
    public static function categoryLabels(): array
    {
        return [
            'entree' => 'Entrée',
            'plat' => 'Plat',
            'dessert' => 'Dessert',
        ];
    }

    public function index(): array
    {
        $this->requireRole(['employee', 'admin']);

        $dishErrors = [];
        $formDish = $this->emptyForm();

        if (
            $_SERVER['REQUEST_METHOD'] === 'POST'
            && ($_POST['action'] ?? '') === 'create_dish'
        ) {
            $formDish = $this->dishInput();
            $dishErrors = $this->validateDish($formDish);

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                array_unshift($dishErrors, 'Le formulaire a expiré, merci de réessayer.');
            }

            if (empty($dishErrors)) {
                $this->dishes->create($formDish);
                $this->redirect('employee-dishes', ['created' => 1]);
            }
        }

        $dishes = $this->dishes->all();
        $categoryLabels = self::categoryLabels();
        $dishCreated = ($_GET['created'] ?? '') === '1';
        $dishUpdated = ($_GET['updated'] ?? '') === '1';

        return $this->render('employee/dishes', 'Gestion des plats', compact(
            'dishErrors',
            'formDish',
            'dishes',
            'categoryLabels',
            'dishCreated',
            'dishUpdated'
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
        $formDish = [
            'name' => (string) $dish['name'],
            'category' => (string) $dish['category'],
            'description' => (string) ($dish['description'] ?? ''),
            'allergens' => (string) ($dish['allergens'] ?? ''),
        ];

        if (
            $_SERVER['REQUEST_METHOD'] === 'POST'
            && ($_POST['action'] ?? '') === 'update_dish'
        ) {
            $formDish = $this->dishInput();
            $editErrors = $this->validateDish($formDish);

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                array_unshift($editErrors, 'Le formulaire a expiré, merci de réessayer.');
            }

            if (empty($editErrors)) {
                $this->dishes->update($dishId, $formDish);
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

    /** @return array<string, string> */
    private function emptyForm(): array
    {
        return [
            'name' => '',
            'category' => 'entree',
            'description' => '',
            'allergens' => '',
        ];
    }

    /** @return array<string, string> */
    private function dishInput(): array
    {
        return [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'category' => (string) ($_POST['category'] ?? ''),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'allergens' => trim((string) ($_POST['allergens'] ?? '')),
        ];
    }

    /**
     * @param array<string, string> $dish
     * @return list<string>
     */
    private function validateDish(array $dish): array
    {
        $errors = [];

        if (mb_strlen($dish['name']) < 2 || mb_strlen($dish['name']) > 150) {
            $errors[] = 'Le nom doit contenir entre 2 et 150 caractères.';
        }

        if (!in_array($dish['category'], self::CATEGORIES, true)) {
            $errors[] = 'La catégorie sélectionnée est invalide.';
        }

        if (mb_strlen($dish['description']) < 10 || mb_strlen($dish['description']) > 1000) {
            $errors[] = 'La description doit contenir entre 10 et 1 000 caractères.';
        }

        if (mb_strlen($dish['allergens']) > 255) {
            $errors[] = 'La liste des allergènes ne doit pas dépasser 255 caractères.';
        }

        return $errors;
    }
}
