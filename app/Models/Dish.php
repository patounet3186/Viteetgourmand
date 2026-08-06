<?php

declare(strict_types=1);

namespace App\Models;

final class Dish extends Model
{
    public function all(): array
    {
        return $this->pdo->query(
            "SELECT
                dishes.id,
                dishes.name,
                dishes.category,
                dishes.description,
                dishes.allergens,
                COUNT(menu_dishes.menu_id) AS menus_count
             FROM dishes
             LEFT JOIN menu_dishes ON menu_dishes.dish_id = dishes.id
             GROUP BY
                dishes.id,
                dishes.name,
                dishes.category,
                dishes.description,
                dishes.allergens
             ORDER BY
                CASE dishes.category
                    WHEN 'entree' THEN 1
                    WHEN 'plat' THEN 2
                    WHEN 'dessert' THEN 3
                    ELSE 4
                END,
                dishes.name"
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

    /** @param list<int> $dishIds */
    public function existingIds(array $dishIds): array
    {
        $dishIds = array_values(array_unique(array_filter(
            $dishIds,
            static fn (int $dishId): bool => $dishId > 0
        )));

        if ($dishIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($dishIds), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT id FROM dishes WHERE id IN ({$placeholders})"
        );
        $stmt->execute($dishIds);

        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    public function usageCount(int $dishId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM menu_dishes WHERE dish_id = :dish_id'
        );
        $stmt->execute(['dish_id' => $dishId]);

        return (int) $stmt->fetchColumn();
    }

    public function deleteUnused(int $dishId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM dishes
             WHERE id = :id
               AND NOT EXISTS (
                   SELECT 1
                   FROM menu_dishes
                   WHERE menu_dishes.dish_id = :linked_dish_id
               )'
        );
        $stmt->execute([
            'id' => $dishId,
            'linked_dish_id' => $dishId,
        ]);

        return $stmt->rowCount() > 0;
    }
}
