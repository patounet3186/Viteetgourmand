<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Menu;
use InvalidArgumentException;

final class MenuManagementService
{
    private const DIETS = ['classique', 'végétarien', 'végan'];
    private const MAX_IMAGES = 6;

    public function __construct(private Menu $menus)
    {
    }

    /** @return array<string, mixed> */
    public static function emptyForm(): array
    {
        return [
            'title' => '',
            'description' => '',
            'theme' => '',
            'diet' => 'classique',
            'min_people' => '',
            'base_price' => '',
            'conditions_text' => '',
            'stock' => '0',
            'image_urls' => '',
            'dish_ids' => [],
            'is_active' => 1,
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public static function normalize(array $input): array
    {
        $postedDishIds = is_array($input['dish_ids'] ?? null)
            ? $input['dish_ids']
            : [];
        $dishIds = array_values(array_unique(array_filter(
            array_map('intval', $postedDishIds),
            static fn (int $dishId): bool => $dishId > 0
        )));

        return [
            'title' => trim((string) ($input['title'] ?? '')),
            'description' => trim((string) ($input['description'] ?? '')),
            'theme' => trim((string) ($input['theme'] ?? '')),
            'diet' => (string) ($input['diet'] ?? ''),
            'min_people' => trim((string) ($input['min_people'] ?? '')),
            'base_price' => trim((string) ($input['base_price'] ?? '')),
            'conditions_text' => trim((string) ($input['conditions_text'] ?? '')),
            'stock' => trim((string) ($input['stock'] ?? '')),
            'image_urls' => trim((string) ($input['image_urls'] ?? '')),
            'dish_ids' => $dishIds,
            'is_active' => isset($input['is_active']) ? 1 : 0,
        ];
    }

    /**
     * @param array<string, mixed> $menu
     * @param list<string> $imageUrls
     * @param list<int> $dishIds
     * @return array<string, mixed>
     */
    public static function fromRecord(
        array $menu,
        array $imageUrls,
        array $dishIds
    ): array {
        return [
            'title' => (string) $menu['title'],
            'description' => (string) $menu['description'],
            'theme' => (string) $menu['theme'],
            'diet' => (string) $menu['diet'],
            'min_people' => (string) $menu['min_people'],
            'base_price' => (string) $menu['base_price'],
            'conditions_text' => (string) $menu['conditions_text'],
            'stock' => (string) $menu['stock'],
            'image_urls' => implode(PHP_EOL, $imageUrls),
            'dish_ids' => $dishIds,
            'is_active' => (int) $menu['is_active'],
        ];
    }

    /**
     * @param array<string, mixed> $menu
     * @param list<array<string, mixed>> $availableDishes
     * @return list<string>
     */
    public static function validate(array $menu, array $availableDishes): array
    {
        $errors = [];

        if (mb_strlen($menu['title']) < 3 || mb_strlen($menu['title']) > 150) {
            $errors[] = 'Le titre doit contenir entre 3 et 150 caractères.';
        }

        if (
            mb_strlen($menu['description']) < 10
            || mb_strlen($menu['description']) > 2000
        ) {
            $errors[] = 'La description doit contenir entre 10 et 2 000 caractères.';
        }

        if (mb_strlen($menu['theme']) < 2 || mb_strlen($menu['theme']) > 80) {
            $errors[] = 'Le thème doit contenir entre 2 et 80 caractères.';
        }

        if (!in_array($menu['diet'], self::DIETS, true)) {
            $errors[] = 'Le régime sélectionné est invalide.';
        }

        if (!ctype_digit($menu['min_people']) || (int) $menu['min_people'] < 1) {
            $errors[] = 'Le nombre minimum de personnes est invalide.';
        }

        $price = str_replace(',', '.', $menu['base_price']);
        if (!is_numeric($price) || (float) $price <= 0) {
            $errors[] = 'Le prix de base est invalide.';
        }

        if (!ctype_digit($menu['stock'])) {
            $errors[] = 'Le stock est invalide.';
        }

        if (
            mb_strlen($menu['conditions_text']) < 10
            || mb_strlen($menu['conditions_text']) > 2000
        ) {
            $errors[] = 'Les conditions doivent contenir entre 10 et 2 000 caractères.';
        }

        $imageUrls = self::parseImageUrls($menu['image_urls']);
        if ($imageUrls === []) {
            $errors[] = 'Ajoutez au moins une image au menu.';
        } elseif (count($imageUrls) > self::MAX_IMAGES) {
            $errors[] = 'Un menu peut contenir au maximum 6 images.';
        }

        foreach ($imageUrls as $imageUrl) {
            if (!self::validImageUrl($imageUrl)) {
                $errors[] = "Le chemin d’image « {$imageUrl} » est invalide.";
            }
        }

        $availableById = [];
        foreach ($availableDishes as $dish) {
            $availableById[(int) $dish['id']] = (string) $dish['category'];
        }

        $selectedCategories = [];
        foreach ($menu['dish_ids'] as $dishId) {
            if (!isset($availableById[$dishId])) {
                $errors[] = 'Un plat sélectionné est introuvable.';
                break;
            }
            $selectedCategories[] = $availableById[$dishId];
        }

        foreach (DishManagementService::categoryLabels() as $category => $label) {
            if (!in_array($category, $selectedCategories, true)) {
                $errors[] = "Sélectionnez au moins un plat dans la catégorie « {$label} ».";
            }
        }

        return array_values(array_unique($errors));
    }

    /** @param array<string, mixed> $menu */
    public function create(array $menu): void
    {
        $imageUrls = self::parseImageUrls($menu['image_urls']);
        $this->menus->create(
            self::toMenuData($menu, $imageUrls),
            $menu['dish_ids'],
            $imageUrls
        );
    }

    /** @param array<string, mixed> $menu */
    public function update(int $menuId, array $menu): void
    {
        $imageUrls = self::parseImageUrls($menu['image_urls']);
        $this->menus->update(
            $menuId,
            self::toMenuData($menu, $imageUrls),
            $menu['dish_ids'],
            $imageUrls
        );
    }

    public function updateState(int $menuId, string $stock, string $isActive): void
    {
        if (
            $menuId <= 0
            || !ctype_digit($stock)
            || !in_array($isActive, ['0', '1'], true)
            || $this->menus->findForManagement($menuId) === null
        ) {
            throw new InvalidArgumentException('Les informations du menu sont invalides.');
        }

        $this->menus->updateState($menuId, (int) $stock, $isActive === '1');
    }

    public function archive(int $menuId): void
    {
        if ($menuId <= 0 || $this->menus->findForManagement($menuId) === null) {
            throw new InvalidArgumentException('Le menu sélectionné est introuvable.');
        }

        $this->menus->archive($menuId);
    }

    /**
     * @param array<string, mixed> $menu
     * @param list<string> $imageUrls
     * @return array<string, mixed>
     */
    private static function toMenuData(array $menu, array $imageUrls): array
    {
        return [
            'title' => $menu['title'],
            'description' => $menu['description'],
            'theme' => $menu['theme'],
            'diet' => $menu['diet'],
            'min_people' => (int) $menu['min_people'],
            'base_price' => (float) str_replace(',', '.', $menu['base_price']),
            'conditions_text' => $menu['conditions_text'],
            'stock' => (int) $menu['stock'],
            'image_url' => $imageUrls[0] ?? null,
            'is_active' => (int) $menu['is_active'],
        ];
    }

    /** @return list<string> */
    private static function parseImageUrls(string $value): array
    {
        $lines = preg_split('/\R/', $value) ?: [];

        return array_values(array_unique(array_filter(array_map(
            static fn (string $line): string => trim($line),
            $lines
        ))));
    }

    private static function validImageUrl(string $imageUrl): bool
    {
        if (mb_strlen($imageUrl) > 255) {
            return false;
        }

        if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            return in_array(
                strtolower((string) parse_url($imageUrl, PHP_URL_SCHEME)),
                ['http', 'https'],
                true
            );
        }

        if (preg_match('#(?:^|/)\.{1,2}(?:/|$)#', $imageUrl) === 1) {
            return false;
        }

        return preg_match(
            '#^/?(?:ECF-2026/)?(?:public/)?images/[A-Za-z0-9._/-]+\.(?:webp|png|jpe?g)$#i',
            $imageUrl
        ) === 1;
    }
}
