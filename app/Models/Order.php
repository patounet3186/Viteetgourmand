<?php

declare(strict_types=1);

namespace App\Models;

final class Order extends Model
{
    /** @param array<string, mixed> $data */
    public function create(array $data): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO orders
                (user_id, menu_id, event_date, event_time, delivery_address, delivery_city,
                 people_count, menu_price, delivery_price, discount_amount, total_price)
             VALUES
                (:user_id, :menu_id, :event_date, :event_time, :delivery_address, :delivery_city,
                 :people_count, :menu_price, :delivery_price, :discount_amount, :total_price)'
        );
        $stmt->execute($data);
    }

    public function findByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                orders.id,
                orders.event_date,
                orders.event_time,
                orders.people_count,
                orders.total_price,
                orders.status,
                orders.created_at,
                menus.title AS menu_title
             FROM orders
             INNER JOIN menus ON menus.id = orders.menu_id
             WHERE orders.user_id = :user_id
             ORDER BY orders.created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function findForReview(int $orderId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                orders.id,
                orders.menu_id,
                orders.status,
                menus.title AS menu_title
             FROM orders
             INNER JOIN menus ON menus.id = orders.menu_id
             WHERE orders.id = :order_id AND orders.user_id = :user_id'
        );
        $stmt->execute([
            'order_id' => $orderId,
            'user_id' => $userId,
        ]);

        $order = $stmt->fetch();

        return $order === false ? null : $order;
    }

    public function allByStatus(?string $status = null): array
    {
        $sql =
            'SELECT
                orders.id,
                orders.event_date,
                orders.event_time,
                orders.people_count,
                orders.total_price,
                orders.status,
                orders.created_at,
                menus.title AS menu_title,
                users.first_name,
                users.last_name,
                users.email
             FROM orders
             INNER JOIN menus ON menus.id = orders.menu_id
             INNER JOIN users ON users.id = orders.user_id';

        $params = [];

        if ($status !== null) {
            $sql .= ' WHERE orders.status = :status';
            $params['status'] = $status;
        }

        $sql .= ' ORDER BY orders.created_at DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function updateStatus(int $orderId, string $status): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE orders SET status = :status WHERE id = :id'
        );
        $stmt->execute(['status' => $status, 'id' => $orderId]);
    }
}
