<?php
require_once 'db.php';
$stmtMax = $pdo->query("SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM sales");
$nextReceiptId = $stmtMax->fetch(PDO::FETCH_ASSOC)['next_id'];
?>

<div class="h-full w-full flex flex-col gap-2 overflow-hidden select-none" id="salesApp">

    <!-- ١. ڕیزی یەکەم: ژمارەی وەسڵ + جۆری وەسڵ + زانیاری کڕیار -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 shadow-sm flex flex-wrap items-center justify-between gap-2 shrink-0">
        
        <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 p-1 rounded-lg">
            <button onclick="navigateReceipt('prev')" class="bg-white dark:bg-slate-800 hover:bg-slate-50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-amber-400 px-2.5 py-1 rounded-md text-xs font-bold transition">
                ⏮ پێشتر
            </button>

            <div class="flex items-center bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-md px-2 py-0.5">
                <span class="text-xs text-slate-400 ml-1">وەسڵ #</span>
                <input type="number" id="receiptNumberInput" value="<?= $nextReceiptId ?>" data-next-id="<?= $nextReceiptId ?>"
                    onkeydown="if(event.key==='Enter') loadExactReceipt()"
                    class="w-14 bg-transparent text-center font-mono font-bold text-emerald-600 dark:text-emerald-400 text-xs focus:outline-none">
                <button onclick="loadExactReceipt()" class="text-xs text-slate-500 hover:text-emerald-600 px-1" title="گەڕان">🔍</button>
            </div>

            <button onclick="navigateReceipt('next')" class="bg-white dark:bg-slate-800 hover:bg-slate-50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-amber-400 px-2.5 py-1 rounded-md text-xs font-bold transition">
                دواتر ⏭
            </button>

            <button onclick="resetNewSale()" class="bg-emerald-600 hover:bg-emerald-500 text-white px-2.5 py-1 rounded-md text-xs font-bold transition">
                + وەسڵی نوێ
            </button>
            <span id="receiptModeBadge" class="text-[11px] bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 px-2 py-0.5 rounded font-bold">نوێ</span>
        </div>

        <!-- جۆری فرۆشتن -->
        <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 p-1 rounded-lg">
            <button type="button" onclick="setSaleType('cash')" id="stype-cash" class="sale-type-btn px-3 py-1 rounded-md text-xs font-bold bg-emerald-600 text-white shadow-sm">💵 نەقد</button>
            <button type="button" onclick="setSaleType('debt')" id="stype-debt" class="sale-type-btn px-3 py-1 rounded-md text-xs font-bold text-slate-600 dark:text-slate-400">📒 قەرز</button>
            <button type="button" onclick="setSaleType('installment')" id="stype-installment" class="sale-type-btn px-3 py-1 rounded-md text-xs font-bold text-slate-600 dark:text-slate-400">📅 قیست</button>
            <button type="button" onclick="setSaleType('quote')" id="stype-quote" class="sale-type-btn px-3 py-1 rounded-md text-xs font-bold text-slate-600 dark:text-slate-400">📄 دەرخستە</button>
        </div>

        <!-- کڕیار -->
        <div class="relative flex-1 min-w-[300px]">
            <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg p-1">
                <span class="text-slate-400 pr-1.5">👤</span>
                <input type="hidden" id="selectedCustomerId" value="">
                <input type="text" id="customerNameInput" placeholder="ناوی کڕیار یان گەڕان بە (ناو / مۆبایل / کەفیل)..."
                    oninput="searchCustomersLive(this.value)"
                    onfocus="searchCustomersLive(this.value)"
                    class="flex-1 bg-transparent text-xs text-slate-900 dark:text-white font-semibold focus:outline-none">
                <input type="text" id="customerPhoneInput" placeholder="ژ.مۆبایل"
                    class="w-28 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-xs text-slate-800 dark:text-slate-200 font-mono focus:outline-none">
                <input type="text" id="customerGuarantorInput" placeholder="ناوی کەفیل"
                    class="w-28 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-xs text-slate-800 dark:text-slate-200 focus:outline-none">
            </div>
            <div id="customerDropdown" class="hidden absolute left-0 right-0 top-full mt-1 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl shadow-xl z-40 max-h-52 overflow-y-auto"></div>
        </div>
    </div>

    <!-- ٢. ڕیزی دووەم: بارکۆد و گەڕانی کاڵا + دوگمەی کاڵای دەرەکی -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 shadow-sm flex flex-wrap items-center gap-2 shrink-0">
        
        <!-- خانەی بارکۆد و ناوی کاڵا -->
        <div class="relative flex-1 min-w-[260px]">
            <div class="flex items-center bg-emerald-50/50 dark:bg-slate-950 border-2 border-emerald-500 rounded-lg px-3 py-1.5">
                <span class="text-base ml-2">🔍</span>
                <input type="text" id="productSearchInput" autofocus
                    placeholder="بارکۆد لێبدە یان ناوی کاڵا بنووسە بۆ فرۆشتن..."
                    oninput="handleProductSearch(this.value)"
                    onkeydown="handleBarcodeEnter(event)"
                    class="w-full bg-transparent text-slate-900 dark:text-white text-sm font-bold focus:outline-none">
            </div>
            <div id="productDropdown" class="hidden absolute left-0 right-0 top-full mt-1 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl shadow-2xl z-30 max-h-60 overflow-y-auto"></div>
        </div>

        <!-- دوگمەی کردنەوەی کاڵای دەرەکی -->
        <button type="button" onclick="toggleExternalForm()" id="toggleExtBtn"
            class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-3.5 py-2 rounded-lg text-xs transition flex items-center gap-1.5 shadow-sm shrink-0">
            <span>⚡</span>
            <span>کاڵای دەرەکی</span>
        </button>

        <!-- فۆڕمی کاڵای دەرەکی (تەنها کاتێک کلیک لە دوگمەکە دەکرێت دەردەکەوێت) -->
        <div id="externalItemPanel" class="hidden items-center gap-1.5 bg-amber-50/90 dark:bg-slate-950 border-2 border-amber-400 dark:border-amber-500/60 rounded-lg px-2.5 py-1 transition-all">
            <input type="text" id="extItemName" placeholder="ناوی کاڵا..."
                class="w-36 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-xs text-slate-900 dark:text-white font-bold focus:outline-none focus:border-amber-500">
            
            <select id="extCurrency" class="bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-1.5 py-1 text-xs text-amber-700 dark:text-amber-400 font-bold">
                <option value="IQD">د.ع</option>
                <option value="USD">$</option>
            </select>

            <input type="number" step="any" id="extBuyPrice" placeholder="نرخی کڕین"
                class="w-24 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-xs text-slate-900 dark:text-white font-mono focus:outline-none">

            <input type="number" step="any" id="extSellPrice" placeholder="نرخی فرۆشتن"
                onkeydown="if(event.key==='Enter') addExternalItem()"
                class="w-24 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-xs text-slate-900 dark:text-white font-mono focus:outline-none">

            <input type="number" step="any" id="extQty" value="1" placeholder="عەدەد"
                onkeydown="if(event.key==='Enter') addExternalItem()"
                class="w-14 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-1.5 py-1 text-xs text-center text-slate-900 dark:text-white font-mono focus:outline-none">

            <button type="button" onclick="addExternalItem()"
                class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-3 py-1 rounded text-xs transition">
                + زیادکردن
            </button>
            <button type="button" onclick="toggleExternalForm()" class="text-slate-400 hover:text-red-500 px-1 text-xs font-bold" title="داخستن">✕</button>
        </div>
    </div>

    <!-- ٣. ڕیزی سێیەم: سەبەتەی فرۆشتن -->
    <div class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-y-auto flex-1">
            <table class="w-full text-right text-sm">
                <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-10 text-xs font-bold">
                    <tr>
                        <th class="py-2.5 px-2 w-14 text-center" title="کلیک بکە لە ژمارەی کاڵا بۆ بینینی نرخی کڕین و قازانج">#</th>
                        <th class="py-2.5 px-3">ناوی کاڵا و کۆگا</th>
                        <th class="py-2.5 px-2 w-64 text-center">زەمان (S/N و ماوە)</th>
                        <th class="py-2.5 px-2 w-32 text-center">عەدەد</th>
                        <th class="py-2.5 px-2 w-36 text-center">نرخی تاک (دینار)</th>
                        <th class="py-2.5 px-2 w-28 text-center">نرخی تاک ($)</th>
                        <th class="py-2.5 px-2 w-36 text-center">کۆی گشتی</th>
                        <th class="py-2.5 px-2 w-24 text-center">دیاری / سڕینەوە</th>
                    </tr>
                </thead>
                <tbody id="cartTableBody" class="divide-y divide-slate-200/70 dark:divide-slate-800">
                    <!-- کاڵاکانی سەبەتە لێرە دەردەکەون -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- ٤. ڕیزی چوارەم: کۆی گشتی، داشکان، صافی، پارەی دراو، باقی، و دوگمەکانی فرۆشتن -->
    <div class="bg-white dark:bg-slate-900 border-2 border-slate-300 dark:border-slate-800 rounded-xl p-2.5 shadow-md shrink-0">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-2.5 items-center">

            <div class="lg:col-span-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-3 py-2 flex flex-col justify-center">
                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                    <span>کۆی گشتی:</span>
                    <span id="itemsCountBadge" class="font-bold text-emerald-600 dark:text-emerald-400">0 کاڵا</span>
                </div>
                <div id="sumSubtotalIQD" class="text-base font-bold text-slate-900 dark:text-white font-mono mt-0.5">0 د.ع</div>
                <div id="sumSubtotalUSD" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 font-mono">$0.00</div>
            </div>

            <div class="lg:col-span-2 bg-amber-50/60 dark:bg-slate-950 border border-amber-200 dark:border-slate-800 rounded-lg px-2.5 py-1.5">
                <label class="block text-[11px] text-amber-800 dark:text-amber-400 font-bold mb-1">🏷️ داشکان (د.ع / $):</label>
                <div class="grid grid-cols-2 gap-1.5">
                    <input type="number" step="any" id="discountIQD" value="0" oninput="onDiscountChange('IQD')" title="داشکان بە دینار"
                        class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                    <input type="number" step="any" id="discountUSD" value="0" oninput="onDiscountChange('USD')" title="داشکان بە دۆلار"
                        class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-xs font-mono font-bold text-emerald-700 dark:text-emerald-400 focus:outline-none focus:border-amber-500">
                </div>
            </div>

            <div class="lg:col-span-2 bg-emerald-50 dark:bg-emerald-950/40 border-2 border-emerald-500 rounded-lg px-3 py-1.5 flex flex-col justify-center">
                <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300">صافی داواکراو:</span>
                <div id="sumNetIQD" class="text-lg font-extrabold text-emerald-950 dark:text-white font-mono leading-tight">0 د.ع</div>
                <div id="sumNetUSD" class="text-xs font-bold text-emerald-700 dark:text-emerald-400 font-mono">$0.00</div>
            </div>

            <div class="lg:col-span-2 bg-blue-50/60 dark:bg-slate-950 border border-blue-200 dark:border-slate-800 rounded-lg px-2.5 py-1.5">
                <div class="flex items-center justify-between mb-1">
                    <label class="text-[11px] text-blue-800 dark:text-blue-400 font-bold">💵 پارەی دراو:</label>
                    <button type="button" onclick="fillExactPaid()" class="text-[10px] bg-blue-600 text-white px-1.5 py-0.2 rounded font-bold hover:bg-blue-500">پڕکردنەوە</button>
                </div>
                <div class="grid grid-cols-2 gap-1.5">
                    <input type="number" step="any" id="paidIQD" value="0" oninput="onPaidChange('IQD')" title="پارەی دراو بە دینار"
                        class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                    <input type="number" step="any" id="paidUSD" value="0" oninput="onPaidChange('USD')" title="پارەی دراو بە دۆلار"
                        class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-xs font-mono font-bold text-emerald-700 dark:text-emerald-400 focus:outline-none focus:border-blue-500">
                </div>
            </div>

            <div class="lg:col-span-2 grid grid-cols-2 gap-1.5">
                <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-2 py-1.5">
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 block font-bold">باقی کڕیار:</span>
                    <div id="sumChangeIQD" class="text-xs font-extrabold text-amber-600 dark:text-amber-400 font-mono mt-0.5">0 د.ع</div>
                    <div id="sumChangeUSD" class="text-[10px] text-slate-500 font-mono">$0.00</div>
                </div>
                <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-2 py-1.5">
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 block font-bold">ماوە (قەرز):</span>
                    <div id="sumRemainingIQD" class="text-xs font-extrabold text-red-600 dark:text-red-400 font-mono mt-0.5">0 د.ع</div>
                    <div id="sumRemainingUSD" class="text-[10px] text-slate-500 font-mono">$0.00</div>
                </div>
            </div>

            <div class="lg:col-span-2 flex flex-col gap-1.5">
                <div class="grid grid-cols-2 gap-1.5">
                    <button type="button" onclick="submitSale('thermal')"
                        class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2 px-2 rounded-lg text-xs transition flex items-center justify-center gap-1 shadow-sm">
                        <span>🖨️</span>
                        <span>کاشێر</span>
                    </button>
                    <button type="button" onclick="submitSale('a4')"
                        class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-2 rounded-lg text-xs transition flex items-center justify-center gap-1 shadow-sm">
                        <span>📄</span>
                        <span>چاپی A4</span>
                    </button>
                </div>
                <button type="button" onclick="submitSale('none')"
                    class="w-full bg-slate-800 hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 text-white font-bold py-1.5 rounded-lg text-xs transition flex items-center justify-center gap-1">
                    <span>💾</span>
                    <span>فرۆشتن بێ وەسڵ</span>
                </button>
            </div>

        </div>
    </div>

