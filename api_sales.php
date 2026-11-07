<?php
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// دڵنیابوونەوە لە هەبوونی ستوونەکانی زەمان و دیاری لە خشتەی sale_items
try {

$cCols = $pdo->query("SHOW COLUMNS FROM customers")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('address', $cCols)) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN address VARCHAR(250) DEFAULT '' AFTER phone");
    }
    if (!in_array('guarantor_phone', $cCols)) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN guarantor_phone VARCHAR(50) DEFAULT '' AFTER guarantor");
    }

    $cols = $pdo->query("SHOW COLUMNS FROM sale_items")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('serial_number', $cols)) {
        $pdo->exec("ALTER TABLE sale_items ADD COLUMN serial_number VARCHAR(150) DEFAULT '' AFTER item_name");
    }
    if (!in_array('warranty', $cols)) {
        $pdo->exec("ALTER TABLE sale_items ADD COLUMN warranty VARCHAR(100) DEFAULT '' AFTER serial_number");
    }
    if (!in_array('is_gift', $cols)) {
        $pdo->exec("ALTER TABLE sale_items ADD COLUMN is_gift TINYINT(1) NOT NULL DEFAULT 0 AFTER is_external");
    }
} catch (Exception $e) {
    // تێپەڕاندن
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// ١. گەڕان بەدوای کاڵا بە بارکۆد یان ناو
if ($action === 'search_products') {
    $q = trim($_GET['q'] ?? '');
    $category = trim($_GET['category'] ?? 'all');
    $brand = trim($_GET['brand'] ?? 'all');
    $exact = isset($_GET['exact']) && $_GET['exact'] === '1';

    if ($exact) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE barcode = ? LIMIT 1");
        $stmt->execute([$q]);
    } else {
        $where = ["1=1"];
        $params = [];
        if ($q !== '') {
            $where[] = "(barcode LIKE ? OR name LIKE ?)";
            $params[] = "%$q%";
            $params[] = "%$q%";
        }
        if ($category !== '' && $category !== 'all') {
            $where[] = "category = ?";
            $params[] = $category;
        }
        if ($brand !== '' && $brand !== 'all') {
            $where[] = "brand = ?";
            $params[] = $brand;
        }
        $whereSql = implode(" AND ", $where);
        $stmt = $pdo->prepare("SELECT * FROM products WHERE $whereSql LIMIT 25");
        $stmt->execute($params);
    }
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}

// ٢. گەڕان بەدوای کڕیار بە (ناو، ژمارە تەلەفۆن، کەفیل)
if ($action === 'search_customers') {
    $q = trim($_GET['q'] ?? '');
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE name LIKE ? OR phone LIKE ? OR guarantor LIKE ? ORDER BY id DESC LIMIT 15");
    $stmt->execute(["%$q%", "%$q%", "%$q%"]);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}

