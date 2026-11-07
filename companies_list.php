<?php require_once 'db.php'; ?>

<div class="h-full w-full flex flex-col gap-3 overflow-hidden select-none" id="companiesPageApp">

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 shadow-sm flex flex-wrap items-center justify-between gap-3 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl">
                🏢
            </div>
            <div>
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white leading-tight">لیستی کۆمپانیاکان</h2>
                <p class="text-[11px] text-slate-500">تۆمارکردنی ناوی کۆمپانیاکان و بینینی ژمارەی وەسڵ و کۆی گشتی کڕین و قەرزەکانیان</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <input type="text" id="compPageSearchInput" oninput="renderStandaloneCompaniesTable()"
                placeholder="🔍 گەڕان بە ناوی کۆمپانیا یان مۆبایل..."
                class="w-72 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs font-bold focus:outline-none focus:border-indigo-500">
            <button onclick="openStandaloneCompanyModal()"
                class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-xl text-xs font-extrabold transition shadow-sm">
                ➕ زیادکردنی کۆمپانیای نوێ
            </button>
        </div>
    </div>

    <div class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-y-auto flex-1">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 font-bold border-b border-slate-200 dark:border-slate-800 sticky top-0">
                    <tr>
                        <th class="py-3 px-3 text-center w-14">#</th>
                        <th class="py-3 px-3">ناوی کۆمپانیا</th>
                        <th class="py-3 px-3">ژمارەی مۆبایل</th>
                        <th class="py-3 px-3">ناونیشان و تێبینی</th>
                        <th class="py-3 px-3 text-center">ژمارەی وەسڵەکان</th>
                        <th class="py-3 px-3 text-center">کۆی گشتی کڕین</th>
                        <th class="py-3 px-3 text-center">کۆی قەرزی ماوە</th>
                        <th class="py-3 px-3 text-center w-40">کردارەکان</th>
                    </tr>
                </thead>
                <tbody id="standaloneCompaniesTableBody" class="divide-y divide-slate-200 dark:divide-slate-800"></tbody>
            </table>
        </div>
    </div>

    <div id="standaloneCompanyModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-2xl w-full max-w-md p-5 shadow-2xl">
            <h3 class="text-base font-extrabold text-slate-900 dark:text-white mb-4">🏢 زانیاری کۆمپانیا</h3>
            <input type="hidden" id="scModalId" value="0">
            <div class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-600 dark:text-slate-400 mb-1">ناوی کۆمپانیا:</label>
                    <input type="text" id="scModalName" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 font-bold">
                </div>
                <div>
                    <label class="block font-bold text-slate-600 dark:text-slate-400 mb-1">ژمارەی مۆبایل:</label>
                    <input type="text" id="scModalPhone" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 font-mono">
                </div>
                <div>
                    <label class="block font-bold text-slate-600 dark:text-slate-400 mb-1">ناونیشان:</label>
                    <input type="text" id="scModalAddress" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2">
                </div>
                <div>
                    <label class="block font-bold text-slate-600 dark:text-slate-400 mb-1">تێبینی:</label>
                    <input type="text" id="scModalNote" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2">
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-5">
                <button onclick="document.getElementById('standaloneCompanyModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-800 text-xs font-bold">پاشگەزبوونەوە</button>
                <button onclick="saveStandaloneCompany()" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold">پاشەکەوتکردن</button>
            </div>
        </div>
    </div>

</div>

<script>
window.standaloneCompaniesData = [];

window.loadCompaniesPageData = function() {
    fetch('api_purchases.php?action=get_overview')
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                window.standaloneCompaniesData = res.companies || [];
                renderStandaloneCompaniesTable();
            }
        });
};

function renderStandaloneCompaniesTable() {
    const tbody = document.getElementById('standaloneCompaniesTableBody');
    if (!tbody) return;
    const q = (document.getElementById('compPageSearchInput')?.value || '').trim().toLowerCase();
    const rate = window.POS_CONFIG.usdRate || 1500;

    const list = window.standaloneCompaniesData.filter(c =>
        c.name.toLowerCase().includes(q) || (c.phone && c.phone.includes(q))
    );

    if (list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="py-16 text-center text-slate-400 font-bold">هیچ کۆمپانیایەک نەدۆزرایەوە</td></tr>`;
        return;
    }

    tbody.innerHTML = list.map((c, idx) => {
        const debtIQD = parseFloat(c.total_debt_iqd) || 0;
        return `
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
            <td class="py-2.5 px-3 text-center font-mono">${idx + 1}</td>
            <td class="py-2.5 px-3 font-extrabold text-slate-900 dark:text-white">🏢 ${c.name}</td>
            <td class="py-2.5 px-3 font-mono">${c.phone || '---'}</td>
            <td class="py-2.5 px-3 text-slate-500">${c.address || ''} ${c.note ? `(${c.note})` : ''}</td>
            <td class="py-2.5 px-3 text-center font-mono font-bold">${c.receipts_count} وەسڵ</td>
            <td class="py-2.5 px-3 text-center font-mono font-bold">${Math.round(c.total_purchased_iqd).toLocaleString()} د.ع</td>
            <td class="py-2.5 px-3 text-center font-mono font-extrabold ${debtIQD > 0 ? 'text-red-600' : 'text-emerald-600'}">
                ${Math.round(debtIQD).toLocaleString()} د.ع ($${(debtIQD / rate).toFixed(2)})
            </td>
            <td class="py-2.5 px-3 text-center">
                <div class="flex items-center justify-center gap-1">
                    <button onclick='openStandaloneCompanyModal(${JSON.stringify(c)})' class="bg-indigo-600 hover:bg-indigo-500 text-white px-2.5 py-1 rounded-lg text-[11px] font-bold">✏️ دەستکاری</button>
                    <button onclick="deleteStandaloneCompany(${c.id})" class="bg-red-100 hover:bg-red-600 text-red-600 hover:text-white px-2 py-1 rounded-lg text-[11px] font-bold">🗑️</button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function openStandaloneCompanyModal(comp = null) {
    document.getElementById('scModalId').value = comp ? comp.id : 0;
    document.getElementById('scModalName').value = comp ? comp.name : '';
    document.getElementById('scModalPhone').value = comp ? (comp.phone || '') : '';
    document.getElementById('scModalAddress').value = comp ? (comp.address || '') : '';
    document.getElementById('scModalNote').value = comp ? (comp.note || '') : '';

    const modal = document.getElementById('standaloneCompanyModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.getElementById('scModalName').focus();
}

function saveStandaloneCompany() {
    fetch('api_purchases.php?action=save_company', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id: document.getElementById('scModalId').value,
            name: document.getElementById('scModalName').value.trim(),
            phone: document.getElementById('scModalPhone').value.trim(),
            address: document.getElementById('scModalAddress').value.trim(),
            note: document.getElementById('scModalNote').value.trim()
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            document.getElementById('standaloneCompanyModal').classList.add('hidden');
            window.refreshAllPurchaseModules();
        } else {
            alert(res.message);
        }
    });
}

function deleteStandaloneCompany(id) {
    if (!confirm('ئایا دڵنیایت لە سڕینەوەی ئەم کۆمپانیایە؟')) return;
    fetch('api_purchases.php?action=delete_company', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    }).then(() => window.refreshAllPurchaseModules());
}

window.loadCompaniesPageData();
</script>