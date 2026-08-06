<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Menu;
use InvalidArgumentException;

final class MenuFilterService
{
    private const DIETS = ['classique', 'végétarien', 'végan'];

    public function __construct(private Menu $menus)
    {
    }

    /**
     * @param array<string, mixed> $input
     * @return list<int>
     */
    public function search(array $input): array
    {
        return $this->menus->filterActiveIds(self::normalize($input));
    }

    /**
     * @param array<string, mixed> $input
     * @return array{
     *     min_price: float|null,
     *     max_price: float|null,
     *     theme: string|null,
     *     diet: string|null,
     *     people: int|null
     * }
     */
    public static function normalize(array $input): array
    {
        $minPrice = self::optionalFloat($input['min_price'] ?? null, 'prix minimum');
        $maxPrice = self::optionalFloat($input['max_price'] ?? null, 'prix maximum');
        $people = self::optionalInteger($input['people'] ?? null);
        $theme = self::optionalText($input['theme'] ?? null, 80, 'thème');
        $diet = self::optionalText($input['diet'] ?? null, 30, 'régime');

        if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            throw new InvalidArgumentException(
                'Le prix minimum ne peut pas dépasser le prix maximum.'
            );
        }

        if ($diet !== null && !in_array($diet, self::DIETS, true)) {
            throw new InvalidArgumentException('Le régime sélectionné est invalide.');
        }

        return [
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'theme' => $theme,
            'diet' => $diet,
            'people' => $people,
        ];
    }

    private static function optionalFloat(mixed $value, string $label): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_scalar($value)) {
            throw new InvalidArgumentException("Le {$label} est invalide.");
        }

        $number = filter_var((string) $value, FILTER_VALIDATE_FLOAT);
        if ($number === false || $number < 0 || $number > 1000000) {
            throw new InvalidArgumentException("Le {$label} est invalide.");
        }

        return (float) $number;
    }

    private static function optionalInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_scalar($value) || !ctype_digit((string) $value)) {
            throw new InvalidArgumentException('Le nombre de personnes est invalide.');
        }

        $people = (int) $value;
        if ($people < 1 || $people > 1000) {
            throw new InvalidArgumentException('Le nombre de personnes est invalide.');
        }

        return $people;
    }

    private static function optionalText(mixed $value, int $maxLength, string $label): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_scalar($value)) {
            throw new InvalidArgumentException("Le {$label} est invalide.");
        }

        $text = trim((string) $value);
        if ($text === '' || mb_strlen($text) > $maxLength) {
            throw new InvalidArgumentException("Le {$label} est invalide.");
        }

        return $text;
    }
}
