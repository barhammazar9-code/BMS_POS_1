<?php
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// دڵنیابوونەوە لە هەبوونی خشتەکان و ستوونەکان بە شێوەی خۆکار
try {
    $pdo->exec("ALTER TABLE products MODIFY COLUMN stock DECIMAL(15, 2) NOT NULL DEFAULT 0");

    $pCols = $pdo->query("SHOW COLUMNS FROM products")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('wholesale_price_iqd', $pCols)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN wholesale_price_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0 AFTER sell_price_iqd");
    }
    if (!in_array('special_price_iqd', $pCols)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN special_price_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0 AFTER wholesale_price_iqd");
    }
    if (!in_array('company_name', $pCols)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN company_name VARCHAR(150) DEFAULT '' AFTER special_price_iqd");
    }
    if (!in_array('brand', $pCols)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN brand VARCHAR(150) DEFAULT '' AFTER company_name");
    }
    if (!in_array('category', $pCols)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN category VARCHAR(150) DEFAULT '' AFTER brand");
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL UNIQUE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS brands (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL UNIQUE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS companies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        phone VARCHAR(50) DEFAULT '',
        address VARCHAR(250) DEFAULT '',
        note VARCHAR(250) DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS purchases (
        id INT AUTO_INCREMENT PRIMARY KEY,
        invoice_number VARCHAR(100) NOT NULL DEFAULT '',
        company_id INT NULL,
        company_name VARCHAR(150) NOT NULL,
        purchase_type ENUM('cash', 'debt') NOT NULL DEFAULT 'cash',
        purchase_date DATE NOT NULL,
        subtotal_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
        discount_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
        total_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
        paid_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
        remaining_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
        usd_rate DECIMAL(10, 2) NOT NULL DEFAULT 1500.00,
        note VARCHAR(255) DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS purchase_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        purchase_id INT NOT NULL,
        product_id INT NOT NULL,
        barcode VARCHAR(100) DEFAULT '',
        item_name VARCHAR(200) NOT NULL,
        qty DECIMAL(15, 2) NOT NULL DEFAULT 1,
        old_stock DECIMAL(15, 2) NOT NULL DEFAULT 0,
        old_buy_price_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
        buy_price_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
        avg_buy_price_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
        sell_price_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
        total_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
        FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE
    )");
} catch (Exception $e) {
    // تێپەڕاندن
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// ١. هێنانی هەموو داتاکانی کاڵا، کۆمپانیا، وەسڵی کڕین، پۆل و براندەکان
if ($action === 'get_overview') {
    $companies = $pdo->query("
        SELECT c.*,
               COUNT(p.id) AS receipts_count,
               COALESCE(SUM(p.total_iqd), 0) AS total_purchased_iqd,
               COALESCE(SUM(p.remaining_iqd), 0) AS total_debt_iqd
        FROM companies c
        LEFT JOIN purchases p ON c.id = p.company_id
        GROUP BY c.id
        ORDER BY c.id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

    $purchases = $pdo->query("
        SELECT p.*, COUNT(pi.id) AS items_count, COALESCE(SUM(pi.qty), 0) AS total_qty
        FROM purchases p
        LEFT JOIN purchase_items pi ON p.id = pi.purchase_id
        GROUP BY p.id
        ORDER BY p.id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $categories = $pdo->query("
        SELECT c.*, COUNT(p.id) AS products_count
        FROM categories c
        LEFT JOIN products p ON p.category = c.name
        GROUP BY c.id
        ORDER BY c.id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $brands = $pdo->query("
        SELECT b.*, COUNT(p.id) AS products_count
        FROM brands b
        LEFT JOIN products p ON p.brand = b.name
        GROUP BY b.id
        ORDER BY b.id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $nextInv = $pdo->query("SELECT COALESCE(MAX(id), 0) + 1 FROM purchases")->fetchColumn();

    echo json_encode([
        'status' => 'success',
        'companies' => $companies,
        'products' => $products,
        'purchases' => $purchases,
        'categories' => $categories,
        'brands' => $brands,
        'next_invoice' => 'INV-' . str_pad($nextInv, 4, '0', STR_PAD_LEFT)
    ]);
    exit;
}

// ٢. زیادکردن یان دەستکاریکردنی پۆل (Category)
if ($action === 'save_category') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $id = intval($payload['id'] ?? 0);
    $name = trim($payload['name'] ?? '');

    if ($name === '') {
        echo json_encode(['status' => 'error', 'message' => 'تکایە ناوی پۆل بنووسە']);
        exit;
    }

    try {
        if ($id > 0) {
            $oldStmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
            $oldStmt->execute([$id]);
            $oldName = $oldStmt->fetchColumn();

            $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
            $stmt->execute([$name, $id]);

            if ($oldName && $oldName !== $name) {
                $pdo->prepare("UPDATE products SET category = ? WHERE category = ?")->execute([$name, $oldName]);
            }
        } else {
            $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
            $stmt->execute([$name]);
            $id = $pdo->lastInsertId();
        }
        echo json_encode(['status' => 'success', 'id' => $id, 'name' => $name]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'ئەم پۆلە پێشتر تۆمار کراوە']);
    }
    exit;
}

if ($action === 'delete_category') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $id = intval($payload['id'] ?? 0);
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
    echo json_encode(['status' => 'success']);
    exit;
}

// ٣. زیادکردن یان دەستکاریکردنی براند (Brand)
if ($action === 'save_brand') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $id = intval($payload['id'] ?? 0);
    $name = trim($payload['name'] ?? '');

    if ($name === '') {
        echo json_encode(['status' => 'error', 'message' => 'تکایە ناوی براند بنووسە']);
        exit;
    }

    try {
        if ($id > 0) {
            $oldStmt = $pdo->prepare("SELECT name FROM brands WHERE id = ?");
            $oldStmt->execute([$id]);
            $oldName = $oldStmt->fetchColumn();

            $stmt = $pdo->prepare("UPDATE brands SET name = ? WHERE id = ?");
            $stmt->execute([$name, $id]);

            if ($oldName && $oldName !== $name) {
                $pdo->prepare("UPDATE products SET brand = ? WHERE brand = ?")->execute([$name, $oldName]);
            }
        } else {
            $stmt = $pdo->prepare("INSERT IGNORE INTO brands (name) VALUES (?)");
            $stmt->execute([$name]);
            $id = $pdo->lastInsertId();
        }
        echo json_encode(['status' => 'success', 'id' => $id, 'name' => $name]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'ئەم براندە پێشتر تۆمار کراوە']);
    }
    exit;
}

if ($action === 'delete_brand') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $id = intval($payload['id'] ?? 0);
    $pdo->prepare("DELETE FROM brands WHERE id = ?")->execute([$id]);
    echo json_encode(['status' => 'success']);
    exit;
}

