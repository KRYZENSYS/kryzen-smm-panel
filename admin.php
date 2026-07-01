<?php
// KRYZEN SMM — Admin Panel
require_once __DIR__ . '/config.php';
require_admin();
$u = $GLOBALS['current_user'];
$__page_title = 'Admin Panel';

// Admin POST amallar
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check();
    $act = $_POST['admin_action'] ?? '';
    try {
        if ($act === 'add_balance') {
            $userId = (int)($_POST['user_id'] ?? 0);
            $amount = (float)($_POST['amount'] ?? 0);
            if ($userId <= 0 || $amount == 0) throw new Exception('User va summa kerak');
            db()->prepare('UPDATE users SET balance = balance + ? WHERE id=?')->execute([$amount, $userId]);
            flash_set('success', "Balans yangilandi (±".number_format($amount,2).")");
        } elseif ($act === 'ban_user') {
            $userId = (int)($_POST['user_id'] ?? 0);
            db()->prepare("UPDATE users SET status='banned' WHERE id=?")->execute([$userId]);
            flash_set('success', 'Foydalanuvchi bloklandi');
        } elseif ($act === 'unban_user') {
            $userId = (int)($_POST['user_id'] ?? 0);
            db()->prepare("UPDATE users SET status='active' WHERE id=?")->execute([$userId]);
            flash_set('success', 'Foydalanuvchi blokdan chiqarildi');
        } elseif ($act === 'make_admin') {
            $userId = (int)($_POST['user_id'] ?? 0);
            db()->prepare("UPDATE users SET role='admin' WHERE id=?")->execute([$userId]);
            flash_set('success', 'Admin qilib tayinlandi');
        } elseif ($act === 'update_api') {
            $apiUrl = trim($_POST['api_url'] ?? '');
            $apiKey = trim($_POST['api_key'] ?? '');
            if ($apiUrl === '' || $apiKey === '') throw new Exception('API URL va key kerak');
            db()->prepare('UPDATE settings SET api_url=?, api_key=? WHERE id=1')->execute([$apiUrl, $apiKey]);
            get_settings(); // reset cache
            flash_set('success', 'API sozlamalari saqlandi');
        }
    } catch (Throwable $e) {
        flash_set('danger', $e->getMessage());
    }
    header('Location: admin.php'); exit;
}

$flash = flash_get();

