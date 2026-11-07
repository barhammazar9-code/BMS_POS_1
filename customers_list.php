<?php require_once 'db.php'; ?>

<div class="h-full w-full flex flex-col gap-3 overflow-hidden select-none" id="customersPageApp">

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 shadow-sm flex flex-wrap items-center justify-between gap-3 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-cyan-50 dark:bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-xl">
                👥
            </div>
            <div>
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white leading-tight">لیستی کڕیاران</h2>
                <p class="text-[11px] text-slate-500">تۆمارکردنی ناوی کڕیار، ڕەقەمی کڕیار، ناونیشانی کڕیار، ناوی کەفیل و ڕەقەمی کەفیلی کڕیار</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <input type="text" id="custPageSearchInput" oninput="renderStandaloneCustomersTable()"
                placeholder="🔍 گەڕان بە ناو، ڕەقەم، ناونیشان، یان کەفیل..."
                class="w-72 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs font-bold focus:outline-none focus:border-cyan-500">
            <button onclick="openStandaloneCustomerModal()"
                class="bg-cyan-600 hover:bg-cyan-500 text-white px-4 py-2 rounded-xl text-xs font-extrabold transition shadow-sm">
                ➕ زیادکردنی کڕیاری نوێ
            </button>
        </div>
    </div>

    <div class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-y-auto flex-1">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 font-bold border-b border-slate-200 dark:border-slate-800 sticky top-0">
                    <tr>
                        <th class="py-3 px-3 text-center w-12">#</th>
                        <th class="py-3 px-3">ناوی کڕیار</th>
                        <th class="py-3 px-3">ڕەقەمی کڕیار</th>
                        <th class="py-3 px-3">ناونیشانی کڕیار</th>
                        <th class="py-3 px-3">ناوی کەفیلی کڕیار</th>
                        <th class="py-3 px-3">ڕەقەمی کەفیلی کڕیار</th>
                        <th class="py-3 px-3 text-center">ژ.وەسڵ</th>
                        <th class="py-3 px-3 text-center">کۆی قەرز</th>
                        <th class="py-3 px-3 text-center">کۆی قیست</th>
                        <th class="py-3 px-3 text-center w-36">کردارەکان</th>
                    </tr>
                </thead>
                <tbody id="standaloneCustomersTableBody" class="divide-y divide-slate-200 dark:divide-slate-800"></tbody>
            </table>
        </div>
    </div>

    <!-- مۆداڵی زیادکردن و دەستکاریکردنی کڕیار بە هەر ٥ خانەکەوە -->
    <div id="standaloneCustomerModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-2xl w-full max-w-md p-5 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3 mb-4">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">👤 فۆڕمی زانیاری کڕیار</h3>
                <button onclick="document.getElementById('standaloneCustomerModal').classList.add('hidden')" class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-bold">✕</button>
            </div>
            <input type="hidden" id="custModalId" value="0">
            <div class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">١. ناوی کڕیار *</label>
                    <input type="text" id="custModalName" placeholder="ناوی سیانی کڕیار..."
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 font-bold focus:outline-none focus:border-cyan-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">٢. ڕەقەمی کڕیار</label>
                    <input type="text" id="custModalPhone" placeholder="0770 / 0750..."
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 font-mono focus:outline-none focus:border-cyan-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">٣. ناونیشانی کڕیار</label>
                    <input type="text" id="custModalAddress" placeholder="شار / گەڕەک / شوێن..."
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 font-bold focus:outline-none focus:border-cyan-500">
                </div>
                <div class="pt-2 border-t border-slate-200 dark:border-slate-800">
                    <label class="block font-bold text-amber-700 dark:text-amber-400 mb-1">٤. ناوی کەفیلی کڕیار</label>
                    <input type="text" id="custModalGuarantor" placeholder="ناوی کەفیل..."
                        class="w-full bg-amber-50/40 dark:bg-slate-950 border border-amber-300 dark:border-slate-700 rounded-xl px-3 py-2 font-bold focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block font-bold text-amber-700 dark:text-amber-400 mb-1">٥. ڕەقەمی کەفیلی کڕیار</label>
                    <input type="text" id="custModalGuarantorPhone" placeholder="ژمارە مۆبایلی کەفیل..."
                        class="w-full bg-amber-50/40 dark:bg-slate-950 border border-amber-300 dark:border-slate-700 rounded-xl px-3 py-2 font-mono focus:outline-none focus:border-amber-500">
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-5">
                <button onclick="document.getElementById('standaloneCustomerModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-800 text-xs font-bold">پاشگەزبوونەوە</button>
                <button onclick="saveStandaloneCustomer()" class="px-6 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-extrabold shadow-md">💾 پاشەکەوتکردن</button>
            </div>
        </div>
    </div>

</div>

<script>
window.standaloneCustomersData = [];

