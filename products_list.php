<?php require_once 'db.php'; ?>

<div class="h-full w-full flex flex-col gap-3 overflow-hidden select-none" id="productsPageApp">

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 shadow-sm flex flex-wrap items-center justify-between gap-3 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-500/20 text-teal-600 dark:text-teal-400 flex items-center justify-center text-xl">
                📦
            </div>
            <div>
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white leading-tight">لیستی کاڵاکان (کۆگا)</h2>
                <p class="text-[11px] text-slate-500">فلتەرکردنی کاڵاکان بەپێی پۆل و براند و کۆمپانیا، لەگەڵ بەڕێوەبردنی نرخەکان</p>
            </div>
        </div>

        <!-- بەشی گەڕان و فلتەری پۆل و براند -->
        <div class="flex flex-wrap items-center gap-2">
            <input type="text" id="prodPageSearchInput" oninput="renderStandaloneProductsTable()"
                placeholder="🔍 گەڕان بە ناوی کاڵا یان بارکۆد..."
                class="w-60 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs font-bold focus:outline-none focus:border-teal-500">

            <!-- فلتەری پۆل -->
            <select id="prodFilterCategory" onchange="renderStandaloneProductsTable()"
                class="bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-teal-700 dark:text-teal-400">
                <option value="all">📂 هەموو پۆلەکان</option>
            </select>

            <!-- فلتەری براند -->
            <select id="prodFilterBrand" onchange="renderStandaloneProductsTable()"
                class="bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-amber-700 dark:text-amber-400">
                <option value="all">🏷️ هەموو براندەکان</option>
            </select>

            <button onclick="openTab('categories_brands', 'پۆل و براندەکان', '🏷️', 'categories_brands.php')"
                class="bg-violet-100 hover:bg-violet-200 dark:bg-violet-500/20 text-violet-700 dark:text-violet-300 px-3 py-2 rounded-xl text-xs font-extrabold transition">
                🏷️ پۆل و براند
            </button>

            <button onclick="openStandaloneProductModal()"
                class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl text-xs font-extrabold transition shadow-sm">
                ➕ زیادکردنی کاڵا
            </button>
        </div>
    </div>

    <div class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-y-auto flex-1">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 font-bold border-b border-slate-200 dark:border-slate-800 sticky top-0">
                    <tr>
                        <th class="py-3 px-2.5 text-center w-12">#</th>
                        <th class="py-3 px-2.5">ناوی کاڵا و بارکۆد</th>
                        <th class="py-3 px-2.5 text-center">کۆمپانیا / براند / پۆل</th>
                        <th class="py-3 px-2.5 text-center w-24">کۆگا</th>
                        <th class="py-3 px-2.5 text-center">نرخی کڕین</th>
                        <th class="py-3 px-2.5 text-center">نرخی تاک</th>
                        <th class="py-3 px-2.5 text-center">نرخی جوملە</th>
                        <th class="py-3 px-2.5 text-center">نرخی تایبەت</th>
                        <th class="py-3 px-2.5 text-center w-36">ئەپدەیت / سڕینەوە</th>
                    </tr>
                </thead>
                <tbody id="standaloneProductsTableBody" class="divide-y divide-slate-200 dark:divide-slate-800"></tbody>
            </table>
        </div>
    </div>

    <!-- مۆداڵی زیادکردن و ئەپدەیتکردنی کاڵا -->
    <div id="standaloneProductModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-2xl w-full max-w-2xl p-5 shadow-2xl max-h-[92vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3 mb-4">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">📦 زانیاری کاڵا (زیادکردن / ئەپدەیت)</h3>
                <button onclick="document.getElementById('standaloneProductModal').classList.add('hidden')" class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-bold">✕</button>
            </div>

            <input type="hidden" id="spModalId" value="0">
            
            <div class="space-y-3.5 text-xs">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">ناوی کاڵا *</label>
                        <input type="text" id="spModalName" placeholder="ناوی کاڵا..."
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 font-bold focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">بارکۆد</label>
                        <input type="text" id="spModalBarcode" placeholder="بارکۆد..."
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 font-mono focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">عەدەد لە کۆگا</label>
                        <input type="number" step="any" id="spModalStock" value="0"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 font-mono font-bold focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <!-- کۆمپانیا، براند، پۆل -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 bg-slate-50 dark:bg-slate-950 p-3 rounded-xl border border-slate-200 dark:border-slate-800">
                    <div>
                        <label class="block font-bold text-indigo-700 dark:text-indigo-400 mb-1">🏢 کۆمپانیا:</label>
                        <input type="text" id="spModalCompany" list="spCompaniesDatalist" placeholder="هەڵبژێرە یان بنووسە..."
                            class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-1.5 font-bold focus:outline-none">
                        <datalist id="spCompaniesDatalist"></datalist>
                    </div>
                    <div>
                        <label class="block font-bold text-amber-700 dark:text-amber-400 mb-1">🏷️ براند (مارکە):</label>
                        <input type="text" id="spModalBrand" list="spBrandsDatalist" placeholder="هەڵبژێرە یان بنووسە..."
                            class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-1.5 font-bold focus:outline-none">
                        <datalist id="spBrandsDatalist"></datalist>
                    </div>
                    <div>
                        <label class="block font-bold text-teal-700 dark:text-teal-400 mb-1">📂 پۆل (بەش):</label>
                        <input type="text" id="spModalCategory" list="spCategoriesDatalist" placeholder="هەڵبژێرە یان بنووسە..."
                            class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-1.5 font-bold focus:outline-none">
                        <datalist id="spCategoriesDatalist"></datalist>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5">
                    <div>
                        <label class="block font-bold text-slate-600 dark:text-slate-400 mb-1">نرخی کڕین (د.ع):</label>
                        <input type="number" step="any" id="spModalBuyIQD" oninput="syncSpCurrency('buy','IQD')"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 font-mono font-bold">
                    </div>
                    <div>
                        <label class="block font-bold text-emerald-600 mb-1">نرخی کڕین ($):</label>
                        <input type="number" step="any" id="spModalBuyUSD" oninput="syncSpCurrency('buy','USD')"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 font-mono font-bold text-emerald-600">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-600 dark:text-slate-400 mb-1">نرخی تاک (د.ع):</label>
                        <input type="number" step="any" id="spModalSellIQD" oninput="syncSpCurrency('sell','IQD')"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 font-mono font-bold">
                    </div>
                    <div>
                        <label class="block font-bold text-emerald-600 mb-1">نرخی تاک ($):</label>
                        <input type="number" step="any" id="spModalSellUSD" oninput="syncSpCurrency('sell','USD')"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 font-mono font-bold text-emerald-600">
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5 bg-amber-50/50 dark:bg-slate-950 p-3 rounded-xl border border-amber-200 dark:border-slate-800">
                    <div>
                        <label class="block font-bold text-blue-700 dark:text-blue-400 mb-1">نرخی جوملە (د.ع):</label>
                        <input type="number" step="any" id="spModalWholesaleIQD" oninput="syncSpCurrency('wholesale','IQD')"
                            class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 font-mono font-bold">
                    </div>
                    <div>
                        <label class="block font-bold text-blue-600 mb-1">نرخی جوملە ($):</label>
                        <input type="number" step="any" id="spModalWholesaleUSD" oninput="syncSpCurrency('wholesale','USD')"
                            class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 font-mono font-bold text-blue-600">
                    </div>
                    <div>
                        <label class="block font-bold text-purple-700 dark:text-purple-400 mb-1">نرخی تایبەت (د.ع):</label>
                        <input type="number" step="any" id="spModalSpecialIQD" oninput="syncSpCurrency('special','IQD')"
                            class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 font-mono font-bold">
                    </div>
                    <div>
                        <label class="block font-bold text-purple-600 mb-1">نرخی تایبەت ($):</label>
                        <input type="number" step="any" id="spModalSpecialUSD" oninput="syncSpCurrency('special','USD')"
                            class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 font-mono font-bold text-purple-600">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-5">
                <button onclick="document.getElementById('standaloneProductModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-800 text-xs font-bold">پاشگەزبوونەوە</button>
                <button onclick="saveStandaloneProduct()" class="px-6 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-extrabold shadow-md">💾 پاشەکەوتکردن</button>
            </div>
        </div>
    </div>

