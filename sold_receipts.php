<?php require_once 'db.php'; ?>

<div class="h-full w-full flex flex-col gap-3 overflow-hidden select-none" id="soldReceiptsApp">

    <!-- ١. بەشی سەرەوە: کورتەی ئامار + گەڕان و فلتەرکردن -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 shadow-sm flex flex-wrap items-center justify-between gap-3 shrink-0">
        
        <!-- ناونیشان و دوگمەی نوێکردنەوە -->
        <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xl font-bold">
                🧾
            </div>
            <div>
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white leading-tight">وەسڵە فرۆشراوەکان</h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">بینینی وەسڵەکان، گەڕاندنەوە و کەمکردنەوەی کاڵا بۆ کۆگا</p>
            </div>
            <button onclick="loadSoldReceipts()" class="mr-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1" title="نوێکردنەوەی لیست">
                <span>🔄</span>
                <span>نوێکردنەوە</span>
            </button>
        </div>

        <!-- فلتەرەکان: گەڕان بە ژمارەی وەسڵ / ناو + جۆری وەسڵ + بەروار -->
        <div class="flex flex-wrap items-center gap-2 flex-1 max-w-3xl justify-end">
            
            <!-- خانەی گەڕان -->
            <div class="flex items-center bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-1.5 flex-1 min-w-[220px]">
                <span class="text-slate-400 ml-2">🔍</span>
                <input type="text" id="srSearchInput" oninput="loadSoldReceipts()"
                    placeholder="گەڕان بە ژمارەی وەسڵ (#)، ناوی کڕیار، مۆبایل، کەفیل..."
                    class="w-full bg-transparent text-xs font-bold text-slate-900 dark:text-white focus:outline-none">
            </div>

            <!-- فلتەری جۆری وەسڵ -->
            <select id="srTypeFilter" onchange="loadSoldReceipts()"
                class="bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs font-bold text-slate-800 dark:text-slate-200 focus:outline-none">
                <option value="all">هەموو جۆرەکان</option>
                <option value="cash">💵 نەقد</option>
                <option value="debt">📒 قەرز</option>
                <option value="installment">📅 قیست</option>
                <option value="quote">📄 دەرخستە</option>
            </select>

            <!-- فلتەری بەروار -->
            <input type="date" id="srDateFilter" onchange="loadSoldReceipts()"
                class="bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs font-mono font-bold text-slate-800 dark:text-slate-200 focus:outline-none">

            <button onclick="resetSrFilters()" class="bg-slate-200/70 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 px-2.5 py-1.5 rounded-xl text-xs font-bold transition">
                پاککردنەوە
            </button>
        </div>
    </div>

    <!-- ٢. کارتی کورتەی ژمارە و کۆی نرخی وەسڵەکان -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5 shrink-0">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 flex items-center justify-between shadow-sm">
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-bold block">ژمارەی وەسڵەکان</span>
                <span id="srStatCount" class="text-lg font-extrabold text-slate-900 dark:text-white font-mono">0</span>
            </div>
            <span class="text-2xl">🧾</span>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-emerald-200 dark:border-emerald-500/30 rounded-xl px-4 py-2.5 flex items-center justify-between shadow-sm">
            <div>
                <span class="text-xs text-emerald-700 dark:text-emerald-400 font-bold block">کۆی صافی فرۆشتن</span>
                <span id="srStatTotalIQD" class="text-base font-extrabold text-emerald-600 dark:text-emerald-400 font-mono">0 د.ع</span>
                <span id="srStatTotalUSD" class="text-[11px] font-bold text-slate-500 font-mono block">$0.00</span>
            </div>
            <span class="text-2xl">💰</span>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-blue-200 dark:border-blue-500/30 rounded-xl px-4 py-2.5 flex items-center justify-between shadow-sm">
            <div>
                <span class="text-xs text-blue-700 dark:text-blue-400 font-bold block">کۆی پارەی وەرگیراو</span>
                <span id="srStatPaidIQD" class="text-base font-extrabold text-blue-600 dark:text-blue-400 font-mono">0 د.ع</span>
                <span id="srStatPaidUSD" class="text-[11px] font-bold text-slate-500 font-mono block">$0.00</span>
            </div>
            <span class="text-2xl">💵</span>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-red-200 dark:border-red-500/30 rounded-xl px-4 py-2.5 flex items-center justify-between shadow-sm">
            <div>
                <span class="text-xs text-red-700 dark:text-red-400 font-bold block">کۆی ماوە (قەرز و قیست)</span>
                <span id="srStatRemIQD" class="text-base font-extrabold text-red-600 dark:text-red-400 font-mono">0 د.ع</span>
                <span id="srStatRemUSD" class="text-[11px] font-bold text-slate-500 font-mono block">$0.00</span>
            </div>
            <span class="text-2xl">📒</span>
        </div>
    </div>

    <!-- ٣. خشتەی وەسڵە فرۆشراوەکان -->
    <div class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-y-auto flex-1">
            <table class="w-full text-right text-sm">
                <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-10 text-xs font-bold">
                    <tr>
                        <th class="py-3 px-3 text-center w-24">ژ.وەسڵ</th>
                        <th class="py-3 px-3">ناوی کڕیار و زانیاری</th>
                        <th class="py-3 px-3 text-center w-28">جۆری وەسڵ</th>
                        <th class="py-3 px-3 text-center w-24">ژ.کاڵا</th>
                        <th class="py-3 px-3 text-center w-36">کۆی وەسڵ (صافی)</th>
                        <th class="py-3 px-3 text-center w-32">پارەی دراو</th>
                        <th class="py-3 px-3 text-center w-32">ماوە (قەرز)</th>
                        <th class="py-3 px-3 text-center w-40">بەروار و کات</th>
                        <th class="py-3 px-3 text-center w-56">کردارەکان / گەڕاندنەوە</th>
                    </tr>
                </thead>
                <tbody id="soldReceiptsTableBody" class="divide-y divide-slate-200 dark:divide-slate-800">
                    <!-- وەسڵەکان لێرە دەردەکەون -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- ٤. مۆداڵی بینینی ناوەڕۆکی وەسڵ و گەڕاندنەوە / کەمکردنەوەی کاڵاکان -->
    <div id="receiptDetailModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-2xl w-full max-w-5xl max-h-[90vh] flex flex-col shadow-2xl overflow-hidden">
            
            <!-- سەرپەڕەی مۆداڵ -->
            <div class="bg-slate-100 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 px-5 py-3.5 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🧾</span>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                            <span>وردەکاری وەسڵی ژمارە</span>
                            <span id="modalReceiptIdBadge" class="bg-emerald-600 text-white px-2.5 py-0.5 rounded-lg font-mono text-sm">#0</span>
                            <span id="modalSaleTypeBadge"></span>
                        </h3>
                        <p id="modalCustomerInfo" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5"></p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button onclick="openReceiptInPOS()" class="bg-blue-600 hover:bg-blue-500 text-white px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-sm">
                        <span>✏️</span>
                        <span>کردنەوە لە شاشەی فرۆشتن</span>
                    </button>
                    <button onclick="printModalReceipt('thermal')" class="bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-sm">
                        <span>🖨️</span>
                        <span>کاشێر</span>
                    </button>
                    <button onclick="printModalReceipt('a4')" class="bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-sm">
                        <span>📄</span>
                        <span>A4</span>
                    </button>
                    <button onclick="closeReceiptModal()" class="bg-slate-200 hover:bg-red-500 hover:text-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 w-8 h-8 rounded-xl font-bold text-sm transition flex items-center justify-center">
                        ✕
                    </button>
                </div>
            </div>

            <!-- خشتەی کاڵاکانی ناو وەسڵەکە لەگەڵ دوگمەی گەڕاندنەوە و کەمکردنەوە -->
            <div class="p-4 overflow-y-auto flex-1">
                <div class="mb-3 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-500/30 rounded-xl px-3.5 py-2 text-xs text-amber-800 dark:text-amber-300 flex items-center justify-between">
                    <span>💡 <b>ڕێنمایی گەڕاندنەوە:</b> لە تەنیشت هەر کاڵایەک عەدەدی گەڕاوە دیاری بکە و کلیک لە <b>«↩️ گەڕاندنەوە»</b> بکە بۆ ئەوەی لە وەسڵەکە کەم ببێتەوە و ڕاستەوخۆ بچێتەوە ناو کۆگا.</span>
                </div>

                <table class="w-full text-right text-xs border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                    <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 font-bold border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="py-2.5 px-3 text-center w-10">#</th>
                            <th class="py-2.5 px-3">ناوی کاڵا و کۆگا</th>
                            <th class="py-2.5 px-3 text-center">زەمان (S/N و ماوە)</th>
                            <th class="py-2.5 px-3 text-center w-28">عەدەدی فرۆشراو</th>
                            <th class="py-2.5 px-3 text-center w-32">نرخی تاک</th>
                            <th class="py-2.5 px-3 text-center w-36">کۆی گشتی</th>
                            <th class="py-2.5 px-3 text-center w-64">گەڕاندنەوەی کاڵا بۆ کۆگا</th>
                        </tr>
                    </thead>
                    <tbody id="modalItemsTableBody" class="divide-y divide-slate-200 dark:divide-slate-800">
                        <!-- کاڵاکانی وەسڵەکە لێرە دەردەکەون -->
                    </tbody>
                </table>
            </div>

            <!-- ژێرپەڕەی مۆداڵ: کورتەی پارەی وەسڵ + هەڵوەشاندنەوەی تەواوی وەسڵ -->
            <div class="bg-slate-100 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 px-5 py-3 flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-4 text-xs">
                    <div>کۆی گشتی: <b id="modalSubtotal" class="font-mono text-slate-900 dark:text-white">0 د.ع</b></div>
                    <div>داشکان: <b id="modalDiscount" class="font-mono text-amber-600">0 د.ع</b></div>
                    <div class="text-sm">صافی وەسڵ: <b id="modalNetTotal" class="font-mono text-emerald-600 dark:text-emerald-400">0 د.ع</b></div>
                    <div>پارەی دراو: <b id="modalPaid" class="font-mono text-blue-600">0 د.ع</b></div>
                    <div>ماوە (قەرز): <b id="modalRemaining" class="font-mono text-red-600">0 د.ع</b></div>
                </div>

                <div class="flex items-center gap-2">
                    <button onclick="deleteEntireReceiptFromModal()" class="bg-red-600 hover:bg-red-500 text-white px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-sm">
                        <span>🗑️</span>
                        <span>گەڕاندنەوەی تەواوی وەسڵەکە بۆ کۆگا</span>
                    </button>
                    <button onclick="closeReceiptModal()" class="bg-slate-300 dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-4 py-2 rounded-xl text-xs font-bold">
                        داخستن
                    </button>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
