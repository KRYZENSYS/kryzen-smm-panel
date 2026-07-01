<?php
// KRYZEN SMM — Replit uchun avtomatik admin yaratish
// Birinchi marta kirganingizda o'zi admin yaratadi
require_once __DIR__ . '/config.php';

$default_user = 'admin';
$default_pass = 'admin123';
$default_mail = 'admin@kryzen.uz';

// Avvalgi adminni tekshirish
$stmt = db()->prepare("SELECT id FROM users WHERE username=?");
$stmt->execute([$default_user]);
$exists = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD']==='POST' && !$exists) {
    $u = trim($_POST['username'] ?? $default_user);
    $e = trim($_POST['email'] ?? $default_mail);
    $p = $_POST['password'] ?? $default_pass;
    $hash = password_hash($p, PASSWORD_DEFAULT);
    try {
        db()->prepare("INSERT INTO users (username, email, password, role, status, balance) VALUES (?,?,?,'admin','active',100000.00)")
            ->execute([$u, $e, $hash]);
        $created = ['u'=>$u, 'p'=>$p, 'e'=>$e];
    } catch (Throwable $err) { $error = $err->getMessage(); }
} elseif ($exists) {
    // Admin mavjud — yangilash imkoniyati
    if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='reset') {
        $p = $_POST['new_password'] ?? '';
        if (strlen($p)>=4) {
            $hash = password_hash($p, PASSWORD_DEFAULT);
            db()->prepare("UPDATE users SET password=?, role='admin', status='active' WHERE username=?")
                ->execute([$hash, $default_user]);
            $reset = ['u'=>$default_user, 'p'=>$p];
        }
    }
}
?><!DOCTYPE html><html lang="uz"><head><meta charset="UTF-8"><title>KRYZEN — Replit Setup</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>*{font-family:Inter,sans-serif}body{background:#0a0e1a;color:#fff}</style>
</head><body class="min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-md bg-[#111827] border border-gray-800 rounded-2xl p-8">
<h1 class="text-3xl font-extrabold text-center mb-1 text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-500">KRYZEN</h1>
<p class="text-center text-gray-400 text-sm mb-6">🚀 Replit uchun tezkor sozlash</p>

<?php if (!empty($created)): ?>
<div class="mb-4 px-4 py-4 rounded-xl bg-green-900/40 text-green-200 border border-green-700 text-sm">
    <div class="font-bold text-base mb-2">✅ Admin yaratildi!</div>
    <div>👤 Login: <code class="bg-black/30 px-2 py-0.5 rounded"><?= htmlspecialchars($created['u']) ?></code></div>
    <div>🔑 Parol: <code class="bg-black/30 px-2 py-0.5 rounded"><?= htmlspecialchars($created['p']) ?></code></div>
    <div class="mt-2 text-xs text-green-300">💰 Demo balans: 100,000 so'm</div>
</div>
<a href="auth.php?action=login" class="block w-full text-center bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 text-white font-semibold py-3 rounded-xl">👉 Tizimga kirish</a>
<p class="text-xs text-gray-500 mt-3 text-center">⚠️ Xavfsizlik uchun install.php ni o'chiring yoki parolni o'zgartiring!</p>

<?php elseif (!empty($reset)): ?>
<div class="mb-4 px-4 py-4 rounded-xl bg-green-900/40 text-green-200 border border-green-700 text-sm">
    <div class="font-bold text-base mb-2">✅ Parol yangilandi!</div>
    <div>👤 Login: <code class="bg-black/30 px-2 py-0.5 rounded"><?= htmlspecialchars($reset['u']) ?></code></div>
    <div>🔑 Yangi parol: <code class="bg-black/30 px-2 py-0.5 rounded"><?= htmlspecialchars($reset['p']) ?></code></div>
</div>
<a href="auth.php?action=login" class="block w-full text-center bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 text-white font-semibold py-3 rounded-xl">👉 Tizimga kirish</a>

<?php elseif ($exists): ?>
<div class="mb-4 px-4 py-3 rounded-xl bg-yellow-900/40 text-yellow-200 border border-yellow-700 text-sm">
    ℹ️ Admin avval yaratilgan: <code class="bg-black/30 px-2 py-0.5 rounded"><?= htmlspecialchars($default_user) ?></code>
</div>
<details class="mb-4">
<summary class="cursor-pointer text-indigo-400 text-sm hover:underline">🔄 Parolni tiklash (reset)</summary>
<form method="POST" class="mt-3 space-y-3">
<input type="hidden" name="action" value="reset">
<input type="text" name="new_password" required minlength="4" placeholder="Yangi parol (kamida 4)" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500">
<button type="submit" class="w-full bg-yellow-700 hover:bg-yellow-600 text-white font-semibold py-2.5 rounded-xl text-sm">Parolni yangilash</button>
</form>
</details>
<a href="auth.php?action=login" class="block w-full text-center bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 text-white font-semibold py-3 rounded-xl">👉 Tizimga kirish</a>

<?php else: ?>
<form method="POST" class="space-y-3">
<input type="text" name="username" value="admin" required placeholder="Username" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:border-indigo-500">
<input type="email" name="email" value="admin@kryzen.uz" required placeholder="Email" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:border-indigo-500">
<input type="text" name="password" value="admin123" required minlength="4" placeholder="Parol (kamida 4)" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:border-indigo-500">
<button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 text-white font-semibold py-3 rounded-xl">🚀 Admin yaratish</button>
</form>
<p class="text-xs text-gray-500 mt-3 text-center">Default login: <b>admin</b> · parol: <b>admin123</b></p>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="mt-3 px-4 py-3 rounded-xl bg-red-900/40 text-red-200 border border-red-700 text-sm"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

</div></body></html>