// ٣. هێنانی وەسڵ بە ژمارە یان گەڕانەوە بۆ پێشتر و دواتر
if ($action === 'get_receipt') {
    $receipt_id = intval($_GET['id'] ?? 0);
    $direction = $_GET['dir'] ?? '';

    if ($direction === 'prev') {
        if ($receipt_id <= 0) {
            $stmt = $pdo->query("SELECT * FROM sales ORDER BY id DESC LIMIT 1");
        } else {
            $stmt = $pdo->prepare("SELECT * FROM sales WHERE id < ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$receipt_id]);
        }
    } elseif ($direction === 'next') {
        $stmt = $pdo->prepare("SELECT * FROM sales WHERE id > ? ORDER BY id ASC LIMIT 1");
        $stmt->execute([$receipt_id]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM sales WHERE id = ? LIMIT 1");
        $stmt->execute([$receipt_id]);
    }

    $sale = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$sale) {
        echo json_encode(['status' => 'not_found', 'message' => 'هیچ وەسڵێک نەدۆزرایەوە']);
        exit;
    }

    $stmtItems = $pdo->prepare("
        SELECT si.*, COALESCE(p.stock, 0) AS stock 
        FROM sale_items si 
        LEFT JOIN products p ON si.product_id = p.id 
        WHERE si.sale_id = ?
    ");
    $stmtItems->execute([$sale['id']]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'sale' => $sale, 'items' => $items]);
    exit;
}

// ٤. خەزنکردن یان دەستکاریکردنی وەسڵی فرۆشتن
if ($action === 'save_sale') {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!$payload || empty($payload['items'])) {
        echo json_encode(['status' => 'error', 'message' => 'تکایە سەرەتا کاڵا زیاد بکە بۆ ناو وەسڵ']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $sale_id        = intval($payload['sale_id'] ?? 0);
        $customer_id    = !empty($payload['customer_id']) ? intval($payload['customer_id']) : null;
        $customer_name  = trim($payload['customer_name'] ?: 'کڕیاری گشتی');
        $customer_phone = trim($payload['customer_phone'] ?? '');
        $guarantor_name = trim($payload['guarantor_name'] ?? '');
        $sale_type      = in_array($payload['sale_type'], ['cash', 'debt', 'installment', 'quote']) ? $payload['sale_type'] : 'cash';
        $subtotal_iqd   = floatval($payload['subtotal_iqd'] ?? 0);
        $discount_iqd   = floatval($payload['discount_iqd'] ?? 0);
        $total_iqd      = floatval($payload['total_iqd'] ?? 0);
        $paid_iqd       = floatval($payload['paid_iqd'] ?? 0);
        $change_iqd     = floatval($payload['change_iqd'] ?? 0);
        $remaining_iqd  = floatval($payload['remaining_iqd'] ?? 0);
        $usd_rate       = floatval($payload['usd_rate'] ?? 1500);

        if (!$customer_id && $customer_name !== 'کڕیاری گشتی') {
            $checkCust = $pdo->prepare("SELECT id FROM customers WHERE name = ? LIMIT 1");
            $checkCust->execute([$customer_name]);
            $foundCust = $checkCust->fetch(PDO::FETCH_ASSOC);
            if ($foundCust) {
                $customer_id = $foundCust['id'];
            } else {
                $insCust = $pdo->prepare("INSERT INTO customers (name, phone, guarantor) VALUES (?, ?, ?)");
                $insCust->execute([$customer_name, $customer_phone, $guarantor_name]);
                $customer_id = $pdo->lastInsertId();
            }
        }

        if ($sale_id > 0) {
            $oldSaleStmt = $pdo->prepare("SELECT sale_type FROM sales WHERE id = ?");
            $oldSaleStmt->execute([$sale_id]);
            $oldSale = $oldSaleStmt->fetch(PDO::FETCH_ASSOC);

            if ($oldSale && $oldSale['sale_type'] !== 'quote') {
                $oldItemsStmt = $pdo->prepare("SELECT product_id, qty FROM sale_items WHERE sale_id = ? AND is_external = 0 AND product_id IS NOT NULL");
                $oldItemsStmt->execute([$sale_id]);
                foreach ($oldItemsStmt->fetchAll(PDO::FETCH_ASSOC) as $oi) {
                    $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?")->execute([$oi['qty'], $oi['product_id']]);
                }
            }

            $upd = $pdo->prepare("UPDATE sales SET customer_id=?, customer_name=?, customer_phone=?, guarantor_name=?, sale_type=?, subtotal_iqd=?, discount_iqd=?, total_iqd=?, paid_iqd=?, change_iqd=?, remaining_iqd=?, usd_rate=? WHERE id=?");
            $upd->execute([$customer_id, $customer_name, $customer_phone, $guarantor_name, $sale_type, $subtotal_iqd, $discount_iqd, $total_iqd, $paid_iqd, $change_iqd, $remaining_iqd, $usd_rate, $sale_id]);

            $pdo->prepare("DELETE FROM sale_items WHERE sale_id = ?")->execute([$sale_id]);
        } else {
            $ins = $pdo->prepare("INSERT INTO sales (customer_id, customer_name, customer_phone, guarantor_name, sale_type, subtotal_iqd, discount_iqd, total_iqd, paid_iqd, change_iqd, remaining_iqd, usd_rate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->execute([$customer_id, $customer_name, $customer_phone, $guarantor_name, $sale_type, $subtotal_iqd, $discount_iqd, $total_iqd, $paid_iqd, $change_iqd, $remaining_iqd, $usd_rate]);
            $sale_id = $pdo->lastInsertId();
        }

        $insItem = $pdo->prepare("
            INSERT INTO sale_items 
            (sale_id, product_id, barcode, item_name, serial_number, warranty, is_external, is_gift, qty, buy_price_iqd, sell_price_iqd, total_iqd) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($payload['items'] as $item) {
            $pid = !empty($item['product_id']) ? intval($item['product_id']) : null;
            $is_ext = !empty($item['is_external']) ? 1 : 0;
            $is_gift = !empty($item['is_gift']) ? 1 : 0;
            $serial_number = trim($item['serial_number'] ?? '');
            $warranty = trim($item['warranty'] ?? '');
            $qty = floatval($item['qty']);
            $buy_iqd = floatval($item['buy_price_iqd']);
            $sell_iqd = $is_gift ? 0 : floatval($item['sell_price_iqd']);
            $row_total = $qty * $sell_iqd;

            $insItem->execute([
                $sale_id,
                $pid,
                $item['barcode'] ?? '',
                $item['item_name'],
                $serial_number,
                $warranty,
                $is_ext,
                $is_gift,
                $qty,
                $buy_iqd,
                $sell_iqd,
                $row_total
            ]);

            if ($sale_type !== 'quote' && !$is_ext && $pid) {
                $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?")->execute([$qty, $pid]);
            }
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'sale_id' => $sale_id]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// ٥. هێنانی لیستی هەموو وەسڵە فرۆشراوەکان بۆ بەشی sold_receipts.php
if ($action === 'get_all_sales') {
    $q = trim($_GET['q'] ?? '');
    $type = trim($_GET['sale_type'] ?? 'all');
    $date = trim($_GET['date'] ?? '');

    $where = ["1=1"];
    $params = [];

    if ($q !== '') {
        $where[] = "(s.id = ? OR s.customer_name LIKE ? OR s.customer_phone LIKE ? OR s.guarantor_name LIKE ?)";
        $params[] = intval($q);
        $params[] = "%$q%";
        $params[] = "%$q%";
        $params[] = "%$q%";
    }

    if ($type !== '' && $type !== 'all') {
        $where[] = "s.sale_type = ?";
        $params[] = $type;
    }

    if ($date !== '') {
        $where[] = "DATE(s.created_at) = ?";
        $params[] = $date;
    }

    $whereSql = implode(" AND ", $where);

    $sql = "
        SELECT s.*, 
               COUNT(si.id) AS items_count,
               COALESCE(SUM(si.qty), 0) AS total_qty
        FROM sales s
        LEFT JOIN sale_items si ON s.id = si.sale_id
        WHERE $whereSql
        GROUP BY s.id
        ORDER BY s.id DESC
        LIMIT 200
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $sales]);
    exit;
}

// ٦. گەڕاندنەوەی کاڵا یان کەمکردنەوەی عەدەدی کاڵا لەناو وەسڵ و گەڕانەوەی بۆ کۆگا
if ($action === 'return_sale_item') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $sale_id = intval($payload['sale_id'] ?? 0);
    $item_id = intval($payload['item_id'] ?? 0);
    $return_qty = floatval($payload['return_qty'] ?? 0);

    if ($sale_id <= 0 || $item_id <= 0 || $return_qty <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'زانیارییەکان نادروستن']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $saleStmt = $pdo->prepare("SELECT * FROM sales WHERE id = ? LIMIT 1");
        $saleStmt->execute([$sale_id]);
        $sale = $saleStmt->fetch(PDO::FETCH_ASSOC);

        $itemStmt = $pdo->prepare("SELECT * FROM sale_items WHERE id = ? AND sale_id = ? LIMIT 1");
        $itemStmt->execute([$item_id, $sale_id]);
        $item = $itemStmt->fetch(PDO::FETCH_ASSOC);

        if (!$sale || !$item) {
            throw new Exception("وەسڵ یان کاڵاکە نەدۆزرایەوە!");
        }

        $current_qty = floatval($item['qty']);
        if ($return_qty > $current_qty) {
            throw new Exception("بڕی گەڕاوە نابێت زیاتر بێت لە عەدەدی فرۆشراو ($current_qty)!");
        }

        // ١. گەڕاندنەوەی عەدەدەکە بۆ ناو کۆگا (ئەگەر دەرخستە نەبوو و کاڵای دەرەکی نەبوو)
        if ($sale['sale_type'] !== 'quote' && intval($item['is_external']) === 0 && !empty($item['product_id'])) {
            $updStock = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            $updStock->execute([$return_qty, $item['product_id']]);
        }

        // ٢. کەمکردنەوەی عەدەد یان سڕینەوەی کاڵاکە لەناو وەسڵ
        $new_qty = $current_qty - $return_qty;
        if ($new_qty <= 0.0001) {
            $pdo->prepare("DELETE FROM sale_items WHERE id = ?")->execute([$item_id]);
        } else {
            $new_row_total = $new_qty * floatval($item['sell_price_iqd']);
            $pdo->prepare("UPDATE sale_items SET qty = ?, total_iqd = ? WHERE id = ?")
                ->execute([$new_qty, $new_row_total, $item_id]);
        }

        // ٣. پشکنینی کاڵا ماوەکانی ناو وەسڵەکە و نوێکردنەوەی حساباتی وەسڵ
        $remStmt = $pdo->prepare("SELECT COUNT(*) AS cnt, COALESCE(SUM(total_iqd), 0) AS subtotal FROM sale_items WHERE sale_id = ?");
        $remStmt->execute([$sale_id]);
        $remData = $remStmt->fetch(PDO::FETCH_ASSOC);

        $receipt_deleted = false;
        if (intval($remData['cnt']) === 0) {
            // ئەگەر هیچ کاڵایەک لە وەسڵەکەدا نەما، وەسڵەکە دەسڕێتەوە
            $pdo->prepare("DELETE FROM sales WHERE id = ?")->execute([$sale_id]);
            $receipt_deleted = true;
        } else {
            $new_subtotal = floatval($remData['subtotal']);
            $discount = min(floatval($sale['discount_iqd']), $new_subtotal);
            $new_total = max(0, $new_subtotal - $discount);

            if ($sale['sale_type'] === 'cash') {
                $new_paid = $new_total;
                $new_change = 0;
                $new_remaining = 0;
            } else {
                $new_paid = min(floatval($sale['paid_iqd']), $new_total);
                $new_change = 0;
                $new_remaining = max(0, $new_total - $new_paid);
            }

            $updSale = $pdo->prepare("
                UPDATE sales 
                SET subtotal_iqd = ?, discount_iqd = ?, total_iqd = ?, paid_iqd = ?, change_iqd = ?, remaining_iqd = ? 
                WHERE id = ?
            ");
            $updSale->execute([$new_subtotal, $discount, $new_total, $new_paid, $new_change, $new_remaining, $sale_id]);
        }

        $pdo->commit();
        echo json_encode([
            'status' => 'success',
            'receipt_deleted' => $receipt_deleted,
            'message' => 'کاڵاکە بە سەرکەوتوویی گەڕێندرایەوە بۆ کۆگا'
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// ٧. گەڕاندنەوە و هەڵوەشاندنەوەی تەواوی وەسڵەکە بەیەکجار
if ($action === 'delete_entire_sale') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $sale_id = intval($payload['sale_id'] ?? 0);

    if ($sale_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ژمارەی وەسڵ نادروستە']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $saleStmt = $pdo->prepare("SELECT * FROM sales WHERE id = ? LIMIT 1");
        $saleStmt->execute([$sale_id]);
        $sale = $saleStmt->fetch(PDO::FETCH_ASSOC);

        if (!$sale) {
            throw new Exception("وەسڵەکە نەدۆزرایەوە!");
        }

        if ($sale['sale_type'] !== 'quote') {
            $itemsStmt = $pdo->prepare("SELECT product_id, qty FROM sale_items WHERE sale_id = ? AND is_external = 0 AND product_id IS NOT NULL");
            $itemsStmt->execute([$sale_id]);
            foreach ($itemsStmt->fetchAll(PDO::FETCH_ASSOC) as $item) {
                $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?")
                    ->execute([$item['qty'], $item['product_id']]);
            }
        }

        $pdo->prepare("DELETE FROM sale_items WHERE sale_id = ?")->execute([$sale_id]);
        $pdo->prepare("DELETE FROM sales WHERE id = ?")->execute([$sale_id]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'تەواوی وەسڵەکە هەڵوەشێندرایەوە و کاڵاکان گەڕانەوە بۆ کۆگا']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
// ٨. هێنانی لیستی کڕیاران لەگەڵ کۆی قەرز و قیستەکانیان
if ($action === 'get_customers_overview') {
    $customers = $pdo->query("
        SELECT c.*,
               COUNT(s.id) AS receipts_count,
               COALESCE(SUM(CASE WHEN s.sale_type != 'quote' THEN s.total_iqd ELSE 0 END), 0) AS total_bought_iqd,
               COALESCE(SUM(CASE WHEN s.sale_type = 'debt' THEN s.remaining_iqd ELSE 0 END), 0) AS debt_iqd,
               COALESCE(SUM(CASE WHEN s.sale_type = 'installment' THEN s.remaining_iqd ELSE 0 END), 0) AS installment_iqd
        FROM customers c
        LEFT JOIN sales s ON c.id = s.customer_id
        GROUP BY c.id
        ORDER BY c.id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'customers' => $customers]);
    exit;
}

// ٩. زیادکردن یان دەستکاریکردنی کڕیار
if ($action === 'save_customer') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $id = intval($payload['id'] ?? 0);
    $name = trim($payload['name'] ?? '');
    $phone = trim($payload['phone'] ?? '');
    $guarantor = trim($payload['guarantor'] ?? '');

    if ($name === '') {
        echo json_encode(['status' => 'error', 'message' => 'تکایە ناوی کڕیار بنووسە']);
        exit;
    }

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE customers SET name = ?, phone = ?, guarantor = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $guarantor, $id]);
        $pdo->prepare("UPDATE sales SET customer_name = ?, customer_phone = ?, guarantor_name = ? WHERE customer_id = ?")
            ->execute([$name, $phone, $guarantor, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO customers (name, phone, guarantor) VALUES (?, ?, ?)");
        $stmt->execute([$name, $phone, $guarantor]);
        $id = $pdo->lastInsertId();
    }

    echo json_encode(['status' => 'success', 'customer_id' => $id]);
    exit;
}

// ١٠. سڕینەوەی کڕیار
if ($action === 'delete_customer') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $id = intval($payload['id'] ?? 0);
    $pdo->prepare("DELETE FROM customers WHERE id = ?")->execute([$id]);
    echo json_encode(['status' => 'success']);
    exit;
}

// ١١. وەرگرتنەوەی قەرز یان قیستی وەسڵی فرۆشتن لە کڕیار
if ($action === 'pay_sale_remaining') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $sale_id = intval($payload['sale_id'] ?? 0);
    $amount_iqd = floatval($payload['amount_iqd'] ?? 0);

    if ($sale_id <= 0 || $amount_iqd <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'تکایە بڕی پارەی وەرگیراو بە دروستی بنووسە']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM sales WHERE id = ? LIMIT 1");
    $stmt->execute([$sale_id]);
    $sale = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sale) {
        echo json_encode(['status' => 'error', 'message' => 'وەسڵەکە نەدۆزرایەوە']);
        exit;
    }

    $rem = floatval($sale['remaining_iqd']);
    $pay = min($rem, $amount_iqd);
    $new_paid = floatval($sale['paid_iqd']) + $pay;
    $new_rem = max(0, $rem - $pay);

    $upd = $pdo->prepare("UPDATE sales SET paid_iqd = ?, remaining_iqd = ? WHERE id = ?");
    $upd->execute([$new_paid, $new_rem, $sale_id]);

    echo json_encode(['status' => 'success', 'new_remaining_iqd' => $new_rem]);
    exit;
}
// ٨. هێنانی لیستی کڕیاران لەگەڵ کۆی قەرز و قیستەکانیان
if ($action === 'get_customers_overview') {
    $customers = $pdo->query("
        SELECT c.*,
               COUNT(s.id) AS receipts_count,
               COALESCE(SUM(CASE WHEN s.sale_type != 'quote' THEN s.total_iqd ELSE 0 END), 0) AS total_bought_iqd,
               COALESCE(SUM(CASE WHEN s.sale_type = 'debt' THEN s.remaining_iqd ELSE 0 END), 0) AS debt_iqd,
               COALESCE(SUM(CASE WHEN s.sale_type = 'installment' THEN s.remaining_iqd ELSE 0 END), 0) AS installment_iqd
        FROM customers c
        LEFT JOIN sales s ON c.id = s.customer_id
        GROUP BY c.id
        ORDER BY c.id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'customers' => $customers]);
    exit;
}

