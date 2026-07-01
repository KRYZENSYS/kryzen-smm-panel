<?php
// KRYZEN SMM — Umumiy layout (header + sidebar)
require_once __DIR__ . '/config.php';
$__page_title = $__page_title ?? 'KRYZEN SMM';
$__current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($__page_title) ?> — KRYZEN SMM</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
*{font-family:'Inter',sans-serif}body{background:#0a0e1a;color:#e5e7eb}
.card{background:#111827;border:1px solid #1f2937;border-radius:1rem}
.glow{box-shadow:0 0 30px rgba(99,102,241,.10)}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;padding:.55rem 1rem;border-radius:.75rem;font-weight:500;transition:all .15s;font-size:.875rem;cursor:pointer;border:0}
.btn-primary{background:linear-gradient(90deg,#4f46e5,#7c3aed);color:#fff}.btn-primary:hover{filter:brightness(1.15)}
.btn-danger{background:rgba(220,38,38,.15);color:#fca5a5;border:1px solid rgba(220,38,38,.3)}.btn-danger:hover{background:rgba(220,38,38,.3)}
.btn-ghost{background:#1f2937;color:#e5e7eb}.btn-ghost:hover{background:#374151}
.input,select,textarea{background:#0a0f1c;border:1px solid #1f2937;color:#fff;border-radius:.75rem;padding:.65rem .9rem;width:100%}
.input:focus,select:focus,textarea:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.2)}
.badge{display:inline-block;padding:.15rem .55rem;border-radius:.5rem;font-size:.72rem;font-weight:600}
.badge-pending{background:rgba(234,179,8,.15);color:#facc15}.badge-progress{background:rgba(59,130,246,.15);color:#93c5fd}
.badge-completed{background:rgba(34,197,94,.15);color:#86efac}.badge-canceled{background:rgba(239,68,68,.15);color:#fca5a5}
.badge-refunded{background:rgba(168,85,247,.15);color:#d8b4fe}
.sidebar-link{display:flex;align-items:center;gap:.7rem;padding:.7rem 1rem;border-radius:.75rem;color:#9ca3af;font-size:.9rem;transition:all .15s;text-decoration:none}
.sidebar-link:hover{background:#1f2937;color:#fff}
.sidebar-link.active{background:linear-gradient(90deg,#4f46e5,#7c3aed);color:#fff;box-shadow:0 6px 20px rgba(99,102,241,.25)}
table{border-collapse:separate;border-spacing:0;width:100%}
th,td{text-align:left;padding:.75rem 1rem;font-size:.875rem}
th{color:#9ca3af;font-weight:600;border-bottom:1px solid #1f2937}tr td{border-bottom:1px solid #1f2937}
tr:hover td{background:#0f1729}
</style>
</head>
<body class="min-h-screen">
<div class="flex">
<aside class="hidden md:flex md:flex-col w-64 min-h-screen bg-[#0a0f1c] border-r border-gray-800 p-4 fixed">
<div class="px-2 py-3 mb-4">
<h1 class="text-2xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-500">KRYZEN</h1>
<p class="text-xs text-gray-500 mt-0.5">SMM Panel v1.0</p>
</div>
<nav class="space-y-1 flex-1">
<a href="dashboard.php" class="sidebar-link <?= $__current==='dashboard.php'?'active':'' ?>">📊 Dashboard</a>
<a href="services.php" class="sidebar-link <?= $__current==='services.php'?'active':'' ?>">⚡ Xizmatlar</a>
<?php if (($GLOBALS['current_user']['role']??'')==='admin'): ?>
<a href="admin.php" class="sidebar-link <?= $__current==='admin.php'?'active':'' ?>">🛠 Admin Panel</a>
<?php endif; ?>
</nav>
<div class="border-t border-gray-800 pt-3 mt-3">
<div class="px-2 py-2 text-xs text-gray-400">
<div class="font-semibold text-white"><?= e($GLOBALS['current_user']['username']??'') ?></div>
<div><?= e($GLOBALS['current_user']['email']??'') ?></div>
<div class="mt-1 text-indigo-400">Balans: <?= fmt_money($GLOBALS['current_user']['balance']??0) ?> so'm</div>
</div>
<a href="logout.php" class="sidebar-link mt-2 text-red-300">🚪 Chiqish</a>
</div>
</aside>
<main class="flex-1 min-h-screen md:ml-64">
<header class="md:hidden bg-[#0a0f1c] border-b border-gray-800 p-4 flex items-center justify-between">
<h1 class="text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-500">KRYZEN</h1>
<a href="logout.php" class="text-xs text-red-300">Chiqish</a>
</header>
<div class="p-5 md:p-8">