// ٤. زیادکردن یان ئەپدەیتکردنی کۆمپانیا
if ($action === 'save_company') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $id = intval($payload['id'] ?? 0);
    $name = trim($payload['name'] ?? '');
    $phone = trim($payload['phone'] ?? '');
    $address = trim($payload['address'] ?? '');
    $note = trim($payload['note'] ?? '');

    if ($name === '') {
        echo json_encode(['status' => 'error', 'message' => 'تکایە ناوی کۆمپانیا بنووسە']);
        exit;
    }

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE companies SET name = ?, phone = ?, address = ?, note = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $address, $note, $id]);
        $pdo->prepare("UPDATE purchases SET company_name = ? WHERE company_id = ?")->execute([$name, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO companies (name, phone, address, note) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $phone, $address, $note]);
        $id = $pdo->lastInsertId();
    }

    echo json_encode(['status' => 'success', 'company_id' => $id, 'name' => $name]);
    exit;
}

if ($action === 'delete_company') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $id = intval($payload['id'] ?? 0);
    $pdo->prepare("DELETE FROM companies WHERE id = ?")->execute([$id]);
    echo json_encode(['status' => 'success']);
    exit;
}

// ٥. زیادکردن یان ئەپدەیتکردنی کاڵا
if ($action === 'save_product') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $id = intval($payload['id'] ?? 0);
    $barcode = trim($payload['barcode'] ?? '');
    $name = trim($payload['name'] ?? '');
    $company_name = trim($payload['company_name'] ?? '');
    $brand = trim($payload['brand'] ?? '');
    $category = trim($payload['category'] ?? '');
    $buy_price_iqd = floatval($payload['buy_price_iqd'] ?? 0);
    $sell_price_iqd = floatval($payload['sell_price_iqd'] ?? 0);
    $wholesale_price_iqd = floatval($payload['wholesale_price_iqd'] ?? 0);
    $special_price_iqd = floatval($payload['special_price_iqd'] ?? 0);
    $stock = floatval($payload['stock'] ?? 0);

    if ($name === '') {
        echo json_encode(['status' => 'error', 'message' => 'تکایە ناوی کاڵا بنووسە']);
        exit;
    }

    if ($barcode === '') $barcode = null;

    // ئەگەر پۆل یان براندی نوێ نووسرابوو خۆکارانە دەچێتە خشتەی پۆل و براندەکانیش
    if ($category !== '') {
        $pdo->prepare("INSERT IGNORE INTO categories (name) VALUES (?)")->execute([$category]);
    }
    if ($brand !== '') {
        $pdo->prepare("INSERT IGNORE INTO brands (name) VALUES (?)")->execute([$brand]);
    }

    try {
        if ($id > 0) {
            $stmt = $pdo->prepare("
                UPDATE products 
                SET barcode = ?, name = ?, company_name = ?, brand = ?, category = ?, 
                    buy_price_iqd = ?, sell_price_iqd = ?, wholesale_price_iqd = ?, special_price_iqd = ?, stock = ? 
                WHERE id = ?
            ");
            $stmt->execute([$barcode, $name, $company_name, $brand, $category, $buy_price_iqd, $sell_price_iqd, $wholesale_price_iqd, $special_price_iqd, $stock, $id]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO products (barcode, name, company_name, brand, category, buy_price_iqd, sell_price_iqd, wholesale_price_iqd, special_price_iqd, stock) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$barcode, $name, $company_name, $brand, $category, $buy_price_iqd, $sell_price_iqd, $wholesale_price_iqd, $special_price_iqd, $stock]);
            $id = $pdo->lastInsertId();
        }

        $prodStmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $prodStmt->execute([$id]);
        echo json_encode(['status' => 'success', 'product' => $prodStmt->fetch(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'ئەم بارکۆدە پێشتر بەکارهاتووە یان هەڵەیەک هەیە']);
    }
    exit;
}