</div>

<script>
window.salesState = {
    saleId: 0,
    saleType: 'cash',
    items: [],
    discountIQD: 0,
    paidIQD: 0,
    manualPaid: false,
    profitTimers: {}
};

// لیستێکی ڕەنگی ناتۆخ و جیاواز بۆ ڕیزەکانی ناو سەبەتە
const softRowColors = [
    'bg-emerald-50/55 dark:bg-emerald-950/25 hover:bg-emerald-100/60 dark:hover:bg-emerald-900/30',
    'bg-sky-50/55 dark:bg-sky-950/25 hover:bg-sky-100/60 dark:hover:bg-sky-900/30',
    'bg-amber-50/55 dark:bg-amber-950/25 hover:bg-amber-100/60 dark:hover:bg-amber-900/30',
    'bg-purple-50/55 dark:bg-purple-950/25 hover:bg-purple-100/60 dark:hover:bg-purple-900/30',
    'bg-rose-50/55 dark:bg-rose-950/25 hover:bg-rose-100/60 dark:hover:bg-rose-900/30',
    'bg-indigo-50/55 dark:bg-indigo-950/25 hover:bg-indigo-100/60 dark:hover:bg-indigo-900/30'
];

// کردنەوە و داخستنی بەشی کاڵای دەرەکی بە دوگمە
function toggleExternalForm() {
    const panel = document.getElementById('externalItemPanel');
    if (panel.classList.contains('hidden')) {
        panel.classList.remove('hidden');
        panel.classList.add('flex');
        document.getElementById('extItemName').focus();
    } else {
        panel.classList.add('hidden');
        panel.classList.remove('flex');
        document.getElementById('productSearchInput').focus();
    }
}

