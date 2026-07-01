-- KRYZEN SMM — SQLite database schema (Replit uchun)
-- Replit'da config.php avtomatik yaratadi, lekin qo'lda ham ishlatish mumkin
-- php: sqlite3 kryzen.db < database.sql

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    balance REAL NOT NULL DEFAULT 0,
    role TEXT NOT NULL DEFAULT 'user',
    status TEXT NOT NULL DEFAULT 'active',
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    service_name TEXT NOT NULL,
    link TEXT NOT NULL,
    quantity INTEGER NOT NULL,
    start_count INTEGER DEFAULT 0,
    remains INTEGER DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'Pending',
    api_order_id TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS settings (
    id INTEGER PRIMARY KEY,
    api_url TEXT NOT NULL,
    api_key TEXT NOT NULL
);

INSERT OR IGNORE INTO settings (id, api_url, api_key)
VALUES (1, 'https://bepulsmm.x404.uz/bot.php', '8631e7de09a0cff79c1b4b89a1589c1e');

CREATE INDEX IF NOT EXISTS idx_user_id ON orders(user_id);
CREATE INDEX IF NOT EXISTS idx_status ON orders(status);
