-- Ajoute la distance utilisée pour calculer les frais de livraison.
-- À exécuter une seule fois sur une base existante.

ALTER TABLE orders
    ADD COLUMN delivery_distance_km DECIMAL(8,2) NOT NULL DEFAULT 0
    AFTER delivery_city;