// ١. گەڕان بەدوای کاڵا بە ناو یان بارکۆد
let prodSearchTimer = null;
function handleProductSearch(query) {
    clearTimeout(prodSearchTimer);
    const dd = document.getElementById('productDropdown');
    if (!query.trim()) {
        dd.classList.add('hidden');
        return;
    }
    prodSearchTimer = setTimeout(() => {
        fetch(`api_sales.php?action=search_products&q=${encodeURIComponent(query)}`)
            .then(r => r.json())
            .then(res => {
                if (!res.data || res.data.length === 0) {
                    dd.innerHTML = `<div class="p-3 text-xs text-slate-500 text-center">هیچ کاڵایەک نەدۆزرایەوە</div>`;
                    dd.classList.remove('hidden');
                    return;
                }
                const rate = window.POS_CONFIG.usdRate;
                dd.innerHTML = res.data.map(p => {
                    const usd = (parseFloat(p.sell_price_iqd) / rate).toFixed(2);
                    return `
                    <div onclick='addProductToCart(${JSON.stringify(p)})'
                        class="p-2.5 hover:bg-emerald-50 dark:hover:bg-slate-800 cursor-pointer flex items-center justify-between border-b border-slate-100 dark:border-slate-800 text-xs">
                        <div>
                            <div class="font-bold text-slate-900 dark:text-white">${p.name}</div>
                            <div class="text-[11px] text-slate-500">بارکۆد: ${p.barcode || '---'} | ماوە لە کۆگا: <b class="text-emerald-600 dark:text-emerald-400">${p.stock}</b></div>
                        </div>
                        <div class="text-left font-mono">
                            <div class="font-bold text-emerald-600 dark:text-emerald-400">${Number(p.sell_price_iqd).toLocaleString()} د.ع</div>
                            <div class="text-[11px] text-slate-500">$${usd}</div>
                        </div>
                    </div>`;
                }).join('');
                dd.classList.remove('hidden');
            });
    }, 100);
}

