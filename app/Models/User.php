<?php

declare(strict_types=1);

namespace App\Models;

final class User extends Model
{
    public function findActiveByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, first_name, last_name, email, role, password_hash
             FROM users
             WHERE email = :email AND is_active = 1'
        );
        $stmt->execute(['email' => $email]);

        $user = $stmt->fetch();

        return $user === false ? null : $user;
    }

    public function emailExists(string $email, ?int $excludedUserId = null): bool
    {
        if ($excludedUserId === null) {
            $stmt = $this->pdo->prepare(
                'SELECT id FROM users WHERE email = :email LIMIT 1'
            );
            $stmt->execute(['email' => $email]);
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1'
            );
            $stmt->execute(['email' => $email, 'id' => $excludedUserId]);
        }

        return $stmt->fetch() !== false;
    }

    /** @param array<string, mixed> $data */
    public function createCustomer(array $data): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO users
                (role, first_name, last_name, email, phone, address, postal_code, city, password_hash)
             VALUES
                ('user', :first_name, :last_name, :email, :phone, :address, :postal_code, :city, :password_hash)"
        );
        $stmt->execute($data);
    }

    /** @param array<string, mixed> $data */
    public function createEmployee(array $data): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO users
                (role, first_name, last_name, email, password_hash, is_active)
             VALUES
                ('employee', :first_name, :last_name, :email, :password_hash, :is_active)"
        );
        $stmt->execute($data);
    }

    public function findActiveProfile(int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT first_name, last_name, email, phone, address, postal_code, city
             FROM users
             WHERE id = :id AND is_active = 1'
        );
        $stmt->execute(['id' => $userId]);

        $profile = $stmt->fetch();

        return $profile === false ? null : $profile;
    }

    /** @param array<string, mixed> $profile */
    public function updateProfile(int $userId, array $profile): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users
             SET first_name = :first_name,
                 last_name = :last_name,
                 email = :email,
                 phone = :phone,
                 address = :address,
                 postal_code = :postal_code,
                 city = :city
             WHERE id = :id'
        );
        $stmt->execute([...$profile, 'id' => $userId]);
    }

    public function findRoleById(int $userId): ?string
    {
        $stmt = $this->pdo->prepare('SELECT role FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);

        $role = $stmt->fetchColumn();

        return $role === false ? null : (string) $role;
    }

    public function updateEmployeeStatus(int $userId, bool $isActive): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE users
             SET is_active = :is_active
             WHERE id = :id AND role = 'employee'"
        );
        $stmt->execute([
            'is_active' => (int) $isActive,
            'id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function all(): array
    {
        return $this->pdo->query(
            'SELECT id, role, first_name, last_name, email, is_active, created_at
             FROM users
             ORDER BY created_at DESC'
        )->fetchAll();
    }
}
