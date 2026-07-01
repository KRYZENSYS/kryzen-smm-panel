<?php
// KRYZEN SMM — Replit uchun SQLite asosida
// MySQL ham ishlaydi, lekin Replit'da SQLite tayyor — DB haqida qayg'urmang
session_start();

define('DB_TYPE', 'sqlite'); // yoki 'mysql'
define('SQLITE_PATH', __DIR__ . '/kryzen.db');

if (DB_TYPE === 'mysql') {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_NAME', getenv('DB_NAME') ?: 'kryzen_smm');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') ?: '');
    define('DB_CHARSET', 'utf8mb4');
}

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    if (DB_TYPE === 'mysql') {
        $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } else {
        $pdo = new PDO('sqlite:' . SQLITE_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        // Birinchi marta — jadvallarni avtomatik yaratish
        $pdo->exec("
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
        ");
        // Settings ni to'ldirish
        $exists = $pdo->query("SELECT id FROM settings WHERE id=1")->fetchColumn();
        if (!$exists) {
            $pdo->exec("INSERT INTO settings (id, api_url, api_key) VALUES (1, 'https://bepulsmm.x404.uz/bot.php', '8631e7de09a0cff79c1b4b89a1589c1e')");
        }
    }
    return $pdo;
}

function get_settings(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    $row = db()->query('SELECT * FROM settings WHERE id=1')->fetch();
    if (!$row) {
        db()->exec("INSERT INTO settings (id, api_url, api_key) VALUES (1, 'https://bepulsmm.x404.uz/bot.php', '8631e7de09a0cff79c1b4b89a1589c1e')");
        $row = db()->query('SELECT * FROM settings WHERE id=1')->fetch();
    }
    $cache = $row; return $cache;
}

function require_login(): void {
    if (empty($_SESSION['user_id'])) { header('Location: auth.php?action=login'); exit; }
    $stmt = db()->prepare('SELECT * FROM users WHERE id=?');
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch();
    if (!$u || $u['status']==='banned') { session_destroy(); header('Location: auth.php?action=login'); exit; }
    $GLOBALS['current_user'] = $u;
}

function require_admin(): void {
    require_login();
    if (($GLOBALS['current_user']['role']??'')!=='admin') { header('Location: dashboard.php'); exit; }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
    return $_SESSION['csrf'];
}

function csrf_check(): void {
    if ($_SERVER['REQUEST_METHOD']==='POST') {
        if (!hash_equals($_SESSION['csrf']??'', $_POST['csrf']??'')) { die('CSRF token xatosi'); }
    }
}

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function flash_set(string $t, string $m): void { $_SESSION['flash'] = ['type'=>$t,'msg'=>$m]; }
function flash_get(): ?array {
    if (!empty($_SESSION['flash'])) { $f = $_SESSION['flash']; unset($_SESSION['flash']); return $f; }
    return null;
}
function fmt_money($n): string { return number_format((float)$n, 2, '.', ' '); }
