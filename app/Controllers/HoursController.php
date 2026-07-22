<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\BusinessHour;
use PDO;

final class HoursController extends Controller
{
    private BusinessHour $hours;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->hours = new BusinessHour($pdo);
    }

    public function manage(): array
    {
        $this->requireRole(['employee', 'admin']);

        $hours = $this->hours->all();
        $errors = [];

        if (
            $_SERVER['REQUEST_METHOD'] === 'POST'
            && ($_POST['action'] ?? '') === 'update_hours'
        ) {
            $hours = $this->hourInput($hours);

            if (!\csrf_is_valid($_POST['csrf_token'] ?? null)) {
                $errors[] = 'Le formulaire a expiré, merci de réessayer.';
            }

            foreach ($hours as $day) {
                $errors = [
                    ...$errors,
                    ...$this->validateDay($day),
                ];
            }

            if ($errors === []) {
                $this->hours->updateAll($hours);
                $this->redirect('employee-hours', ['updated' => 1]);
            }
        }

        $hoursUpdated = ($_GET['updated'] ?? '') === '1';

        return $this->render('employee/hours', 'Gestion des horaires', compact(
            'hours',
            'errors',
            'hoursUpdated'
        ));
    }

    /**
     * @param list<array<string, mixed>> $currentHours
     * @return list<array<string, mixed>>
     */
    private function hourInput(array $currentHours): array
    {
        $hours = [];

        foreach ($currentHours as $day) {
            $dayNumber = (int) $day['day_of_week'];
            $isClosed = isset($_POST['is_closed'][$dayNumber]);
            $hours[] = [
                'day_of_week' => $dayNumber,
                'day_label' => (string) $day['day_label'],
                'first_open' => $isClosed
                    ? null
                    : $this->timeInput('first_open', $dayNumber),
                'first_close' => $isClosed
                    ? null
                    : $this->timeInput('first_close', $dayNumber),
                'second_open' => $isClosed
                    ? null
                    : $this->timeInput('second_open', $dayNumber),
                'second_close' => $isClosed
                    ? null
                    : $this->timeInput('second_close', $dayNumber),
                'is_closed' => (int) $isClosed,
            ];
        }

        return $hours;
    }

    private function timeInput(string $field, int $dayNumber): ?string
    {
        $value = trim((string) ($_POST[$field][$dayNumber] ?? ''));

        return $value === '' ? null : $value;
    }

    /**
     * @param array<string, mixed> $day
     * @return list<string>
     */
    private function validateDay(array $day): array
    {
        if ((int) $day['is_closed'] === 1) {
            return [];
        }

        $label = (string) $day['day_label'];
        $firstOpen = $day['first_open'];
        $firstClose = $day['first_close'];
        $secondOpen = $day['second_open'];
        $secondClose = $day['second_close'];

        if (
            !$this->validTime($firstOpen)
            || !$this->validTime($firstClose)
            || $firstOpen >= $firstClose
        ) {
            return ["Les horaires principaux du {$label} sont invalides."];
        }

        if (($secondOpen === null) !== ($secondClose === null)) {
            return ["La seconde plage du {$label} doit être entièrement renseignée."];
        }

        if (
            $secondOpen !== null
            && (
                !$this->validTime($secondOpen)
                || !$this->validTime($secondClose)
                || $secondOpen >= $secondClose
                || $secondOpen < $firstClose
            )
        ) {
            return ["La seconde plage du {$label} est invalide."];
        }

        return [];
    }

    private function validTime(mixed $value): bool
    {
        return is_string($value)
            && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value) === 1;
    }
}
