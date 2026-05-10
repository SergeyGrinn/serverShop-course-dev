-- Server Components

CREATE TABLE server_components (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL,
    component_id INT NOT NULL,
    FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE,
    FOREIGN KEY (component_id) REFERENCES components(id) ON DELETE CASCADE
);