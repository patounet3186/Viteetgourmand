<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ContactMessage;
use PDO;

final class ContactController extends Controller
{
    private ContactMessage $messages;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->messages = new ContactMessage($pdo);
    }

    public function index(): array
    {
        $errors = [];
        $form = [
            'full_name' => '',
            'email' => '',
            'subject' => '',
            'message' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $form = [
                'full_name' => trim((string) ($_POST['full_name'] ?? '')),
                'email' => trim((string) ($_POST['email'] ?? '')),
                'subject' => trim((string) ($_POST['subject'] ?? '')),
                'message' => trim((string) ($_POST['message'] ?? '')),
            ];

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                $errors[] = 'Le formulaire a expiré, merci de réessayer.';
            }

            if (mb_strlen($form['full_name']) < 2 || mb_strlen($form['full_name']) > 100) {
                $errors[] = 'Le nom doit contenir entre 2 et 100 caractères.';
            }

            if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'L’adresse e-mail est invalide.';
            }

            if (mb_strlen($form['subject']) < 3 || mb_strlen($form['subject']) > 150) {
                $errors[] = 'Le sujet doit contenir entre 3 et 150 caractères.';
            }

            if (mb_strlen($form['message']) < 10 || mb_strlen($form['message']) > 3000) {
                $errors[] = 'Le message doit contenir entre 10 et 3 000 caractères.';
            }

            if (empty($errors)) {
                $this->messages->create($form);
                $this->redirect('contact', ['sent' => 1]);
            }
        }

        $messageSent = ($_GET['sent'] ?? '') === '1';

        return $this->render('pages/contact', 'Contact', compact(
            'errors',
            'form',
            'messageSent'
        ));
    }
}
