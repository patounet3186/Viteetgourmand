<?php

declare(strict_types=1);

namespace App\Services;

final class OrderPricing
{
    /**
     * @param array<string, mixed> $menu
     * @return array{menu_price: float, delivery_price: float, discount_amount: float, total_price: float}
     */
    public static function calculate(array $menu, int $peopleCount, string $city): array
    {
        $minimum = max(1, (int) $menu['min_people']);
        $menuPrice = (float) $menu['base_price'] * ($peopleCount / $minimum);
        $discount = $peopleCount >= $minimum + 5 ? $menuPrice * 0.10 : 0.0;
        $deliveryPrice = mb_strtolower(trim($city)) === 'bordeaux' ? 0.0 : 5.0;

        return [
            'menu_price' => round($menuPrice, 2),
            'delivery_price' => round($deliveryPrice, 2),
            'discount_amount' => round($discount, 2),
            'total_price' => round($menuPrice + $deliveryPrice - $discount, 2),
        ];
    }
}