function handleBarcodeEnter(e) {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    const q = e.target.value.trim();
    if (!q) return;

    fetch(`api_sales.php?action=search_products&q=${encodeURIComponent(q)}&exact=1`)
        .then(r => r.json())
        .then(res => {
            if (res.data && res.data.length > 0) {
                addProductToCart(res.data[0]);
            } else {
                fetch(`api_sales.php?action=search_products&q=${encodeURIComponent(q)}`)
                    .then(r2 => r2.json())
                    .then(res2 => {
                        if (res2.data && res2.data.length > 0) {
                            addProductToCart(res2.data[0]);
                        }
                    });
            }
        });
}

function addProductToCart(p) {
    document.getElementById('productDropdown').classList.add('hidden');
    const input = document.getElementById('productSearchInput');
    input.value = '';
    input.focus();

    const existing = window.salesState.items.find(i => !i.is_external && !i.is_gift && intval(i.product_id) === intval(p.id));
    if (existing) {
        existing.qty += 1;
        existing.stock = parseFloat(p.stock) || 0;
    } else {
        const sellPrice = parseFloat(p.sell_price_iqd) || 0;
        window.salesState.items.push({
            product_id: p.id,
            barcode: p.barcode || '',
            item_name: p.name,
            serial_number: '',
            warranty: '',
            stock: parseFloat(p.stock) || 0,
            is_external: 0,
            is_gift: 0,
            qty: 1,
            buy_price_iqd: parseFloat(p.buy_price_iqd) || 0,
            sell_price_iqd: sellPrice,
            original_sell_price_iqd: sellPrice,
            show_profit: false
        });
    }
    renderCart();
}

function intval(v) { return parseInt(v, 10) || 0; }

// ٢. زیادکردنی کاڵای دەرەکی
function addExternalItem() {
    const name = document.getElementById('extItemName').value.trim();
    const cur = document.getElementById('extCurrency').value;
    const buyInput = parseFloat(document.getElementById('extBuyPrice').value) || 0;
    const sellInput = parseFloat(document.getElementById('extSellPrice').value) || 0;
    const qty = parseFloat(document.getElementById('extQty').value) || 1;

    if (!name || sellInput <= 0) {
        alert('تکایە ناوی کاڵای دەرەکی و نرخی فرۆشتن بنووسە!');
        return;
    }

    const rate = window.POS_CONFIG.usdRate;
    const buyIQD = cur === 'USD' ? Math.round(buyInput * rate) : buyInput;
    const sellIQD = cur === 'USD' ? Math.round(sellInput * rate) : sellInput;

    window.salesState.items.push({
        product_id: null,
        barcode: 'EXT',
        item_name: name,
        serial_number: '',
        warranty: '',
        stock: null,
        is_external: 1,
        is_gift: 0,
        qty: qty,
        buy_price_iqd: buyIQD,
        sell_price_iqd: sellIQD,
        original_sell_price_iqd: sellIQD,
        show_profit: false
    });

    document.getElementById('extItemName').value = '';
    document.getElementById('extBuyPrice').value = '';
    document.getElementById('extSellPrice').value = '';
    document.getElementById('extQty').value = '1';

    // داخستنی خانەی کاڵای دەرەکی دوای زیادکردن و گەڕانەوە بۆ سەر بارکۆد
    const panel = document.getElementById('externalItemPanel');
    panel.classList.add('hidden');
    panel.classList.remove('flex');
    document.getElementById('productSearchInput').focus();

    renderCart();
}

// پێشاندانی نرخی کڕین و قازانج بۆ ماوەی ٥ چرکە کاتێک کلیک لە ژمارەی کاڵا دەکرێت
function revealItemProfit(idx) {
    const item = window.salesState.items[idx];
    if (!item) return;

    item.show_profit = true;
    renderCart();

    if (window.salesState.profitTimers[idx]) {
        clearTimeout(window.salesState.profitTimers[idx]);
    }

    window.salesState.profitTimers[idx] = setTimeout(() => {
        if (window.salesState.items[idx]) {
            window.salesState.items[idx].show_profit = false;
            renderCart();
        }
    }, 5000);
}

// گۆڕینی کاڵا بۆ دیاری (نرخی سفر) یان گەڕاندنەوەی بۆ نرخی ئاسایی
function toggleGiftItem(idx) {
    const item = window.salesState.items[idx];
    if (!item) return;

    if (!item.is_gift) {
        item.original_sell_price_iqd = item.sell_price_iqd > 0 ? item.sell_price_iqd : (item.original_sell_price_iqd || 0);
        item.is_gift = 1;
        item.sell_price_iqd = 0;
    } else {
        item.is_gift = 0;
        item.sell_price_iqd = item.original_sell_price_iqd || 0;
    }
    renderCart();
}

