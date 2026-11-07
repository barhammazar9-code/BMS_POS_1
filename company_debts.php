<?php require_once 'db.php'; ?>

<div class="h-full w-full flex flex-col gap-3 overflow-hidden select-none" id="companyDebtsPageApp">

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 shadow-sm flex flex-wrap items-center justify-between gap-3 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl">
                💳
            </div>
            <div>
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white leading-tight">قەرزی کۆمپانیاکان (وەسڵە قەرزەکان)</h2>
                <p class="text-[11px] text-slate-500">گەڕان بەدوای وەسڵە قەرزەکان و دانەوەی قەرزی هەر کۆمپانیایەک</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 flex-1 justify-end">
            <!-- خانەی گەڕان لە قەرزی کۆمپانیاکان -->
            <div class="flex items-center bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-1.5 min-w-[250px]">
                <span class="text-slate-400 ml-2">🔍</span>
                <input type="text" id="cdPageSearchInput" oninput="renderStandaloneCompanyDebtsTable()"
                    placeholder="گەڕان بە ناوی کۆمپانیا، ژمارەی وەسڵ، بەروار..."
                    class="w-full bg-transparent text-xs font-bold text-slate-900 dark:text-white focus:outline-none">
            </div>

            <select id="cdPageCompanySelect" onchange="renderStandaloneCompanyDebtsTable()"
                class="bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs font-bold">
                <option value="all">هەموو کۆمپانیاکان</option>
            </select>

            <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-500/40 rounded-xl px-4 py-1.5 text-xs">
                <span class="text-rose-800 dark:text-rose-300 font-bold">کۆی قەرز: </span>
                <b id="cdPageTotalIQD" class="font-mono text-sm text-rose-600 dark:text-rose-400">0 د.ع</b>
                <span id="cdPageTotalUSD" class="font-mono text-slate-500 mr-1">($0.00)</span>
            </div>
        </div>
    </div>

    <div class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-y-auto flex-1">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 font-bold border-b border-slate-200 dark:border-slate-800 sticky top-0">
                    <tr>
                        <th class="py-3 px-3 text-center w-32">ژمارەی وەسڵ</th>
                        <th class="py-3 px-3">هی چ کۆمپانیایەکە</th>
                        <th class="py-3 px-3 text-center">بەرواری وەسڵ</th>
                        <th class="py-3 px-3 text-center">کۆی گشتی وەسڵ</th>
                        <th class="py-3 px-3 text-center">پارەی دراو</th>
                        <th class="py-3 px-3 text-center">بڕی قەرزی ماوە</th>
                        <th class="py-3 px-3 text-center w-64">دانەوەی قەرزی وەسڵ</th>
                    </tr>
                </thead>
                <tbody id="standaloneCompanyDebtsBody" class="divide-y divide-slate-200 dark:divide-slate-800"></tbody>
            </table>
        </div>
    </div>

</div>

<script>
window.cdPagePurchases = [];
window.cdPageCompanies = [];

window.loadCompanyDebtsPageData = function() {
    fetch('api_purchases.php?action=get_overview')
        .then(r => r.json())
        .then(res => {
            if (res.status !== 'success') return;
            window.cdPagePurchases = res.purchases || [];
            window.cdPageCompanies = res.companies || [];

            const sel = document.getElementById('cdPageCompanySelect');
            if (sel) {
                const cur = sel.value || 'all';
                sel.innerHTML = `<option value="all">هەموو کۆمپانیاکان</option>` +
                    window.cdPageCompanies.map(c => `<option value="${c.id}">🏢 ${c.name}</option>`).join('');
                sel.value = cur;
            }
            renderStandaloneCompanyDebtsTable();
        });
};

function renderStandaloneCompanyDebtsTable() {
    const tbody = document.getElementById('standaloneCompanyDebtsBody');
    if (!tbody) return;
    const rate = window.POS_CONFIG.usdRate || 1500;
    const compFilter = document.getElementById('cdPageCompanySelect')?.value || 'all';
    const q = (document.getElementById('cdPageSearchInput')?.value || '').trim().toLowerCase();

    const debtList = window.cdPagePurchases.filter(p => {
        const hasDebt = parseFloat(p.remaining_iqd) > 0;
        const matchComp = compFilter === 'all' || String(p.company_id) === String(compFilter);
        const matchSearch = !q ||
            (p.company_name && p.company_name.toLowerCase().includes(q)) ||
            (p.invoice_number && p.invoice_number.toLowerCase().includes(q)) ||
            (p.purchase_date && p.purchase_date.includes(q));
        return hasDebt && matchComp && matchSearch;
    });

    const totalDebtIQD = debtList.reduce((s, p) => s + (parseFloat(p.remaining_iqd) || 0), 0);
    document.getElementById('cdPageTotalIQD').innerText = Math.round(totalDebtIQD).toLocaleString() + ' د.ع';
    document.getElementById('cdPageTotalUSD').innerText = `($${(totalDebtIQD / rate).toFixed(2)})`;

    if (debtList.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="py-16 text-center text-emerald-600 font-bold">هیچ وەسڵێکی قەرز نەدۆزرایەوە!</td></tr>`;
        return;
    }

    tbody.innerHTML = debtList.map(p => {
        const remIQD = parseFloat(p.remaining_iqd) || 0;
        return `
        <tr class="bg-rose-50/30 dark:bg-rose-950/15 hover:bg-rose-50/60 transition">
            <td class="py-2.5 px-3 text-center">
                <span class="bg-white dark:bg-slate-800 border border-rose-300 px-2.5 py-1 rounded-lg font-mono font-extrabold text-rose-700 dark:text-rose-400">
                    ${p.invoice_number}
                </span>
            </td>
            <td class="py-2.5 px-3 font-extrabold text-slate-900 dark:text-white">🏢 ${p.company_name}</td>
            <td class="py-2.5 px-3 text-center font-mono">${p.purchase_date}</td>
            <td class="py-2.5 px-3 text-center font-mono font-bold">${Math.round(p.total_iqd).toLocaleString()} د.ع</td>
            <td class="py-2.5 px-3 text-center font-mono font-bold text-blue-600">${Math.round(p.paid_iqd).toLocaleString()} د.ع</td>
            <td class="py-2.5 px-3 text-center font-mono">
                <div class="font-extrabold text-rose-600 dark:text-rose-400 text-sm">${Math.round(remIQD).toLocaleString()} د.ع</div>
                <div class="text-[11px] text-slate-500 font-bold">$${(remIQD / rate).toFixed(2)}</div>
            </td>
            <td class="py-2.5 px-3 text-center">
                <div class="flex items-center justify-center gap-1.5">
                    <input type="number" step="any" id="cdPayInput_${p.id}" value="${Math.round(remIQD)}"
                        class="w-28 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1 text-center font-mono font-bold text-xs">
                    <button onclick="submitStandaloneDebtPayment(${p.id})"
                        class="bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-1 rounded-lg text-[11px] font-bold shadow-sm">
                        💵 دانەوەی قەرز
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function submitStandaloneDebtPayment(purchaseId) {
    const inp = document.getElementById(`cdPayInput_${purchaseId}`);
    const amountIQD = parseFloat(inp?.value) || 0;
    if (amountIQD <= 0) { alert('تکایە بڕی پارە بنووسە!'); return; }
    if (!confirm(`ئایا دڵنیایت لە دانەوەی بڕی (${Math.round(amountIQD).toLocaleString()} د.ع)؟`)) return;

    fetch('api_purchases.php?action=pay_debt', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ purchase_id: purchaseId, amount_iqd: amountIQD })
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

window.loadCompanyDebtsPageData();
</script>