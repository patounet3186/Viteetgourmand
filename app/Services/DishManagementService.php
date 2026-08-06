<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Dish;
use DomainException;
use InvalidArgumentException;

final class DishManagementService
{
    private const CATEGORIES = ['entree', 'plat', 'dessert'];

    public function __construct(private Dish $dishes)
    {
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

    /** @return array<string, string> */
    public static function emptyForm(): array
    {
        return [
            'name' => '',
            'category' => 'entree',
            'description' => '',
            'allergens' => '',
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, string>
     */
    public static function normalize(array $input): array
    {
        return [
            'name' => trim((string) ($input['name'] ?? '')),
            'category' => (string) ($input['category'] ?? ''),
            'description' => trim((string) ($input['description'] ?? '')),
            'allergens' => trim((string) ($input['allergens'] ?? '')),
        ];
    }

    /**
     * @param array<string, mixed> $dish
     * @return array<string, string>
     */
    public static function fromRecord(array $dish): array
    {
        return self::normalize($dish);
    }

    /**
     * @param array<string, string> $dish
     * @return list<string>
     */
    public static function validate(array $dish): array
    {
        $errors = [];

        if (mb_strlen($dish['name']) < 2 || mb_strlen($dish['name']) > 150) {
            $errors[] = 'Le nom doit contenir entre 2 et 150 caractères.';
        }

        if (!in_array($dish['category'], self::CATEGORIES, true)) {
            $errors[] = 'La catégorie sélectionnée est invalide.';
        }

        if (
            mb_strlen($dish['description']) < 10
            || mb_strlen($dish['description']) > 1000
        ) {
            $errors[] = 'La description doit contenir entre 10 et 1 000 caractères.';
        }

        if (mb_strlen($dish['allergens']) > 255) {
            $errors[] = 'La liste des allergènes ne doit pas dépasser 255 caractères.';
        }

        return $errors;
    }

    /** @param array<string, string> $dish */
    public function create(array $dish): void
    {
        $this->dishes->create($dish);
    }

    /** @param array<string, string> $dish */
    public function update(int $dishId, array $dish): void
    {
        $this->dishes->update($dishId, $dish);
    }

    public function delete(int $dishId): void
    {
        if ($dishId <= 0 || $this->dishes->find($dishId) === null) {
            throw new InvalidArgumentException('Le plat sélectionné est introuvable.');
        }

        $usageCount = $this->dishes->usageCount($dishId);
        if ($usageCount > 0) {
            $suffix = $usageCount > 1 ? 's' : '';
            throw new DomainException(
                "Ce plat est utilisé dans {$usageCount} menu{$suffix}. "
                . 'Retirez-le de ces menus avant de le supprimer.'
            );
        }

        if (!$this->dishes->deleteUnused($dishId)) {
            throw new DomainException(
                'Le plat ne peut plus être supprimé car il vient d’être associé à un menu.'
            );
        }
    }
}
