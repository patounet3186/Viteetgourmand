<?php

declare(strict_types=1);

namespace App\Models;

final class Notification extends Model
{
    public function create(
        int $userId,
        ?int $orderId,
        string $type,
        string $message,
        string $targetPage
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO notifications
                (user_id, order_id, type, message, target_page)
             VALUES
                (:user_id, :order_id, :type, :message, :target_page)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'order_id' => $orderId,
            'type' => $type,
            'message' => $message,
            'target_page' => $targetPage,
        ]);
    }

    public function unreadCount(int $userId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM notifications
             WHERE user_id = :user_id AND is_read = 0'
        );
        $stmt->execute(['user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    public function markOrderRead(int $userId, int $orderId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE notifications
             SET is_read = 1
             WHERE user_id = :user_id AND order_id = :order_id'
        );
        $stmt->execute([
            'user_id' => $userId,
            'order_id' => $orderId,
        ]);
    }
}