window.loadCustomersPageData = function() {
    fetch('api_sales.php?action=get_customers_overview')
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                window.standaloneCustomersData = res.customers || [];
                renderStandaloneCustomersTable();
            }
        });
};

function renderStandaloneCustomersTable() {
    const tbody = document.getElementById('standaloneCustomersTableBody');
    if (!tbody) return;
    const q = (document.getElementById('custPageSearchInput')?.value || '').trim().toLowerCase();
    const rate = window.POS_CONFIG.usdRate || 1500;

    const list = window.standaloneCustomersData.filter(c =>
        c.name.toLowerCase().includes(q) ||
        (c.phone && c.phone.includes(q)) ||
        (c.address && c.address.toLowerCase().includes(q)) ||
        (c.guarantor && c.guarantor.toLowerCase().includes(q)) ||
        (c.guarantor_phone && c.guarantor_phone.includes(q))
    );

    if (list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="10" class="py-16 text-center text-slate-400 font-bold">هیچ کڕیارێک نەدۆزرایەوە</td></tr>`;
        return;
    }

    tbody.innerHTML = list.map((c, idx) => {
        const debtIQD = parseFloat(c.debt_iqd) || 0;
        const instIQD = parseFloat(c.installment_iqd) || 0;
        return `
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
            <td class="py-2.5 px-3 text-center font-mono">${idx + 1}</td>
            <td class="py-2.5 px-3 font-extrabold text-slate-900 dark:text-white">👤 ${c.name}</td>
            <td class="py-2.5 px-3 font-mono font-bold text-slate-700 dark:text-slate-300">${c.phone || '---'}</td>
            <td class="py-2.5 px-3 text-slate-600 dark:text-slate-400">${c.address || '---'}</td>
            <td class="py-2.5 px-3 font-bold text-amber-700 dark:text-amber-400">${c.guarantor ? `🛡️ ${c.guarantor}` : '---'}</td>
            <td class="py-2.5 px-3 font-mono text-amber-700 dark:text-amber-400">${c.guarantor_phone || '---'}</td>
            <td class="py-2.5 px-3 text-center font-mono font-bold">${c.receipts_count} وەسڵ</td>
            <td class="py-2.5 px-3 text-center font-mono font-extrabold ${debtIQD > 0 ? 'text-orange-600' : 'text-slate-400'}">
                ${Math.round(debtIQD).toLocaleString()} د.ع ($${(debtIQD / rate).toFixed(2)})
            </td>
            <td class="py-2.5 px-3 text-center font-mono font-extrabold ${instIQD > 0 ? 'text-pink-600' : 'text-slate-400'}">
                ${Math.round(instIQD).toLocaleString()} د.ع ($${(instIQD / rate).toFixed(2)})
            </td>
            <td class="py-2.5 px-3 text-center">
                <div class="flex items-center justify-center gap-1">
                    <button onclick='openStandaloneCustomerModal(${JSON.stringify(c)})' class="bg-cyan-600 hover:bg-cyan-500 text-white px-2.5 py-1 rounded-lg text-[11px] font-bold">✏️ دەستکاری</button>
                    <button onclick="deleteStandaloneCustomer(${c.id})" class="bg-red-100 hover:bg-red-600 text-red-600 hover:text-white px-2 py-1 rounded-lg text-[11px] font-bold">🗑️</button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function openStandaloneCustomerModal(cust = null) {
    document.getElementById('custModalId').value = cust ? cust.id : 0;
    document.getElementById('custModalName').value = cust ? cust.name : '';
    document.getElementById('custModalPhone').value = cust ? (cust.phone || '') : '';
    document.getElementById('custModalAddress').value = cust ? (cust.address || '') : '';
    document.getElementById('custModalGuarantor').value = cust ? (cust.guarantor || '') : '';
    document.getElementById('custModalGuarantorPhone').value = cust ? (cust.guarantor_phone || '') : '';

    const modal = document.getElementById('standaloneCustomerModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.getElementById('custModalName').focus();
}

function saveStandaloneCustomer() {
    fetch('api_sales.php?action=save_customer', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id: document.getElementById('custModalId').value,
            name: document.getElementById('custModalName').value.trim(),
            phone: document.getElementById('custModalPhone').value.trim(),
            address: document.getElementById('custModalAddress').value.trim(),
            guarantor: document.getElementById('custModalGuarantor').value.trim(),
            guarantor_phone: document.getElementById('custModalGuarantorPhone').value.trim()
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            document.getElementById('standaloneCustomerModal').classList.add('hidden');
            window.refreshAllPurchaseModules();
        } else {
            alert(res.message);
        }
    });
}

function deleteStandaloneCustomer(id) {
    if (!confirm('ئایا دڵنیایت لە سڕینەوەی ئەم کڕیارە؟')) return;
    fetch('api_sales.php?action=delete_customer', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    }).then(() => window.refreshAllPurchaseModules());
}

window.loadCustomersPageData();
</script>