<?php
require_once 'db.php';

if (isset($_GET['ajax_alerts'])) {
    header('Content-Type: application/json; charset=utf-8');
    $stagnant_days = intval($_GET['days'] ?? 30);

    // ١. ئاگاداری قەرز
    $debts = $pdo->query("SELECT *, DATEDIFF(CURDATE(), DATE(created_at)) AS days_passed FROM sales WHERE sale_type = 'debt' AND remaining_iqd > 0 ORDER BY created_at ASC")->fetchAll(PDO::FETCH_ASSOC);

    // ٢. ئاگاداری قیست
    $installments = $pdo->query("SELECT *, DATEDIFF(CURDATE(), DATE(created_at)) AS days_passed FROM sales WHERE sale_type = 'installment' AND remaining_iqd > 0 ORDER BY created_at ASC")->fetchAll(PDO::FETCH_ASSOC);

    // ٣. ئاگاداری کاڵای مەیوو (نەفرۆشراو بۆ ماوەی دیاریکراو)
    $stmt_stag = $pdo->prepare("
        SELECT p.*, MAX(s.created_at) AS last_sold_date,
               DATEDIFF(CURDATE(), DATE(MAX(s.created_at))) AS days_unsold
        FROM products p
        LEFT JOIN sale_items si ON p.id = si.product_id
        LEFT JOIN sales s ON si.sale_id = s.id AND s.sale_type != 'quote'
        WHERE p.stock > 0
        GROUP BY p.id
        HAVING last_sold_date IS NULL OR DATEDIFF(CURDATE(), DATE(last_sold_date)) >= ?
        ORDER BY last_sold_date ASC
    ");
    $stmt_stag->execute([$stagnant_days]);
    $stagnant = $stmt_stag->fetchAll(PDO::FETCH_ASSOC);

    // ٤. ئاگاداری کەمبوونی کاڵا
    $checkCol = $pdo->query("SHOW COLUMNS FROM products LIKE 'min_stock'")->fetch();
    $minStockCond = $checkCol ? "stock <= min_stock" : "stock <= 5";
    $low_stock = $pdo->query("SELECT * FROM products WHERE $minStockCond ORDER BY stock ASC")->fetchAll(PDO::FETCH_ASSOC);

    // ٥. ئاگاداری بەسەرچوون
    $expiry = [];
    $checkExp = $pdo->query("SHOW COLUMNS FROM products LIKE 'expiry_date'")->fetch();
    if ($checkExp) {
        $expiry = $pdo->query("
            SELECT *, DATEDIFF(expiry_date, CURDATE()) AS days_left 
            FROM products 
            WHERE expiry_date IS NOT NULL AND expiry_date != '0000-00-00'
              AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            ORDER BY expiry_date ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode([
        'debts'        => $debts,
        'installments' => $installments,
        'stagnant'     => $stagnant,
        'low_stock'    => $low_stock,
        'expiry'       => $expiry
    ]);
    exit;
}
?>

<div class="h-full flex flex-col gap-3 overflow-hidden">
    <!-- دووگمەکانی هەر ٥ بەشی ئاگاداری -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-3 shadow-sm flex flex-wrap items-center justify-between gap-2 shrink-0">
        <div class="flex flex-wrap gap-2">
            <button onclick="switchAlertTab('debts')" id="al-btn-debts" class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 bg-red-600 text-white">
                <span>📒 ئاگاداری قەرز</span>
                <span id="cnt-debts" class="bg-white/20 px-2 py-0.5 rounded-full text-[11px]">0</span>
            </button>
            <button onclick="switchAlertTab('installments')" id="al-btn-installments" class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                <span>📅 ئاگاداری قیست</span>
                <span id="cnt-installments" class="bg-rose-500 text-white px-2 py-0.5 rounded-full text-[11px]">0</span>
            </button>
            <button onclick="switchAlertTab('stagnant')" id="al-btn-stagnant" class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                <span>🕸️ کاڵای مەیوو (نەفرۆشراو)</span>
                <span id="cnt-stagnant" class="bg-amber-500 text-white px-2 py-0.5 rounded-full text-[11px]">0</span>
            </button>
            <button onclick="switchAlertTab('low_stock')" id="al-btn-low_stock" class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                <span>📉 کەمبوونی کاڵا</span>
                <span id="cnt-low_stock" class="bg-orange-500 text-white px-2 py-0.5 rounded-full text-[11px]">0</span>
            </button>
            <button onclick="switchAlertTab('expiry')" id="al-btn-expiry" class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                <span>⏳ بەسەرچوونی کاڵا</span>
                <span id="cnt-expiry" class="bg-purple-600 text-white px-2 py-0.5 rounded-full text-[11px]">0</span>
            </button>
        </div>

        <!-- هەڵبژاردنی ماوەی کاڵای مەیوو -->
        <div id="stagnantFilterBox" class="hidden items-center gap-2 text-xs">
            <span class="font-bold text-slate-500">نەفرۆشراو لە ماوەی:</span>
            <select id="stagnantDaysSelect" onchange="loadAlertsCenterData()" class="bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-1.5 font-bold">
                <option value="15">١٥ ڕۆژدا</option>
                <option value="30" selected>٣٠ ڕۆژدا (١ مانگ)</option>
                <option value="60">٦٠ ڕۆژدا (٢ مانگ)</option>
                <option value="90">٩٠ ڕۆژدا (٣ مانگ)</option>
            </select>
        </div>
    </div>

    <!-- ناوەڕۆکی خشتەکانی ئاگاداری -->
    <div class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-y-auto flex-1">
            <table class="w-full text-xs text-right border-collapse">
                <thead id="alertsTableHead" class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 sticky top-0"></thead>
                <tbody id="alertsTableBody" class="divide-y divide-slate-200 dark:divide-slate-800"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
window.currentAlertSubTab = 'debts';
window.alertsDataCache = {};

window.switchAlertTab = function(tab) {
    window.currentAlertSubTab = tab;
    ['debts', 'installments', 'stagnant', 'low_stock', 'expiry'].forEach(t => {
        const btn = document.getElementById('al-btn-' + t);
        if (!btn) return;
        if (t === tab) {
            btn.className = "px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 bg-emerald-600 text-white shadow";
        } else {
            btn.className = "px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300";
        }
    });

    const stagBox = document.getElementById('stagnantFilterBox');
    if (stagBox) {
        stagBox.className = (tab === 'stagnant') ? 'flex items-center gap-2 text-xs' : 'hidden items-center gap-2 text-xs';
    }
    window.renderAlertsActiveTable();
};

window.loadAlertsCenterData = function() {
    const days = document.getElementById('stagnantDaysSelect')?.value || 30;
    fetch('alerts_center.php?ajax_alerts=1&days=' + days)
        .then(r => r.json())
        .then(data => {
            window.alertsDataCache = data;
            document.getElementById('cnt-debts').innerText = data.debts.length;
            document.getElementById('cnt-installments').innerText = data.installments.length;
            document.getElementById('cnt-stagnant').innerText = data.stagnant.length;
            document.getElementById('cnt-low_stock').innerText = data.low_stock.length;
            document.getElementById('cnt-expiry').innerText = data.expiry.length;
            window.renderAlertsActiveTable();
        });
};

window.renderAlertsActiveTable = function() {
    const head = document.getElementById('alertsTableHead');
    const body = document.getElementById('alertsTableBody');
    if (!head || !body) return;

    const tab = window.currentAlertSubTab;
    const rows = window.alertsDataCache[tab] || [];

    if (tab === 'debts' || tab === 'installments') {
        head.innerHTML = `<tr>
            <th class="p-3 border-b dark:border-slate-700">ژ. وەسڵ</th>
            <th class="p-3 border-b dark:border-slate-700">کڕیار</th>
            <th class="p-3 border-b dark:border-slate-700">مۆبایل</th>
            <th class="p-3 border-b dark:border-slate-700">کەفیل</th>
            <th class="p-3 border-b dark:border-slate-700">کۆی وەسڵ</th>
            <th class="p-3 border-b dark:border-slate-700">واسڵکراو</th>
            <th class="p-3 border-b dark:border-slate-700">بڕی ماوە</th>
            <th class="p-3 border-b dark:border-slate-700">بەروار</th>
            <th class="p-3 border-b dark:border-slate-700">ماوەی تێپەڕیو</th>
        </tr>`;
        body.innerHTML = rows.length ? rows.map(r => `
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                <td class="p-3 font-mono font-bold">#${r.id}</td>
                <td class="p-3 font-bold">${r.customer_name}</td>
                <td class="p-3 font-mono">${r.customer_phone || '-'}</td>
                <td class="p-3">${r.guarantor_name || '-'}</td>
                <td class="p-3 font-mono">${formatMoney(r.total_iqd)}</td>
                <td class="p-3 font-mono text-emerald-600">${formatMoney(r.paid_iqd)}</td>
                <td class="p-3 font-mono font-bold text-red-600">${formatMoney(r.remaining_iqd)}</td>
                <td class="p-3 font-mono">${r.created_at}</td>
                <td class="p-3"><span class="bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300 px-2.5 py-1 rounded-lg font-bold">${r.days_passed} ڕۆژ</span></td>
            </tr>
        `).join('') : `<tr><td colspan="9" class="text-center py-8 text-slate-400">هیچ ئاگادارییەک نییە</td></tr>`;
    } else if (tab === 'stagnant') {
        head.innerHTML = `<tr>
            <th class="p-3 border-b dark:border-slate-700">بارکۆد</th>
            <th class="p-3 border-b dark:border-slate-700">ناوی کاڵا</th>
            <th class="p-3 border-b dark:border-slate-700">بەش</th>
            <th class="p-3 border-b dark:border-slate-700">بڕی ماوە لە کۆگا</th>
            <th class="p-3 border-b dark:border-slate-700">نرخی کڕین</th>
            <th class="p-3 border-b dark:border-slate-700">نرخی فرۆشتن</th>
            <th class="p-3 border-b dark:border-slate-700">کۆتا فرۆشتن</th>
        </tr>`;
        body.innerHTML = rows.length ? rows.map(r => `
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                <td class="p-3 font-mono">${r.barcode || '-'}</td>
                <td class="p-3 font-bold">${r.name}</td>
                <td class="p-3">${r.category || '-'}</td>
                <td class="p-3 font-mono font-bold text-blue-600">${parseFloat(r.stock)}</td>
                <td class="p-3 font-mono">${formatMoney(r.buy_price_iqd)}</td>
                <td class="p-3 font-mono">${formatMoney(r.sell_price_iqd)}</td>
                <td class="p-3"><span class="bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300 px-2.5 py-1 rounded-lg font-bold">${r.last_sold_date ? r.days_unsold + ' ڕۆژ پێش ئێستا' : 'هیچ کات نەفرۆشراوە'}</span></td>
            </tr>
        `).join('') : `<tr><td colspan="7" class="text-center py-8 text-slate-400">هیچ کاڵایەکی مەیوو نییە</td></tr>`;
    } else if (tab === 'low_stock') {
        head.innerHTML = `<tr>
            <th class="p-3 border-b dark:border-slate-700">بارکۆد</th>
            <th class="p-3 border-b dark:border-slate-700">ناوی کاڵا</th>
            <th class="p-3 border-b dark:border-slate-700">کۆمپانیا</th>
            <th class="p-3 border-b dark:border-slate-700">عەدەدی ماوە</th>
            <th class="p-3 border-b dark:border-slate-700">بارودۆخ</th>
        </tr>`;
        body.innerHTML = rows.length ? rows.map(r => `
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                <td class="p-3 font-mono">${r.barcode || '-'}</td>
                <td class="p-3 font-bold">${r.name}</td>
                <td class="p-3">${r.company_name || '-'}</td>
                <td class="p-3 font-mono font-extrabold text-red-600 text-sm">${parseFloat(r.stock)}</td>
                <td class="p-3"><span class="bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300 px-2.5 py-1 rounded-lg font-bold">${parseFloat(r.stock) <= 0 ? 'تەواو بووە (٠)' : 'کەمبووەتەوە'}</span></td>
            </tr>
        `).join('') : `<tr><td colspan="5" class="text-center py-8 text-slate-400">هیچ کاڵایەک کەم نەبووەتەوە</td></tr>`;
    } else if (tab === 'expiry') {
        head.innerHTML = `<tr>
            <th class="p-3 border-b dark:border-slate-700">بارکۆد</th>
            <th class="p-3 border-b dark:border-slate-700">ناوی کاڵا</th>
            <th class="p-3 border-b dark:border-slate-700">عەدەدی ماوە</th>
            <th class="p-3 border-b dark:border-slate-700">بەرواری بەسەرچوون</th>
            <th class="p-3 border-b dark:border-slate-700">دۆخی بەسەرچوون</th>
        </tr>`;
        body.innerHTML = rows.length ? rows.map(r => `
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                <td class="p-3 font-mono">${r.barcode || '-'}</td>
                <td class="p-3 font-bold">${r.name}</td>
                <td class="p-3 font-mono">${parseFloat(r.stock)}</td>
                <td class="p-3 font-mono font-bold">${r.expiry_date}</td>
                <td class="p-3"><span class="bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300 px-2.5 py-1 rounded-lg font-bold">${r.days_left <= 0 ? 'بەسەرچووە!' : r.days_left + ' ڕۆژی ماوە'}</span></td>
            </tr>
        `).join('') : `<tr><td colspan="5" class="text-center py-8 text-slate-400">هیچ کاڵایەکی نزیک لە بەسەرچوون نییە</td></tr>`;
    }
};

window.loadAlertsCenterData();
</script>