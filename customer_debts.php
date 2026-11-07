<?php
require_once 'db.php';
session_start();

// ١. کرداری واسڵکردنی قەرزی وەسڵ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_debt'])) {
    $sale_id    = intval($_POST['sale_id']);
    $pay_amount = floatval($_POST['pay_amount']);
    $note       = trim($_POST['note'] ?? '');

    // هێنانی زانیاری وەسڵەکە
    $stmt = $pdo->prepare("SELECT * FROM sales WHERE id = ?");
    $stmt->execute([$sale_id]);
    $sale = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sale && $pay_amount > 0) {
        $actual_pay = min($pay_amount, floatval($sale['remaining_iqd']));
        $new_remaining = floatval($sale['remaining_iqd']) - $actual_pay;
        $new_paid = floatval($sale['paid_iqd']) + $actual_pay;

        $pdo->beginTransaction();
        try {
            // ئەگەر بە تەواوی واسڵ بوو، کاتی واسڵکردن (paid_at) تۆمار دەکرێت و وەسڵەکە دەمێنێتەوە
            if ($new_remaining <= 0) {
                $upd = $pdo->prepare("UPDATE sales SET paid_iqd = ?, remaining_iqd = 0, paid_at = NOW() WHERE id = ?");
                $upd->execute([$new_paid, $sale_id]);
            } else {
                $upd = $pdo->prepare("UPDATE sales SET paid_iqd = ?, remaining_iqd = ? WHERE id = ?");
                $upd->execute([$new_paid, $new_remaining, $sale_id]);
            }

            // تۆمارکردنی بەڵگەی واسڵکردن لە خشتەی debt_payments
            $ins = $pdo->prepare("INSERT INTO debt_payments (sale_id, customer_id, paid_amount_iqd, usd_rate, payment_date, note) VALUES (?, ?, ?, ?, NOW(), ?)");
            $ins->execute([$sale_id, $sale['customer_id'], $actual_pay, $sale['usd_rate'], $note]);

            // کەمکردنەوەی باڵانسی قەرزی کڕیار ئەگەر کڕیارەکە تۆمارکرابێت
            if (!empty($sale['customer_id'])) {
                $upd_cust = $pdo->prepare("UPDATE customers SET balance_iqd = GREATEST(0, balance_iqd - ?) WHERE id = ?");
                $upd_cust->execute([$actual_pay, $sale['customer_id']]);
            }

            $pdo->commit();
            header("Location: customer_debts.php?msg=paid");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
        }
    }
}

// ٢. فلتەری بینینی قەرزە ماوەکان یان وەسڵە واسڵکراوەکان
$view = $_GET['view'] ?? 'unpaid';
$search = trim($_GET['search'] ?? '');

$where = ["sale_type = 'debt'"];
$params = [];

if ($view === 'paid') {
    // تەنها ئەو وەسڵە قەرزانەی بە تەواوی واسڵ کراون
    $where[] = "remaining_iqd <= 0";
} else {
    // تەنها ئەو وەسڵانەی قەرزیان ماوە
    $where[] = "remaining_iqd > 0";
}

if ($search !== '') {
    $where[] = "(customer_name LIKE ? OR customer_phone LIKE ? OR guarantor_name LIKE ? OR id = ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = $search;
}

