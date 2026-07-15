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
    private Review $reviews;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->orders = new Order($pdo);
        $this->reviews = new Review();
    }

    public function create(): array
    {
        $user = $this->requireUser();
        $orderId = (int) ($_GET['order_id'] ?? 0);
        $order = $this->orders->findForReview($orderId, (int) $user['id']);

        if ($order === null) {
            $this->abort(404, 'Commande non trouvée');
        }

        if (!in_array($order['status'], ['livre', 'terminee'], true)) {
            $this->abort(
                403,
                'Avis indisponible',
                'Vous pourrez laisser un avis après la livraison.'
            );
        }

        $existingReview = $this->reviews->findByOrderId($orderId);
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

            if (mb_strlen($comment) < 10) {
                $errors[] = 'Le commentaire doit contenir au moins 10 caractères.';
            }

            if (empty($errors)) {
                $this->reviews->create([
                    'order_id' => $order['id'],
                    'user_id' => $user['id'],
                    'user_name' => $user['first_name'] . ' ' . $user['last_name'],
                    'menu_id' => $order['menu_id'],
                    'menu_title' => $order['menu_title'],
                    'rating' => $rating,
                    'comment' => $comment,
                ]);
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
}
