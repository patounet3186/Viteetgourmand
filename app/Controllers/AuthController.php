<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Url;
use App\Models\Order;
use App\Models\PasswordResetToken;
use App\Models\Review;
use App\Models\User;
use App\Services\MailService;
use PDO;

final class AuthController extends Controller
{
    private User $users;
    private Order $orders;
    private PasswordResetToken $resetTokens;
    private MailService $mailer;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->users = new User($pdo);
        $this->orders = new Order($pdo);
        $this->resetTokens = new PasswordResetToken($pdo);
        $this->mailer = new MailService();
    }

    public function register(): array
    {
        $errors = [];
        $success = ($_GET['created'] ?? '') === '1'
            ? 'Compte créé avec succès. Vous pouvez maintenant vous connecter.'
            : null;
        $form = [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'phone' => '',
            'address' => '',
            'postal_code' => '',
            'city' => '',
        ];
        $termsAccepted = false;

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
            $termsAccepted = isset($_POST['terms_accepted']);

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

            if (
                mb_strlen($form['first_name']) < 2
                || mb_strlen($form['first_name']) > 100
                || mb_strlen($form['last_name']) < 2
                || mb_strlen($form['last_name']) > 100
            ) {
                $errors[] = 'Le prénom et le nom doivent contenir entre 2 et 100 caractères.';
            }

            if (mb_strlen($form['email']) > 180) {
                $errors[] = 'L’adresse e-mail est trop longue.';
            }

            if (
                $form['phone'] !== ''
                && (
                    mb_strlen($form['phone']) > 30
                    || preg_match('/^[0-9+().\s-]+$/', $form['phone']) !== 1
                )
            ) {
                $errors[] = 'Le numéro de téléphone est invalide.';
            }

            if (
                mb_strlen($form['address']) > 255
                || mb_strlen($form['postal_code']) > 20
                || mb_strlen($form['city']) > 100
            ) {
                $errors[] = 'Une information d’adresse est trop longue.';
            }

            if (!$this->strongPassword($password)) {
                $errors[] = 'Le mot de passe doit contenir 10 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.';
            }

            if (!$termsAccepted) {
                $errors[] = 'Vous devez accepter les conditions générales et la politique de confidentialité.';
            }

            if (empty($errors) && $this->users->emailExists($form['email'])) {
                $errors[] = 'Un compte existe déjà avec cet email.';
            }

            if (empty($errors)) {
                $this->users->createCustomer([
                    ...$form,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ]);
                $this->redirect('register', ['created' => 1]);
            }
        }

        return $this->render('auth/register', 'Inscription', compact(
            'errors',
            'success',
            'form',
            'termsAccepted'
        ));
    }

    public function login(): array
    {
        $errors = [];
        $email = '';
        $passwordReset = ($_GET['reset'] ?? '') === '1';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim((string) ($_POST['email'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');

            if ($this->loginIsBlocked()) {
                $errors[] = 'Trop de tentatives. Réessayez dans quelques minutes.';
            } elseif (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                $errors[] = 'Le formulaire a expiré, merci de réessayer.';
            }

            if ($email === '' || $password === '') {
                $errors[] = 'Email et mot de passe obligatoires.';
            }

            if (empty($errors)) {
                $user = $this->users->findActiveByEmail($email);

                if ($user === null || !password_verify($password, $user['password_hash'])) {
                    $errors[] = 'Identifiants incorrects.';
                    $this->recordLoginFailure();
                } else {
                    $this->clearLoginFailures();
                    session_regenerate_id(true);

                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'first_name' => $user['first_name'],
                        'last_name' => $user['last_name'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                    ];

                    $redirectPage = match ($user['role']) {
                        'user' => 'menus',
                        'employee' => 'employee-orders',
                        'admin' => 'admin-dashboard',
                        default => 'home',
                    };

                    $intendedRequest = $_SESSION['intended_request'] ?? null;
                    unset($_SESSION['intended_request']);

                    if (
                        $user['role'] === 'user'
                        && is_array($intendedRequest)
                        && ($intendedRequest['page'] ?? '') === 'order-create'
                    ) {
                        header('Location: ?' . http_build_query($intendedRequest));
                        exit;
                    }

                    $this->redirect($redirectPage);
                }
            }
        }

        return $this->render('auth/login', 'Connexion', compact(
            'errors',
            'email',
            'passwordReset'
        ));
    }

    public function forgotPassword(): array
    {
        $errors = [];
        $email = '';
        $requestSent = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim((string) ($_POST['email'] ?? ''));

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                $errors[] = 'Le formulaire a expiré, merci de réessayer.';
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'L’adresse e-mail est invalide.';
            }

            if ($errors === []) {
                $user = $this->users->findActiveForPasswordReset($email);

                if ($user !== null) {
                    $rawToken = $this->resetTokens->create((int) $user['id']);
                    $this->mailer->passwordReset(
                        (string) $user['email'],
                        (string) $user['first_name'],
                        Url::page('reset-password', ['token' => $rawToken])
                    );
                }

                $requestSent = true;
            }
        }

        return $this->render(
            'auth/forgot-password',
            'Mot de passe oublié',
            compact('errors', 'email', 'requestSent')
        );
    }

    public function resetPassword(): array
    {
        $rawToken = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
        $token = $this->resetTokens->findValid($rawToken);
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token !== null) {
            $password = (string) ($_POST['password'] ?? '');
            $confirmation = (string) ($_POST['password_confirmation'] ?? '');

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                $errors[] = 'Le formulaire a expiré, merci de réessayer.';
            }

            if (!$this->strongPassword($password)) {
                $errors[] = 'Le mot de passe doit contenir 10 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.';
            }

            if ($password !== $confirmation) {
                $errors[] = 'Les deux mots de passe ne correspondent pas.';
            }

            if (
                $errors === []
                && $this->resetTokens->consume(
                    $rawToken,
                    password_hash($password, PASSWORD_DEFAULT)
                )
            ) {
                $this->redirect('login', ['reset' => 1]);
            }
        }

        $tokenValid = $token !== null;

        return $this->render(
            'auth/reset-password',
            'Nouveau mot de passe',
            compact('rawToken', 'tokenValid', 'errors')
        );
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
        $reviewsAvailable = true;

        try {
            $reviews = new Review();
            foreach ($orders as &$order) {
                $order['existing_review'] = $reviews->findByOrderId((int) $order['id']);
            }
            unset($order);
        } catch (\Throwable $exception) {
            error_log('Avis indisponibles dans l’espace client : ' . $exception->getMessage());
            $reviewsAvailable = false;

            foreach ($orders as &$order) {
                $order['existing_review'] = null;
            }
            unset($order);
        }

        foreach ($orders as &$order) {
            $order['can_review'] = $reviewsAvailable && $order['status'] === 'terminee';
            $order['can_modify'] = $order['status'] === 'nouvelle';
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
            'reviewsAvailable',
            'reviewCreated',
            'profileUpdated'
        ));
    }

    public function logout(): never
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->abort(405, 'Méthode non autorisée');
        }

        if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
            $this->abort(
                403,
                'Formulaire expiré',
                'Rechargez la page avant de vous déconnecter.'
            );
        }

        session_unset();
        session_destroy();
        $this->redirect('home');
    }

    private function strongPassword(string $password): bool
    {
        return preg_match(
            '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{10,}$/',
            $password
        ) === 1;
    }

    private function loginIsBlocked(): bool
    {
        return (int) ($_SESSION['login_guard']['blocked_until'] ?? 0) > time();
    }

    private function recordLoginFailure(): void
    {
        $now = time();
        $guard = is_array($_SESSION['login_guard'] ?? null)
            ? $_SESSION['login_guard']
            : [];
        $firstAttemptAt = (int) ($guard['first_attempt_at'] ?? $now);
        $attempts = (int) ($guard['attempts'] ?? 0);

        if ($now - $firstAttemptAt > 900) {
            $firstAttemptAt = $now;
            $attempts = 0;
        }

        $attempts++;
        $_SESSION['login_guard'] = [
            'attempts' => $attempts,
            'first_attempt_at' => $firstAttemptAt,
            'blocked_until' => $attempts >= 5 ? $now + 900 : 0,
        ];
    }

    private function clearLoginFailures(): void
    {
        unset($_SESSION['login_guard']);
    }
}
