<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Review;
use PDO;

final class ReviewController extends Controller
{
    private Order $orders;
    private ?Review $reviews = null;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->orders = new Order($pdo);
    }

    public function create(): array
    {
        $user = $this->requireUser();
        $orderId = (int) ($_GET['order_id'] ?? 0);
        $order = $this->orders->findForReview($orderId, (int) $user['id']);

        if ($order === null) {
            $this->abort(404, 'Commande non trouvée');
        }

        if ($order['status'] !== 'terminee') {
            $this->abort(
                403,
                'Avis indisponible',
                'Vous pourrez laisser un avis quand la commande sera terminée.'
            );
        }

        $reviews = $this->reviewModel();
        try {
            $existingReview = $reviews->findByOrderId($orderId);
        } catch (\Throwable $exception) {
            $this->reviewServiceUnavailable($exception);
        }
        $errors = [];
        $rating = 5;
        $comment = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $existingReview === null) {
            $rating = (int) ($_POST['rating'] ?? 0);
            $comment = trim((string) ($_POST['comment'] ?? ''));

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                $errors[] = 'Le formulaire a expiré, merci de réessayer.';
            }

            if ($rating < 1 || $rating > 5) {
                $errors[] = 'La note doit être comprise entre 1 et 5.';
            }

            if (mb_strlen($comment) < 10 || mb_strlen($comment) > 2000) {
                $errors[] = 'Le commentaire doit contenir entre 10 et 2 000 caractères.';
            }

            if (empty($errors)) {
                try {
                    $reviews->create([
                        'order_id' => $order['id'],
                        'user_id' => $user['id'],
                        'user_name' => $user['first_name'] . ' ' . $user['last_name'],
                        'menu_id' => $order['menu_id'],
                        'menu_title' => $order['menu_title'],
                        'rating' => $rating,
                        'comment' => $comment,
                    ]);
                } catch (\Throwable $exception) {
                    $this->reviewServiceUnavailable($exception);
                }
                $this->redirect('account', ['review' => 'created']);
            }
        }

        return $this->render('reviews/create', 'Déposer un avis', compact(
            'order',
            'existingReview',
            'errors',
            'rating',
            'comment'
        ));
    }

    public function manage(): array
    {
        $this->requireRole(['employee', 'admin']);
        $reviews = $this->reviewModel();
        $errors = [];

        if (
            $_SERVER['REQUEST_METHOD'] === 'POST'
            && ($_POST['action'] ?? '') === 'moderate_review'
        ) {
            $reviewId = trim((string) ($_POST['review_id'] ?? ''));
            $reviewStatus = (string) ($_POST['review_status'] ?? '');

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                $errors[] = 'Le formulaire a expiré, merci de réessayer.';
            }

            if (
                $reviewId === ''
                || !in_array($reviewStatus, ['validated', 'refused'], true)
            ) {
                $errors[] = 'La décision de modération est invalide.';
            }

            if ($errors === []) {
                try {
                    $reviews->updateStatus($reviewId, $reviewStatus);
                } catch (\Throwable $exception) {
                    $this->reviewServiceUnavailable($exception);
                }
                $this->redirect('employee-reviews', ['updated' => 1]);
            }
        }

        try {
            $pendingReviews = $reviews->byStatus('pending');
        } catch (\Throwable $exception) {
            $this->reviewServiceUnavailable($exception);
        }
        $reviewUpdated = ($_GET['updated'] ?? '') === '1';

        return $this->render('employee/reviews', 'Gestion des avis', compact(
            'pendingReviews',
            'errors',
            'reviewUpdated'
        ));
    }

    private function reviewModel(): Review
    {
        try {
            return $this->reviews ??= new Review();
        } catch (\Throwable $exception) {
            error_log('Service d’avis indisponible : ' . $exception->getMessage());
            $this->abort(
                503,
                'Avis temporairement indisponibles',
                'Le service d’avis est momentanément inaccessible. Réessayez plus tard.'
            );
        }
    }

    private function reviewServiceUnavailable(\Throwable $exception): never
    {
        error_log('Service d’avis indisponible : ' . $exception->getMessage());
        $this->abort(
            503,
            'Avis temporairement indisponibles',
            'Le service d’avis est momentanément inaccessible. Réessayez plus tard.'
        );
    }
}
