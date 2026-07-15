<?php

declare(strict_types=1);

namespace App\Models;

final class Menu extends Model
{
    public function allActive(): array
    {
        return $this->pdo->query(
            'SELECT id, title, description, theme, diet, min_people, base_price, stock, image_url
             FROM menus
             WHERE is_active = 1
             ORDER BY created_at DESC'
        )->fetchAll();
    }

    public function findActive(int $menuId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM menus WHERE id = :id AND is_active = 1'
        );
        $stmt->execute(['id' => $menuId]);

        $menu = $stmt->fetch();

        return $menu === false ? null : $menu;
    }

    public function allForManagement(): array
    {
        return $this->pdo->query(
            'SELECT id, title, theme, diet, min_people, base_price, stock, is_active
             FROM menus
             ORDER BY created_at DESC'
        )->fetchAll();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO menus
                (title, description, theme, diet, min_people, base_price, conditions_text, stock, image_url, is_active)
             VALUES
                (:title, :description, :theme, :diet, :min_people, :base_price, :conditions_text, :stock, :image_url, :is_active)'
        );
        $stmt->execute($data);
    }

    public function updateState(int $menuId, int $stock, bool $isActive): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE menus
             SET stock = :stock, is_active = :is_active
             WHERE id = :id'
        );
        $stmt->execute([
            'stock' => $stock,
            'is_active' => (int) $isActive,
            'id' => $menuId,
        ]);
    }

    public function statistics(): array
    {
        return $this->pdo->query(
            'SELECT
                menus.id,
                menus.title,
                COUNT(orders.id) AS orders_count,
                COALESCE(SUM(orders.total_price), 0) AS turnover
             FROM menus
             LEFT JOIN orders ON orders.menu_id = menus.id
             GROUP BY menus.id, menus.title
             ORDER BY orders_count DESC'
        )->fetchAll();
    }
}
