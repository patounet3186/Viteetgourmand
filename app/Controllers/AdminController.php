<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Menu;
use App\Models\Review;
use App\Models\User;
use PDO;

final class AdminController extends Controller
{
    private User $users;
    private Menu $menus;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->users = new User($pdo);
        $this->menus = new Menu($pdo);
    }

    public function dashboard(): array
    {
        $this->requireRole(['admin']);
        $reviewModel = new Review();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_action'])) {
            $reviewId = (string) ($_POST['review_id'] ?? '');
            $reviewAction = (string) ($_POST['review_action'] ?? '');

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                $this->redirect('admin-dashboard', ['csrf' => 1]);
            }

            if ($reviewId !== '' && in_array($reviewAction, ['validated', 'refused'], true)) {
                $reviewModel->updateStatus($reviewId, $reviewAction);
                $this->redirect('admin-dashboard', ['review_updated' => 1]);
            }
        }

        $stats = $this->menus->statistics();
        $totalOrders = 0;
        $totalTurnover = 0.0;

        foreach ($stats as $stat) {
            $totalOrders += (int) $stat['orders_count'];
            $totalTurnover += (float) $stat['turnover'];
        }

        $reviews = $reviewModel->all();
        $pendingReviews = $reviewModel->byStatus('pending');
        $totalReviews = count($reviews);
        $pendingReviewsCount = count($pendingReviews);
        $ratingSum = 0;

        foreach ($reviews as $review) {
            $ratingSum += (int) ($review['rating'] ?? 0);
        }

        $averageRating = $totalReviews > 0 ? $ratingSum / $totalReviews : null;
        $reviewUpdated = isset($_GET['review_updated']);

        return $this->render('admin/dashboard', 'Administration', compact(
            'stats',
            'totalOrders',
            'totalTurnover',
            'pendingReviews',
            'pendingReviewsCount',
            'averageRating',
            'reviewUpdated'
        ));
    }

    public function users(): array
    {
        $currentUser = $this->requireRole(['admin']);
        $currentUserId = (int) $currentUser['id'];
        $roles = [
            'user' => 'Client',
            'employee' => 'Employé',
            'admin' => 'Administrateur',
        ];
        $errors = [];
        $employeeForm = [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'is_active' => '1',
        ];
        $action = (string) ($_POST['action'] ?? '');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create_employee') {
            $employeeForm = [
                'first_name' => trim((string) ($_POST['first_name'] ?? '')),
                'last_name' => trim((string) ($_POST['last_name'] ?? '')),
                'email' => trim((string) ($_POST['email'] ?? '')),
                'is_active' => (string) ($_POST['is_active'] ?? '1'),
            ];
            $password = (string) ($_POST['password'] ?? '');

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                $errors[] = 'Le formulaire a expiré, merci de réessayer.';
            }

            if (
                mb_strlen($employeeForm['first_name']) < 2
                || mb_strlen($employeeForm['first_name']) > 100
            ) {
                $errors[] = 'Le prénom doit contenir entre 2 et 100 caractères.';
            }

            if (
                mb_strlen($employeeForm['last_name']) < 2
                || mb_strlen($employeeForm['last_name']) > 100
            ) {
                $errors[] = 'Le nom doit contenir entre 2 et 100 caractères.';
            }

            if (
                !filter_var($employeeForm['email'], FILTER_VALIDATE_EMAIL)
                || mb_strlen($employeeForm['email']) > 180
            ) {
                $errors[] = 'L’adresse e-mail est invalide.';
            }

            if (!preg_match(
                '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{10,}$/',
                $password
            )) {
                $errors[] = 'Le mot de passe doit contenir au moins 10 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.';
            }

            if (!in_array($employeeForm['is_active'], ['0', '1'], true)) {
                $errors[] = 'Le statut du compte est invalide.';
            }

            if (empty($errors) && $this->users->emailExists($employeeForm['email'])) {
                $errors[] = 'Un compte existe déjà avec cette adresse e-mail.';
            }

            if (empty($errors)) {
                $this->users->createEmployee([
                    'first_name' => $employeeForm['first_name'],
                    'last_name' => $employeeForm['last_name'],
                    'email' => $employeeForm['email'],
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'is_active' => (int) $employeeForm['is_active'],
                ]);
                $this->redirect('admin-users', ['employee_created' => 1]);
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update_employee_status') {
            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                $this->redirect('admin-users', ['csrf' => 1]);
            }

            $userId = (int) ($_POST['user_id'] ?? 0);
            $isActive = (string) ($_POST['is_active'] ?? '');

            if ($userId <= 0) {
                $errors[] = 'Utilisateur invalide.';
            }

            if (!in_array($isActive, ['0', '1'], true)) {
                $errors[] = 'Statut invalide.';
            }

            if (empty($errors)) {
                $targetRole = $this->users->findRoleById($userId);

                if ($targetRole === null) {
                    $errors[] = 'Utilisateur introuvable.';
                } elseif ($targetRole !== 'employee') {
                    $errors[] = 'Seul le statut d’un employé peut être modifié.';
                }
            }

            if (empty($errors)) {
                $this->users->updateEmployeeStatus($userId, $isActive === '1');
                $this->redirect('admin-users', ['updated' => 1]);
            }
        }

        $users = $this->users->all();
        $userUpdated = isset($_GET['updated']);
        $csrfError = isset($_GET['csrf']);
        $employeeCreated = ($_GET['employee_created'] ?? '') === '1';

        return $this->render('admin/users', 'Gestion des rôles et des accès', compact(
            'currentUserId',
            'roles',
            'errors',
            'employeeForm',
            'users',
            'userUpdated',
            'csrfError',
            'employeeCreated'
        ));
    }
}
