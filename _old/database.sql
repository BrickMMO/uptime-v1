CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 

CREATE TABLE IF NOT EXISTS assets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    url VARCHAR(500) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_url (url)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS checks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL,
    response_time DECIMAL(10,3) NULL,
    status ENUM('up', 'down') NOT NULL,
    status_code INT NULL,
    error_message TEXT NULL,
    checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    INDEX idx_asset_date (asset_id, checked_at)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS page_errors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL,
    error_type VARCHAR(100) NOT NULL,
    error_message TEXT NOT NULL,
    occurred_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    INDEX idx_asset_date (asset_id, occurred_at)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS screenshots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NULL,
    taken_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    INDEX idx_asset_date (asset_id, taken_at)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS performance_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL,
    metric_type ENUM('load_time', 'dom_ready', 'first_paint', 'largest_contentful_paint') NOT NULL,
    value DECIMAL(10,3) NOT NULL,
    measured_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    INDEX idx_asset_metric_date (asset_id, metric_type, measured_at)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO admins (username, password, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@brickmmo.com')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO assets (name, url, description) VALUES 
('Account', 'https://account.brickmmo.com/', 'BrickMMO Account Management'),
('Applications', 'https://applications.brickmmo.com/', 'BrickMMO Applications Portal'),
('Bricksum', 'https://bricksum.brickmmo.com/', 'BrickMMO Bricksum Tool'),
('Colours', 'https://colours.brickmmo.com/', 'BrickMMO Colours Reference'),
('Conversions', 'https://conversions.brickmmo.com/', 'BrickMMO Conversions Tool'),
('Console', 'https://console.brickmmo.com/', 'BrickMMO Console Application'),
('Display', 'https://display.brickmmo.com/', 'BrickMMO Display System'),
('Demo', 'https://demo.brickmmo.com/', 'BrickMMO Demo Platform'),
('Events', 'https://events.brickmmo.com/', 'BrickMMO Events Calendar'),
('Flow', 'https://flow.brickmmo.com/', 'BrickMMO Flow Tool'),
('Glyphs', 'https://glyphs.brickmmo.com/', 'BrickMMO Glyphs Library'),
('List', 'https://list.brickmmo.com/', 'BrickMMO List Manager'),
('Maps', 'https://maps.brickmmo.com/', 'BrickMMO Maps Platform'),
('Media', 'https://media.brickmmo.com/', 'BrickMMO Media Library'),
('Panel', 'https://panel.brickmmo.com/', 'BrickMMO Admin Panel'),
('Parts', 'https://parts.brickmmo.com/', 'BrickMMO Parts Directory'),
('Pixelate', 'https://pixelate.brickmmo.com/', 'BrickMMO Pixelate Tool'),
('Placekit', 'https://placekit.brickmmo.com/', 'BrickMMO Placekit System'),
('QR', 'https://qr.brickmmo.com/', 'BrickMMO QR Code Generator'),
('Search', 'https://search.brickmmo.com/', 'BrickMMO Search Portal'),
('Stats', 'https://stats.brickmmo.com/', 'BrickMMO Statistics Dashboard'),
('Stores', 'https://stores.brickmmo.com/', 'BrickMMO Stores Directory'),
('Videokit', 'https://videokit.brickmmo.com/', 'BrickMMO Videokit Tool')
ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description);
