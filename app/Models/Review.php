<?php

declare(strict_types=1);

namespace App\Models;

use MongoDB\Client;
use MongoDB\Collection;

final class Review
{
    private Collection $collection;

    public function __construct()
    {
        $config = require dirname(__DIR__, 2) . '/config/mongodb.php';
        $client = new Client($config['uri']);
        $this->collection = $client->selectCollection(
            $config['database'],
            $config['collection']
        );
    }

    public function all(): array
    {
        return $this->findMany([]);
    }

    public function byStatus(string $status): array
    {
        return $this->findMany(['status' => $status]);
    }

    public function findByOrderId(int $orderId): ?array
    {
        $review = $this->collection->findOne(
            ['order_id' => $orderId],
            ['typeMap' => $this->typeMap()]
        );

        return $review === null ? null : $this->normalize($review);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): void
    {
        $this->collection->insertOne([
            'id' => 'review_' . bin2hex(random_bytes(8)),
            'order_id' => (int) $data['order_id'],
            'user_id' => (int) $data['user_id'],
            'user_name' => (string) $data['user_name'],
            'menu_id' => (int) $data['menu_id'],
            'menu_title' => (string) $data['menu_title'],
            'rating' => (int) $data['rating'],
            'comment' => trim((string) $data['comment']),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateStatus(string $reviewId, string $status): void
    {
        if (!in_array($status, ['pending', 'validated', 'refused'], true)) {
            return;
        }

        $this->collection->updateOne(
            ['id' => $reviewId],
            [
                '$set' => [
                    'status' => $status,
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
            ]
        );
    }

    /** @param array<string, mixed> $filter */
    private function findMany(array $filter): array
    {
        $cursor = $this->collection->find($filter, [
            'sort' => ['created_at' => -1],
            'typeMap' => $this->typeMap(),
        ]);

        $reviews = [];

        foreach ($cursor as $review) {
            $reviews[] = $this->normalize($review);
        }

        return $reviews;
    }

    /** @return array{root: string, document: string, array: string} */
    private function typeMap(): array
    {
        return [
            'root' => 'array',
            'document' => 'array',
            'array' => 'array',
        ];
    }

    /** @param array<string, mixed> $review */
    private function normalize(array $review): array
    {
        if (isset($review['_id'])) {
            $review['_id'] = (string) $review['_id'];
        }

        return $review;
    }
}