window.currentModalSaleId = 0;

const saleTypeMeta = {
    cash: { label: '💵 نەقد', cls: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-500/40' },
    debt: { label: '📒 قەرز', cls: 'bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-300 border border-red-300 dark:border-red-500/40' },
    installment: { label: '📅 قیست', cls: 'bg-purple-100 text-purple-800 dark:bg-purple-500/20 dark:text-purple-300 border border-purple-300 dark:border-purple-500/40' },
    quote: { label: '📄 دەرخستە', cls: 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300 border border-amber-300 dark:border-amber-500/40' }
};

const srRowColors = [
    'bg-emerald-50/40 dark:bg-emerald-950/15 hover:bg-emerald-100/50 dark:hover:bg-emerald-900/25',
    'bg-sky-50/40 dark:bg-sky-950/15 hover:bg-sky-100/50 dark:hover:bg-sky-900/25',
    'bg-amber-50/40 dark:bg-amber-950/15 hover:bg-amber-100/50 dark:hover:bg-amber-900/25',
    'bg-purple-50/40 dark:bg-purple-950/15 hover:bg-purple-100/50 dark:hover:bg-purple-900/25'
];

// ١. هێنانی لیستی وەسڵە فرۆشراوەکان
let srTimer = null;
function loadSoldReceipts() {
    clearTimeout(srTimer);
    srTimer = setTimeout(() => {
        const q = document.getElementById('srSearchInput')?.value.trim() || '';
        const type = document.getElementById('srTypeFilter')?.value || 'all';
        const date = document.getElementById('srDateFilter')?.value || '';

        fetch(`api_sales.php?action=get_all_sales&q=${encodeURIComponent(q)}&sale_type=${encodeURIComponent(type)}&date=${encodeURIComponent(date)}`)
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    renderSoldReceiptsTable(res.data || []);
                }
            });
    }, 120);
}

