<?php
// KRYZEN SMM — API Handler (SQLite + MySQL bilan ishlaydi)
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function call_api(array $params): array {
    $s = get_settings();
    $params['key'] = $s['api_key'];
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $s['api_url'],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($params),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_TIMEOUT => 30,
    ]);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($err) return ['__error' => $err, '__http' => $code];
    $j = json_decode($resp, true);
    return $j === null ? ['__raw' => $resp, '__http' => $code] : $j;
}

$action = $_REQUEST['action'] ?? '';
$out = ['ok' => false, 'action' => $action];

try {
    switch ($action) {
        case 'balance': {
            $r = call_api(['action' => 'balance']);
            $out['ok'] = true; $out['data'] = $r; break;
        }
        case 'services': {
            $r = call_api(['action' => 'services']);
            if (!is_array($r) || isset($r['__error']) || isset($r['__raw'])) {
                $local = json_decode(file_get_contents(__DIR__ . '/services.json'), true);
                $out['ok'] = true; $out['data'] = $local ?: [];
            } else {
                $out['ok'] = true; $out['data'] = $r;
            }
            break;
        }
        case 'add': {
            csrf_check();
            require_login();
            $u = $GLOBALS['current_user'];
            $service = (int)($_POST['service'] ?? 0);
            $link = trim($_POST['link'] ?? '');
            $quantity = (int)($_POST['quantity'] ?? 0);
            if ($service <= 0 || $link === '' || $quantity <= 0) throw new Exception("Barcha maydonlar to'ldirilishi shart");
            $services = json_decode(file_get_contents(__DIR__ . '/services.json'), true) ?: [];
            $svc = null;
            foreach ($services as $s) { if ((int)$s['service'] === $service) { $svc = $s; break; } }
            if (!$svc) throw new Exception('Xizmat topilmadi');
            if ($quantity < (int)$svc['min'] || $quantity > (int)$svc['max']) {
                throw new Exception('Miqdor ['.$svc['min'].'..'.$svc['max'].'] oraliqda bo‘lishi kerak');
            }
            $cost = ((float)$svc['rate']) * $quantity / 1000.0;
            if ((float)$u['balance'] < $cost) throw new Exception('Balans yetarli emas');
            $r = call_api(['action'=>'add','service'=>$service,'link'=>$link,'quantity'=>$quantity]);
            if (isset($r['__error'])) throw new Exception('API: ' . $r['__error']);
            $apiOrderId = null;
            if (is_array($r) && isset($r['order'])) $apiOrderId = (string)$r['order'];
            elseif (is_array($r) && isset($r['order_id'])) $apiOrderId = (string)$r['order_id'];
            db()->beginTransaction();
            try {
                $stmt = db()->prepare("INSERT INTO orders (user_id, service_id, service_name, link, quantity, start_count, remains, status, api_order_id) VALUES (?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$u['id'], $service, $svc['name'], $link, $quantity, 0, $quantity, 'Pending', $apiOrderId]);
                $newOrderId = (int)db()->lastInsertId();
                if ($cost > 0) { db()->prepare('UPDATE users SET balance = balance - ? WHERE id=?')->execute([$cost, $u['id']]); }
                db()->commit();
            } catch (Throwable $ee) { db()->rollBack(); throw $ee; }
            $out['ok'] = true;
            $out['data'] = ['order_id'=>$newOrderId, 'api_order_id'=>$apiOrderId, 'cost'=>$cost, 'api_response'=>$r];
            break;
        }
        case 'status': {
            csrf_check();
            require_login();
            $u = $GLOBALS['current_user'];
            $orderId = (int)($_POST['order_id'] ?? 0);
            $stmt = db()->prepare('SELECT * FROM orders WHERE id=? AND user_id=?');
            $stmt->execute([$orderId, $u['id']]);
            $o = $stmt->fetch();
            if (!$o) throw new Exception('Buyurtma topilmadi');
            $r = [];
            if (!empty($o['api_order_id'])) {
                $r = call_api(['action'=>'status','order'=>$o['api_order_id']]);
            }
            if (is_array($r) && isset($r['status'])) {
                $newStatus = (string)$r['status'];
                $remains = isset($r['remains']) ? (int)$r['remains'] : (int)$o['remains'];
                $start = isset($r['start_count']) ? (int)$r['start_count'] : (int)$o['start_count'];
                db()->prepare('UPDATE orders SET status=?, remains=?, start_count=? WHERE id=?')->execute([$newStatus, $remains, $start, $o['id']]);
                $o['status'] = $newStatus; $o['remains'] = $remains; $o['start_count'] = $start;
            }
            $out['ok'] = true; $out['data'] = ['order'=>$o, 'api'=>$r];
            break;
        }
        case 'orders': {
            require_login();
            $u = $GLOBALS['current_user'];
            $stmt = db()->prepare('SELECT * FROM orders WHERE user_id=? ORDER BY id DESC LIMIT 200');
            $stmt->execute([$u['id']]);
            $out['ok'] = true; $out['data'] = $stmt->fetchAll();
            break;
        }
        case 'cancel': {
            csrf_check();
            require_login();
            $u = $GLOBALS['current_user'];
            $orderId = (int)($_POST['order_id'] ?? 0);
            $stmt = db()->prepare('SELECT * FROM orders WHERE id=? AND user_id=?');
            $stmt->execute([$orderId, $u['id']]);
            $o = $stmt->fetch();
            if (!$o) throw new Exception('Buyurtma topilmadi');
            $svc = null;
            $services = json_decode(file_get_contents(__DIR__ . '/services.json'), true) ?: [];
            foreach ($services as $s) { if ((int)$s['service'] === (int)$o['service_id']) { $svc = $s; break; } }
            if ($svc && empty($svc['cancel'])) throw new Exception('Bu xizmat uchun bekor qilish ruxsat berilmagan');
            if (empty($o['api_order_id'])) {
                db()->prepare("UPDATE orders SET status='Canceled' WHERE id=?")->execute([$o['id']]);
                $out['ok'] = true; $out['data'] = ['local'=>true];
                break;
            }
            $r = call_api(['action'=>'cancel','order'=>$o['api_order_id']]);
            db()->prepare("UPDATE orders SET status='Canceled' WHERE id=?")->execute([$o['id']]);
            $out['ok'] = true; $out['data'] = $r;
            break;
        }
        case 'refill': {
            csrf_check();
            require_login();
            $u = $GLOBALS['current_user'];
            $orderId = (int)($_POST['order_id'] ?? 0);
            $stmt = db()->prepare('SELECT * FROM orders WHERE id=? AND user_id=?');
            $stmt->execute([$orderId, $u['id']]);
            $o = $stmt->fetch();
            if (!$o) throw new Exception('Buyurtma topilmadi');
            $svc = null;
            $services = json_decode(file_get_contents(__DIR__ . '/services.json'), true) ?: [];
            foreach ($services as $s) { if ((int)$s['service'] === (int)$o['service_id']) { $svc = $s; break; } }
            if ($svc && empty($svc['refill'])) throw new Exception('Bu xizmat uchun refill ruxsat berilmagan');
            if (empty($o['api_order_id'])) throw new Exception("API order id yo‘q");
            $r = call_api(['action'=>'refill','order'=>$o['api_order_id']]);
            $out['ok'] = true; $out['data'] = $r;
            break;
        }
        default:
            throw new Exception("Noma'lum action: " . $action);
    }
} catch (Throwable $e) {
    http_response_code(400);
    $out['ok'] = false;
    $out['error'] = $e->getMessage();
}

echo json_encode($out, JSON_UNESCAPED_UNICODE);