// ٩. زیادکردن یان دەستکاریکردنی کڕیار (ناو، ڕەقەمی کڕیار، ناونیشان، ناوی کەفیل، ڕەقەمی کەفیل)
if ($action === 'save_customer') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $id = intval($payload['id'] ?? 0);
    $name = trim($payload['name'] ?? '');
    $phone = trim($payload['phone'] ?? '');
    $address = trim($payload['address'] ?? '');
    $guarantor = trim($payload['guarantor'] ?? '');
    $guarantor_phone = trim($payload['guarantor_phone'] ?? '');

    if ($name === '') {
        echo json_encode(['status' => 'error', 'message' => 'تکایە ناوی کڕیار بنووسە']);
        exit;
    }

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE customers SET name = ?, phone = ?, address = ?, guarantor = ?, guarantor_phone = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $address, $guarantor, $guarantor_phone, $id]);
        $pdo->prepare("UPDATE sales SET customer_name = ?, customer_phone = ?, guarantor_name = ? WHERE customer_id = ?")
            ->execute([$name, $phone, $guarantor, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO customers (name, phone, address, guarantor, guarantor_phone) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $phone, $address, $guarantor, $guarantor_phone]);
        $id = $pdo->lastInsertId();
    }

    echo json_encode(['status' => 'success', 'customer_id' => $id]);
    exit;
}

