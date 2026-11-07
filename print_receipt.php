<?php
require_once 'db.php';

$id = intval($_GET['id'] ?? 0);
$format = ($_GET['format'] ?? 'thermal') === 'a4' ? 'a4' : 'thermal';

$stmt = $pdo->prepare("SELECT * FROM sales WHERE id = ?");
$stmt->execute([$id]);
$sale = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sale) {
    die("وەسڵ نەدۆزرایەوە!");
}

$stmtItems = $pdo->prepare("SELECT * FROM sale_items WHERE sale_id = ?");
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

$saleTypesKurdish = [
    'cash' => 'نەقد',
    'debt' => 'قەرز',
    'installment' => 'قیست',
    'quote' => 'وەسڵی دەرخستە'
];
$rate = floatval($sale['usd_rate']) ?: 1500;
?>
<!DOCTYPE html>
<html lang="ku" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>وەسڵی ژمارە #<?= $sale['id'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            margin: 0;
            padding: 15px;
            color: #000;
            background: #fff;
        }
        <?php if ($format === 'thermal'): ?>
        .receipt-box { width: 76mm; margin: 0 auto; font-size: 12px; }
        <?php else: ?>
        .receipt-box { max-width: 210mm; margin: 0 auto; font-size: 14px; border: 1px solid #ccc; padding: 25px; border-radius: 8px; }
        <?php endif; ?>
        .header { text-align: center; border-bottom: 2px dashed #000; padding-bottom: 10px; margin-bottom: 10px; }
        .meta { display: flex; flex-wrap: wrap; justify-content: space-between; font-size: 12px; margin-bottom: 10px; gap: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border-bottom: 1px solid #ddd; padding: 6px 4px; text-align: right; }
        th { background: #f3f4f6; font-weight: bold; }
        .totals { border-top: 2px dashed #000; padding-top: 8px; space-y-1; }
        .totals-row { display: flex; justify-content: space-between; margin-bottom: 4px; font-weight: bold; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="text-align:center; margin-bottom:15px;">
        <button onclick="window.print()" style="padding:8px 16px; font-weight:bold; cursor:pointer;">🖨️ پرێنتکردن</button>
        <button onclick="window.close()" style="padding:8px 16px; cursor:pointer;">داخستن</button>
    </div>

    <div class="receipt-box">
        <div class="header">
            <h2 style="margin:0 0 4px 0;"><?= htmlspecialchars($app_settings['shop_name']) ?></h2>
            <div>جۆری وەسڵ: <strong><?= $saleTypesKurdish[$sale['sale_type']] ?? 'نەقد' ?></strong> | وەسڵی ژمارە: <strong>#<?= $sale['id'] ?></strong></div>
            <div style="font-size:11px; margin-top:3px;">بەروار: <?= $sale['created_at'] ?></div>
        </div>

        <div class="meta">
            <div><strong>کڕیار:</strong> <?= htmlspecialchars($sale['customer_name']) ?></div>
            <?php if ($sale['customer_phone']): ?>
                <div><strong>مۆبایل:</strong> <?= htmlspecialchars($sale['customer_phone']) ?></div>
            <?php endif; ?>
            <?php if ($sale['guarantor_name']): ?>
                <div><strong>کەفیل:</strong> <?= htmlspecialchars($sale['guarantor_name']) ?></div>
            <?php endif; ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th>کاڵا</th>
                    <th>عەدەد</th>
                    <th>نرخ</th>
                    <th>کۆی گشتی</th>
                </tr>
            </thead>
            <tbody>
<?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <div>
                            <?= htmlspecialchars($item['item_name']) ?>
                            <?php if (!empty($item['is_gift'])): ?>
                                <strong>(🎁 دیاری)</strong>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($item['serial_number']) || !empty($item['warranty'])): ?>
                            <div style="font-size:11px; color:#444; margin-top:2px;">
                                <?php if (!empty($item['serial_number'])): ?>
                                    <span>S/N: <?= htmlspecialchars($item['serial_number']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($item['warranty'])): ?>
                                    <span> | زەمان: <?= htmlspecialchars($item['warranty']) ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td><?= floatval($item['qty']) ?></td>
                    <td><?= !empty($item['is_gift']) ? 'دیاری (0)' : number_format($item['sell_price_iqd']) ?></td>
                    <td><?= number_format($item['total_iqd']) ?></td>
                </tr>
                <?php endforeach; ?>

                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['item_name']) ?></td>
                    <td><?= floatval($item['qty']) ?></td>
                    <td><?= number_format($item['sell_price_iqd']) ?></td>
                    <td><?= number_format($item['total_iqd']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-row">
                <span>کۆی گشتی:</span>
                <span><?= number_format($sale['subtotal_iqd']) ?> د.ع ($<?= number_format($sale['subtotal_iqd'] / $rate, 2) ?>)</span>
            </div>
            <?php if ($sale['discount_iqd'] > 0): ?>
            <div class="totals-row">
                <span>داشکان:</span>
                <span><?= number_format($sale['discount_iqd']) ?> د.ع ($<?= number_format($sale['discount_iqd'] / $rate, 2) ?>)</span>
            </div>
            <?php endif; ?>
            <div class="totals-row" style="font-size:15px; border-top:1px solid #000; padding-top:4px;">
                <span>صافی داواکراو:</span>
                <span><?= number_format($sale['total_iqd']) ?> د.ع ($<?= number_format($sale['total_iqd'] / $rate, 2) ?>)</span>
            </div>
            <div class="totals-row">
                <span>پارەی دراو:</span>
                <span><?= number_format($sale['paid_iqd']) ?> د.ع</span>
            </div>
            <?php if ($sale['change_iqd'] > 0): ?>
            <div class="totals-row">
                <span>باقی بۆ کڕیار:</span>
                <span><?= number_format($sale['change_iqd']) ?> د.ع</span>
            </div>
            <?php endif; ?>
            <?php if ($sale['remaining_iqd'] > 0): ?>
            <div class="totals-row">
                <span>ماوە لەسەر کڕیار (قەرز):</span>
                <span><?= number_format($sale['remaining_iqd']) ?> د.ع ($<?= number_format($sale['remaining_iqd'] / $rate, 2) ?>)</span>
            </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>