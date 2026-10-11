CREATE TABLE components (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    type ENUM('ram', 'cpu', 'ssd', 'hdd', 'gpu', 'storage') NOT NULL,
    value VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    server_id INT DEFAULT NULL,
    available TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
