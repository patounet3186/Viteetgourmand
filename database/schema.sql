CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('user', 'employee', 'admin') NOT NULL DEFAULT 'user',
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    phone VARCHAR(30),
    address VARCHAR(255),
    postal_code VARCHAR(20),
    city VARCHAR(100),
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    theme VARCHAR(80) NOT NULL,
    diet VARCHAR(80) NOT NULL,
    min_people INT NOT NULL,
    base_price DECIMAL(10,2) NOT NULL,
    conditions_text TEXT NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image_url VARCHAR(255),
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS menu_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    position INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS dishes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category ENUM('entree', 'plat', 'dessert') NOT NULL,
    description TEXT,
    allergens VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS menu_dishes (
    menu_id INT NOT NULL,
    dish_id INT NOT NULL,
    PRIMARY KEY (menu_id, dish_id),
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
    FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    menu_id INT NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    delivery_address VARCHAR(255) NOT NULL,
    delivery_city VARCHAR(100) NOT NULL,
    people_count INT NOT NULL,
    menu_price DECIMAL(10,2) NOT NULL,
    delivery_price DECIMAL(10,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('nouvelle', 'accepte', 'en_preparation', 'en_livraison', 'livre', 'attente_materiel', 'terminee', 'annulee') NOT NULL DEFAULT 'nouvelle',
    cancellation_reason TEXT,
    cancellation_contact_method ENUM('telephone', 'email'),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (menu_id) REFERENCES menus(id)
);

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

CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    rating INT NOT NULL,
    comment TEXT NOT NULL,
    is_validated TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

INSERT INTO menus (title, description, theme, diet, min_people, base_price, conditions_text, stock, image_url) VALUES
('Menu Noel', 'Menu festif avec entrée, plat chaud et dessert gourmand.', 'Noël', 'classique', 6, 180.00, 'Commandé au moins 10 jours avant la prestation.', 5, 'public/images/menu-noel.webp'),
('Menu Paques', 'Menu familial autour de produits de saison.', 'Pâques', 'classique', 4, 120.00, 'A conserver au frais avant dégustation.', 8, 'public/images/menu-paques.webp'),
('Menu Classique', 'Menu simple et efficace pour repas de groupe.', 'Classique', 'végétarien', 2, 45.00, 'Commandé au moins 48h avant la prestation.', 12, 'public/images/menu-classique.webp');

INSERT INTO dishes (name, category, description, allergens) VALUES
('Veloute de saison', 'entree', 'Soupe maison selon les legumes disponibles.', 'lait'),
('Volaille rotie', 'plat', 'Plat chaud accompagne de legumes.', 'aucun'),
('Tarte gourmande', 'dessert', 'Dessert maison aux fruits.', 'gluten, oeufs');

INSERT INTO menu_dishes (menu_id, dish_id) VALUES
(1, 1), (1, 2), (1, 3),
(2, 1), (2, 2), (2, 3),
(3, 1), (3, 3);

CREATE TABLE IF NOT EXISTS contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(180) NOT NULL,
  subject VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
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
    (7, 'Dimanche', '11:00:00', '15:00:00', '17:00:00', '23:00:00', 0);

INSERT INTO menu_images (menu_id, image_url, position)
SELECT id, image_url, 0
FROM menus
WHERE image_url IS NOT NULL AND image_url != '';
