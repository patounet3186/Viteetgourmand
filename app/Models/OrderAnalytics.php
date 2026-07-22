<?php

declare(strict_types=1);

namespace App\Models;

use MongoDB\Client;
use MongoDB\Collection;

final class OrderAnalytics
{
    private Collection $collection;

    public function __construct()
    {
        $config = require dirname(__DIR__, 2) . '/config/mongodb.php';
        $client = new Client($config['uri']);
        $this->collection = $client->selectCollection(
            $config['database'],
            $config['analytics_collection'] ?? 'order_analytics'
        );
    }

    /** @param list<array<string, mixed>> $orders */
    public function synchronize(array $orders): void
    {
        foreach ($orders as $order) {
            $this->collection->updateOne(
                ['order_id' => (int) $order['order_id']],
                [
                    '$set' => [
                        'menu_id' => (int) $order['menu_id'],
                        'menu_title' => (string) $order['menu_title'],
                        'total_price' => (float) $order['total_price'],
                        'status' => (string) $order['status'],
                        'created_at' => (string) $order['created_at'],
                        'synchronized_at' => date('Y-m-d H:i:s'),
                    ],
                ],
                ['upsert' => true]
            );
        }
    }

    public function statistics(
        ?int $menuId,
        ?string $dateFrom,
        ?string $dateTo
    ): array {
        $match = ['status' => ['$ne' => 'annulee']];

        if ($menuId !== null) {
            $match['menu_id'] = $menuId;
        }

        if ($dateFrom !== null || $dateTo !== null) {
            $match['created_at'] = [];

            if ($dateFrom !== null) {
                $match['created_at']['$gte'] = $dateFrom . ' 00:00:00';
            }

            if ($dateTo !== null) {
                $match['created_at']['$lte'] = $dateTo . ' 23:59:59';
            }
        }

        $cursor = $this->collection->aggregate([
            ['$match' => $match],
            [
                '$group' => [
                    '_id' => [
                        'menu_id' => '$menu_id',
                        'menu_title' => '$menu_title',
                    ],
                    'orders_count' => ['$sum' => 1],
                    'turnover' => ['$sum' => '$total_price'],
                ],
            ],
            ['$sort' => ['orders_count' => -1, '_id.menu_title' => 1]],
        ], [
            'typeMap' => [
                'root' => 'array',
                'document' => 'array',
                'array' => 'array',
            ],
        ]);

        $statistics = [];

        foreach ($cursor as $row) {
            $statistics[] = [
                'menu_id' => (int) $row['_id']['menu_id'],
                'title' => (string) $row['_id']['menu_title'],
                'orders_count' => (int) $row['orders_count'],
                'turnover' => (float) $row['turnover'],
            ];
        }

        return $statistics;
    }
}
