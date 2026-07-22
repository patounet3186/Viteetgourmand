<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Review;

final class HomeController extends Controller
{
    public function index(): array
    {
        try {
            $reviewModel = new Review();
            $reviews = array_slice($reviewModel->byStatus('validated'), 0, 3);
        } catch (\Throwable $exception) {
            error_log('Avis indisponibles sur l’accueil : ' . $exception->getMessage());
            $reviews = [];
        }

        return $this->render('pages/home', 'Accueil', compact('reviews'));
    }
}
