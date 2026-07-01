-- KRYZEN SMM Panel — Database Schema
CREATE DATABASE IF NOT EXISTS kryzen_smm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kryzen_smm;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL UNIQUE,
    email VARCHAR(128) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    status ENUM('active','banned') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    service_name VARCHAR(255) NOT NULL,
    link VARCHAR(512) NOT NULL,
    quantity INT NOT NULL,
    start_count INT DEFAULT 0,
    remains INT DEFAULT 0,
    status VARCHAR(32) NOT NULL DEFAULT 'Pending',
    api_order_id VARCHAR(64) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    api_url VARCHAR(255) NOT NULL,
    api_key VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (id, api_url, api_key) VALUES (1, 'https://bepulsmm.x404.uz/bot.php', '8631e7de09a0cff79c1b4b89a1589c1e')
ON DUPLICATE KEY UPDATE api_url=VALUES(api_url), api_key=VALUES(api_key);
