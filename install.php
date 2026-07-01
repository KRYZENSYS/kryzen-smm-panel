<?php
// KRYZEN SMM — Birinchi admin yaratish (faqat bir marta ishlatiladi)
require_once __DIR__ . '/config.php';

if (!empty($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }

$msg = null; $err = null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $u = trim($_POST['username'] ?? '');
    $e = trim($_POST['email'] ?? '');
    $p = $_POST['password'] ?? '';
    $c = $_POST['confirm'] ?? '';
    if ($u===''||$e===''||$p===''||$c==='') $err = "Barcha maydonlarni to'ldiring";
    elseif ($p!==$c) $err = 'Parollar mos kelmadi';
    elseif (strlen($p)<6) $err = "Parol kamida 6 belgi";
    else {
        $stmt = db()->prepare('SELECT id FROM users WHERE username=? OR email=?');
        $stmt->execute([$u, $e]);
        if ($stmt->fetch()) $err = 'Bu username yoki email band';
        else {
            $hash = password_hash($p, PASSWORD_DEFAULT);
            db()->prepare("INSERT INTO users (username,email,password,role) VALUES (?,?,?,'admin')")->execute([$u, $e, $hash]);
            $msg = "✅ Admin yaratildi: $u. Endi tizimga kiring.";
        }
    }
}
?><!DOCTYPE html><html lang="uz"><head><meta charset="UTF-8"><title>KRYZEN — Admin yaratish</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>*{font-family:Inter,sans-serif}body{background:#0a0e1a;color:#fff}</style>
</head><body class="min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-md bg-[#111827] border border-gray-800 rounded-2xl p-8">
<h1 class="text-2xl font-bold text-center mb-2 text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-500">KRYZEN</h1>
<p class="text-center text-gray-400 text-sm mb-6">Birinchi admin yaratish</p>
<?php if ($msg): ?><div class="mb-4 px-4 py-3 rounded-xl bg-green-900/50 text-green-300 border border-green-700 text-sm"><?= htmlspecialchars($msg) ?></div>
<p class="text-center"><a href="auth.php?action=login" class="text-indigo-400 hover:underline">→ Tizimga kirish</a></p>
<?php elseif ($err): ?><div class="mb-4 px-4 py-3 rounded-xl bg-red-900/50 text-red-300 border border-red-700 text-sm"><?= htmlspecialchars($err) ?></div>
<?php endif; ?>
<?php if (!$msg): ?>
<form method="POST" class="space-y-4">
<input type="text" name="username" required placeholder="Username" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:border-indigo-500">
<input type="email" name="email" required placeholder="Email" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:border-indigo-500">
<input type="password" name="password" required placeholder="Parol (kamida 6)" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:border-indigo-500">
<input type="password" name="confirm" required placeholder="Parolni takrorlang" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:border-indigo-500">
<button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 text-white font-semibold py-3 rounded-xl">Admin yaratish</button>
</form>
<p class="text-xs text-gray-500 mt-4 text-center">⚠️ O'rnatishdan keyin install.php ni o'chirib tashlang!</p>
<?php endif; ?>
</div></body></html>
