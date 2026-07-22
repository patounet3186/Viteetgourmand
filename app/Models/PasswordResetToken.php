<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;

final class PasswordResetToken extends Model
{
    public function create(int $userId): string
    {
        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        $expiresAt = (new DateTimeImmutable('+1 hour'))->format('Y-m-d H:i:s');

        $delete = $this->pdo->prepare(
            'DELETE FROM password_reset_tokens
             WHERE user_id = :user_id OR expires_at < NOW()'
        );
        $delete->execute(['user_id' => $userId]);

        $stmt = $this->pdo->prepare(
            'INSERT INTO password_reset_tokens (user_id, token_hash, expires_at)
             VALUES (:user_id, :token_hash, :expires_at)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);

        return $rawToken;
    }

    public function findValid(string $rawToken): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $rawToken)) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT
                password_reset_tokens.id,
                password_reset_tokens.user_id,
                users.first_name,
                users.email
             FROM password_reset_tokens
             INNER JOIN users ON users.id = password_reset_tokens.user_id
             WHERE password_reset_tokens.token_hash = :token_hash
               AND password_reset_tokens.used_at IS NULL
               AND password_reset_tokens.expires_at > NOW()
               AND users.is_active = 1'
        );
        $stmt->execute(['token_hash' => hash('sha256', $rawToken)]);
        $token = $stmt->fetch();

        return $token === false ? null : $token;
    }

    public function consume(string $rawToken, string $passwordHash): bool
    {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                'SELECT id, user_id
                 FROM password_reset_tokens
                 WHERE token_hash = :token_hash
                   AND used_at IS NULL
                   AND expires_at > NOW()
                 FOR UPDATE'
            );
            $stmt->execute(['token_hash' => hash('sha256', $rawToken)]);
            $token = $stmt->fetch();

            if ($token === false) {
                $this->pdo->rollBack();
                return false;
            }

            $updateUser = $this->pdo->prepare(
                'UPDATE users SET password_hash = :password_hash
                 WHERE id = :user_id AND is_active = 1'
            );
            $updateUser->execute([
                'password_hash' => $passwordHash,
                'user_id' => $token['user_id'],
            ]);

            if ($updateUser->rowCount() !== 1) {
                $this->pdo->rollBack();
                return false;
            }

            $markUsed = $this->pdo->prepare(
                'UPDATE password_reset_tokens SET used_at = NOW() WHERE id = :id'
            );
            $markUsed->execute(['id' => $token['id']]);
            $this->pdo->commit();

            return true;
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }
}
