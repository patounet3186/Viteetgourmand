<?php

declare(strict_types=1);

namespace App\Models;

final class Dish extends Model
{
    public function all(): array
    {
        return $this->pdo->query(
            "SELECT id, name, category, description, allergens
             FROM dishes
             ORDER BY
                CASE category
                    WHEN 'entree' THEN 1
                    WHEN 'plat' THEN 2
                    WHEN 'dessert' THEN 3
                    ELSE 4
                END,
                name"
        )->fetchAll();
    }

    public function find(int $dishId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, category, description, allergens
             FROM dishes
             WHERE id = :id'
        );
        $stmt->execute(['id' => $dishId]);

        $dish = $stmt->fetch();

        return $dish === false ? null : $dish;
    }

    public function forMenu(int $menuId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                dishes.id,
                dishes.name,
                dishes.category,
                dishes.description,
                dishes.allergens
             FROM menu_dishes
             INNER JOIN dishes ON dishes.id = menu_dishes.dish_id
             WHERE menu_dishes.menu_id = :menu_id
             ORDER BY
                CASE dishes.category
                    WHEN 'entree' THEN 1
                    WHEN 'plat' THEN 2
                    WHEN 'dessert' THEN 3
                    ELSE 4
                END,
                dishes.name"
        );
        $stmt->execute(['menu_id' => $menuId]);

        return $stmt->fetchAll();
    }

    /** @param array<string, string> $data */
    public function create(array $data): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO dishes (name, category, description, allergens)
             VALUES (:name, :category, :description, :allergens)'
        );
        $stmt->execute($data);
    }

    /** @param array<string, string> $data */
    public function update(int $dishId, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE dishes
             SET name = :name,
                 category = :category,
                 description = :description,
                 allergens = :allergens
             WHERE id = :id'
        );
        $stmt->execute([...$data, 'id' => $dishId]);
    }
}
