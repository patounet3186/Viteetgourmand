<?php

declare(strict_types=1);

use MongoDB\Collection;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$config = require dirname(__DIR__) . '/config/mongodb.php';
$client = new MongoDB\Client($config['uri']);
$database = $client->selectDatabase($config['database']);
$reviews = $database->selectCollection($config['collection']);
$analytics = $database->selectCollection(
    $config['analytics_collection'] ?? 'order_analytics'
);

/** @return bool */
function hasDuplicateOrderIds(Collection $collection): bool
{
    $duplicates = $collection->aggregate([
        [
            '$group' => [
                '_id' => '$order_id',
                'count' => ['$sum' => 1],
            ],
        ],
        ['$match' => ['count' => ['$gt' => 1]]],
        ['$limit' => 1],
    ])->toArray();

    return $duplicates !== [];
}

if (hasDuplicateOrderIds($reviews) || hasDuplicateOrderIds($analytics)) {
    fwrite(
        STDERR,
        "Des identifiants de commande sont en double. Aucun index n’a été créé.\n"
    );
    exit(1);
}

$reviews->createIndex(
    ['order_id' => 1],
    ['unique' => true, 'name' => 'unique_review_per_order']
);
$reviews->createIndex(
    ['status' => 1, 'created_at' => -1],
    ['name' => 'reviews_by_status_and_date']
);
$analytics->createIndex(
    ['order_id' => 1],
    ['unique' => true, 'name' => 'unique_analytics_order']
);
$analytics->createIndex(
    ['menu_id' => 1, 'created_at' => 1],
    ['name' => 'analytics_by_menu_and_date']
);

echo "MongoDB index checks passed.\n";
