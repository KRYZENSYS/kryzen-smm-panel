<?php
// KRYZEN SMM — Asosiy konfiguratsiya va yordamchi funksiyalar
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'kryzen_smm');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) { die('DB error: '.htmlspecialchars($e->getMessage())); }
    }
    return $pdo;
}

function get_settings(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    $stmt = db()->query('SELECT * FROM settings WHERE id=1');
    $row = $stmt ? $stmt->fetch() : null;
    if (!$row) {
        db()->exec("INSERT INTO settings (id, api_url, api_key) VALUES (1, 'https://bepulsmm.x404.uz/bot.php', '8631e7de09a0cff79c1b4b89a1589c1e')");
        $row = ['id'=>1,'api_url'=>'https://bepulsmm.x404.uz/bot.php','api_key'=>'8631e7de09a0cff79c1b4b89a1589c1e'];
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
