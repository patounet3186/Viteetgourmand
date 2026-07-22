<?php

declare(strict_types=1);

namespace App\Models;

use DomainException;

final class Order extends Model
{
    /** @param array<string, mixed> $data */
    public function create(array $data): int
    {
        $this->pdo->beginTransaction();

        try {
            $menuStmt = $this->pdo->prepare(
                'SELECT stock, is_active FROM menus WHERE id = :id FOR UPDATE'
            );
            $menuStmt->execute(['id' => $data['menu_id']]);
            $menu = $menuStmt->fetch();

            if (
                $menu === false
                || (int) $menu['is_active'] !== 1
                || (int) $menu['stock'] < 1
            ) {
                throw new DomainException('Ce menu n’est plus disponible.');
            }

            $stmt = $this->pdo->prepare(
                'INSERT INTO orders
                    (user_id, menu_id, event_date, event_time, delivery_address,
                     delivery_city, people_count, menu_price, delivery_price,
                     discount_amount, total_price)
                 VALUES
                    (:user_id, :menu_id, :event_date, :event_time, :delivery_address,
                     :delivery_city, :people_count, :menu_price, :delivery_price,
                     :discount_amount, :total_price)'
            );
            $stmt->execute($data);
            $orderId = (int) $this->pdo->lastInsertId();

            $stockStmt = $this->pdo->prepare(
                'UPDATE menus SET stock = stock - 1
                 WHERE id = :id AND stock > 0'
            );
            $stockStmt->execute(['id' => $data['menu_id']]);

            if ($stockStmt->rowCount() !== 1) {
                throw new DomainException('Le dernier exemplaire vient d’être commandé.');
            }

            $this->insertHistory(
                $orderId,
                'nouvelle',
                (int) $data['user_id'],
                'Commande créée par le client.'
            );
            $this->pdo->commit();

            return $orderId;
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    public function findByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                orders.id,
                orders.event_date,
                orders.event_time,
                orders.delivery_city,
                orders.people_count,
                orders.total_price,
                orders.status,
                orders.created_at,
                orders.updated_at,
                menus.title AS menu_title
             FROM orders
             INNER JOIN menus ON menus.id = orders.menu_id
             WHERE orders.user_id = :user_id
             ORDER BY orders.created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function findDetailedForUser(int $orderId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                orders.*,
                menus.title AS menu_title,
                menus.min_people,
                menus.base_price,
                users.first_name,
                users.last_name,
                users.email,
                users.phone
             FROM orders
             INNER JOIN menus ON menus.id = orders.menu_id
             INNER JOIN users ON users.id = orders.user_id
             WHERE orders.id = :order_id AND orders.user_id = :user_id'
        );
        $stmt->execute([
            'order_id' => $orderId,
            'user_id' => $userId,
        ]);
        $order = $stmt->fetch();

        return $order === false ? null : $order;
    }

    public function findForManagement(int $orderId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                orders.*,
                menus.title AS menu_title,
                users.first_name,
                users.last_name,
                users.email,
                users.phone
             FROM orders
             INNER JOIN menus ON menus.id = orders.menu_id
             INNER JOIN users ON users.id = orders.user_id
             WHERE orders.id = :id'
        );
        $stmt->execute(['id' => $orderId]);
        $order = $stmt->fetch();

        return $order === false ? null : $order;
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

    public function allByFilters(?string $status, string $customer): array
    {
        $sql =
            'SELECT
                orders.id,
                orders.user_id,
                orders.event_date,
                orders.event_time,
                orders.delivery_address,
                orders.delivery_city,
                orders.people_count,
                orders.total_price,
                orders.status,
                orders.cancellation_reason,
                orders.cancellation_contact_method,
                orders.created_at,
                menus.title AS menu_title,
                users.first_name,
                users.last_name,
                users.email,
                users.phone
             FROM orders
             INNER JOIN menus ON menus.id = orders.menu_id
             INNER JOIN users ON users.id = orders.user_id';

        $where = [];
        $params = [];

        if ($status !== null) {
            $where[] = 'orders.status = :status';
            $params['status'] = $status;
        }

        if ($customer !== '') {
            $where[] =
                "(CONCAT(users.first_name, ' ', users.last_name) LIKE :customer
                   OR users.email LIKE :customer)";
            $params['customer'] = '%' . $customer . '%';
        }

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY orders.created_at DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /** @param array<string, mixed> $data */
    public function updateByUser(int $orderId, int $userId, array $data): bool
    {
        $this->pdo->beginTransaction();

        try {
            $lock = $this->pdo->prepare(
                "SELECT status FROM orders
                 WHERE id = :id AND user_id = :user_id
                 FOR UPDATE"
            );
            $lock->execute(['id' => $orderId, 'user_id' => $userId]);

            if ($lock->fetchColumn() !== 'nouvelle') {
                $this->pdo->rollBack();
                return false;
            }

            $stmt = $this->pdo->prepare(
                "UPDATE orders
                 SET event_date = :event_date,
                     event_time = :event_time,
                     delivery_address = :delivery_address,
                     delivery_city = :delivery_city,
                     people_count = :people_count,
                     menu_price = :menu_price,
                     delivery_price = :delivery_price,
                     discount_amount = :discount_amount,
                     total_price = :total_price
                 WHERE id = :id AND user_id = :user_id AND status = 'nouvelle'"
            );
            $stmt->execute([...$data, 'id' => $orderId, 'user_id' => $userId]);
            $this->insertHistory(
                $orderId,
                'nouvelle',
                $userId,
                'Commande modifiée par le client.'
            );
            $this->pdo->commit();

            return true;
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    public function cancelByUser(int $orderId, int $userId): bool
    {
        $this->pdo->beginTransaction();

        try {
            $lock = $this->pdo->prepare(
                "SELECT menu_id, status FROM orders
                 WHERE id = :id AND user_id = :user_id
                 FOR UPDATE"
            );
            $lock->execute(['id' => $orderId, 'user_id' => $userId]);
            $order = $lock->fetch();

            if ($order === false || $order['status'] !== 'nouvelle') {
                $this->pdo->rollBack();
                return false;
            }

            $stmt = $this->pdo->prepare(
                "UPDATE orders
                 SET status = 'annulee',
                     cancellation_reason = 'Annulation demandée par le client'
                 WHERE id = :id"
            );
            $stmt->execute(['id' => $orderId]);
            $this->restoreStock((int) $order['menu_id']);
            $this->insertHistory(
                $orderId,
                'annulee',
                $userId,
                'Commande annulée par le client avant acceptation.'
            );
            $this->pdo->commit();

            return true;
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function updateStatus(
        int $orderId,
        string $status,
        int $employeeId,
        ?string $contactMethod,
        ?string $reason,
        string $expectedStatus
    ): ?array {
        $this->pdo->beginTransaction();

        try {
            $lock = $this->pdo->prepare(
                'SELECT id, user_id, menu_id, status
                 FROM orders WHERE id = :id FOR UPDATE'
            );
            $lock->execute(['id' => $orderId]);
            $order = $lock->fetch();

            if ($order === false || $order['status'] !== $expectedStatus) {
                $this->pdo->rollBack();
                return null;
            }

            $stmt = $this->pdo->prepare(
                'UPDATE orders
                 SET status = :status,
                     cancellation_contact_method = :contact_method,
                     cancellation_reason = :reason
                 WHERE id = :id'
            );
            $stmt->execute([
                'status' => $status,
                'contact_method' => $status === 'annulee' ? $contactMethod : null,
                'reason' => $status === 'annulee' ? $reason : null,
                'id' => $orderId,
            ]);

            if ($status === 'annulee' && $order['status'] !== 'annulee') {
                $this->restoreStock((int) $order['menu_id']);
            }

            $this->insertHistory(
                $orderId,
                $status,
                $employeeId,
                $status === 'annulee' ? $reason : 'Statut mis à jour par l’équipe.'
            );
            $this->pdo->commit();

            return $this->findForManagement($orderId);
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    public function history(int $orderId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                order_status_history.status,
                order_status_history.comment,
                order_status_history.created_at,
                users.first_name,
                users.last_name
             FROM order_status_history
             LEFT JOIN users ON users.id = order_status_history.changed_by_user_id
             WHERE order_status_history.order_id = :order_id
             ORDER BY order_status_history.created_at, order_status_history.id'
        );
        $stmt->execute(['order_id' => $orderId]);

        return $stmt->fetchAll();
    }

    public function countNew(): int
    {
        return (int) $this->pdo->query(
            "SELECT COUNT(*) FROM orders WHERE status = 'nouvelle'"
        )->fetchColumn();
    }

    public function analyticsRows(): array
    {
        return $this->pdo->query(
            "SELECT
                orders.id AS order_id,
                orders.menu_id,
                menus.title AS menu_title,
                orders.total_price,
                orders.status,
                orders.created_at
             FROM orders
             INNER JOIN menus ON menus.id = orders.menu_id"
        )->fetchAll();
    }

    private function restoreStock(int $menuId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE menus SET stock = stock + 1 WHERE id = :id'
        );
        $stmt->execute(['id' => $menuId]);
    }

    private function insertHistory(
        int $orderId,
        string $status,
        ?int $changedByUserId,
        ?string $comment
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO order_status_history
                (order_id, status, changed_by_user_id, comment)
             VALUES
                (:order_id, :status, :changed_by_user_id, :comment)'
        );
        $stmt->execute([
            'order_id' => $orderId,
            'status' => $status,
            'changed_by_user_id' => $changedByUserId,
            'comment' => $comment,
        ]);
    }
}