// ٣. پیشاندانی سەبەتەی کڕین
function renderCart() {
    const tbody = document.getElementById('cartTableBody');
    if (!tbody) return;
    const rate = window.POS_CONFIG.usdRate;

    if (window.salesState.items.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="py-20 text-center text-slate-400 dark:text-slate-500 text-sm font-semibold">
                    🛒 سەبەتەی فرۆشتن بەتاڵە — بارکۆد لێبدە یان ناوی کاڵا لە سەرەوە بنووسە
                </td>
            </tr>`;
        recalculateTotals();
        return;
    }

    tbody.innerHTML = window.salesState.items.map((item, idx) => {
        const unitUSD = (item.sell_price_iqd / rate).toFixed(2);
        const rowTotalIQD = item.qty * item.sell_price_iqd;
        const rowTotalUSD = (rowTotalIQD / rate).toFixed(2);
        const colorClass = softRowColors[idx % softRowColors.length];

        // حسابکردنی عەدەدی ماوە لە کۆگا
        const remainingStock = item.stock !== null && !item.is_external ? (parseFloat(item.stock) - parseFloat(item.qty)) : null;

        // حسابکردنی قازانج بەم نرخە
        const unitProfitIQD = item.sell_price_iqd - item.buy_price_iqd;
        const totalProfitIQD = unitProfitIQD * item.qty;
        const buyPriceUSD = (item.buy_price_iqd / rate).toFixed(2);
        const totalProfitUSD = (totalProfitIQD / rate).toFixed(2);

        return `
        <tr class="${colorClass} transition">
            <!-- دوگمەی ژمارەی کاڵا (١، ٢، ٣) بۆ پیشاندانی نرخی کڕین و قازانج بۆ ٥ چرکە -->
            <td class="py-2 px-2 text-center">
                <button type="button" onclick="revealItemProfit(${idx})"
                    title="کلیک بکە بۆ بینینی نرخی کڕین و قازانج بۆ ٥ چرکە"
                    class="w-8 h-8 rounded-lg bg-white dark:bg-slate-800 hover:bg-emerald-600 hover:text-white border border-slate-300 dark:border-slate-700 font-mono font-extrabold text-xs text-slate-700 dark:text-slate-200 shadow-sm transition">
                    ${idx + 1}
                </button>
            </td>

            <!-- ناوی کاڵا + عەدەدی ماوە لە کۆگا + زانیاری کڕین و قازانج (بۆ ٥ چرکە) -->
            <td class="py-2 px-3">
                <div class="font-bold text-slate-900 dark:text-white flex flex-wrap items-center gap-1.5">
                    <span>${item.item_name}</span>
                    ${item.is_external ? '<span class="text-[10px] bg-amber-200/80 dark:bg-amber-500/20 text-amber-900 dark:text-amber-300 px-1.5 py-0.5 rounded font-bold">⚡ دەرەکی</span>' : ''}
                    ${!item.is_external && remainingStock !== null ? `
                        <span class="text-[11px] px-2 py-0.5 rounded-md font-bold ${remainingStock <= 0 ? 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300'}">
                            📦 ماوە لە کۆگا: ${remainingStock}
                        </span>
                    ` : ''}
                    ${item.is_gift ? '<span class="text-[10px] bg-pink-100 dark:bg-pink-500/20 text-pink-700 dark:text-pink-300 border border-pink-300 dark:border-pink-500/40 px-2 py-0.5 rounded-full font-extrabold">🎁 دیاری (بێبەرامبەر)</span>' : ''}
                </div>

                ${item.show_profit ? `
                <div class="mt-1.5 inline-flex flex-wrap items-center gap-3 bg-slate-900 dark:bg-slate-950 text-white px-3 py-1 rounded-lg text-[11px] font-mono shadow-md border border-emerald-500/40 animate-pulse">
                    <span>🔒 نرخی کڕین: <b class="text-amber-400">${Math.round(item.buy_price_iqd).toLocaleString()} د.ع</b> ($${buyPriceUSD})</span>
                    <span>|</span>
                    <span>📈 قازانج بەم نرخە: <b class="${totalProfitIQD >= 0 ? 'text-emerald-400' : 'text-red-400'}">${Math.round(totalProfitIQD).toLocaleString()} د.ع</b> ($${totalProfitUSD})</span>
                </div>
                ` : ''}
            </td>

            <!-- بەشی زەمان: S/N و ماوەی زەمان -->
            <td class="py-2 px-2 text-center">
                <div class="flex items-center gap-1">
                    <input type="text" placeholder="S/N کۆدی زەمان" value="${item.serial_number || ''}"
                        oninput="window.salesState.items[${idx}].serial_number = this.value"
                        class="w-32 bg-white/90 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                    <input type="text" placeholder="ماوەی زەمان (نموونە: ١ ساڵ)" value="${item.warranty || ''}"
                        oninput="window.salesState.items[${idx}].warranty = this.value"
                        class="w-28 bg-white/90 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                </div>
            </td>

            <!-- عەدەد -->
            <td class="py-2 px-2 text-center">
                <div class="inline-flex items-center border border-slate-300 dark:border-slate-700 rounded-lg overflow-hidden bg-white dark:bg-slate-950">
                    <button type="button" onclick="stepCartQty(${idx}, -1)" class="px-2 py-1 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 font-bold">-</button>
                    <input type="number" step="any" min="0.01" value="${item.qty}"
                        oninput="updateCartItemQty(${idx}, this.value)"
                        class="w-14 bg-transparent text-center font-mono font-bold text-slate-900 dark:text-white focus:outline-none">
                    <button type="button" onclick="stepCartQty(${idx}, 1)" class="px-2 py-1 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 font-bold">+</button>
                </div>
            </td>

            <!-- نرخی تاک بە دینار -->
            <td class="py-2 px-2 text-center">
                <input type="number" step="any" value="${Math.round(item.sell_price_iqd)}"
                    ${item.is_gift ? 'readonly' : ''}
                    oninput="updateCartItemPrice(${idx}, 'IQD', this.value)"
                    class="w-32 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1 text-center font-mono font-bold ${item.is_gift ? 'text-pink-600 line-through opacity-75' : 'text-slate-900 dark:text-white'} focus:outline-none focus:border-emerald-500">
            </td>

            <!-- نرخی تاک بە دۆلار -->
            <td class="py-2 px-2 text-center">
                <input type="number" step="any" value="${unitUSD}"
                    ${item.is_gift ? 'readonly' : ''}
                    oninput="updateCartItemPrice(${idx}, 'USD', this.value)"
                    class="w-24 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1 text-center font-mono font-bold text-emerald-700 dark:text-emerald-400 focus:outline-none focus:border-emerald-500">
            </td>

            <!-- کۆی گشتی ڕیز -->
            <td class="py-2 px-2 text-center font-mono">
                <div class="font-bold text-slate-900 dark:text-white row-total-iqd">${Math.round(rowTotalIQD).toLocaleString()} د.ع</div>
                <div class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold row-total-usd">$${rowTotalUSD}</div>
            </td>

            <!-- دوگمەی دیاری + دوگمەی سڕینەوە -->
            <td class="py-2 px-2 text-center">
                <div class="flex items-center justify-center gap-1">
                    <button type="button" onclick="toggleGiftItem(${idx})"
                        title="کردن بە دیاری (نرخی سفر)"
                        class="px-2 py-1 rounded-lg text-xs font-bold border transition ${item.is_gift ? 'bg-pink-600 text-white border-pink-600 shadow-sm' : 'bg-white dark:bg-slate-800 text-pink-600 dark:text-pink-400 border-pink-300 dark:border-slate-700 hover:bg-pink-50'}">
                        🎁 دیاری
                    </button>
                    <button type="button" onclick="removeCartItem(${idx})" class="text-red-500 hover:bg-red-50 dark:hover:bg-red-500/20 p-1.5 rounded-lg transition" title="سڕینەوە">🗑️</button>
                </div>
            </td>
        </tr>`;
    }).join('');

    recalculateTotals();
}

function stepCartQty(idx, delta) {
    const nextQty = Math.max(1, (parseFloat(window.salesState.items[idx].qty) || 1) + delta);
    window.salesState.items[idx].qty = nextQty;
    renderCart();
}

function updateCartItemQty(idx, val) {
    const qty = parseFloat(val) || 0;
    window.salesState.items[idx].qty = qty;
    updateRowDisplayOnly(idx);
    recalculateTotals();
}

function updateCartItemPrice(idx, currency, val) {
    const item = window.salesState.items[idx];
    if (item.is_gift) return;

    const num = parseFloat(val) || 0;
    const rate = window.POS_CONFIG.usdRate;
    const row = document.getElementById('cartTableBody').children[idx];

    if (currency === 'IQD') {
        item.sell_price_iqd = num;
        item.original_sell_price_iqd = num;
        if (row) {
            row.querySelectorAll('input[type="number"]')[2].value = (num / rate).toFixed(2);
        }
    } else {
        const iqd = Math.round(num * rate);
        item.sell_price_iqd = iqd;
        item.original_sell_price_iqd = iqd;
        if (row) {
            row.querySelectorAll('input[type="number"]')[1].value = iqd;
        }
    }
    updateRowDisplayOnly(idx);
    recalculateTotals();
}

function updateRowDisplayOnly(idx) {
    const item = window.salesState.items[idx];
    const rate = window.POS_CONFIG.usdRate;
    const row = document.getElementById('cartTableBody').children[idx];
    if (!row) return;
    const rowTotalIQD = item.qty * item.sell_price_iqd;
    row.querySelector('.row-total-iqd').innerText = Math.round(rowTotalIQD).toLocaleString() + ' د.ع';
    row.querySelector('.row-total-usd').innerText = '$' + (rowTotalIQD / rate).toFixed(2);
}

function removeCartItem(idx) {
    if (window.salesState.profitTimers[idx]) {
        clearTimeout(window.salesState.profitTimers[idx]);
        delete window.salesState.profitTimers[idx];
    }
    window.salesState.items.splice(idx, 1);
    renderCart();
}

// ٤. حسابکردنی کۆی گشتی و داشکان و باقی
function recalculateTotals() {
    const rate = window.POS_CONFIG.usdRate;
    const subtotalIQD = window.salesState.items.reduce((sum, i) => sum + (i.qty * i.sell_price_iqd), 0);
    const discountIQD = window.salesState.discountIQD || 0;
    const netIQD = Math.max(0, subtotalIQD - discountIQD);

    if (!window.salesState.manualPaid) {
        if (window.salesState.saleType === 'cash') {
            window.salesState.paidIQD = netIQD;
        } else if (window.salesState.saleType === 'debt' || window.salesState.saleType === 'quote') {
            window.salesState.paidIQD = 0;
        }
        document.getElementById('paidIQD').value = Math.round(window.salesState.paidIQD);
        document.getElementById('paidUSD').value = (window.salesState.paidIQD / rate).toFixed(2);
    }

    const paidIQD = window.salesState.paidIQD || 0;
    const changeIQD = paidIQD > netIQD ? (paidIQD - netIQD) : 0;
    const remainingIQD = paidIQD < netIQD ? (netIQD - paidIQD) : 0;

    document.getElementById('itemsCountBadge').innerText = `${window.salesState.items.length} کاڵا`;
    document.getElementById('sumSubtotalIQD').innerText = Math.round(subtotalIQD).toLocaleString() + ' د.ع';
    document.getElementById('sumSubtotalUSD').innerText = '$' + (subtotalIQD / rate).toFixed(2);

    document.getElementById('sumNetIQD').innerText = Math.round(netIQD).toLocaleString() + ' د.ع';
    document.getElementById('sumNetUSD').innerText = '$' + (netIQD / rate).toFixed(2);

    document.getElementById('sumChangeIQD').innerText = Math.round(changeIQD).toLocaleString() + ' د.ع';
    document.getElementById('sumChangeUSD').innerText = '$' + (changeIQD / rate).toFixed(2);

    document.getElementById('sumRemainingIQD').innerText = Math.round(remainingIQD).toLocaleString() + ' د.ع';
    document.getElementById('sumRemainingUSD').innerText = '$' + (remainingIQD / rate).toFixed(2);
}

function onDiscountChange(cur) {
    const rate = window.POS_CONFIG.usdRate;
    if (cur === 'IQD') {
        const iqd = parseFloat(document.getElementById('discountIQD').value) || 0;
        window.salesState.discountIQD = iqd;
        document.getElementById('discountUSD').value = (iqd / rate).toFixed(2);
    } else {
        const usd = parseFloat(document.getElementById('discountUSD').value) || 0;
        const iqd = Math.round(usd * rate);
        window.salesState.discountIQD = iqd;
        document.getElementById('discountIQD').value = iqd;
    }
    recalculateTotals();
}

function onPaidChange(cur) {
    window.salesState.manualPaid = true;
    const rate = window.POS_CONFIG.usdRate;
    if (cur === 'IQD') {
        const iqd = parseFloat(document.getElementById('paidIQD').value) || 0;
        window.salesState.paidIQD = iqd;
        document.getElementById('paidUSD').value = (iqd / rate).toFixed(2);
    } else {
        const usd = parseFloat(document.getElementById('paidUSD').value) || 0;
        const iqd = Math.round(usd * rate);
        window.salesState.paidIQD = iqd;
        document.getElementById('paidIQD').value = iqd;
    }
    recalculateTotals();
}

function fillExactPaid() {
    window.salesState.manualPaid = false;
    window.salesState.saleType = 'cash';
    recalculateTotals();
}

function setSaleType(type) {
    window.salesState.saleType = type;
    window.salesState.manualPaid = false;
    document.querySelectorAll('.sale-type-btn').forEach(btn => {
        btn.className = "sale-type-btn px-3 py-1 rounded-md text-xs font-bold text-slate-600 dark:text-slate-400";
    });
    const activeBtn = document.getElementById(`stype-${type}`);
    if (activeBtn) {
        activeBtn.className = "sale-type-btn px-3 py-1 rounded-md text-xs font-bold bg-emerald-600 text-white shadow-sm";
    }
    recalculateTotals();
}

// ٥. گەڕانی کڕیار
let custTimer = null;
function searchCustomersLive(q) {
    clearTimeout(custTimer);
    document.getElementById('selectedCustomerId').value = '';
    const dd = document.getElementById('customerDropdown');

    custTimer = setTimeout(() => {
        fetch(`api_sales.php?action=search_customers&q=${encodeURIComponent(q)}`)
            .then(r => r.json())
            .then(res => {
                if (!res.data || res.data.length === 0) {
                    dd.classList.add('hidden');
                    return;
                }
                dd.innerHTML = res.data.map(c => `
                    <div onclick='selectCustomer(${JSON.stringify(c)})'
                        class="p-2.5 hover:bg-emerald-50 dark:hover:bg-slate-800 cursor-pointer border-b border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                        <div>
                            <span class="font-bold text-slate-900 dark:text-white">${c.name}</span>
                            <span class="text-slate-500 mr-2">📞 ${c.phone || '---'}</span>
                        </div>
                        <div class="text-[11px] font-semibold text-amber-600 dark:text-amber-400">
                            کەفیل: ${c.guarantor || '---'}
                        </div>
                    </div>
                `).join('');
                dd.classList.remove('hidden');
            });
    }, 150);
}

function selectCustomer(c) {
    document.getElementById('selectedCustomerId').value = c.id;
    document.getElementById('customerNameInput').value = c.name;
    document.getElementById('customerPhoneInput').value = c.phone || '';
    document.getElementById('customerGuarantorInput').value = c.guarantor || '';
    document.getElementById('customerDropdown').classList.add('hidden');
}

document.addEventListener('click', (e) => {
    if (!e.target.closest('#customerNameInput') && !e.target.closest('#customerDropdown')) {
        const cd = document.getElementById('customerDropdown');
        if (cd) cd.classList.add('hidden');
    }
    if (!e.target.closest('#productSearchInput') && !e.target.closest('#productDropdown')) {
        const pd = document.getElementById('productDropdown');
        if (pd) pd.classList.add('hidden');
    }
});

// ٦. گەڕانەوە بۆ وەسڵی پێشتر
function navigateReceipt(dir) {
    const currentId = window.salesState.saleId;
    fetch(`api_sales.php?action=get_receipt&id=${currentId}&dir=${dir}`)
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                populateReceiptData(res.sale, res.items);
            } else {
                alert(res.message || 'وەسڵ نەدۆزرایەوە');
            }
        });
}

function loadExactReceipt() {
    const id = intval(document.getElementById('receiptNumberInput').value);
    if (id <= 0) return;
    fetch(`api_sales.php?action=get_receipt&id=${id}&dir=exact`)
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                populateReceiptData(res.sale, res.items);
            } else {
                alert(`وەسڵی ژمارە #${id} بوونی نییە!`);
            }
        });
}