// ١٠. سڕینەوەی کڕیار
if ($action === 'delete_customer') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $id = intval($payload['id'] ?? 0);
    $pdo->prepare("DELETE FROM customers WHERE id = ?")->execute([$id]);
    echo json_encode(['status' => 'success']);
    exit;
}

// ١١. واصلکردنی پارەی یەک وەسڵی دیاریکراو (بڕێک یان هەمووی)
if ($action === 'pay_sale_remaining') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $sale_id = intval($payload['sale_id'] ?? 0);
    $amount_iqd = floatval($payload['amount_iqd'] ?? 0);

    if ($sale_id <= 0 || $amount_iqd <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'تکایە بڕی پارەی واصلکراو بە دروستی بنووسە']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM sales WHERE id = ? LIMIT 1");
    $stmt->execute([$sale_id]);
    $sale = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sale) {
        echo json_encode(['status' => 'error', 'message' => 'وەسڵەکە نەدۆزرایەوە']);
        exit;
    }

    $rem = floatval($sale['remaining_iqd']);
    $pay = min($rem, $amount_iqd);
    $new_paid = floatval($sale['paid_iqd']) + $pay;
    $new_rem = max(0, $rem - $pay);

    $upd = $pdo->prepare("UPDATE sales SET paid_iqd = ?, remaining_iqd = ? WHERE id = ?");
    $upd->execute([$new_paid, $new_rem, $sale_id]);

    echo json_encode(['status' => 'success', 'new_remaining_iqd' => $new_rem]);
    exit;
}

