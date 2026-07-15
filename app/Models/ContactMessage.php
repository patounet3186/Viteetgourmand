<?php

declare(strict_types=1);

namespace App\Models;

final class ContactMessage extends Model
{
    /** @param array<string, string> $data */
    public function create(array $data): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO contact_messages (full_name, email, subject, message)
             VALUES (:full_name, :email, :subject, :message)'
        );
        $stmt->execute($data);
    }
}
