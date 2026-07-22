// À exécuter une fois dans mongosh après avoir sélectionné vite_et_gourmand.

db.reviews.createIndex(
  { order_id: 1 },
  { unique: true, name: "unique_review_per_order" }
);

db.reviews.createIndex(
  { status: 1, created_at: -1 },
  { name: "reviews_by_status_and_date" }
);

db.order_analytics.createIndex(
  { order_id: 1 },
  { unique: true, name: "unique_analytics_order" }
);

db.order_analytics.createIndex(
  { menu_id: 1, created_at: 1 },
  { name: "analytics_by_menu_and_date" }
);