</div>

<script>
window.standaloneProductsData = [];

window.loadProductsPageData = function() {
    fetch('api_purchases.php?action=get_overview')
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                window.standaloneProductsData = res.products || [];
                const companies = res.companies || [];
                const categories = res.categories || [];
                const brands = res.brands || [];

                document.getElementById('spCompaniesDatalist').innerHTML = companies.map(c => `<option value="${c.name}">`).join('');
                document.getElementById('spCategoriesDatalist').innerHTML = categories.map(c => `<option value="${c.name}">`).join('');
                document.getElementById('spBrandsDatalist').innerHTML = brands.map(b => `<option value="${b.name}">`).join('');

                const catSel = document.getElementById('prodFilterCategory');
                const curCat = catSel.value || 'all';
                catSel.innerHTML = `<option value="all">📂 هەموو پۆلەکان</option>` + categories.map(c => `<option value="${c.name}">📂 ${c.name}</option>`).join('');
                catSel.value = curCat;

                const brandSel = document.getElementById('prodFilterBrand');
                const curBrand = brandSel.value || 'all';
                brandSel.innerHTML = `<option value="all">🏷️ هەموو براندەکان</option>` + brands.map(b => `<option value="${b.name}">🏷️ ${b.name}</option>`).join('');
                brandSel.value = curBrand;

                renderStandaloneProductsTable();
            }
        });
};

