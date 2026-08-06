<?php

return [
    'uri' => getenv('MONGODB_URI')
        ?: 'mongodb+srv://UTILISATEUR:MOT_DE_PASSE@cluster.mongodb.net/',
    'database' => getenv('MONGODB_DATABASE') ?: 'vite_et_gourmand',
    'collection' => getenv('MONGODB_REVIEWS_COLLECTION') ?: 'reviews',
    'analytics_collection' => getenv('MONGODB_ANALYTICS_COLLECTION')
        ?: 'order_analytics',
];
