<?php require_once 'db.php'; ?>

<div class="h-full w-full flex flex-col gap-3 overflow-hidden select-none" id="catBrandPageApp">

    <!-- سەرپەڕە -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 shadow-sm flex flex-wrap items-center justify-between gap-3 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-violet-50 dark:bg-violet-500/20 text-violet-600 dark:text-violet-400 flex items-center justify-center text-xl">
                🏷️
            </div>
            <div>
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white leading-tight">بەڕێوەبردنی پۆل و براندەکان</h2>
                <p class="text-[11px] text-slate-500">زیادکردن و دەستکاریکردنی پۆل (بەشەکان) و براند (مارکەکان) بۆ پۆلێنکردن و فلتەرکردنی کاڵاکان</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <input type="text" id="cbSearchInput" oninput="renderCategoriesAndBrands()"
                placeholder="🔍 گەڕان لە پۆل و براندەکان..."
                class="w-64 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs font-bold focus:outline-none focus:border-violet-500">
        </div>
    </div>

    <!-- دوو ستوونی تەنیشت یەک: لای ڕاست (پۆلەکان) + لای چەپ (براندەکان) -->
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-2 gap-3 overflow-hidden">

        <!-- ١. بەشی پۆلەکان (Categories) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm flex flex-col overflow-hidden">
            <div class="bg-teal-50/70 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 p-3 flex flex-col gap-2.5 shrink-0">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-extrabold text-teal-900 dark:text-teal-300 flex items-center gap-1.5">
                        <span>📂</span>
                        <span>بەشی پۆلەکان (Categories)</span>
                    </h3>
                    <span id="catTotalBadge" class="bg-teal-600 text-white px-2 py-0.5 rounded-lg font-mono text-xs font-bold">0</span>
                </div>

                <!-- فۆڕمی زیادکردن / ئەپدەیتی پۆل -->
                <div class="flex items-center gap-2">
                    <input type="hidden" id="editCategoryId" value="0">
                    <input type="text" id="categoryNameInput" placeholder="ناوی پۆلی نوێ بنووسە (نموونە: خۆراک، پاککەرەوە...)"
                        onkeydown="if(event.key==='Enter') saveCategoryItem()"
                        class="flex-1 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-bold focus:outline-none focus:border-teal-500">
                    <button onclick="saveCategoryItem()" id="btnSaveCategory"
                        class="bg-teal-600 hover:bg-teal-500 text-white px-4 py-2 rounded-xl text-xs font-extrabold transition shadow-sm">
                        + زیادکردنی پۆل
                    </button>
                    <button onclick="resetCategoryForm()" id="btnCancelCategory" class="hidden bg-slate-200 dark:bg-slate-800 text-slate-600 px-2.5 py-2 rounded-xl text-xs font-bold">✕</button>
                </div>
            </div>

            <div class="overflow-y-auto flex-1">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 font-bold border-b border-slate-200 dark:border-slate-800 sticky top-0">
                        <tr>
                            <th class="py-2.5 px-3 text-center w-12">#</th>
                            <th class="py-2.5 px-3">ناوی پۆل (بەش)</th>
                            <th class="py-2.5 px-3 text-center w-28">ژمارەی کاڵا</th>
                            <th class="py-2.5 px-3 text-center w-32">کردارەکان</th>
                        </tr>
                    </thead>
                    <tbody id="categoriesTableBody" class="divide-y divide-slate-200 dark:divide-slate-800"></tbody>
                </table>
            </div>
        </div>

        <!-- ٢. بەشی براندەکان (Brands) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm flex flex-col overflow-hidden">
            <div class="bg-amber-50/70 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 p-3 flex flex-col gap-2.5 shrink-0">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-extrabold text-amber-900 dark:text-amber-300 flex items-center gap-1.5">
                        <span>🏷️</span>
                        <span>بەشی براندەکان (Brands)</span>
                    </h3>
                    <span id="brandTotalBadge" class="bg-amber-500 text-white px-2 py-0.5 rounded-lg font-mono text-xs font-bold">0</span>
                </div>

                <!-- فۆڕمی زیادکردن / ئەپدەیتی براند -->
                <div class="flex items-center gap-2">
                    <input type="hidden" id="editBrandId" value="0">
                    <input type="text" id="brandNameInput" placeholder="ناوی براندی نوێ بنووسە (نموونە: مەحموود، نیدۆ، جیهان...)"
                        onkeydown="if(event.key==='Enter') saveBrandItem()"
                        class="flex-1 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-bold focus:outline-none focus:border-amber-500">
                    <button onclick="saveBrandItem()" id="btnSaveBrand"
                        class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-xl text-xs font-extrabold transition shadow-sm">
                        + زیادکردنی براند
                    </button>
                    <button onclick="resetBrandForm()" id="btnCancelBrand" class="hidden bg-slate-200 dark:bg-slate-800 text-slate-600 px-2.5 py-2 rounded-xl text-xs font-bold">✕</button>
                </div>
            </div>

            <div class="overflow-y-auto flex-1">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 font-bold border-b border-slate-200 dark:border-slate-800 sticky top-0">
                        <tr>
                            <th class="py-2.5 px-3 text-center w-12">#</th>
                            <th class="py-2.5 px-3">ناوی براند (مارکە)</th>
                            <th class="py-2.5 px-3 text-center w-28">ژمارەی کاڵا</th>
                            <th class="py-2.5 px-3 text-center w-32">کردارەکان</th>
                        </tr>
                    </thead>
                    <tbody id="brandsTableBody" class="divide-y divide-slate-200 dark:divide-slate-800"></tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<script>
