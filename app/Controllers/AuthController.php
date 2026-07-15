<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use PDO;

final class AuthController extends Controller
{
    private User $users;
    private Order $orders;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->users = new User($pdo);
        $this->orders = new Order($pdo);
    }

    public function register(): array
    {
        $errors = [];
        $success = null;
        $form = [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'phone' => '',
            'address' => '',
            'postal_code' => '',
            'city' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $form = [
                'first_name' => trim((string) ($_POST['first_name'] ?? '')),
                'last_name' => trim((string) ($_POST['last_name'] ?? '')),
                'email' => trim((string) ($_POST['email'] ?? '')),
                'phone' => trim((string) ($_POST['phone'] ?? '')),
                'address' => trim((string) ($_POST['address'] ?? '')),
                'postal_code' => trim((string) ($_POST['postal_code'] ?? '')),
                'city' => trim((string) ($_POST['city'] ?? '')),
            ];
            $password = (string) ($_POST['password'] ?? '');

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                $errors[] = 'Le formulaire a expiré, merci de réessayer.';
            }

            if (
                $form['first_name'] === ''
                || $form['last_name'] === ''
                || $form['email'] === ''
                || $password === ''
            ) {
                $errors[] = 'Les champs obligatoires doivent être remplis.';
            }

            if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Adresse email invalide.';
            }

            if (!preg_match(
                '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{10,}$/',
                $password
            )) {
                $errors[] = 'Le mot de passe doit contenir 10 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.';
            }

            if (empty($errors) && $this->users->emailExists($form['email'])) {
                $errors[] = 'Un compte existe déjà avec cet email.';
            }

            if (empty($errors)) {
                $this->users->createCustomer([
                    ...$form,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ]);
                $success = 'Compte créé avec succès. Vous pouvez maintenant vous connecter.';
            }
        }

        return $this->render('auth/register', 'Inscription', compact(
            'errors',
            'success',
            'form'
        ));
    }

    public function login(): array
    {
        $errors = [];
        $email = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim((string) ($_POST['email'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                $errors[] = 'Le formulaire a expiré, merci de réessayer.';
            }

            if ($email === '' || $password === '') {
                $errors[] = 'Email et mot de passe obligatoires.';
            }

            if (empty($errors)) {
                $user = $this->users->findActiveByEmail($email);

                if ($user === null || !password_verify($password, $user['password_hash'])) {
                    $errors[] = 'Identifiants incorrects.';
                } else {
                    session_regenerate_id(true);

                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'first_name' => $user['first_name'],
                        'last_name' => $user['last_name'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                    ];

                    $redirectPage = match ($user['role']) {
                        'user' => 'home',
                        'employee' => 'employee-orders',
                        'admin' => 'admin-dashboard',
                        default => 'home',
                    };

                    $this->redirect($redirectPage);
                }
            }
        }

        return $this->render('auth/login', 'Connexion', compact('errors', 'email'));
    }

    public function account(): array
    {
        $user = $this->requireUser();
        $userId = (int) $user['id'];
        $profileErrors = [];
        $profile = $this->users->findActiveProfile($userId);

        if ($profile === null) {
            session_unset();
            session_destroy();
            $this->redirect('login');
        }

        if (
            $_SERVER['REQUEST_METHOD'] === 'POST'
            && ($_POST['form_action'] ?? '') === 'update_profile'
        ) {
            $profile = [
                'first_name' => trim((string) ($_POST['first_name'] ?? '')),
                'last_name' => trim((string) ($_POST['last_name'] ?? '')),
                'email' => trim((string) ($_POST['email'] ?? '')),
                'phone' => trim((string) ($_POST['phone'] ?? '')),
                'address' => trim((string) ($_POST['address'] ?? '')),
                'postal_code' => trim((string) ($_POST['postal_code'] ?? '')),
                'city' => trim((string) ($_POST['city'] ?? '')),
            ];

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                $profileErrors[] = 'Le formulaire a expiré, merci de réessayer.';
            }

            if (mb_strlen($profile['first_name']) < 2 || mb_strlen($profile['first_name']) > 100) {
                $profileErrors[] = 'Le prénom doit contenir entre 2 et 100 caractères.';
            }

            if (mb_strlen($profile['last_name']) < 2 || mb_strlen($profile['last_name']) > 100) {
                $profileErrors[] = 'Le nom doit contenir entre 2 et 100 caractères.';
            }

            if (!filter_var($profile['email'], FILTER_VALIDATE_EMAIL)) {
                $profileErrors[] = 'L’adresse e-mail est invalide.';
            }

            if (
                mb_strlen($profile['phone']) > 30
                || ($profile['phone'] !== '' && !preg_match('/^[0-9+().\s-]+$/', $profile['phone']))
            ) {
                $profileErrors[] = 'Le numéro de téléphone est invalide.';
            }

            if (
                mb_strlen($profile['address']) > 255
                || mb_strlen($profile['postal_code']) > 20
                || mb_strlen($profile['city']) > 100
            ) {
                $profileErrors[] = 'Une information est trop longue.';
            }

            if (
                empty($profileErrors)
                && $this->users->emailExists($profile['email'], $userId)
            ) {
                $profileErrors[] = 'Cette adresse e-mail est déjà utilisée.';
            }

            if (empty($profileErrors)) {
                $this->users->updateProfile($userId, $profile);

                $_SESSION['user']['first_name'] = $profile['first_name'];
                $_SESSION['user']['last_name'] = $profile['last_name'];
                $_SESSION['user']['email'] = $profile['email'];

                $this->redirect('account', ['profile' => 'updated']);
            }
        }

        $statuses = OrderController::statuses();
        $orders = $this->orders->findByUser($userId);
        $reviews = new Review();

        foreach ($orders as &$order) {
            $order['existing_review'] = $reviews->findByOrderId((int) $order['id']);
            $order['can_review'] = in_array($order['status'], ['livre', 'terminee'], true);
        }
        unset($order);

        $reviewCreated = ($_GET['review'] ?? '') === 'created';
        $profileUpdated = ($_GET['profile'] ?? '') === 'updated';

        return $this->render('auth/account', 'Mon espace', compact(
            'user',
            'profile',
            'profileErrors',
            'statuses',
            'orders',
            'reviewCreated',
            'profileUpdated'
        ));
    }

    public function logout(): never
    {
        session_unset();
        session_destroy();
        $this->redirect('home');
    }
}