function populateReceiptData(sale, items) {
    const rate = window.POS_CONFIG.usdRate;
    window.salesState.saleId = intval(sale.id);
    window.salesState.saleType = sale.sale_type;
    window.salesState.discountIQD = parseFloat(sale.discount_iqd) || 0;
    window.salesState.paidIQD = parseFloat(sale.paid_iqd) || 0;
    window.salesState.manualPaid = true;

    document.getElementById('receiptNumberInput').value = sale.id;
    const badge = document.getElementById('receiptModeBadge');
    badge.innerText = `دەستکاری #${sale.id}`;
    badge.className = "text-[11px] bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 px-2 py-0.5 rounded font-bold";

    document.getElementById('selectedCustomerId').value = sale.customer_id || '';
    document.getElementById('customerNameInput').value = sale.customer_name || '';
    document.getElementById('customerPhoneInput').value = sale.customer_phone || '';
    document.getElementById('customerGuarantorInput').value = sale.guarantor_name || '';

    document.getElementById('discountIQD').value = Math.round(window.salesState.discountIQD);
    document.getElementById('discountUSD').value = (window.salesState.discountIQD / rate).toFixed(2);
    document.getElementById('paidIQD').value = Math.round(window.salesState.paidIQD);
    document.getElementById('paidUSD').value = (window.salesState.paidIQD / rate).toFixed(2);

    setSaleType(sale.sale_type);
    window.salesState.manualPaid = true;

    window.salesState.items = items.map(i => ({
        product_id: i.product_id,
        barcode: i.barcode,
        item_name: i.item_name,
        serial_number: i.serial_number || '',
        warranty: i.warranty || '',
        stock: i.stock !== undefined ? parseFloat(i.stock) : null,
        is_external: intval(i.is_external),
        is_gift: intval(i.is_gift),
        qty: parseFloat(i.qty),
        buy_price_iqd: parseFloat(i.buy_price_iqd),
        sell_price_iqd: parseFloat(i.sell_price_iqd),
        original_sell_price_iqd: parseFloat(i.sell_price_iqd),
        show_profit: false
    }));

    renderCart();
}

