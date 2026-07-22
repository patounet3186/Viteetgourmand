-- Migration additive pour une base Vite & Gourmand deja existante.
-- A executer une seule fois depuis phpMyAdmin ou la console MySQL.

CREATE TABLE IF NOT EXISTS menu_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    position INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE
);

ALTER TABLE orders
    ADD COLUMN cancellation_contact_method ENUM('telephone', 'email') NULL AFTER cancellation_reason,
    ADD COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

CREATE TABLE IF NOT EXISTS order_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    status VARCHAR(40) NOT NULL,
    changed_by_user_id INT,
    comment VARCHAR(500),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by_user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT,
    type VARCHAR(50) NOT NULL,
    message VARCHAR(255) NOT NULL,
    target_page VARCHAR(100) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS business_hours (
    day_of_week TINYINT PRIMARY KEY,
    day_label VARCHAR(20) NOT NULL,
    first_open TIME,
    first_close TIME,
    second_open TIME,
    second_close TIME,
    is_closed TINYINT(1) NOT NULL DEFAULT 0
);

INSERT INTO business_hours
    (day_of_week, day_label, first_open, first_close, second_open, second_close, is_closed)
VALUES
    (1, 'Lundi', NULL, NULL, NULL, NULL, 1),
    (2, 'Mardi', '11:00:00', '15:00:00', '17:00:00', '23:00:00', 0),
    (3, 'Mercredi', '11:00:00', '15:00:00', '17:00:00', '23:00:00', 0),
    (4, 'Jeudi', '11:00:00', '15:00:00', '17:00:00', '23:00:00', 0),
    (5, 'Vendredi', '11:00:00', '15:00:00', '17:00:00', '23:00:00', 0),
    (6, 'Samedi', '11:00:00', '15:00:00', '17:00:00', '23:00:00', 0),
    (7, 'Dimanche', '11:00:00', '15:00:00', '17:00:00', '23:00:00', 0)
ON DUPLICATE KEY UPDATE day_label = VALUES(day_label);

INSERT INTO menu_images (menu_id, image_url, position)
SELECT menus.id, menus.image_url, 0
FROM menus
WHERE menus.image_url IS NOT NULL
  AND menus.image_url != ''
  AND NOT EXISTS (
      SELECT 1
      FROM menu_images
      WHERE menu_images.menu_id = menus.id
  );

INSERT INTO order_status_history (order_id, status, changed_by_user_id, comment, created_at)
SELECT orders.id, orders.status, NULL, 'Etat initial importe', orders.created_at
FROM orders
WHERE NOT EXISTS (
    SELECT 1
    FROM order_status_history
    WHERE order_status_history.order_id = orders.id
);
