ALTER TABLE order_items
    ADD COLUMN server_name_snapshot VARCHAR(255) DEFAULT NULL,
    ADD COLUMN server_description_snapshot TEXT DEFAULT NULL,
    ADD COLUMN server_image_snapshot VARCHAR(255) DEFAULT NULL,
    ADD COLUMN server_base_price_snapshot DECIMAL(10,2) DEFAULT NULL,
    ADD COLUMN components_snapshot LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL;

UPDATE order_items oi
INNER JOIN servers s ON s.id = oi.server_id
SET oi.server_name_snapshot = s.name,
    oi.server_description_snapshot = s.description,
    oi.server_image_snapshot = s.image,
    oi.server_base_price_snapshot = s.base_price;

ALTER TABLE order_items
    DROP FOREIGN KEY order_items_ibfk_2;