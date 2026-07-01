<?php
// KRYZEN SMM — Dashboard (Replit uchun SQLite mos)
require_once __DIR__ . '/config.php';
require_login();
$u = $GLOBALS['current_user'];
$__page_title = 'Dashboard';
include __DIR__ . '/layout_top.php';

$stmt = db()->prepare('SELECT * FROM orders WHERE user_id=? ORDER BY id DESC LIMIT 50');
$stmt->execute([$u['id']]);
$orders = $stmt->fetchAll();

$services = json_decode(file_get_contents(__DIR__ . '/services.json'), true) ?: [];
$categories = [];
foreach ($services as $s) {
    $cat = $s['category'] ?? 'Boshqa';
    if (!isset($categories[$cat])) $categories[$cat] = [];
    $categories[$cat][] = $s;
}
?>
<div class="max-w-6xl mx-auto">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <h2 class="text-2xl font-bold text-white">Dashboard</h2>
        <div class="flex items-center gap-3 mt-3 md:mt-0">
            <span class="text-gray-400 text-sm">Balans:</span>
            <span class="text-xl font-bold text-indigo-400" id="balanceText"><?= fmt_money($u['balance']) ?> so‘m</span>
            <button onclick="refreshBalance()" class="btn btn-ghost text-xs">🔄 Yangilash</button>
        </div>
    </div>

    <div class="card p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">📦 Yangi Buyurtma</h3>
        <form id="orderForm" class="space-y-4">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-400 text-sm mb-1">Kategoriya</label>
                    <select id="catSelect" class="input" onchange="filterServices()">
                        <option value="">— Kategoriya tanlang —</option>
                        <?php foreach (array_keys($categories) as $cat): ?>
                        <option value="<?= e($cat) ?>"><?= e($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-400 text-sm mb-1">Xizmat</label>
                    <select name="service" id="svcSelect" class="input" onchange="showServiceInfo()" required>
                        <option value="">— Avval kategoriya tanlang —</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-400 text-sm mb-1">Havola (link)</label>
                    <input type="text" name="link" id="linkInput" class="input" placeholder="https://t.me/..." required>
                </div>
                <div>
                    <label class="block text-gray-400 text-sm mb-1">Miqdor</label>
                    <input type="number" name="quantity" id="qtyInput" class="input" placeholder="10" min="1" required>
                </div>
            </div>
            <div id="svcInfo" class="hidden bg-gray-900 rounded-xl p-4 text-sm space-y-1 border border-gray-700">
                <div class="flex gap-4 flex-wrap">
                    <span><span class="text-gray-400">Min:</span> <span id="svcMin" class="text-white font-medium">—</span></span>
                    <span><span class="text-gray-400">Max:</span> <span id="svcMax" class="text-white font-medium">—</span></span>
                    <span><span class="text-gray-400">Type:</span> <span id="svcType" class="text-white font-medium">—</span></span>
                    <span><span class="text-gray-400">Narx:</span> <span id="svcRate" class="text-white font-medium">—</span></span>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Buyurtma berish</button>
        </form>
    </div>

    <div class="card p-6">
        <h3 class="text-lg font-semibold text-white mb-4">📋 Buyurtmalar Tarixi</h3>
        <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr><th>#</th><th>Xizmat</th><th>Link</th><th>Miqdor</th><th>Qoldi</th><th>Holat</th><th>Sana</th><th>Amal</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td class="text-gray-400">#<?= $o['id'] ?></td>
                        <td class="max-w-[150px] truncate"><?= e($o['service_name']) ?></td>
                        <td class="max-w-[150px] truncate"><a href="<?= e($o['link']) ?>" target="_blank" class="text-indigo-400 hover:underline"><?= e($o['link']) ?></a></td>
                        <td><?= (int)$o['quantity'] ?></td>
                        <td><?= (int)$o['remains'] ?></td>
                        <td><span class="badge badge-<?= strtolower($o['status']) ?>"><?= e($o['status']) ?></span></td>
                        <td class="text-gray-400 text-xs"><?= htmlspecialchars($o['created_at']) ?></td>
                        <td class="flex gap-1">
                            <?php
                            $svc = null;
                            foreach ($services as $s) { if ((int)$s['service'] === (int)$o['service_id']) { $svc = $s; break; } }
                            if ($svc && !empty($svc['cancel']) && $o['status']!=='Canceled' && $o['status']!=='Completed'): ?>
                            <button onclick="cancelOrder(<?= $o['id'] ?>)" class="btn btn-danger text-xs py-1 px-2">✕</button>
                            <?php endif; ?>
                            <?php if ($svc && !empty($svc['refill']) && $o['status']==='Completed'): ?>
                            <button onclick="refillOrder(<?= $o['id'] ?>)" class="btn btn-ghost text-xs py-1 px-2">🔄</button>
                            <?php endif; ?>
                            <button onclick="checkStatus(<?= $o['id'] ?>)" class="btn btn-ghost text-xs py-1 px-2">👁</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($orders)): ?>
                    <tr><td colspan="8" class="text-center text-gray-500 py-6">Hali buyurtmalar yo‘q</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const services = <?= json_encode($services, JSON_UNESCAPED_UNICODE) ?>;

