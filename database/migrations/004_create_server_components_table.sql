CREATE TABLE server_components (
    id INT NOT NULL AUTO_INCREMENT,
    server_id INT NOT NULL,
    component_id INT NOT NULL,
    PRIMARY KEY (id),
    KEY server_id (server_id),
    KEY component_id (component_id),
    CONSTRAINT server_components_ibfk_1 FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE,
    CONSTRAINT server_components_ibfk_2 FOREIGN KEY (component_id) REFERENCES components(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;