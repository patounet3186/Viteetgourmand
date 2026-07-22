<?php

declare(strict_types=1);

namespace App\Models;

use PDOException;

final class BusinessHour extends Model
{
    public function all(): array
    {
        try {
            $hours = $this->pdo->query(
                'SELECT day_of_week, day_label, first_open, first_close,
                        second_open, second_close, is_closed
                 FROM business_hours
                 ORDER BY day_of_week'
            )->fetchAll();

            return $hours !== [] ? $hours : self::defaults();
        } catch (PDOException) {
            return self::defaults();
        }
    }

    /** @param list<array<string, mixed>> $hours */
    public function updateAll(array $hours): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE business_hours
             SET first_open = :first_open,
                 first_close = :first_close,
                 second_open = :second_open,
                 second_close = :second_close,
                 is_closed = :is_closed
             WHERE day_of_week = :day_of_week'
        );

        $this->pdo->beginTransaction();

        try {
            foreach ($hours as $day) {
                $stmt->execute($day);
            }

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    /** @return list<array<string, mixed>> */
    public static function defaults(): array
    {
        $labels = [
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
            7 => 'Dimanche',
        ];

        $hours = [];

        foreach ($labels as $number => $label) {
            $isClosed = $number === 1;
            $hours[] = [
                'day_of_week' => $number,
                'day_label' => $label,
                'first_open' => $isClosed ? null : '11:00:00',
                'first_close' => $isClosed ? null : '15:00:00',
                'second_open' => $isClosed ? null : '17:00:00',
                'second_close' => $isClosed ? null : '23:00:00',
                'is_closed' => (int) $isClosed,
            ];
        }

        return $hours;
    }
}