function filterServices() {
    const cat = document.getElementById('catSelect').value;
    const sel = document.getElementById('svcSelect');
    sel.innerHTML = '<option value="">— Xizmat tanlang —</option>';
    document.getElementById('svcInfo').classList.add('hidden');
    if (!cat) return;
    services.filter(s => s.category === cat).forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.service;
        opt.textContent = s.name + ' (' + s.rate + ' so‘m/1000)';
        sel.appendChild(opt);
    });
}

function showServiceInfo() {
    const id = parseInt(document.getElementById('svcSelect').value);
    const info = document.getElementById('svcInfo');
    if (!id) { info.classList.add('hidden'); return; }
    const s = services.find(x => x.service === id);
    if (!s) { info.classList.add('hidden'); return; }
    document.getElementById('svcMin').textContent = s.min;
    document.getElementById('svcMax').textContent = s.max;
    document.getElementById('svcType').textContent = s.type;
    document.getElementById('svcRate').textContent = s.rate + ' so‘m/1000';
    document.getElementById('qtyInput').min = s.min;
    document.getElementById('qtyInput').max = s.max;
    info.classList.remove('hidden');
}

document.getElementById('orderForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector("button[type=submit]");
    btn.disabled = true; btn.textContent = '⏳ Yuborilmoqda...';
    const fd = new FormData(this);
    fd.append('action', 'add');
    try {
        const r = await fetch('api_handler.php', { method:'POST', body: new URLSearchParams(fd), credentials:'same-origin' });
        const j = await r.json();
        if (j.ok) { toast('✅ Buyurtma berildi! #' + j.data.order_id, 'success'); setTimeout(() => location.reload(), 1200); }
        else { toast('❌ ' + (j.error || 'Xatolik'), 'error'); btn.disabled = false; btn.textContent = 'Buyurtma berish'; }
    } catch(e) { toast('❌ Tarmoq xatosi', 'error'); btn.disabled = false; btn.textContent = 'Buyurtma berish'; }
});

async function cancelOrder(id) {
    if (!confirm('Buyurtmani bekor qilishni tasdiqlaysizmi?')) return;
    const r = await api('cancel', { order_id: id, csrf: '<?= csrf_token() ?>' });
    if (r.ok) { toast('✅ Bekor qilindi', 'success'); setTimeout(() => location.reload(), 1000); }
    else toast('❌ ' + (r.error || 'Xatolik'), 'error');
}

async function refillOrder(id) {
    if (!confirm('Refill so‘rovini yuborishni tasdiqlaysizmi?')) return;
    const r = await api('refill', { order_id: id, csrf: '<?= csrf_token() ?>' });
    if (r.ok) toast('✅ Refill so‘rovi yuborildi', 'success');
    else toast('❌ ' + (r.error || 'Xatolik'), 'error');
}

async function checkStatus(id) {
    const r = await api('status', { order_id: id, csrf: '<?= csrf_token() ?>' });
    if (r.ok) {
        const o = r.data.order;
        toast('📊 #' + o.id + ' — ' + o.status + ' (qoldi: ' + o.remains + ')', 'info');
        setTimeout(() => location.reload(), 1500);
    } else toast('❌ ' + (r.error || 'Xatolik'), 'error');
}

async function refreshBalance() {
    const r = await api('balance');
    if (r.ok) { toast('💰 Balans yangilandi', 'success'); setTimeout(() => location.reload(), 1000); }
    else toast('❌ ' + (r.error || 'Xatolik'), 'error');
}
</script>

<?php include __DIR__ . '/layout_bottom.php'; ?>