if ($action === 'delete_product') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $id = intval($payload['id'] ?? 0);
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    echo json_encode(['status' => 'success']);
    exit;
}

// ٦. خەزنکردنی وەسڵی نوێ یان دەستکاریکردنی وەسڵی کڕین
if ($action === 'save_purchase') {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!$payload || empty($payload['items'])) {
        echo json_encode(['status' => 'error', 'message' => 'تکایە سەرەتا کاڵا داخڵ بکە بۆ ناو وەسڵی کڕین']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $purchase_id    = intval($payload['purchase_id'] ?? 0);
        $company_id     = !empty($payload['company_id']) ? intval($payload['company_id']) : null;
        $company_name   = trim($payload['company_name'] ?? '');
        $invoice_number = trim($payload['invoice_number'] ?? '');
        $purchase_type  = ($payload['purchase_type'] ?? 'cash') === 'debt' ? 'debt' : 'cash';
        $purchase_date  = trim($payload['purchase_date'] ?: date('Y-m-d'));
        $discount_iqd   = floatval($payload['discount_iqd'] ?? 0);
        $paid_input_iqd = floatval($payload['paid_iqd'] ?? 0);
        $usd_rate       = floatval($payload['usd_rate'] ?? 1500);
        $note           = trim($payload['note'] ?? '');

        if ($company_name === '') {
            throw new Exception("تکایە ناوی کۆمپانیا هەڵبژێرە یان بنووسە!");
        }

        if (!$company_id) {
            $chkComp = $pdo->prepare("SELECT id FROM companies WHERE name = ? LIMIT 1");
            $chkComp->execute([$company_name]);
            $foundComp = $chkComp->fetch(PDO::FETCH_ASSOC);
            if ($foundComp) {
                $company_id = $foundComp['id'];
            } else {
                $insComp = $pdo->prepare("INSERT INTO companies (name) VALUES (?)");
                $insComp->execute([$company_name]);
                $company_id = $pdo->lastInsertId();
            }
        }

        $subtotal_iqd = 0;
        foreach ($payload['items'] as $it) {
            $subtotal_iqd += floatval($it['qty']) * floatval($it['buy_price_iqd']);
        }
        $total_iqd = max(0, $subtotal_iqd - $discount_iqd);

        if ($purchase_type === 'cash') {
            $paid_iqd = $total_iqd;
            $remaining_iqd = 0;
        } else {
            $paid_iqd = min($total_iqd, max(0, $paid_input_iqd));
            $remaining_iqd = max(0, $total_iqd - $paid_iqd);
        }

        if ($purchase_id > 0) {
            $oldItemsStmt = $pdo->prepare("SELECT * FROM purchase_items WHERE purchase_id = ?");
            $oldItemsStmt->execute([$purchase_id]);
            foreach ($oldItemsStmt->fetchAll(PDO::FETCH_ASSOC) as $oldIt) {
                $revertBuy = floatval($oldIt['old_buy_price_iqd']) > 0 ? floatval($oldIt['old_buy_price_iqd']) : floatval($oldIt['buy_price_iqd']);
                $pdo->prepare("UPDATE products SET stock = stock - ?, buy_price_iqd = ? WHERE id = ?")
                    ->execute([floatval($oldIt['qty']), $revertBuy, intval($oldIt['product_id'])]);
            }

            if ($invoice_number === '') {
                $invoice_number = 'INV-' . str_pad($purchase_id, 4, '0', STR_PAD_LEFT);
            }

            $updPurchase = $pdo->prepare("
                UPDATE purchases 
                SET invoice_number=?, company_id=?, company_name=?, purchase_type=?, purchase_date=?, subtotal_iqd=?, discount_iqd=?, total_iqd=?, paid_iqd=?, remaining_iqd=?, usd_rate=?, note=?
                WHERE id=?
            ");
            $updPurchase->execute([
                $invoice_number, $company_id, $company_name, $purchase_type, $purchase_date,
                $subtotal_iqd, $discount_iqd, $total_iqd, $paid_iqd, $remaining_iqd, $usd_rate, $note, $purchase_id
            ]);

            $pdo->prepare("DELETE FROM purchase_items WHERE purchase_id = ?")->execute([$purchase_id]);
        } else {
            $insPurchase = $pdo->prepare("
                INSERT INTO purchases 
                (invoice_number, company_id, company_name, purchase_type, purchase_date, subtotal_iqd, discount_iqd, total_iqd, paid_iqd, remaining_iqd, usd_rate, note)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $insPurchase->execute([
                $invoice_number, $company_id, $company_name, $purchase_type, $purchase_date,
                $subtotal_iqd, $discount_iqd, $total_iqd, $paid_iqd, $remaining_iqd, $usd_rate, $note
            ]);
            $purchase_id = $pdo->lastInsertId();

            if ($invoice_number === '') {
                $invoice_number = 'INV-' . str_pad($purchase_id, 4, '0', STR_PAD_LEFT);
                $pdo->prepare("UPDATE purchases SET invoice_number = ? WHERE id = ?")->execute([$invoice_number, $purchase_id]);
            }
        }

        $insItem = $pdo->prepare("
            INSERT INTO purchase_items
            (purchase_id, product_id, barcode, item_name, qty, old_stock, old_buy_price_iqd, buy_price_iqd, avg_buy_price_iqd, sell_price_iqd, total_iqd)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($payload['items'] as $item) {
            $pid = !empty($item['product_id']) ? intval($item['product_id']) : 0;
            $barcode = trim($item['barcode'] ?? '');
            $item_name = trim($item['item_name'] ?? '');
            $qty = floatval($item['qty'] ?? 0);
            $new_buy_iqd = floatval($item['buy_price_iqd'] ?? 0);
            $new_sell_iqd = floatval($item['sell_price_iqd'] ?? 0);
            $row_total_iqd = $qty * $new_buy_iqd;

            if ($qty <= 0 || $item_name === '') continue;

            $existingProd = null;
            if ($pid > 0) {
                $pStmt = $pdo->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
                $pStmt->execute([$pid]);
                $existingProd = $pStmt->fetch(PDO::FETCH_ASSOC);
            } elseif ($barcode !== '') {
                $pStmt = $pdo->prepare("SELECT * FROM products WHERE barcode = ? LIMIT 1");
                $pStmt->execute([$barcode]);
                $existingProd = $pStmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $pStmt = $pdo->prepare("SELECT * FROM products WHERE name = ? LIMIT 1");
                $pStmt->execute([$item_name]);
                $existingProd = $pStmt->fetch(PDO::FETCH_ASSOC);
            }

            if ($existingProd) {
                $pid = intval($existingProd['id']);
                $old_stock = floatval($existingProd['stock']);
                $old_buy_iqd = floatval($existingProd['buy_price_iqd']);
                $current_sell_iqd = floatval($existingProd['sell_price_iqd']);

                $effective_old_stock = max(0, $old_stock);
                if (($effective_old_stock + $qty) > 0) {
                    $avg_buy_iqd = (($effective_old_stock * $old_buy_iqd) + ($qty * $new_buy_iqd)) / ($effective_old_stock + $qty);
                } else {
                    $avg_buy_iqd = $new_buy_iqd;
                }

                $final_sell_iqd = $new_sell_iqd > 0 ? $new_sell_iqd : $current_sell_iqd;
                $final_barcode = $barcode !== '' ? $barcode : $existingProd['barcode'];
                $new_total_stock = $old_stock + $qty;

                $updProd = $pdo->prepare("
                    UPDATE products 
                    SET name = ?, barcode = ?, company_name = ?, stock = ?, buy_price_iqd = ?, sell_price_iqd = ?
                    WHERE id = ?
                ");
                $updProd->execute([
                    $item_name,
                    $final_barcode ?: null,
                    $company_name,
                    $new_total_stock,
                    round($avg_buy_iqd, 2),
                    $final_sell_iqd,
                    $pid
                ]);
            } else {
                $old_stock = 0;
                $old_buy_iqd = 0;
                $avg_buy_iqd = $new_buy_iqd;
                $final_sell_iqd = $new_sell_iqd > 0 ? $new_sell_iqd : $new_buy_iqd;

                $insProd = $pdo->prepare("
                    INSERT INTO products (barcode, name, company_name, buy_price_iqd, sell_price_iqd, stock)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $insProd->execute([
                    $barcode !== '' ? $barcode : null,
                    $item_name,
                    $company_name,
                    round($avg_buy_iqd, 2),
                    $final_sell_iqd,
                    $qty
                ]);
                $pid = $pdo->lastInsertId();
            }

            $insItem->execute([
                $purchase_id,
                $pid,
                $barcode,
                $item_name,
                $qty,
                $old_stock,
                $old_buy_iqd,
                $new_buy_iqd,
                round($avg_buy_iqd, 2),
                $final_sell_iqd,
                $row_total_iqd
            ]);
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'purchase_id' => $purchase_id]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'get_purchase_details') {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM purchases WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $purchase = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$purchase) {
        echo json_encode(['status' => 'error', 'message' => 'وەسڵ نەدۆزرایەوە']);
        exit;
    }

    $itemsStmt = $pdo->prepare("SELECT * FROM purchase_items WHERE purchase_id = ?");
    $itemsStmt->execute([$id]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'purchase' => $purchase, 'items' => $items]);
    exit;
}

if ($action === 'pay_debt') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $purchase_id = intval($payload['purchase_id'] ?? 0);
    $amount_iqd = floatval($payload['amount_iqd'] ?? 0);

    if ($purchase_id <= 0 || $amount_iqd <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'تکایە بڕی پارەی دراوە بە دروستی بنووسە']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM purchases WHERE id = ? LIMIT 1");
    $stmt->execute([$purchase_id]);
    $purchase = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$purchase) {
        echo json_encode(['status' => 'error', 'message' => 'وەسڵەکە نەدۆزرایەوە']);
        exit;
    }

    $rem = floatval($purchase['remaining_iqd']);
    $pay = min($rem, $amount_iqd);
    $new_paid = floatval($purchase['paid_iqd']) + $pay;
    $new_rem = max(0, $rem - $pay);

    $upd = $pdo->prepare("UPDATE purchases SET paid_iqd = ?, remaining_iqd = ? WHERE id = ?");
    $upd->execute([$new_paid, $new_rem, $purchase_id]);

    echo json_encode(['status' => 'success', 'new_remaining_iqd' => $new_rem]);
    exit;
}

if ($action === 'delete_purchase') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $purchase_id = intval($payload['purchase_id'] ?? 0);

    try {
        $pdo->beginTransaction();
        $itemsStmt = $pdo->prepare("SELECT * FROM purchase_items WHERE purchase_id = ?");
        $itemsStmt->execute([$purchase_id]);
        foreach ($itemsStmt->fetchAll(PDO::FETCH_ASSOC) as $it) {
            $pdo->prepare("UPDATE products SET stock = stock - ?, buy_price_iqd = ? WHERE id = ?")
                ->execute([$it['qty'], $it['old_buy_price_iqd'] > 0 ? $it['old_buy_price_iqd'] : $it['buy_price_iqd'], $it['product_id']]);
        }

        $pdo->prepare("DELETE FROM purchase_items WHERE purchase_id = ?")->execute([$purchase_id]);
        $pdo->prepare("DELETE FROM purchases WHERE id = ?")->execute([$purchase_id]);
        $pdo->commit();
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}