<?php
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'toggle_currency') {
    $currency = $_POST['currency'] === 'USD' ? 'USD' : 'IQD';
    $stmt = $pdo->prepare("UPDATE settings SET active_currency = ? WHERE id = 1");
    $stmt->execute([$currency]);
    echo json_encode(['status' => 'success', 'currency' => $currency]);
    exit;
}

if ($action === 'update_rate') {
    // ئەگەر بەکارهێنەر نرخی 100 دۆلاری نووسی (بۆ نموونە 150000) دەیکەینە نرخی 1 دۆلار (1500)
    $rate_input = floatval($_POST['rate']);
    if ($rate_input > 10000) {
        $usd_rate = $rate_input / 100;
    } else {
        $usd_rate = $rate_input;
    }

    if ($usd_rate > 0) {
        $stmt = $pdo->prepare("UPDATE settings SET usd_rate = ? WHERE id = 1");
        $stmt->execute([$usd_rate]);
        echo json_encode(['status' => 'success', 'usd_rate' => $usd_rate]);
        exit;
    }
}

if ($action === 'update_settings') {
    $shop_name = trim($_POST['shop_name']);
    $rate_input = floatval($_POST['usd_rate']);
    $usd_rate = ($rate_input > 10000) ? ($rate_input / 100) : $rate_input;

    $stmt = $pdo->prepare("UPDATE settings SET shop_name = ?, usd_rate = ? WHERE id = 1");
    $stmt->execute([$shop_name, $usd_rate]);
    echo json_encode(['status' => 'success', 'shop_name' => $shop_name, 'usd_rate' => $usd_rate]);
    exit;
}

echo json_encode(['status' => 'error']);