// Statistika
$stats = [
    'users' => (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'orders' => (int)db()->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'pending' => (int)db()->query("SELECT COUNT(*) FROM orders WHERE status='Pending'")->fetchColumn(),
    'completed' => (int)db()->query("SELECT COUNT(*) FROM orders WHERE status='Completed'")->fetchColumn(),
    'canceled' => (int)db()->query("SELECT COUNT(*) FROM orders WHERE status='Canceled'")->fetchColumn(),
    'revenue' => (float)db()->query('SELECT COALESCE(SUM(quantity * 0), 0) FROM orders')->fetchColumn(),
];

// API balans
$apiBalance = null;
$ch = curl_init();
$s = get_settings();
$params = ['key'=>$s['api_key'], 'action'=>'balance'];
curl_setopt_array($ch, [
    CURLOPT_URL => $s['api_url'], CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($params),
    CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 10,
]);
$resp = curl_exec($ch); curl_close($ch);
$apiBalance = json_decode($resp, true);

// Foydalanuvchilar
$users = db()->query('SELECT * FROM users ORDER BY id DESC LIMIT 100')->fetchAll();

// Buyurtmalar
$orders = db()->query('SELECT o.*, u.username FROM orders o LEFT JOIN users u ON u.id=o.user_id ORDER BY o.id DESC LIMIT 100')->fetchAll();

include __DIR__ . '/layout_top.php';
?>

<div class="max-w-6xl mx-auto">
    <h2 class="text-2xl font-bold text-white mb-6">🛠 Admin Panel</h2>

    <?php if ($flash): ?>
    <div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium <?= $flash['type']==='success'?'bg-green-900/50 text-green-300 border border-green-700':'bg-red-900/50 text-red-300 border border-red-700' ?>"><?= e($flash['msg']) ?></div>
    <?php endif; ?>

    <!-- Statistika -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="card p-4"><div class="text-gray-400 text-xs">Foydalanuvchilar</div><div class="text-2xl font-bold text-white mt-1"><?= $stats['users'] ?></div></div>
        <div class="card p-4"><div class="text-gray-400 text-xs">Jami buyurtmalar</div><div class="text-2xl font-bold text-indigo-400 mt-1"><?= $stats['orders'] ?></div></div>
        <div class="card p-4"><div class="text-gray-400 text-xs">Pending</div><div class="text-2xl font-bold text-yellow-400 mt-1"><?= $stats['pending'] ?></div></div>
        <div class="card p-4"><div class="text-gray-400 text-xs">Completed</div><div class="text-2xl font-bold text-green-400 mt-1"><?= $stats['completed'] ?></div></div>
    </div>

    <!-- API sozlamalari -->
    <div class="card p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-3">🔌 API Sozlamalari</h3>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="admin_action" value="update_api">
            <input type="text" name="api_url" value="<?= e($s['api_url']) ?>" placeholder="API URL" class="input" required>
            <input type="text" name="api_key" value="<?= e($s['api_key']) ?>" placeholder="API key" class="input" required>
            <button type="submit" class="btn btn-primary">Saqlash</button>
        </form>
        <div class="mt-3 text-sm text-gray-400">API Balans: <span class="text-white font-medium"><?= is_array($apiBalance) ? json_encode($apiBalance, JSON_UNESCAPED_UNICODE) : '—' ?></span></div>
    </div>

    <!-- Foydalanuvchilar -->
    <div class="card p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-3">👥 Foydalanuvchilar</h3>
        <div class="overflow-x-auto">
            <table>
                <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Balans</th><th>Role</th><th>Status</th><th>Amal</th></tr></thead>
                <tbody>
                    <?php foreach ($users as $uu): ?>
                    <tr>
                        <td class="text-gray-400">#<?= $uu['id'] ?></td>
                        <td class="font-medium"><?= e($uu['username']) ?></td>
                        <td class="text-xs text-gray-400"><?= e($uu['email']) ?></td>
                        <td class="text-indigo-400"><?= fmt_money($uu['balance']) ?></td>
                        <td><span class="badge <?= $uu['role']==='admin'?'badge-completed':'badge-pending' ?>"><?= e($uu['role']) ?></span></td>
                        <td><span class="badge <?= $uu['status']==='active'?'badge-completed':'badge-canceled' ?>"><?= e($uu['status']) ?></span></td>
                        <td class="flex gap-1 flex-wrap">
                            <form method="POST" class="inline" onsubmit="return prompt('Summani kiriting (+ yoki -):') && true;">
                                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                                <input type="hidden" name="admin_action" value="add_balance">
                                <input type="hidden" name="user_id" value="<?= $uu['id'] ?>">
                                <input type="hidden" name="amount" value="">
                                <button type="button" onclick="let p=prompt('Summa (+ yoki -):'); if(p){this.previousElementSibling.value=p; this.closest('form').submit();}" class="btn btn-ghost text-xs py-1 px-2">💰</button>
                            </form>
                            <?php if ($uu['status']==='active'): ?>
                            <form method="POST" class="inline" onsubmit="return confirm('Bloklash?')">
                                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                                <input type="hidden" name="admin_action" value="ban_user">
                                <input type="hidden" name="user_id" value="<?= $uu['id'] ?>">
                                <button type="submit" class="btn btn-danger text-xs py-1 px-2">🚫</button>
                            </form>
                            <?php else: ?>
                            <form method="POST" class="inline">
                                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                                <input type="hidden" name="admin_action" value="unban_user">
                                <input type="hidden" name="user_id" value="<?= $uu['id'] ?>">
                                <button type="submit" class="btn btn-ghost text-xs py-1 px-2">✅</button>
                            </form>
                            <?php endif; ?>
                            <?php if ($uu['role']!=='admin'): ?>
                            <form method="POST" class="inline" onsubmit="return confirm('Admin qilish?')">
                                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                                <input type="hidden" name="admin_action" value="make_admin">
                                <input type="hidden" name="user_id" value="<?= $uu['id'] ?>">
                                <button type="submit" class="btn btn-ghost text-xs py-1 px-2">👑</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Barcha buyurtmalar -->
    <div class="card p-6">
        <h3 class="text-lg font-semibold text-white mb-3">📦 Barcha buyurtmalar</h3>
        <div class="overflow-x-auto">
            <table>
                <thead><tr><th>#</th><th>User</th><th>Xizmat</th><th>Link</th><th>Miqdor</th><th>Holat</th><th>Sana</th></tr></thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td class="text-gray-400">#<?= $o['id'] ?></td>
                        <td class="text-xs"><?= e($o['username']??'#'.$o['user_id']) ?></td>
                        <td class="max-w-[200px] truncate text-xs"><?= e($o['service_name']) ?></td>
                        <td class="max-w-[150px] truncate text-xs"><a href="<?= e($o['link']) ?>" target="_blank" class="text-indigo-400 hover:underline"><?= e($o['link']) ?></a></td>
                        <td><?= (int)$o['quantity'] ?></td>
                        <td><span class="badge badge-<?= strtolower($o['status']) ?>"><?= e($o['status']) ?></span></td>
                        <td class="text-gray-400 text-xs"><?= date('d.m H:i', strtotime($o['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout_bottom.php'; ?>
