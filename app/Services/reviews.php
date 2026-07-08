<?php

require_once __DIR__ . '/../../vendor/autoload.php';

function getReviewsCollection(): MongoDB\Collection
{
    static $collection = null;

    if ($collection !== null) {
        return $collection;
    }

    $config = require __DIR__ . '/../../config/mongodb.php';

    $client = new MongoDB\Client($config['uri']);
    $collection = $client->selectCollection($config['database'], $config['collection']);

    return $collection;
}

function normalizeReview(array $review): array
{
    if (isset($review['_id'])) {
        $review['_id'] = (string) $review['_id'];
    }

    return $review;
}

function getReviews(): array
{
    $cursor = getReviewsCollection()->find([], [
        'sort' => ['created_at' => -1],
        'typeMap' => [
            'root' => 'array',
            'document' => 'array',
            'array' => 'array',
        ],
    ]);

    $reviews = [];

    foreach ($cursor as $review) {
        $reviews[] = normalizeReview($review);
    }

    return $reviews;
}

function saveReviews(array $reviews): void
{
    $collection = getReviewsCollection();
    $collection->deleteMany([]);

    if (empty($reviews)) {
        return;
    }

    $documents = [];

    foreach ($reviews as $review) {
        unset($review['_id']);
        $documents[] = $review;
    }

    $collection->insertMany($documents);
}

function addReview(array $data): void
{
    getReviewsCollection()->insertOne([
        'id' => 'review_' . bin2hex(random_bytes(8)),
        'order_id' => (int) $data['order_id'],
        'user_id' => (int) $data['user_id'],
        'user_name' => $data['user_name'],
        'menu_id' => (int) $data['menu_id'],
        'menu_title' => $data['menu_title'],
        'rating' => (int) $data['rating'],
        'comment' => trim($data['comment']),
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s'),
    ]);
}

function findReviewByOrderId(int $orderId): ?array
{
    $review = getReviewsCollection()->findOne(
        ['order_id' => $orderId],
        [
            'typeMap' => [
                'root' => 'array',
                'document' => 'array',
                'array' => 'array',
            ],
        ]
    );

    return $review === null ? null : normalizeReview($review);
}

function getReviewsByStatus(string $status): array
{
    $cursor = getReviewsCollection()->find(
        ['status' => $status],
        [
            'sort' => ['created_at' => -1],
            'typeMap' => [
                'root' => 'array',
                'document' => 'array',
                'array' => 'array',
            ],
        ]
    );

    $reviews = [];

    foreach ($cursor as $review) {
        $reviews[] = normalizeReview($review);
    }

    return $reviews;
}

function updateReviewStatus(string $reviewId, string $status): void
{
    if (!in_array($status, ['pending', 'validated', 'refused'], true)) {
        return;
    }

    getReviewsCollection()->updateOne(
        ['id' => $reviewId],
        [
            '$set' => [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ]
    );
}