function resetSrFilters() {
    document.getElementById('srSearchInput').value = '';
    document.getElementById('srTypeFilter').value = 'all';
    document.getElementById('srDateFilter').value = '';
    loadSoldReceipts();
}

function renderSoldReceiptsTable(sales) {
    const tbody = document.getElementById('soldReceiptsTableBody');
    if (!tbody) return;
    const rate = window.POS_CONFIG.usdRate || 1500;

    let sumTotalIQD = 0;
    let sumPaidIQD = 0;
    let sumRemIQD = 0;

    sales.forEach(s => {
        if (s.sale_type !== 'quote') {
            sumTotalIQD += parseFloat(s.total_iqd) || 0;
            sumPaidIQD += parseFloat(s.paid_iqd) || 0;
            sumRemIQD += parseFloat(s.remaining_iqd) || 0;
        }
    });

    document.getElementById('srStatCount').innerText = sales.length;
    document.getElementById('srStatTotalIQD').innerText = Math.round(sumTotalIQD).toLocaleString() + ' د.ع';
    document.getElementById('srStatTotalUSD').innerText = '$' + (sumTotalIQD / rate).toFixed(2);
    document.getElementById('srStatPaidIQD').innerText = Math.round(sumPaidIQD).toLocaleString() + ' د.ع';
    document.getElementById('srStatPaidUSD').innerText = '$' + (sumPaidIQD / rate).toFixed(2);
    document.getElementById('srStatRemIQD').innerText = Math.round(sumRemIQD).toLocaleString() + ' د.ع';
    document.getElementById('srStatRemUSD').innerText = '$' + (sumRemIQD / rate).toFixed(2);

    if (sales.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="py-16 text-center text-slate-400 dark:text-slate-500 font-bold">
                    هیچ وەسڵێکی فرۆشراو نەدۆزرایەوە
                </td>
            </tr>`;
        return;
    }

    tbody.innerHTML = sales.map((s, idx) => {
        const meta = saleTypeMeta[s.sale_type] || saleTypeMeta.cash;
        const rowColor = srRowColors[idx % srRowColors.length];
        const sRate = parseFloat(s.usd_rate) || rate;
        const totalIQD = parseFloat(s.total_iqd) || 0;
        const totalUSD = (totalIQD / sRate).toFixed(2);
        const paidIQD = parseFloat(s.paid_iqd) || 0;
        const remIQD = parseFloat(s.remaining_iqd) || 0;

        return `
        <tr class="${rowColor} transition">
            <td class="py-2.5 px-3 text-center">
                <span class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 px-2.5 py-1 rounded-lg font-mono font-extrabold text-emerald-700 dark:text-emerald-400 text-xs">
                    #${s.id}
                </span>
            </td>
            <td class="py-2.5 px-3">
                <div class="font-bold text-slate-900 dark:text-white">${s.customer_name}</div>
                <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                    ${s.customer_phone ? `<span>📞 ${s.customer_phone}</span>` : ''}
                    ${s.guarantor_name ? `<span>🛡️ کەفیل: ${s.guarantor_name}</span>` : ''}
                </div>
            </td>
            <td class="py-2.5 px-3 text-center">
                <span class="px-2.5 py-1 rounded-lg text-xs font-bold ${meta.cls}">
                    ${meta.label}
                </span>
            </td>
            <td class="py-2.5 px-3 text-center font-mono text-xs font-bold text-slate-700 dark:text-slate-300">
                ${s.items_count} جۆر (${parseFloat(s.total_qty)})
            </td>
            <td class="py-2.5 px-3 text-center font-mono">
                <div class="font-extrabold text-slate-900 dark:text-white">${Math.round(totalIQD).toLocaleString()} د.ع</div>
                <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold">$${totalUSD}</div>
            </td>
            <td class="py-2.5 px-3 text-center font-mono font-bold text-blue-600 dark:text-blue-400 text-xs">
                ${Math.round(paidIQD).toLocaleString()} د.ع
            </td>
            <td class="py-2.5 px-3 text-center font-mono font-bold ${remIQD > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-400'} text-xs">
                ${Math.round(remIQD).toLocaleString()} د.ع
            </td>
            <td class="py-2.5 px-3 text-center font-mono text-xs text-slate-600 dark:text-slate-400">
                ${s.created_at}
            </td>
            <td class="py-2.5 px-3 text-center">
                <div class="flex items-center justify-center gap-1.5">
                    <button onclick="openReceiptModal(${s.id})"
                        class="bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 shadow-sm">
                        <span>👁️</span>
                        <span>بینین / گەڕاندنەوە</span>
                    </button>
                    <button onclick="window.open('print_receipt.php?id=${s.id}&format=thermal', '_blank', 'width=900,height=700')"
                        class="bg-white dark:bg-slate-800 hover:bg-slate-100 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 p-1.5 rounded-lg text-xs transition" title="چاپکردن">
                        🖨️
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

// ٢. کردنەوەی مۆداڵی وردەکاری وەسڵ و گەڕاندنەوەی کاڵاکان
function openReceiptModal(saleId) {
    window.currentModalSaleId = saleId;
    fetch(`api_sales.php?action=get_receipt&id=${saleId}&dir=exact`)
        .then(r => r.json())
        .then(res => {
            if (res.status !== 'success') {
                alert(res.message || 'وەسڵ نەدۆزرایەوە');
                return;
            }
            populateModalDetails(res.sale, res.items);
            const modal = document.getElementById('receiptDetailModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
}

function closeReceiptModal() {
    const modal = document.getElementById('receiptDetailModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function populateModalDetails(sale, items) {
    const rate = parseFloat(sale.usd_rate) || window.POS_CONFIG.usdRate || 1500;
    const meta = saleTypeMeta[sale.sale_type] || saleTypeMeta.cash;

    document.getElementById('modalReceiptIdBadge').innerText = `#${sale.id}`;
    document.getElementById('modalSaleTypeBadge').innerHTML = `<span class="px-2.5 py-0.5 rounded-lg text-xs font-bold ${meta.cls}">${meta.label}</span>`;
    document.getElementById('modalCustomerInfo').innerText =
        `👤 کڕیار: ${sale.customer_name} ` +
        (sale.customer_phone ? ` | 📞 مۆبایل: ${sale.customer_phone}` : '') +
        (sale.guarantor_name ? ` | 🛡️ کەفیل: ${sale.guarantor_name}` : '') +
        ` | 🕒 بەروار: ${sale.created_at}`;

    document.getElementById('modalSubtotal').innerText = `${Math.round(sale.subtotal_iqd).toLocaleString()} د.ع ($${(sale.subtotal_iqd / rate).toFixed(2)})`;
    document.getElementById('modalDiscount').innerText = `${Math.round(sale.discount_iqd).toLocaleString()} د.ع`;
    document.getElementById('modalNetTotal').innerText = `${Math.round(sale.total_iqd).toLocaleString()} د.ع ($${(sale.total_iqd / rate).toFixed(2)})`;
    document.getElementById('modalPaid').innerText = `${Math.round(sale.paid_iqd).toLocaleString()} د.ع`;
    document.getElementById('modalRemaining').innerText = `${Math.round(sale.remaining_iqd).toLocaleString()} د.ع`;

    const tbody = document.getElementById('modalItemsTableBody');
    tbody.innerHTML = items.map((item, idx) => {
        const qty = parseFloat(item.qty);
        const unitIQD = parseFloat(item.sell_price_iqd);
        const rowTotalIQD = parseFloat(item.total_iqd);
        const isExt = parseInt(item.is_external) === 1;
        const isGift = parseInt(item.is_gift) === 1;

        return `
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
            <td class="py-2.5 px-3 text-center font-mono font-bold text-slate-500">${idx + 1}</td>
            <td class="py-2.5 px-3">
                <div class="font-bold text-slate-900 dark:text-white flex flex-wrap items-center gap-1.5">
                    <span>${item.item_name}</span>
                    ${isExt ? '<span class="text-[10px] bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded font-bold">⚡ دەرەکی</span>' : `<span class="text-[10px] bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 px-1.5 py-0.5 rounded font-bold">📦 کۆگای ئێستا: ${item.stock}</span>`}
                    ${isGift ? '<span class="text-[10px] bg-pink-100 text-pink-700 px-1.5 py-0.5 rounded font-bold">🎁 دیاری</span>' : ''}
                </div>
            </td>
            <td class="py-2.5 px-3 text-center font-mono text-[11px] text-slate-600 dark:text-slate-400">
                ${item.serial_number ? `<div>S/N: <b>${item.serial_number}</b></div>` : ''}
                ${item.warranty ? `<div>زەمان: <b>${item.warranty}</b></div>` : ''}
                ${!item.serial_number && !item.warranty ? '---' : ''}
            </td>
            <td class="py-2.5 px-3 text-center font-mono font-extrabold text-sm text-emerald-700 dark:text-emerald-400">
                ${qty}
            </td>
            <td class="py-2.5 px-3 text-center font-mono font-bold text-slate-800 dark:text-slate-200">
                ${Math.round(unitIQD).toLocaleString()} د.ع
            </td>
            <td class="py-2.5 px-3 text-center font-mono font-extrabold text-slate-900 dark:text-white">
                ${Math.round(rowTotalIQD).toLocaleString()} د.ع
            </td>
            <td class="py-2.5 px-3 text-center">
                <div class="flex items-center justify-center gap-1.5">
                    <input type="number" step="any" min="0.01" max="${qty}" value="1" id="retQty_${item.id}"
                        class="w-16 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1 text-center font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:border-amber-500"
                        title="بڕی گەڕاوە بنووسە">
                    <button onclick="returnItemQty(${sale.id}, ${item.id}, false, ${qty})"
                        class="bg-amber-500 hover:bg-amber-600 text-white px-2.5 py-1 rounded-lg text-[11px] font-bold transition shadow-sm"
                        title="کەمکردنەوە و گەڕاندنەوەی ئەم عەدەدە بۆ کۆگا">
                        ↩️ گەڕاندنەوە
                    </button>
                    <button onclick="returnItemQty(${sale.id}, ${item.id}, true, ${qty})"
                        class="bg-red-100 hover:bg-red-600 text-red-700 hover:text-white dark:bg-red-500/20 dark:text-red-300 px-2 py-1 rounded-lg text-[11px] font-bold transition"
                        title="گەڕاندنەوەی تەواوی ئەم کاڵایە (${qty} دانە) بۆ کۆگا">
                        هەمووی
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

// ٣. گەڕاندنەوەی بەشێک یان تەواوی کاڵایەک بۆ کۆگا
function returnItemQty(saleId, itemId, returnAll, maxQty) {
    const qtyInput = document.getElementById(`retQty_${itemId}`);
    const returnQty = returnAll ? maxQty : (parseFloat(qtyInput?.value) || 0);

    if (returnQty <= 0 || returnQty > maxQty) {
        alert(`تکایە عەدەدێکی دروست لە نێوان 1 تا ${maxQty} دیاری بکە!`);
        return;
    }

    if (!confirm(`ئایا دڵنیایت لە گەڕاندنەوەی (${returnQty}) دانە لەم کاڵایە بۆ ناو کۆگا؟`)) {
        return;
    }

    fetch('api_sales.php?action=return_sale_item', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            sale_id: saleId,
            item_id: itemId,
            return_qty: returnQty
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            loadSoldReceipts();
            if (res.receipt_deleted) {
                alert('هەموو کاڵاکانی ناو وەسڵەکە گەڕێندرانەوە بۆ کۆگا و وەسڵەکە سڕایەوە.');
                closeReceiptModal();
            } else {
                openReceiptModal(saleId);
            }
        } else {
            alert('هەڵە: ' + res.message);
        }
    });
}

// ٤. هەڵوەشاندنەوە و گەڕاندنەوەی تەواوی وەسڵەکە بەیەکجار بۆ کۆگا
function deleteEntireReceiptFromModal() {
    const saleId = window.currentModalSaleId;
    if (!saleId) return;

    if (!confirm(`ئایا دڵنیایت لە هەڵوەشاندنەوەی تەواوی وەسڵی ژمارە #${saleId} و گەڕاندنەوەی هەموو کاڵاکانی بۆ کۆگا؟`)) {
        return;
    }

    fetch('api_sales.php?action=delete_entire_sale', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ sale_id: saleId })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            closeReceiptModal();
            loadSoldReceipts();
        } else {
            alert('هەڵە: ' + res.message);
        }
    });
}

// ٥. کردنەوەی وەسڵەکە ڕاستەوخۆ لە شاشەی فرۆشتن (sales.php)
function openReceiptInPOS() {
    const saleId = window.currentModalSaleId;
    if (!saleId) return;
    closeReceiptModal();

    if (typeof openTab === 'function') {
        openTab('sales', 'بەشی فرۆشتن', '🛒', 'sales.php');
        setTimeout(() => {
            const inp = document.getElementById('receiptNumberInput');
            if (inp && typeof loadExactReceipt === 'function') {
                inp.value = saleId;
                loadExactReceipt();
            }
        }, 300);
    }
}

function printModalReceipt(format) {
    if (!window.currentModalSaleId) return;
    window.open(`print_receipt.php?id=${window.currentModalSaleId}&format=${format}`, '_blank', 'width=900,height=700');
}

loadSoldReceipts();
</script>