$sql = "SELECT * FROM sales WHERE " . implode(" AND ", $where) . " ORDER BY " . ($view === 'paid' ? "paid_at DESC, id DESC" : "id DESC");
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$debts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ku" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>بەشی قەرزەکانی کڕیاران</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>body { font-family: 'Segoe UI', Tahoma, sans-serif; }</style>
</head>
<body class="bg-light p-4">
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">
            <?= $view === 'paid' ? '✅ وەسڵە قەرزە واسڵکراوەکان (پارێزراو بۆ بەڵگە)' : '📒 بەشی قەرزەکانی کڕیاران' ?>
        </h3>
        <div class="d-flex gap-2">
            <?php if ($view === 'paid'): ?>
                <a href="customer_debts.php?view=unpaid" class="btn btn-primary fw-bold">
                    ⬅️ گەڕانەوە بۆ قەرزە ماوەکان
                </a>
            <?php else: ?>
                <a href="customer_debts.php?view=paid" class="btn btn-success fw-bold">
                    📜 بینینی وەسڵە واسڵکراوەکان
                </a>
            <?php endif; ?>
            <a href="index.php" class="btn btn-secondary">سەرەتا</a>
        </div>
    </div>

    <!-- گەڕان -->
    <form method="GET" class="card p-3 mb-4 shadow-sm border-0">
        <input type="hidden" name="view" value="<?= htmlspecialchars($view) ?>">
        <div class="row g-2">
            <div class="col-md-10">
                <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="گەڕان بە ناوی کڕیار، ژمارە مۆبایل، ناوی کەفیل، یان ژمارەی وەسڵ...">
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-dark">گەڕان</button>
            </div>
        </div>
    </form>

    <!-- خشتەی وەسڵەکان -->
    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover text-center align-middle">
                <thead class="<?= $view === 'paid' ? 'table-success' : 'table-dark' ?>">
                    <tr>
                        <th>ژ. وەسڵ</th>
                        <th>ناوی کڕیار</th>
                        <th>مۆبایل</th>
                        <th>کەفیل</th>
                        <th>کۆی گشتی وەسڵ</th>
                        <th>بڕی واسڵکراو</th>
                        <th>بڕی ماوە</th>
                        <th>بەرواری وەسڵ</th>
                        <th>کاتی تەواو واسڵبوون</th>
                        <th>کردارەکان / بەڵگە</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($debts as $row): ?>
                    <tr>
                        <td class="fw-bold">#<?= $row['id'] ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($row['customer_name']) ?></td>
                        <td><?= htmlspecialchars($row['customer_phone']) ?></td>
                        <td><?= htmlspecialchars($row['guarantor_name'] ?: '---') ?></td>
                        <td><?= number_format($row['total_iqd']) ?> د.ع</td>
                        <td class="text-success fw-bold"><?= number_format($row['paid_iqd']) ?> د.ع</td>
                        <td class="text-danger fw-bold"><?= number_format($row['remaining_iqd']) ?> د.ع</td>
                        <td><?= date('Y-m-d h:i A', strtotime($row['created_at'])) ?></td>
                        <td>
                            <?php if ($row['remaining_iqd'] <= 0): ?>
                                <span class="badge bg-success">
                                    <?= $row['paid_at'] ? date('Y-m-d h:i A', strtotime($row['paid_at'])) : 'بە تەواوی واسڵکراوە' ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">تەواو واسڵ نەبووە</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-1">
                                <?php if ($row['remaining_iqd'] > 0): ?>
                                    <button class="btn btn-sm btn-primary" onclick="openPayModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['customer_name'], ENT_QUOTES) ?>', <?= $row['remaining_iqd'] ?>)">
                                        💵 واسڵکردن
                                    </button>
                                <?php endif; ?>
                                <a href="print_receipt.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-outline-dark">
                                    🖨️ وەسڵ (بەڵگە)
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($debts)): ?>
                    <tr><td colspan="10" class="text-muted py-4">هیچ وەسڵێک لەم بەشەدا بوونی نییە.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- مۆداڵی واسڵکردنی قەرز -->
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">واسڵکردنی وەسڵی قەرز</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="sale_id" id="modal_sale_id">
                <p>کڕیار: <strong id="modal_customer_name"></strong></p>
                <p>بڕی قەرزی ماوە: <strong class="text-danger" id="modal_remaining"></strong> د.ع</p>
                <div class="mb-3">
                    <label class="form-label fw-bold">بڕی پارەی وەرگیراو (دینار)</label>
                    <input type="number" step="250" name="pay_amount" id="modal_pay_amount" class="form-control" required>
                    <button type="button" class="btn btn-sm btn-link p-0 mt-1" onclick="document.getElementById('modal_pay_amount').value = currentMaxDebt;">
                        واسڵکردنی تەواوی وەسڵەکە
                    </button>
                </div>
                <div class="mb-3">
                    <label class="form-label">تێبینی</label>
                    <input type="text" name="note" class="form-control" placeholder="تێبینی بنووسە...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="pay_debt" class="btn btn-success w-100">تۆمارکردنی واسڵکردن</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let currentMaxDebt = 0;
function openPayModal(saleId, customerName, remaining) {
    currentMaxDebt = remaining;
    document.getElementById('modal_sale_id').value = saleId;
    document.getElementById('modal_customer_name').innerText = customerName;
    document.getElementById('modal_remaining').innerText = remaining.toLocaleString();
    document.getElementById('modal_pay_amount').value = remaining;
    document.getElementById('modal_pay_amount').max = remaining;
    new bootstrap.Modal(document.getElementById('payModal')).show();
}
</script>
</body>
</html>