window.cbPageCategories = [];
window.cbPageBrands = [];

window.loadCategoriesBrandsPageData = function() {
    fetch('api_purchases.php?action=get_overview')
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                window.cbPageCategories = res.categories || [];
                window.cbPageBrands = res.brands || [];
                renderCategoriesAndBrands();
            }
        });
};

function renderCategoriesAndBrands() {
    const q = (document.getElementById('cbSearchInput')?.value || '').trim().toLowerCase();

    const cats = window.cbPageCategories.filter(c => c.name.toLowerCase().includes(q));
    const brands = window.cbPageBrands.filter(b => b.name.toLowerCase().includes(q));

    document.getElementById('catTotalBadge').innerText = cats.length;
    document.getElementById('brandTotalBadge').innerText = brands.length;

    const catTbody = document.getElementById('categoriesTableBody');
    if (cats.length === 0) {
        catTbody.innerHTML = `<tr><td colspan="4" class="py-12 text-center text-slate-400 font-bold">هیچ پۆلێک نەدۆزرایەوە</td></tr>`;
    } else {
        catTbody.innerHTML = cats.map((c, i) => `
            <tr class="hover:bg-teal-50/40 dark:hover:bg-slate-800/40 transition">
                <td class="py-2.5 px-3 text-center font-mono">${i + 1}</td>
                <td class="py-2.5 px-3 font-extrabold text-slate-900 dark:text-white">📂 ${c.name}</td>
                <td class="py-2.5 px-3 text-center font-mono font-bold text-teal-600">${c.products_count} کاڵا</td>
                <td class="py-2.5 px-3 text-center">
                    <div class="flex items-center justify-center gap-1">
                        <button onclick="startEditCategory(${c.id}, '${c.name.replace(/'/g, "\\'")}')" class="bg-teal-600 text-white px-2.5 py-1 rounded-lg text-[11px] font-bold">✏️</button>
                        <button onclick="deleteCategoryItem(${c.id})" class="bg-red-100 hover:bg-red-600 text-red-600 hover:text-white px-2 py-1 rounded-lg text-[11px] font-bold">🗑️</button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    const brandTbody = document.getElementById('brandsTableBody');
    if (brands.length === 0) {
        brandTbody.innerHTML = `<tr><td colspan="4" class="py-12 text-center text-slate-400 font-bold">هیچ براندێک نەدۆزرایەوە</td></tr>`;
    } else {
        brandTbody.innerHTML = brands.map((b, i) => `
            <tr class="hover:bg-amber-50/40 dark:hover:bg-slate-800/40 transition">
                <td class="py-2.5 px-3 text-center font-mono">${i + 1}</td>
                <td class="py-2.5 px-3 font-extrabold text-slate-900 dark:text-white">🏷️ ${b.name}</td>
                <td class="py-2.5 px-3 text-center font-mono font-bold text-amber-600">${b.products_count} کاڵا</td>
                <td class="py-2.5 px-3 text-center">
                    <div class="flex items-center justify-center gap-1">
                        <button onclick="startEditBrand(${b.id}, '${b.name.replace(/'/g, "\\'")}')" class="bg-amber-500 text-white px-2.5 py-1 rounded-lg text-[11px] font-bold">✏️</button>
                        <button onclick="deleteBrandItem(${b.id})" class="bg-red-100 hover:bg-red-600 text-red-600 hover:text-white px-2 py-1 rounded-lg text-[11px] font-bold">🗑️</button>
                    </div>
                </td>
            </tr>
        `).join('');
    }
}

function saveCategoryItem() {
    const id = parseInt(document.getElementById('editCategoryId').value) || 0;
    const name = document.getElementById('categoryNameInput').value.trim();
    if (!name) return;

    fetch('api_purchases.php?action=save_category', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, name })
    }).then(r => r.json()).then(res => {
        if (res.status === 'success') {
            resetCategoryForm();
            window.refreshAllPurchaseModules();
        } else {
            alert(res.message);
        }
    });
}

function startEditCategory(id, name) {
    document.getElementById('editCategoryId').value = id;
    document.getElementById('categoryNameInput').value = name;
    document.getElementById('btnSaveCategory').innerText = '💾 نوێکردنەوە';
    document.getElementById('btnCancelCategory').classList.remove('hidden');
    document.getElementById('categoryNameInput').focus();
}

function resetCategoryForm() {
    document.getElementById('editCategoryId').value = 0;
    document.getElementById('categoryNameInput').value = '';
    document.getElementById('btnSaveCategory').innerText = '+ زیادکردنی پۆل';
    document.getElementById('btnCancelCategory').classList.add('hidden');
}

function deleteCategoryItem(id) {
    if (!confirm('ئایا دڵنیایت لە سڕینەوەی ئەم پۆلە؟')) return;
    fetch('api_purchases.php?action=delete_category', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    }).then(() => window.refreshAllPurchaseModules());
}

function saveBrandItem() {
    const id = parseInt(document.getElementById('editBrandId').value) || 0;
    const name = document.getElementById('brandNameInput').value.trim();
    if (!name) return;

    fetch('api_purchases.php?action=save_brand', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, name })
    }).then(r => r.json()).then(res => {
        if (res.status === 'success') {
            resetBrandForm();
            window.refreshAllPurchaseModules();
        } else {
            alert(res.message);
        }
    });
}

function startEditBrand(id, name) {
    document.getElementById('editBrandId').value = id;
    document.getElementById('brandNameInput').value = name;
    document.getElementById('btnSaveBrand').innerText = '💾 نوێکردنەوە';
    document.getElementById('btnCancelBrand').classList.remove('hidden');
    document.getElementById('brandNameInput').focus();
}

function resetBrandForm() {
    document.getElementById('editBrandId').value = 0;
    document.getElementById('brandNameInput').value = '';
    document.getElementById('btnSaveBrand').innerText = '+ زیادکردنی براند';
    document.getElementById('btnCancelBrand').classList.add('hidden');
}

function deleteBrandItem(id) {
    if (!confirm('ئایا دڵنیایت لە سڕینەوەی ئەم براندە؟')) return;
    fetch('api_purchases.php?action=delete_brand', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    }).then(() => window.refreshAllPurchaseModules());
}

window.loadCategoriesBrandsPageData();
</script>