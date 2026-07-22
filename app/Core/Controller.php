<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

abstract class Controller
{
    public function __construct(protected PDO $pdo)
    {
    }

    /**
     * @param array<string, mixed> $data
     * @return array{view: string, title: string, data: array<string, mixed>}
     */
    protected function render(string $view, string $title, array $data = []): array
    {
        return [
            'view' => dirname(__DIR__) . '/Views/' . $view . '.php',
            'title' => $title,
            'data' => $data,
        ];
    }

    /** @param array<string, scalar> $params */
    protected function redirect(string $page, array $params = []): never
    {
        $query = http_build_query(['page' => $page] + $params);
        header('Location: ?' . $query);
        exit;
    }

    /** @return array<string, mixed> */
    protected function requireUser(): array
    {
        if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
            $intendedRequest = [];

            foreach ($_GET as $key => $value) {
                if (is_string($key) && is_scalar($value)) {
                    $intendedRequest[$key] = (string) $value;
                }
            }

            if ($intendedRequest !== []) {
                $_SESSION['intended_request'] = $intendedRequest;
            }

            $this->redirect('login');
        }

        $userId = (int) ($_SESSION['user']['id'] ?? 0);
        $stmt = $this->pdo->prepare(
            'SELECT id, first_name, last_name, email, role
             FROM users
             WHERE id = :id AND is_active = 1'
        );
        $stmt->execute(['id' => $userId]);
        $activeUser = $stmt->fetch();

        if ($activeUser === false) {
            session_unset();
            session_regenerate_id(true);
            $this->redirect('login');
        }

        $_SESSION['user'] = $activeUser;

        return $activeUser;
    }

    /**
     * @param list<string> $roles
     * @return array<string, mixed>
     */
    protected function requireRole(array $roles): array
    {
        $user = $this->requireUser();

        if (!in_array($user['role'] ?? '', $roles, true)) {
            $this->abort(403, 'Accès refusé', 'Vous ne disposez pas des droits nécessaires.');
        }

        return $user;
    }

    protected function abort(int $statusCode, string $title, string $message = ''): never
    {
        throw new HttpException($statusCode, $title, $message);
    }
}
