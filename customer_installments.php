<?php require_once 'db.php'; ?>

<div class="h-full w-full flex flex-col gap-3 overflow-hidden select-none" id="customerInstallmentsPageApp">

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 shadow-sm flex flex-wrap items-center justify-between gap-3 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-pink-50 dark:bg-pink-500/20 text-pink-600 dark:text-pink-400 flex items-center justify-center text-xl">
                📅
            </div>
            <div>
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white leading-tight">بەشی قیستەکان (قیستی کڕیاران)</h2>
                <p class="text-[11px] text-slate-500">چاودێریکردنی هەموو وەسڵەکانی قیست، زانیاری کەفیل، و وەرگرتنی قیستی مانگانە</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 flex-1 justify-end">
            <div class="flex items-center bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-1.5 min-w-[260px]">
                <span class="text-slate-400 ml-2">🔍</span>
                <input type="text" id="custInstSearchInput" oninput="renderCustomerInstallmentsTable()"
                    placeholder="گەڕان بە ناوی کڕیار، کەفیل، مۆبایل، ژمارەی وەسڵ (#)..."
                    class="w-full bg-transparent text-xs font-bold text-slate-900 dark:text-white focus:outline-none">
            </div>

            <div class="bg-pink-50 dark:bg-pink-950/40 border border-pink-200 dark:border-pink-500/40 rounded-xl px-4 py-1.5 text-xs">
                <span class="text-pink-800 dark:text-pink-300 font-bold">کۆی قیستی ماوە: </span>
                <b id="custInstTotalIQD" class="font-mono text-sm text-pink-600 dark:text-pink-400">0 د.ع</b>
                <span id="custInstTotalUSD" class="font-mono text-slate-500 mr-1">($0.00)</span>
            </div>
        </div>
    </div>

    <div class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-y-auto flex-1">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 font-bold border-b border-slate-200 dark:border-slate-800 sticky top-0">
                    <tr>
                        <th class="py-3 px-3 text-center w-24">ژ.وەسڵ</th>
                        <th class="py-3 px-3">ناوی کڕیار و مۆبایل</th>
                        <th class="py-3 px-3">ناوی کەفیل</th>
                        <th class="py-3 px-3 text-center">بەرواری وەسڵ</th>
                        <th class="py-3 px-3 text-center">کۆی وەسڵ</th>
                        <th class="py-3 px-3 text-center">پێشەکی / دراو</th>
                        <th class="py-3 px-3 text-center">ماوەی قیست</th>
                        <th class="py-3 px-3 text-center w-72">وەرگرتنی قیست / چاپکردن</th>
                    </tr>
                </thead>
                <tbody id="customerInstallmentsTableBody" class="divide-y divide-slate-200 dark:divide-slate-800"></tbody>
            </table>
        </div>
    </div>

</div>

<script>
window.custInstSalesData = [];

window.loadCustomerInstallmentsPageData = function() {
    fetch('api_sales.php?action=get_all_sales&sale_type=installment')
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                window.custInstSalesData = (res.data || []).filter(s => parseFloat(s.remaining_iqd) > 0);
                renderCustomerInstallmentsTable();
            }
        });
};

function renderCustomerInstallmentsTable() {
    const tbody = document.getElementById('customerInstallmentsTableBody');
    if (!tbody) return;
    const rate = window.POS_CONFIG.usdRate || 1500;
    const q = (document.getElementById('custInstSearchInput')?.value || '').trim().toLowerCase();

    const list = window.custInstSalesData.filter(s =>
        !q ||
        String(s.id).includes(q) ||
        (s.customer_name && s.customer_name.toLowerCase().includes(q)) ||
        (s.customer_phone && s.customer_phone.includes(q)) ||
        (s.guarantor_name && s.guarantor_name.toLowerCase().includes(q))
    );

    const totalRemIQD = list.reduce((sum, s) => sum + (parseFloat(s.remaining_iqd) || 0), 0);
    document.getElementById('custInstTotalIQD').innerText = Math.round(totalRemIQD).toLocaleString() + ' د.ع';
    document.getElementById('custInstTotalUSD').innerText = `($${(totalRemIQD / rate).toFixed(2)})`;

    if (list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="py-16 text-center text-emerald-600 font-bold">🎉 هیچ قیستێکی ماوە لەسەر کڕیاران نییە!</td></tr>`;
        return;
    }

    tbody.innerHTML = list.map(s => {
        const remIQD = parseFloat(s.remaining_iqd) || 0;
        return `
        <tr class="bg-pink-50/30 dark:bg-pink-950/15 hover:bg-pink-50/60 transition">
            <td class="py-2.5 px-3 text-center">
                <span class="bg-white dark:bg-slate-800 border border-pink-300 px-2.5 py-1 rounded-lg font-mono font-extrabold text-pink-700 dark:text-pink-400">
                    #${s.id}
                </span>
            </td>
            <td class="py-2.5 px-3">
                <div class="font-extrabold text-slate-900 dark:text-white">👤 ${s.customer_name}</div>
                <div class="text-[11px] font-mono text-slate-500">${s.customer_phone ? `📞 ${s.customer_phone}` : ''}</div>
            </td>
            <td class="py-2.5 px-3 font-bold text-amber-700 dark:text-amber-400">
                ${s.guarantor_name ? `🛡️ ${s.guarantor_name}` : '---'}
            </td>
            <td class="py-2.5 px-3 text-center font-mono">${s.created_at}</td>
            <td class="py-2.5 px-3 text-center font-mono font-bold">${Math.round(s.total_iqd).toLocaleString()} د.ع</td>
            <td class="py-2.5 px-3 text-center font-mono font-bold text-blue-600">${Math.round(s.paid_iqd).toLocaleString()} د.ع</td>
            <td class="py-2.5 px-3 text-center font-mono">
                <div class="font-extrabold text-pink-600 dark:text-pink-400 text-sm">${Math.round(remIQD).toLocaleString()} د.ع</div>
                <div class="text-[11px] text-slate-500 font-bold">$${(remIQD / rate).toFixed(2)}</div>
            </td>
            <td class="py-2.5 px-3 text-center">
                <div class="flex items-center justify-center gap-1.5">
                    <input type="number" step="any" id="custInstPayInp_${s.id}" placeholder="بڕی قیست..." value="${Math.round(remIQD)}"
                        class="w-28 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1 text-center font-mono font-bold text-xs">
                    <button onclick="payCustomerInstallment(${s.id})"
                        class="bg-pink-600 hover:bg-pink-500 text-white px-2.5 py-1 rounded-lg text-[11px] font-bold shadow-sm">
                        📅 وەرگرتنی قیست
                    </button>
                    <button onclick="window.open('print_receipt.php?id=${s.id}&format=a4','_blank','width=900,height=700')"
                        class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 p-1.5 rounded-lg text-xs" title="چاپکردن">🖨️</button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function payCustomerInstallment(saleId) {
    const inp = document.getElementById(`custInstPayInp_${saleId}`);
    const amountIQD = parseFloat(inp?.value) || 0;
    if (amountIQD <= 0) { alert('تکایە بڕی قیستی وەرگیراو بنووسە!'); return; }
    if (!confirm(`ئایا دڵنیایت لە وەرگرتنی قیست بە بڕی (${Math.round(amountIQD).toLocaleString()} د.ع)؟`)) return;

    fetch('api_sales.php?action=pay_sale_remaining', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ sale_id: saleId, amount_iqd: amountIQD })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            window.refreshAllPurchaseModules();
        } else {
            alert(res.message);
        }
    });
}

window.loadCustomerInstallmentsPageData();
</script>