function renderStandaloneProductsTable() {
    const tbody = document.getElementById('standaloneProductsTableBody');
    if (!tbody) return;
    const q = (document.getElementById('prodPageSearchInput')?.value || '').trim().toLowerCase();
    const catFilter = document.getElementById('prodFilterCategory')?.value || 'all';
    const brandFilter = document.getElementById('prodFilterBrand')?.value || 'all';
    const rate = window.POS_CONFIG.usdRate || 1500;

    const list = window.standaloneProductsData.filter(p => {
        const matchQ = !q ||
            (p.name && p.name.toLowerCase().includes(q)) ||
            (p.barcode && p.barcode.toLowerCase().includes(q)) ||
            (p.company_name && p.company_name.toLowerCase().includes(q));
        const matchCat = catFilter === 'all' || p.category === catFilter;
        const matchBrand = brandFilter === 'all' || p.brand === brandFilter;
        return matchQ && matchCat && matchBrand;
    });

    if (list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="py-16 text-center text-slate-400 font-bold">هیچ کاڵایەک نەدۆزرایەوە</td></tr>`;
        return;
    }

    tbody.innerHTML = list.map((p, idx) => {
        const buyIQD = parseFloat(p.buy_price_iqd) || 0;
        const sellIQD = parseFloat(p.sell_price_iqd) || 0;
        const wholesaleIQD = parseFloat(p.wholesale_price_iqd) || 0;
        const specialIQD = parseFloat(p.special_price_iqd) || 0;

        return `
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
            <td class="py-2.5 px-2.5 text-center font-mono">${idx + 1}</td>
            <td class="py-2.5 px-2.5">
                <div class="font-extrabold text-slate-900 dark:text-white">${p.name}</div>
                <div class="text-[11px] font-mono text-slate-500">بارکۆد: ${p.barcode || '---'}</div>
            </td>
            <td class="py-2.5 px-2.5 text-center">
                <div class="flex flex-wrap items-center justify-center gap-1">
                    ${p.company_name ? `<span class="bg-indigo-50 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 px-2 py-0.5 rounded text-[10px] font-bold">🏢 ${p.company_name}</span>` : ''}
                    ${p.brand ? `<span class="bg-amber-50 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 px-2 py-0.5 rounded text-[10px] font-bold">🏷️ ${p.brand}</span>` : ''}
                    ${p.category ? `<span class="bg-teal-50 dark:bg-teal-500/20 text-teal-700 dark:text-teal-300 px-2 py-0.5 rounded text-[10px] font-bold">📂 ${p.category}</span>` : ''}
                    ${!p.company_name && !p.brand && !p.category ? '<span class="text-slate-400">---</span>' : ''}
                </div>
            </td>
            <td class="py-2.5 px-2.5 text-center">
                <span class="px-2.5 py-0.5 rounded-lg font-mono font-extrabold ${parseFloat(p.stock) <= 0 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-800'}">
                    ${parseFloat(p.stock)}
                </span>
            </td>
            <td class="py-2.5 px-2.5 text-center font-mono">
                <div class="font-bold">${Math.round(buyIQD).toLocaleString()} د.ع</div>
                <div class="text-[10px] text-amber-600 font-bold">$${(buyIQD / rate).toFixed(2)}</div>
            </td>
            <td class="py-2.5 px-2.5 text-center font-mono">
                <div class="font-bold text-emerald-700 dark:text-emerald-400">${Math.round(sellIQD).toLocaleString()} د.ع</div>
                <div class="text-[10px] text-emerald-600 font-bold">$${(sellIQD / rate).toFixed(2)}</div>
            </td>
            <td class="py-2.5 px-2.5 text-center font-mono">
                <div class="font-bold text-blue-700 dark:text-blue-400">${wholesaleIQD > 0 ? Math.round(wholesaleIQD).toLocaleString() + ' د.ع' : '---'}</div>
                ${wholesaleIQD > 0 ? `<div class="text-[10px] text-blue-500 font-bold">$${(wholesaleIQD / rate).toFixed(2)}</div>` : ''}
            </td>
            <td class="py-2.5 px-2.5 text-center font-mono">
                <div class="font-bold text-purple-700 dark:text-purple-400">${specialIQD > 0 ? Math.round(specialIQD).toLocaleString() + ' د.ع' : '---'}</div>
                ${specialIQD > 0 ? `<div class="text-[10px] text-purple-500 font-bold">$${(specialIQD / rate).toFixed(2)}</div>` : ''}
            </td>
            <td class="py-2.5 px-2.5 text-center">
                <div class="flex items-center justify-center gap-1">
                    <button onclick='openStandaloneProductModal(${JSON.stringify(p)})' class="bg-amber-500 hover:bg-amber-600 text-white px-2.5 py-1 rounded-lg text-[11px] font-bold">✏️ ئەپدەیت</button>
                    <button onclick="deleteStandaloneProduct(${p.id})" class="bg-red-100 hover:bg-red-600 text-red-600 hover:text-white px-2 py-1 rounded-lg text-[11px] font-bold">🗑️</button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function openStandaloneProductModal(prod = null) {
    const rate = window.POS_CONFIG.usdRate || 1500;
    document.getElementById('spModalId').value = prod ? prod.id : 0;
    document.getElementById('spModalName').value = prod ? prod.name : '';
    document.getElementById('spModalBarcode').value = prod ? (prod.barcode || '') : '';
    document.getElementById('spModalCompany').value = prod ? (prod.company_name || '') : '';
    document.getElementById('spModalBrand').value = prod ? (prod.brand || '') : '';
    document.getElementById('spModalCategory').value = prod ? (prod.category || '') : '';

    const buyIQD = prod ? parseFloat(prod.buy_price_iqd) : 0;
    const sellIQD = prod ? parseFloat(prod.sell_price_iqd) : 0;
    const wholesaleIQD = prod ? parseFloat(prod.wholesale_price_iqd) : 0;
    const specialIQD = prod ? parseFloat(prod.special_price_iqd) : 0;

    document.getElementById('spModalBuyIQD').value = buyIQD ? Math.round(buyIQD) : '';
    document.getElementById('spModalBuyUSD').value = buyIQD ? (buyIQD / rate).toFixed(2) : '';
    document.getElementById('spModalSellIQD').value = sellIQD ? Math.round(sellIQD) : '';
    document.getElementById('spModalSellUSD').value = sellIQD ? (sellIQD / rate).toFixed(2) : '';
    document.getElementById('spModalWholesaleIQD').value = wholesaleIQD ? Math.round(wholesaleIQD) : '';
    document.getElementById('spModalWholesaleUSD').value = wholesaleIQD ? (wholesaleIQD / rate).toFixed(2) : '';
    document.getElementById('spModalSpecialIQD').value = specialIQD ? Math.round(specialIQD) : '';
    document.getElementById('spModalSpecialUSD').value = specialIQD ? (specialIQD / rate).toFixed(2) : '';
    document.getElementById('spModalStock').value = prod ? parseFloat(prod.stock) : 0;

    const modal = document.getElementById('standaloneProductModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.getElementById('spModalName').focus();
}

function syncSpCurrency(field, cur) {
    const rate = window.POS_CONFIG.usdRate || 1500;
    const map = {
        buy: ['spModalBuyIQD', 'spModalBuyUSD'],
        sell: ['spModalSellIQD', 'spModalSellUSD'],
        wholesale: ['spModalWholesaleIQD', 'spModalWholesaleUSD'],
        special: ['spModalSpecialIQD', 'spModalSpecialUSD']
    };
    const [iqdId, usdId] = map[field];
    if (cur === 'IQD') {
        const v = parseFloat(document.getElementById(iqdId).value) || 0;
        document.getElementById(usdId).value = v ? (v / rate).toFixed(2) : '';
    } else {
        const v = parseFloat(document.getElementById(usdId).value) || 0;
        document.getElementById(iqdId).value = v ? Math.round(v * rate) : '';
    }
}

function saveStandaloneProduct() {
    fetch('api_purchases.php?action=save_product', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id: document.getElementById('spModalId').value,
            name: document.getElementById('spModalName').value.trim(),
            barcode: document.getElementById('spModalBarcode').value.trim(),
            company_name: document.getElementById('spModalCompany').value.trim(),
            brand: document.getElementById('spModalBrand').value.trim(),
            category: document.getElementById('spModalCategory').value.trim(),
            buy_price_iqd: parseFloat(document.getElementById('spModalBuyIQD').value) || 0,
            sell_price_iqd: parseFloat(document.getElementById('spModalSellIQD').value) || 0,
            wholesale_price_iqd: parseFloat(document.getElementById('spModalWholesaleIQD').value) || 0,
            special_price_iqd: parseFloat(document.getElementById('spModalSpecialIQD').value) || 0,
            stock: parseFloat(document.getElementById('spModalStock').value) || 0
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            document.getElementById('standaloneProductModal').classList.add('hidden');
            window.refreshAllPurchaseModules();
        } else {
            alert(res.message);
        }
    });
}

function deleteStandaloneProduct(id) {
    if (!confirm('ئایا دڵنیایت لە سڕینەوەی ئەم کاڵایە؟')) return;
    fetch('api_purchases.php?action=delete_product', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    }).then(() => window.refreshAllPurchaseModules());
}

window.loadProductsPageData();
</script>