// ١٢. واصلکردنی کۆی قەرزی کڕیارێک بەسەر وەسڵەکانیدا (بڕێک یان هەمووی بەیەکجار)
if ($action === 'pay_customer_total_debt') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $customer_key = trim($payload['customer_key'] ?? '');
    $amount_iqd = floatval($payload['amount_iqd'] ?? 0);

    if ($customer_key === '' || $amount_iqd <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'زانیارییەکان نادروستن']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        if (strpos($customer_key, 'id_') === 0) {
            $cid = intval(substr($customer_key, 3));
            $stmt = $pdo->prepare("SELECT * FROM sales WHERE sale_type = 'debt' AND remaining_iqd > 0 AND customer_id = ? ORDER BY id ASC");
            $stmt->execute([$cid]);
        } else {
            $cname = substr($customer_key, 5);
            $stmt = $pdo->prepare("SELECT * FROM sales WHERE sale_type = 'debt' AND remaining_iqd > 0 AND customer_name = ? ORDER BY id ASC");
            $stmt->execute([$cname]);
        }

        $receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $left_to_pay = $amount_iqd;

        foreach ($receipts as $r) {
            if ($left_to_pay <= 0) break;
            $rem = floatval($r['remaining_iqd']);
            $pay = min($rem, $left_to_pay);
            $new_paid = floatval($r['paid_iqd']) + $pay;
            $new_rem = max(0, $rem - $pay);

            $pdo->prepare("UPDATE sales SET paid_iqd = ?, remaining_iqd = ? WHERE id = ?")
                ->execute([$new_paid, $new_rem, $r['id']]);

            $left_to_pay -= $pay;
        }

        $pdo->commit();
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}