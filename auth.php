<?php
// KRYZEN SMM — Login & Register
require_once __DIR__ . '/config.php';
$action = $_GET['action'] ?? 'login';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check();
    if ($action==='register') {
        $username = trim($_POST['username']??'');
        $email    = trim($_POST['email']??'');
        $password = $_POST['password']??'';
        $confirm  = $_POST['confirm']??'';
        if ($username===''||$email===''||$password===''||$confirm==='') flash_set('danger',"Barcha maydonlarni to'ldiring");
        elseif ($password!==$confirm) flash_set('danger','Parollar mos kelmadi');
        elseif (strlen($password)<4) flash_set('danger',"Parol kamida 4 belgi bo'lishi kerak");
        else {
            $stmt = db()->prepare('SELECT id FROM users WHERE username=? OR email=?');
            $stmt->execute([$username,$email]);
            if ($stmt->fetch()) flash_set('danger','Bu username yoki email allaqachon mavjud');
            else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                db()->prepare('INSERT INTO users (username,email,password) VALUES (?,?,?)')->execute([$username,$email,$hash]);
                flash_set('success',"Ro'yxatdan o'tdingiz! Tizimga kiring.");
                header('Location: auth.php?action=login'); exit;
            }
        }
    } elseif ($action==='login') {
        $username = trim($_POST['username']??'');
        $password = $_POST['password']??'';
        if ($username===''||$password==='') flash_set('danger','Username va parolni kiriting');
        else {
            $stmt = db()->prepare('SELECT * FROM users WHERE username=? OR email=?');
            $stmt->execute([$username,$username]);
            $u = $stmt->fetch();
            if (!$u || !password_verify($password,$u['password'])) flash_set('danger',"Noto'g'ri username yoki parol");
            elseif ($u['status']==='banned') flash_set('danger','Hisobingiz bloklangan');
            else { $_SESSION['user_id'] = (int)$u['id']; header('Location: dashboard.php'); exit; }
        }
    }
    header('Location: auth.php?action='.urlencode($action)); exit;
}
$flash = flash_get();
?><!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KRYZEN SMM — <?= $action==="login"?"Kirish":"Ro'yxatdan o'tish" ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
*{font-family:'Inter',sans-serif}body{background:#0a0e1a}
.input-glow:focus{box-shadow:0 0 20px rgba(99,102,241,.25)}
</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-md">
<div class="text-center mb-8">
<h1 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-500">KRYZEN</h1>
<p class="text-gray-400 mt-1 text-sm">SMM Panel</p>
</div>
<div class="bg-[#111827] rounded-2xl p-8 border border-gray-800" style="box-shadow:0 0 30px rgba(99,102,241,.15)">
<div class="flex mb-6 bg-gray-900 rounded-xl p-1">
<a href="auth.php?action=login" class="flex-1 text-center py-2 rounded-lg text-sm font-medium transition <?= $action==="login"?"bg-indigo-600 text-white":"text-gray-400 hover:text-white" ?>">Kirish</a>
<a href="auth.php?action=register" class="flex-1 text-center py-2 rounded-lg text-sm font-medium transition <?= $action==="register"?"bg-indigo-600 text-white":"text-gray-400 hover:text-white" ?>">Ro'yxatdan o'tish</a>
</div>
<?php if ($flash): ?>
<div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium <?= $flash['type']==='success'?"bg-green-900/50 text-green-300 border border-green-700":"bg-red-900/50 text-red-300 border border-red-700" ?>"><?= e($flash['msg']) ?></div>
<?php endif; ?>
<form method="POST" class="space-y-4">
<input type="hidden" name="csrf" value="<?= csrf_token() ?>">
<?php if ($action==='register'): ?>
<div><label class="block text-gray-400 text-sm mb-1">Username</label><input type="text" name="username" required class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500 input-glow transition" placeholder="username"></div>
<div><label class="block text-gray-400 text-sm mb-1">Email</label><input type="email" name="email" required class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500 input-glow transition" placeholder="email@example.com"></div>
<?php else: ?>
<div><label class="block text-gray-400 text-sm mb-1">Username yoki Email</label><input type="text" name="username" required class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500 input-glow transition" placeholder="username yoki email"></div>
<?php endif; ?>
<div><label class="block text-gray-400 text-sm mb-1">Parol</label><input type="password" name="password" required class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500 input-glow transition" placeholder="••••••••"></div>
<?php if ($action==='register'): ?>
<div><label class="block text-gray-400 text-sm mb-1">Parolni takrorlang</label><input type="password" name="confirm" required class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500 input-glow transition" placeholder="••••••••"></div>
<?php endif; ?>
<button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold py-3 rounded-xl transition shadow-lg shadow-indigo-600/20"><?= $action==="login"?"Kirish":"Ro'yxatdan o'tish" ?></button>
</form>
</div>
<p class="text-gray-500 text-xs text-center mt-6">KRYZEN SMM Panel v1.0</p>
</div>
</body>
</html>
