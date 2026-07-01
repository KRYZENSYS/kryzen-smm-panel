<?php
// KRYZEN SMM — Barcha xizmatlar sahifasi
require_once __DIR__ . '/config.php';
require_login();
$__page_title = 'Xizmatlar';
include __DIR__ . '/layout_top.php';
$services = json_decode(file_get_contents(__DIR__ . '/services.json'), true) ?: [];
?>
<div class="max-w-6xl mx-auto">
    <h2 class="text-2xl font-bold text-white mb-2">⚡ Barcha Xizmatlar</h2>
    <p class="text-gray-400 text-sm mb-6">Jami <?= count($services) ?> ta xizmat mavjud</p>
    <div class="card p-4 overflow-x-auto">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kategoriya</th>
                    <th>Xizmat nomi</th>
                    <th>Narx (1000)</th>
                    <th>Min</th>
                    <th>Max</th>
                    <th>Type</th>
                    <th>Refill</th>
                    <th>Cancel</th>
                    <th>Dripfeed</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $s): ?>
                <tr>
                    <td class="text-indigo-400 font-mono"><?= (int)$s['service'] ?></td>
                    <td class="text-xs"><?= e($s['category']) ?></td>
                    <td class="font-medium"><?= e($s['name']) ?></td>
                    <td class="text-green-400"><?= e((string)$s['rate']) ?> so'm</td>
                    <td><?= (int)$s['min'] ?></td>
                    <td><?= (int)$s['max'] ?></td>
                    <td class="text-xs text-gray-400"><?= e($s['type']) ?></td>
                    <td class="text-center text-lg"><?= !empty($s['refill']) ? '<span class="text-green-400">✓</span>' : '<span class="text-red-400">✗</span>' ?></td>
                    <td class="text-center text-lg"><?= !empty($s['cancel']) ? '<span class="text-green-400">✓</span>' : '<span class="text-red-400">✗</span>' ?></td>
                    <td class="text-center text-lg"><?= !empty($s['dripfeed']) ? '<span class="text-green-400">✓</span>' : '<span class="text-red-400">✗</span>' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/layout_bottom.php'; ?>
