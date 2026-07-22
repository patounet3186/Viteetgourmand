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

    public function findForManagement(int $menuId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM menus WHERE id = :id'
        );
        $stmt->execute(['id' => $menuId]);

        $menu = $stmt->fetch();

        return $menu === false ? null : $menu;
    }

    public function allForManagement(): array
    {
        return $this->pdo->query(
            'SELECT
                menus.id,
                menus.title,
                menus.theme,
                menus.diet,
                menus.min_people,
                menus.base_price,
                menus.stock,
                menus.is_active,
                COUNT(menu_dishes.dish_id) AS dishes_count
             FROM menus
             LEFT JOIN menu_dishes ON menu_dishes.menu_id = menus.id
             GROUP BY
                menus.id,
                menus.title,
                menus.theme,
                menus.diet,
                menus.min_people,
                menus.base_price,
                menus.stock,
                menus.is_active,
                menus.created_at
             ORDER BY created_at DESC'
        )->fetchAll();
    }

    /** @param array<string, mixed> $data */
    public function create(
        array $data,
        array $dishIds = [],
        array $imageUrls = []
    ): int
    {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO menus
                    (title, description, theme, diet, min_people, base_price,
                     conditions_text, stock, image_url, is_active)
                 VALUES
                    (:title, :description, :theme, :diet, :min_people, :base_price,
                     :conditions_text, :stock, :image_url, :is_active)'
            );
            $stmt->execute($data);
            $menuId = (int) $this->pdo->lastInsertId();

            $this->replaceDishes($menuId, $dishIds);
            $this->replaceImages($menuId, $imageUrls);
            $this->pdo->commit();

            return $menuId;
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    /** @param array<string, mixed> $data */
    public function update(
        int $menuId,
        array $data,
        array $dishIds,
        array $imageUrls
    ): void {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                'UPDATE menus
                 SET title = :title,
                     description = :description,
                     theme = :theme,
                     diet = :diet,
                     min_people = :min_people,
                     base_price = :base_price,
                     conditions_text = :conditions_text,
                     stock = :stock,
                     image_url = :image_url,
                     is_active = :is_active
                 WHERE id = :id'
            );
            $stmt->execute([...$data, 'id' => $menuId]);

            $this->replaceDishes($menuId, $dishIds);
            $this->replaceImages($menuId, $imageUrls);
            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
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

    public function archive(int $menuId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE menus SET stock = 0, is_active = 0 WHERE id = :id'
        );
        $stmt->execute(['id' => $menuId]);
    }

    /** @return list<int> */
    public function dishIds(int $menuId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT dish_id FROM menu_dishes WHERE menu_id = :menu_id'
        );
        $stmt->execute(['menu_id' => $menuId]);

        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    /** @return list<string> */
    public function imageUrls(int $menuId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT image_url
             FROM menu_images
             WHERE menu_id = :menu_id
             ORDER BY position, id'
        );
        $stmt->execute(['menu_id' => $menuId]);
        $urls = array_map('strval', $stmt->fetchAll(\PDO::FETCH_COLUMN));

        if ($urls !== []) {
            return $urls;
        }

        $menu = $this->findForManagement($menuId);
        $fallback = trim((string) ($menu['image_url'] ?? ''));

        return $fallback === '' ? [] : [$fallback];
    }

    public function statistics(
        ?int $menuId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array
    {
        $sql =
            'SELECT
                menus.id,
                menus.title,
                COUNT(orders.id) AS orders_count,
                COALESCE(SUM(orders.total_price), 0) AS turnover
             FROM menus
             LEFT JOIN orders
               ON orders.menu_id = menus.id
              AND orders.status != \'annulee\'';
        $params = [];

        if ($dateFrom !== null) {
            $sql .= ' AND orders.created_at >= :date_from';
            $params['date_from'] = $dateFrom . ' 00:00:00';
        }

        if ($dateTo !== null) {
            $sql .= ' AND orders.created_at <= :date_to';
            $params['date_to'] = $dateTo . ' 23:59:59';
        }

        if ($menuId !== null) {
            $sql .= ' WHERE menus.id = :menu_id';
            $params['menu_id'] = $menuId;
        }

        $sql .=
            ' GROUP BY menus.id, menus.title
              ORDER BY orders_count DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /** @param list<int> $dishIds */
    private function replaceDishes(int $menuId, array $dishIds): void
    {
        $delete = $this->pdo->prepare(
            'DELETE FROM menu_dishes WHERE menu_id = :menu_id'
        );
        $delete->execute(['menu_id' => $menuId]);

        $insert = $this->pdo->prepare(
            'INSERT INTO menu_dishes (menu_id, dish_id)
             VALUES (:menu_id, :dish_id)'
        );

        foreach (array_values(array_unique($dishIds)) as $dishId) {
            $insert->execute([
                'menu_id' => $menuId,
                'dish_id' => $dishId,
            ]);
        }
    }

    /** @param list<string> $imageUrls */
    private function replaceImages(int $menuId, array $imageUrls): void
    {
        $delete = $this->pdo->prepare(
            'DELETE FROM menu_images WHERE menu_id = :menu_id'
        );
        $delete->execute(['menu_id' => $menuId]);

        $insert = $this->pdo->prepare(
            'INSERT INTO menu_images (menu_id, image_url, position)
             VALUES (:menu_id, :image_url, :position)'
        );

        foreach (array_values(array_unique($imageUrls)) as $position => $imageUrl) {
            $insert->execute([
                'menu_id' => $menuId,
                'image_url' => $imageUrl,
                'position' => $position,
            ]);
        }
    }
}
