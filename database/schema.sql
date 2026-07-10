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
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (menu_id) REFERENCES menus(id)
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
('Veloute de saison', 'entrée', 'Soupe maison selon les légumes disponibles.', 'lait'),
('Volaille rôtie', 'plat', 'Plat chaud accompagne de légumes.', 'aucun'),
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