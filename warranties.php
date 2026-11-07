<?php
require_once 'db.php';

// ئەگەر داواکاری AJAX بوو بۆ هێنانی داتای گەرەنتی و سیریال
if (isset($_GET['ajax_warranties'])) {
    header('Content-Type: application/json; charset=utf-8');

    $serial    = trim($_GET['serial'] ?? '');
    $item      = trim($_GET['item'] ?? '');
    $customer  = trim($_GET['customer'] ?? '');
    $warranty  = trim($_GET['warranty'] ?? '');
    $date_from = $_GET['date_from'] ?? '';
    $date_to   = $_GET['date_to'] ?? '';

    $where = ["(si.warranty != '' OR si.serial_number != '')"];
    $params = [];

    if ($serial !== '') {
        $where[] = "si.serial_number LIKE ?";
        $params[] = "%$serial%";
    }
    if ($item !== '') {
        $where[] = "(si.item_name LIKE ? OR si.barcode LIKE ?)";
        $params[] = "%$item%";
        $params[] = "%$item%";
    }
    if ($customer !== '') {
        $where[] = "(s.customer_name LIKE ? OR s.customer_phone LIKE ?)";
        $params[] = "%$customer%";
        $params[] = "%$customer%";
    }
    if ($warranty !== '') {
        $where[] = "si.warranty LIKE ?";
        $params[] = "%$warranty%";
    }
    if ($date_from !== '') {
        $where[] = "DATE(s.created_at) >= ?";
        $params[] = $date_from;
    }
    if ($date_to !== '') {
        $where[] = "DATE(s.created_at) <= ?";
        $params[] = $date_to;
    }

    $sql = "SELECT si.*, s.customer_name, s.customer_phone, s.created_at AS sale_date
            FROM sale_items si
            INNER JOIN sales s ON si.sale_id = s.id
            WHERE " . implode(" AND ", $where) . "
            ORDER BY s.created_at DESC, si.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
?>

<div class="h-full flex flex-col gap-3 overflow-hidden">
    <!-- سەرپەڕە و فلتەرەکانی گەڕان -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm shrink-0">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <span>🛡️</span>
                <span>گەڕان و بەڕێوەبردنی گەرەنتی و سیریال</span>
            </h2>
            <button onclick="resetWarrantyFilters()" class="text-xs bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 px-3 py-1.5 rounded-xl font-bold transition">
                🔄 پاککردنەوەی گەڕان
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 text-xs">
            <div>
                <label class="block text-slate-500 dark:text-slate-400 mb-1 font-bold">ژمارەی سیریال (S/N)</label>
                <input type="text" id="w_serial" oninput="loadWarrantiesPageData()" placeholder="سیریال بنووسە..."
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 focus:outline-none focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-slate-500 dark:text-slate-400 mb-1 font-bold">کاڵا یان بارکۆد</label>
                <input type="text" id="w_item" oninput="loadWarrantiesPageData()" placeholder="ناوی کاڵا / بارکۆد..."
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 focus:outline-none focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-slate-500 dark:text-slate-400 mb-1 font-bold">کڕیار (ناو / مۆبایل)</label>
                <input type="text" id="w_customer" oninput="loadWarrantiesPageData()" placeholder="ناوی کڕیار یان مۆبایل..."
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 focus:outline-none focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-slate-500 dark:text-slate-400 mb-1 font-bold">ماوەی گەرەنتی</label>
                <input type="text" id="w_warranty" oninput="loadWarrantiesPageData()" placeholder="نمونە: ١ ساڵ، ٦ مانگ..."
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 focus:outline-none focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-slate-500 dark:text-slate-400 mb-1 font-bold">لە بەرواری</label>
                <input type="date" id="w_date_from" onchange="loadWarrantiesPageData()"
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 focus:outline-none focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-slate-500 dark:text-slate-400 mb-1 font-bold">بۆ بەرواری</label>
                <input type="date" id="w_date_to" onchange="loadWarrantiesPageData()"
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 focus:outline-none focus:border-emerald-500">
            </div>
        </div>
    </div>

    <!-- خشتەی کاڵا فرۆشراوەکانی گەرەنتی -->
    <div class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-y-auto flex-1">
            <table class="w-full text-xs text-right border-collapse">
                <thead class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 sticky top-0">
                    <tr>
                        <th class="p-3 border-b dark:border-slate-700">ژ. وەسڵ</th>
                        <th class="p-3 border-b dark:border-slate-700">بارکۆد</th>
                        <th class="p-3 border-b dark:border-slate-700">ناوی کاڵا</th>
                        <th class="p-3 border-b dark:border-slate-700">سیریال نەمبەر (S/N)</th>
                        <th class="p-3 border-b dark:border-slate-700">گەرەنتی</th>
                        <th class="p-3 border-b dark:border-slate-700">کڕیار</th>
                        <th class="p-3 border-b dark:border-slate-700">بڕ</th>
                        <th class="p-3 border-b dark:border-slate-700">نرخی فرۆشتن</th>
                        <th class="p-3 border-b dark:border-slate-700">بەرواری فرۆشتن</th>
                        <th class="p-3 border-b dark:border-slate-700 text-center">وەسڵ</th>
                    </tr>
                </thead>
                <tbody id="warrantiesTableBody" class="divide-y divide-slate-200 dark:divide-slate-800"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
window.loadWarrantiesPageData = function() {
    const params = new URLSearchParams({
        ajax_warranties: 1,
        serial: document.getElementById('w_serial')?.value || '',
        item: document.getElementById('w_item')?.value || '',
        customer: document.getElementById('w_customer')?.value || '',
        warranty: document.getElementById('w_warranty')?.value || '',
        date_from: document.getElementById('w_date_from')?.value || '',
        date_to: document.getElementById('w_date_to')?.value || ''
    });

    fetch('warranties.php?' + params.toString())
        .then(r => r.json())
        .then(rows => {
            const tbody = document.getElementById('warrantiesTableBody');
            if (!tbody) return;
            if (!rows.length) {
                tbody.innerHTML = `<tr><td colspan="10" class="text-center py-8 text-slate-400">هیچ کاڵایەکی گەرەنتی یان سیریال نەدۆزرایەوە</td></tr>`;
                return;
            }
            tbody.innerHTML = rows.map(r => `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                    <td class="p-3 font-mono font-bold text-blue-600 dark:text-blue-400">#${r.sale_id}</td>
                    <td class="p-3 font-mono">${r.barcode || '-'}</td>
                    <td class="p-3 font-bold text-slate-900 dark:text-white">${r.item_name}</td>
                    <td class="p-3">
                        <span class="bg-slate-900 text-white dark:bg-slate-700 px-2.5 py-1 rounded-lg font-mono font-bold">${r.serial_number || '---'}</span>
                    </td>
                    <td class="p-3">
                        <span class="bg-emerald-50 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30 px-2.5 py-1 rounded-lg font-bold">${r.warranty || '---'}</span>
                    </td>
                    <td class="p-3 font-bold">
                        ${r.customer_name}
                        ${r.customer_phone ? `<div class="text-[11px] font-mono text-slate-400">${r.customer_phone}</div>` : ''}
                    </td>
                    <td class="p-3 font-mono">${parseFloat(r.qty)}</td>
                    <td class="p-3 font-mono font-bold text-emerald-600 dark:text-emerald-400">${formatMoney(r.sell_price_iqd)}</td>
                    <td class="p-3 font-mono text-slate-500">${r.sale_date}</td>
                    <td class="p-3 text-center">
                        <a href="print_receipt.php?id=${r.sale_id}" target="_blank" class="bg-blue-50 hover:bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-300 px-3 py-1 rounded-lg font-bold">🖨️ وەسڵ</a>
                    </td>
                </tr>
            `).join('');
        });
};

window.resetWarrantyFilters = function() {
    ['w_serial', 'w_item', 'w_customer', 'w_warranty', 'w_date_from', 'w_date_to'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    window.loadWarrantiesPageData();
};

window.loadWarrantiesPageData();
</script>