function resetNewSale() {
    const nextIdInp = document.getElementById('receiptNumberInput');
    window.salesState = {
        saleId: 0,
        saleType: 'cash',
        items: [],
        discountIQD: 0,
        paidIQD: 0,
        manualPaid: false,
        profitTimers: {}
    };
    nextIdInp.value = nextIdInp.getAttribute('data-next-id');
    const badge = document.getElementById('receiptModeBadge');
    badge.innerText = 'نوێ';
    badge.className = "text-[11px] bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 px-2 py-0.5 rounded font-bold";

    document.getElementById('selectedCustomerId').value = '';
    document.getElementById('customerNameInput').value = '';
    document.getElementById('customerPhoneInput').value = '';
    document.getElementById('customerGuarantorInput').value = '';
    document.getElementById('discountIQD').value = 0;
    document.getElementById('discountUSD').value = 0;
    document.getElementById('paidIQD').value = 0;
    document.getElementById('paidUSD').value = 0;

    setSaleType('cash');
    renderCart();
    document.getElementById('productSearchInput').focus();
}

function submitSale(printFormat) {
    if (window.salesState.items.length === 0) {
        alert('تکایە سەرەتا کاڵا زیاد بکە بۆ ناو سەبەتە!');
        return;
    }

    const subtotalIQD = window.salesState.items.reduce((sum, i) => sum + (i.qty * i.sell_price_iqd), 0);
    const discountIQD = window.salesState.discountIQD || 0;
    const netIQD = Math.max(0, subtotalIQD - discountIQD);
    const paidIQD = window.salesState.paidIQD || 0;
    const changeIQD = paidIQD > netIQD ? (paidIQD - netIQD) : 0;
    const remainingIQD = paidIQD < netIQD ? (netIQD - paidIQD) : 0;

    const payload = {
        sale_id: window.salesState.saleId,
        customer_id: document.getElementById('selectedCustomerId').value,
        customer_name: document.getElementById('customerNameInput').value.trim() || 'کڕیاری گشتی',
        customer_phone: document.getElementById('customerPhoneInput').value.trim(),
        guarantor_name: document.getElementById('customerGuarantorInput').value.trim(),
        sale_type: window.salesState.saleType,
        subtotal_iqd: subtotalIQD,
        discount_iqd: discountIQD,
        total_iqd: netIQD,
        paid_iqd: paidIQD,
        change_iqd: changeIQD,
        remaining_iqd: remainingIQD,
        usd_rate: window.POS_CONFIG.usdRate,
        items: window.salesState.items
    };

    fetch('api_sales.php?action=save_sale', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            const savedId = res.sale_id;
            const nextInp = document.getElementById('receiptNumberInput');
            if (savedId >= intval(nextInp.getAttribute('data-next-id'))) {
                nextInp.setAttribute('data-next-id', savedId + 1);
            }

            if (printFormat === 'thermal' || printFormat === 'a4') {
                window.open(`print_receipt.php?id=${savedId}&format=${printFormat}`, '_blank', 'width=900,height=700');
            }
            resetNewSale();
            if (typeof loadSoldReceipts === 'function') {
    loadSoldReceipts();
}
        } else {
            alert('هەڵە: ' + res.message);
        }
    });
}

renderCart();
</script>