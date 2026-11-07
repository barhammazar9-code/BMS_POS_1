<?php require_once 'db.php'; ?>

<div class="h-full w-full flex flex-col gap-2.5 overflow-hidden select-none" id="purchasedReceiptsCleanApp">

    <!-- سەرپەڕە: دوگمەی چوونە نێوان (لیستی وەسڵە کڕدراوەکان) و (زیادکردنی وەسڵی نوێ) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 shadow-sm flex flex-wrap items-center justify-between gap-3 shrink-0">
        
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl">
                📥
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white leading-tight">بەشی وەسڵە کڕدراوەکان</h2>
                    <span id="purActiveCurrencyBadge" class="text-[11px] bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 px-2 py-0.5 rounded-lg font-extrabold"></span>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">بینین، دەستکاریکردن و داخڵکردنی وەسڵی کڕین بەپێی دراوی چالاکی سیستەم</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="togglePurchaseView('list')" id="btnViewPurList"
                class="px-4 py-2 rounded-xl text-xs font-extrabold transition flex items-center gap-1.5 bg-slate-800 text-white shadow-sm">
                <span>🧾</span>
                <span>لیستی وەسڵە کڕدراوەکان</span>
                <span id="prTotalCountBadge" class="bg-white/20 px-2 py-0.2 rounded-md font-mono">0</span>
            </button>

            <button type="button" onclick="startNewPurchaseForm()" id="btnViewPurAdd"
                class="px-4 py-2 rounded-xl text-xs font-extrabold transition flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm">
                <span>➕</span>
                <span id="btnViewPurAddLabel">زیادکردنی وەسڵی نوێ</span>
            </button>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- دیمەنی ١: خشتەی وەسڵە کڕدراوەکان                                        -->
    <!-- ======================================================================= -->
    <div id="purViewListPanel" class="flex-1 flex flex-col gap-2.5 overflow-hidden">
        
        <div class="grid grid-cols-1 md:grid-cols-12 gap-2.5 shrink-0">
            <div class="md:col-span-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 flex items-center gap-2">
                <span class="text-slate-400">🔍</span>
                <input type="text" id="prSearchInput" oninput="renderCleanPurchasesTable()"
                    placeholder="گەڕان بە ژمارەی وەسڵ یان ناوی کۆمپانیا..."
                    class="w-full bg-transparent text-xs font-bold text-slate-900 dark:text-white focus:outline-none">
            </div>

            <div class="md:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 flex items-center">
                <select id="prTypeFilter" onchange="renderCleanPurchasesTable()"
                    class="w-full bg-transparent text-xs font-bold text-slate-800 dark:text-slate-200 focus:outline-none">
                    <option value="all">هەموو وەسڵەکان (نەقد و قەرز)</option>
                    <option value="cash">💵 تەنها وەسڵی نەقد</option>
                    <option value="debt">📒 تەنها وەسڵی قەرز</option>
                </select>
            </div>

            <div class="md:col-span-2 bg-white dark:bg-slate-900 border border-emerald-200 dark:border-emerald-500/30 rounded-xl px-3 py-1.5 flex flex-col justify-center">
                <span class="text-[10px] text-slate-500 font-bold">کۆی گشتی کڕین:</span>
                <span id="prSumTotalDisplay" class="text-xs font-extrabold text-emerald-600 font-mono">0</span>
            </div>

            <div class="md:col-span-3 bg-white dark:bg-slate-900 border border-red-200 dark:border-red-500/30 rounded-xl px-3 py-1.5 flex items-center justify-between">
                <div>
                    <span class="text-[10px] text-red-600 font-bold block">کۆی قەرزی ماوە:</span>
                    <span id="prSumDebtDisplay" class="text-xs font-extrabold text-red-600 font-mono">0</span>
                </div>
                <button onclick="openTab('company_debts', 'قەرزی کۆمپانیاکان', '💳', 'company_debts.php')"
                    class="bg-red-50 hover:bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-300 px-2.5 py-1 rounded-lg text-[11px] font-bold transition">
                    بەشی قەرزەکان ⬅️
                </button>
            </div>
        </div>

        <div class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="overflow-y-auto flex-1">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 font-bold border-b border-slate-200 dark:border-slate-800 sticky top-0">
                        <tr>
                            <th class="py-3 px-3 text-center w-28">ژمارەی وەسڵ</th>
                            <th class="py-3 px-3">ناوی کۆمپانیا</th>
                            <th class="py-3 px-3 text-center w-28">جۆری کڕین</th>
                            <th class="py-3 px-3 text-center w-28">بەرواری وەسڵ</th>
                            <th class="py-3 px-3 text-center w-28">ژ.کاڵا</th>
                            <th class="py-3 px-3 text-center w-36">کۆی وەسڵ</th>
                            <th class="py-3 px-3 text-center w-32">پارەی دراو</th>
                            <th class="py-3 px-3 text-center w-32">ماوە (قەرز)</th>
                            <th class="py-3 px-3 text-center w-52">بینین / دەستکاری / سڕینەوە</th>
                        </tr>
                    </thead>
                    <tbody id="cleanPurchasesTableBody" class="divide-y divide-slate-200 dark:divide-slate-800"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- دیمەنی ٢: فۆڕمی زیادکردن یان دەستکاریکردنی وەسڵی کڕین                    -->
    <!-- ======================================================================= -->
    <div id="purViewAddPanel" class="hidden flex-1 flex-col gap-2 overflow-hidden">
        
        <!-- شریتی ئاگادارکردنەوە لە کاتی دەستکاریکردنی وەسڵی پێشوو -->
        <div id="purEditModeBanner" class="hidden bg-amber-100 dark:bg-amber-950/60 border-2 border-amber-400 rounded-xl px-4 py-2 items-center justify-between text-xs shrink-0">
            <div class="font-extrabold text-amber-900 dark:text-amber-300 flex items-center gap-2">
                <span>✏️</span>
                <span id="purEditModeText">دەستکاریکردنی وەسڵی کڕین</span>
            </div>
            <button type="button" onclick="startNewPurchaseForm()" class="bg-white dark:bg-slate-800 hover:bg-red-50 text-red-600 px-3 py-1 rounded-lg font-bold border border-amber-300">
                ✕ هەڵوەشاندنەوەی دەستکاری و کردنەوەی وەسڵی نوێ
            </button>
        </div>

        <!-- بەشی ١: زانیاری سەرەکی وەسڵ -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 shadow-sm grid grid-cols-1 md:grid-cols-12 gap-3 items-center shrink-0">
            
            <div class="md:col-span-4 relative">
                <label class="block text-[11px] font-extrabold text-slate-600 dark:text-slate-400 mb-1">🏢 ١. ناوی کۆمپانیا:</label>
                <input type="hidden" id="purCompanyId" value="">
                <input type="text" id="purCompanyName" placeholder="ناوی کۆمپانیا هەڵبژێرە یان بنووسە..."
                    oninput="filterPurCompanyDropdown(this.value)"
                    onfocus="filterPurCompanyDropdown(this.value)"
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                <div id="purCompanyDropdown" class="hidden absolute left-0 right-0 top-full mt-1 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl shadow-xl z-40 max-h-48 overflow-y-auto"></div>
            </div>

            <div class="md:col-span-3">
                <label class="block text-[11px] font-extrabold text-slate-600 dark:text-slate-400 mb-1">💳 ٢. جۆری کڕین:</label>
                <div class="grid grid-cols-2 gap-1.5 bg-slate-100 dark:bg-slate-950 p-1 rounded-lg border border-slate-200 dark:border-slate-800">
                    <button type="button" onclick="setCleanPurchaseType('cash')" id="purTypeBtn-cash"
                        class="py-1.5 rounded-md text-xs font-extrabold bg-emerald-600 text-white shadow-sm transition">
                        💵 نەقد
                    </button>
                    <button type="button" onclick="setCleanPurchaseType('debt')" id="purTypeBtn-debt"
                        class="py-1.5 rounded-md text-xs font-extrabold text-slate-600 dark:text-slate-400 transition">
                        📒 قەرز
                    </button>
                </div>
            </div>

            <div class="md:col-span-2">
                <label class="block text-[11px] font-extrabold text-slate-600 dark:text-slate-400 mb-1">🔢 ٣. ژمارەی وەسڵ:</label>
                <input type="text" id="purInvoiceNumber" placeholder="ژمارەی وەسڵ..."
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-mono font-bold text-emerald-700 dark:text-emerald-400 focus:outline-none">
            </div>

            <div class="md:col-span-3">
                <label class="block text-[11px] font-extrabold text-slate-600 dark:text-slate-400 mb-1">📅 ٤. بەرواری وەسڵ:</label>
                <input type="date" id="purDateInput" value="<?= date('Y-m-d') ?>"
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none">
            </div>
        </div>

        <!-- بەشی ٢: داخڵکردنی کاڵا بۆ ناو وەسڵ (بەپێی دراوی چالاک) -->
        <div class="bg-emerald-50/70 dark:bg-slate-900 border-2 border-emerald-500/80 rounded-xl p-3 shadow-sm shrink-0">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-2 items-end">
                
                <div class="md:col-span-4 relative">
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-[11px] font-extrabold text-emerald-950 dark:text-emerald-300">📦 ناوی کاڵا (هەڵبژێرە یان نوێ بنووسە):</label>
                        <span id="entryProdModeBadge" class="text-[10px] bg-amber-100 text-amber-800 px-2 py-0.2 rounded font-bold">✨ کاڵای نوێ</span>
                    </div>
                    <input type="hidden" id="entryProductId" value="">
                    <input type="text" id="entryProductName" placeholder="ناوی کاڵا یان بارکۆد بنووسە..."
                        oninput="filterCleanEntryProducts(this.value)"
                        onfocus="filterCleanEntryProducts(this.value)"
                        class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                    <div id="entryProductDropdown" class="hidden absolute left-0 right-0 top-full mt-1 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl shadow-2xl z-40 max-h-56 overflow-y-auto"></div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">بارکۆد:</label>
                    <input type="text" id="entryBarcode" placeholder="بارکۆد"
                        class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-2 text-xs font-mono text-slate-900 dark:text-white focus:outline-none">
                </div>

                <div class="md:col-span-1">
                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">دانە:</label>
                    <input type="number" step="any" min="0.01" id="entryQty" value="1"
                        oninput="updateCleanAvgPreview()"
                        onkeydown="if(event.key==='Enter') addItemToCleanPurCart()"
                        class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-2 text-xs font-mono font-extrabold text-center text-slate-900 dark:text-white focus:outline-none">
                </div>

                <div class="md:col-span-2">
                    <label id="lblEntryBuyPrice" class="block text-[11px] font-bold text-emerald-800 dark:text-emerald-400 mb-1">نرخی کڕینی تاک:</label>
                    <div class="flex items-center gap-1">
                        <select id="entryCurrency" onchange="onEntryCurrencyManualChange()"
                            class="bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-1.5 py-2 text-xs font-bold text-emerald-700 dark:text-emerald-400">
                            <option value="IQD">د.ع</option>
                            <option value="USD">$</option>
                        </select>
                        <input type="number" step="any" id="entryBuyPrice" placeholder="نرخی کڕین"
                            oninput="updateCleanAvgPreview()"
                            onkeydown="if(event.key==='Enter') addItemToCleanPurCart()"
                            class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-2 text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none">
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label id="lblEntrySellPrice" class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">نرخی فرۆشتنی تاک:</label>
                    <input type="number" step="any" id="entrySellPrice" placeholder="نرخی فرۆشتن"
                        onkeydown="if(event.key==='Enter') addItemToCleanPurCart()"
                        class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-2 text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none">
                </div>

                <div class="md:col-span-1">
                    <button type="button" onclick="addItemToCleanPurCart()"
                        class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold py-2 px-2 rounded-lg text-xs transition shadow-sm">
                        + داخڵکردن
                    </button>
                </div>
            </div>

            <!-- شریتی نیشاندانی تێکڕای نرخی کڕین -->
            <div id="cleanAvgPreviewBox" class="hidden mt-2 bg-amber-50 dark:bg-slate-950 border border-amber-300 dark:border-amber-500/40 rounded-lg px-3 py-1.5 text-xs flex flex-wrap items-center justify-between">
                <div id="cleanAvgPreviewText" class="font-mono text-slate-800 dark:text-slate-200"></div>
                <span class="text-[11px] text-amber-700 dark:text-amber-400 font-bold">⚖️ کۆکردنەوەی نرخی کۆن و نوێ دابەشی کۆی دانەکان</span>
            </div>
        </div>

        <!-- بەشی ٣: خشتەی کاڵاکانی ناو وەسڵەکە (دەتوانیت ڕاستەوخۆ دەستکاری دانە و نرخی کڕین و فرۆشتنیش بکەیت) -->
        <div class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="overflow-y-auto flex-1">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 border-b border-slate-200 dark:border-slate-800 sticky top-0 font-bold">
                        <tr>
                            <th class="py-2.5 px-3 text-center w-10">#</th>
                            <th class="py-2.5 px-3">ناوی کاڵا</th>
                            <th class="py-2.5 px-3 text-center">کۆگای پێشوو و نرخی کۆن</th>
                            <th class="py-2.5 px-3 text-center w-28">دانەی کڕدراو</th>
                            <th class="py-2.5 px-3 text-center w-40" id="thCartBuyPrice">نرخی کڕینی ئێستا</th>
                            <th class="py-2.5 px-3 text-center w-44">تێکڕای نرخی نوێ (کۆکراوە ÷ دانە)</th>
                            <th class="py-2.5 px-3 text-center w-40" id="thCartSellPrice">نرخی فرۆشتن</th>
                            <th class="py-2.5 px-3 text-center w-36">کۆی گشتی</th>
                            <th class="py-2.5 px-3 text-center w-14">سڕینەوە</th>
                        </tr>
                    </thead>
                    <tbody id="cleanPurCartBody" class="divide-y divide-slate-200 dark:divide-slate-800"></tbody>
                </table>
            </div>
        </div>

        <!-- بەشی ٤: خوارەوەی وەسڵ -->
        <div class="bg-white dark:bg-slate-900 border-2 border-slate-300 dark:border-slate-800 rounded-xl p-2.5 shadow-md shrink-0">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-2.5 items-center">
                
                <div class="md:col-span-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-3 py-1.5">
                    <span class="text-[11px] text-slate-500 block font-bold">کۆی گشتی:</span>
                    <div id="cleanPurSubtotalPrimary" class="text-sm font-extrabold text-slate-900 dark:text-white font-mono">0</div>
                    <div id="cleanPurSubtotalSecondary" class="text-[11px] text-emerald-600 font-mono font-bold">0</div>
                </div>

                <div class="md:col-span-2 bg-amber-50/60 dark:bg-slate-950 border border-amber-200 dark:border-slate-800 rounded-lg px-2.5 py-1.5">
                    <label class="block text-[11px] text-amber-800 dark:text-amber-400 font-bold mb-1">داشکان (د.ع / $):</label>
                    <div class="grid grid-cols-2 gap-1">
                        <input type="number" step="any" id="cleanPurDiscountIQD" value="0" oninput="onCleanPurDiscount('IQD')" title="داشکان بە دینار"
                            class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-xs font-mono font-bold">
                        <input type="number" step="any" id="cleanPurDiscountUSD" value="0" oninput="onCleanPurDiscount('USD')" title="داشکان بە دۆلار"
                            class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-xs font-mono font-bold text-emerald-600">
                    </div>
                </div>

                <div class="md:col-span-2 bg-emerald-50 dark:bg-emerald-950/30 border-2 border-emerald-500 rounded-lg px-3 py-1.5">
                    <span class="text-[11px] text-emerald-800 dark:text-emerald-300 block font-bold">صافی وەسڵ:</span>
                    <div id="cleanPurNetPrimary" class="text-base font-extrabold text-emerald-950 dark:text-white font-mono">0</div>
                    <div id="cleanPurNetSecondary" class="text-[11px] text-emerald-700 dark:text-emerald-400 font-mono font-bold">0</div>
                </div>

                <div class="md:col-span-2 bg-blue-50/60 dark:bg-slate-950 border border-blue-200 dark:border-slate-800 rounded-lg px-2.5 py-1.5">
                    <label class="block text-[11px] text-blue-800 dark:text-blue-400 font-bold mb-1">پارەی دراو (د.ع / $):</label>
                    <div class="grid grid-cols-2 gap-1">
                        <input type="number" step="any" id="cleanPurPaidIQD" value="0" oninput="onCleanPurPaid('IQD')" title="پارەی دراو بە دینار"
                            class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-xs font-mono font-bold">
                        <input type="number" step="any" id="cleanPurPaidUSD" value="0" oninput="onCleanPurPaid('USD')" title="پارەی دراو بە دۆلار"
                            class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-xs font-mono font-bold text-emerald-600">
                    </div>
                </div>

                <div class="md:col-span-2 bg-red-50/60 dark:bg-slate-950 border border-red-200 dark:border-slate-800 rounded-lg px-3 py-1.5">
                    <span class="text-[11px] text-red-700 dark:text-red-400 block font-bold">ماوەی قەرز:</span>
                    <div id="cleanPurRemPrimary" class="text-sm font-extrabold text-red-600 dark:text-red-400 font-mono">0</div>
                    <div id="cleanPurRemSecondary" class="text-[11px] text-slate-500 font-mono font-bold">0</div>
                </div>

                <div class="md:col-span-2">
                    <button type="button" onclick="submitCleanPurchaseReceipt()" id="btnSubmitCleanPurchase"
                        class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold py-3 px-3 rounded-xl text-xs transition shadow-md flex items-center justify-center gap-1.5">
                        <span>💾</span>
                        <span id="btnSubmitCleanPurchaseText">خەزنکردنی وەسڵ</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- مۆداڵی بینینی وردەکاری وەسڵی کڕین -->
    <div id="cleanPurDetailModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-2xl w-full max-w-4xl max-h-[88vh] flex flex-col shadow-2xl overflow-hidden">
            <div class="bg-slate-100 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 px-5 py-3 flex items-center justify-between">
                <div>
                    <h3 id="cleanPurDetailTitle" class="text-sm font-extrabold text-slate-900 dark:text-white">وردەکاری وەسڵی کڕین</h3>
                    <p id="cleanPurDetailMeta" class="text-xs text-slate-500 mt-0.5"></p>
                </div>
                <div class="flex items-center gap-2">
                    <button id="btnEditFromDetailModal" class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-xl text-xs font-bold">
                        ✏️ دەستکاریکردنی ئەم وەسڵە
                    </button>
                    <button onclick="document.getElementById('cleanPurDetailModal').classList.add('hidden')" class="w-8 h-8 rounded-xl bg-slate-200 dark:bg-slate-800 font-bold text-xs">✕</button>
                </div>
            </div>
            <div class="p-4 overflow-y-auto flex-1">
                <table class="w-full text-right text-xs border border-slate-200 dark:border-slate-800">
                    <thead class="bg-slate-100 dark:bg-slate-950 font-bold">
                        <tr>
                            <th class="py-2 px-3">#</th>
                            <th class="py-2 px-3">کاڵا</th>
                            <th class="py-2 px-3 text-center">عەدەدی پێشوو</th>
                            <th class="py-2 px-3 text-center">نرخی کۆن</th>
                            <th class="py-2 px-3 text-center">دانەی کڕدراو</th>
                            <th class="py-2 px-3 text-center">نرخی کڕین</th>
                            <th class="py-2 px-3 text-center">تێکڕای نرخی نوێ</th>
                            <th class="py-2 px-3 text-center">کۆی گشتی</th>
                        </tr>
                    </thead>
                    <tbody id="cleanPurDetailBody" class="divide-y divide-slate-200 dark:divide-slate-800"></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
window.cleanPurState = {
    purchaseId: 0,
    nextInvoice: '',
    companies: [],
    products: [],
    purchases: [],
    purchaseType: 'cash',
    cart: [],
    discountIQD: 0,
    paidIQD: 0,
    manualPaid: false
};

// فەنکشنی یارمەتیدەر بۆ پیشاندانی پارە بەپێی دراوی چالاکی سیستەم (USD یان IQD)
function fmtPurMoney(iqdAmount, customRate = null) {
    const rate = customRate || window.POS_CONFIG.usdRate || 1500;
    const isUSD = window.POS_CONFIG.currency === 'USD';
    const val = parseFloat(iqdAmount) || 0;
    if (isUSD) {
        return '$' + (val / rate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    return Math.round(val).toLocaleString('en-US') + ' د.ع';
}

function fmtPurDual(iqdAmount, customRate = null) {
    const rate = customRate || window.POS_CONFIG.usdRate || 1500;
    const isUSD = window.POS_CONFIG.currency === 'USD';
    const val = parseFloat(iqdAmount) || 0;
    const usdStr = '$' + (val / rate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const iqdStr = Math.round(val).toLocaleString('en-US') + ' د.ع';
    return isUSD ? { primary: usdStr, secondary: iqdStr } : { primary: iqdStr, secondary: usdStr };
}

// هاوکاتکردنی هەڵبژاردەی دراوی داخڵکردن لەگەڵ دراوی گشتیی سیستەمەکە
function syncPurchaseCurrencyWithSystem() {
    const sysCur = window.POS_CONFIG?.currency === 'USD' ? 'USD' : 'IQD';
    const curSelect = document.getElementById('entryCurrency');
    if (curSelect) {
        curSelect.value = sysCur;
    }

    const badge = document.getElementById('purActiveCurrencyBadge');
    if (badge) {
        badge.innerText = sysCur === 'USD' ? 'دراوی چالاک: دۆلار ($)' : 'دراوی چالاک: دینار (د.ع)';
    }

    const sym = sysCur === 'USD' ? '($)' : '(د.ع)';
    const lblBuy = document.getElementById('lblEntryBuyPrice');
    const lblSell = document.getElementById('lblEntrySellPrice');
    const thBuy = document.getElementById('thCartBuyPrice');
    const thSell = document.getElementById('thCartSellPrice');

    if (lblBuy) lblBuy.innerText = `نرخی کڕینی تاک ${sym}:`;
    if (lblSell) lblSell.innerText = `نرخی فرۆشتنی تاک ${sym}:`;
    if (thBuy) thBuy.innerText = `نرخی کڕینی ئێستا ${sym}`;
    if (thSell) thSell.innerText = `نرخی فرۆشتن ${sym}`;

    // ئەگەر کاڵایەک لە خانەی داخڵکردندا هەڵبژێردرابوو، نرخەکەی دەگۆڕێت بۆ دراوە نوێیەکە
    const selectedPid = parseInt(document.getElementById('entryProductId')?.value) || 0;
    if (selectedPid > 0) {
        const prod = window.cleanPurState.products.find(p => parseInt(p.id) === selectedPid);
        if (prod) {
            const rate = window.POS_CONFIG.usdRate || 1500;
            document.getElementById('entryBuyPrice').value = sysCur === 'USD' ? (prod.buy_price_iqd / rate).toFixed(2) : Math.round(prod.buy_price_iqd);
            document.getElementById('entrySellPrice').value = sysCur === 'USD' ? (prod.sell_price_iqd / rate).toFixed(2) : Math.round(prod.sell_price_iqd);
        }
    }

    updateCleanAvgPreview();
    renderCleanPurCart();
    renderCleanPurchasesTable();
}

function onEntryCurrencyManualChange() {
    const cur = document.getElementById('entryCurrency').value;
    const sym = cur === 'USD' ? '($)' : '(د.ع)';
    document.getElementById('lblEntryBuyPrice').innerText = `نرخی کڕینی تاک ${sym}:`;
    document.getElementById('lblEntrySellPrice').innerText = `نرخی فرۆشتنی تاک ${sym}:`;
    updateCleanAvgPreview();
}

function togglePurchaseView(view) {
    const listPanel = document.getElementById('purViewListPanel');
    const addPanel = document.getElementById('purViewAddPanel');
    const btnList = document.getElementById('btnViewPurList');
    const btnAdd = document.getElementById('btnViewPurAdd');

    syncPurchaseCurrencyWithSystem();

    if (view === 'add') {
        listPanel.classList.add('hidden');
        listPanel.classList.remove('flex');
        addPanel.classList.remove('hidden');
        addPanel.classList.add('flex');
        btnAdd.className = "px-4 py-2 rounded-xl text-xs font-extrabold transition flex items-center gap-1.5 bg-emerald-600 text-white shadow-md ring-2 ring-emerald-400";
        btnList.className = "px-4 py-2 rounded-xl text-xs font-extrabold transition flex items-center gap-1.5 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300";
        document.getElementById('purCompanyName').focus();
    } else {
        addPanel.classList.add('hidden');
        addPanel.classList.remove('flex');
        listPanel.classList.remove('hidden');
        listPanel.classList.add('flex');
        btnList.className = "px-4 py-2 rounded-xl text-xs font-extrabold transition flex items-center gap-1.5 bg-slate-800 dark:bg-emerald-600 text-white shadow-sm";
        btnAdd.className = "px-4 py-2 rounded-xl text-xs font-extrabold transition flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm";
    }
}

// دەستپێکردنی وەسڵێکی نوێی پاک
function startNewPurchaseForm() {
    window.cleanPurState.purchaseId = 0;
    window.cleanPurState.cart = [];
    window.cleanPurState.discountIQD = 0;
    window.cleanPurState.paidIQD = 0;
    window.cleanPurState.manualPaid = false;

    document.getElementById('purCompanyId').value = '';
    document.getElementById('purCompanyName').value = '';
    document.getElementById('purInvoiceNumber').value = window.cleanPurState.nextInvoice || '';
    document.getElementById('purDateInput').value = new Date().toISOString().slice(0, 10);
    document.getElementById('cleanPurDiscountIQD').value = '0';
    document.getElementById('cleanPurDiscountUSD').value = '0';

    document.getElementById('purEditModeBanner').classList.add('hidden');
    document.getElementById('purEditModeBanner').classList.remove('flex');
    document.getElementById('btnViewPurAddLabel').innerText = 'زیادکردنی وەسڵی نوێ';
    document.getElementById('btnSubmitCleanPurchaseText').innerText = 'خەزنکردنی وەسڵ';

    setCleanPurchaseType('cash');
    syncPurchaseCurrencyWithSystem();
    togglePurchaseView('add');
}

// هێنانی وەسڵی خەزنکراو بۆ ناو فۆڕم بۆ دەستکاریکردن
function editSavedPurchaseReceipt(purchaseId) {
    document.getElementById('cleanPurDetailModal')?.classList.add('hidden');

    fetch(`api_purchases.php?action=get_purchase_details&id=${purchaseId}`)
        .then(r => r.json())
        .then(res => {
            if (res.status !== 'success') {
                alert(res.message || 'وەسڵ نەدۆزرایەوە');
                return;
            }

            const p = res.purchase;
            const rate = window.POS_CONFIG.usdRate || 1500;

            window.cleanPurState.purchaseId = parseInt(p.id);
            window.cleanPurState.purchaseType = p.purchase_type;
            window.cleanPurState.discountIQD = parseFloat(p.discount_iqd) || 0;
            window.cleanPurState.paidIQD = parseFloat(p.paid_iqd) || 0;
            window.cleanPurState.manualPaid = true;

            document.getElementById('purCompanyId').value = p.company_id || '';
            document.getElementById('purCompanyName').value = p.company_name || '';
            document.getElementById('purInvoiceNumber').value = p.invoice_number || '';
            document.getElementById('purDateInput').value = p.purchase_date || '';

            document.getElementById('cleanPurDiscountIQD').value = Math.round(window.cleanPurState.discountIQD);
            document.getElementById('cleanPurDiscountUSD').value = (window.cleanPurState.discountIQD / rate).toFixed(2);
            document.getElementById('cleanPurPaidIQD').value = Math.round(window.cleanPurState.paidIQD);
            document.getElementById('cleanPurPaidUSD').value = (window.cleanPurState.paidIQD / rate).toFixed(2);

            setCleanPurchaseType(p.purchase_type);
            window.cleanPurState.manualPaid = true;

            window.cleanPurState.cart = (res.items || []).map(it => ({
                product_id: it.product_id,
                barcode: it.barcode || '',
                item_name: it.item_name,
                qty: parseFloat(it.qty) || 1,
                old_stock: parseFloat(it.old_stock) || 0,
                old_buy_price_iqd: parseFloat(it.old_buy_price_iqd) || 0,
                buy_price_iqd: parseFloat(it.buy_price_iqd) || 0,
                avg_buy_price_iqd: parseFloat(it.avg_buy_price_iqd) || 0,
                sell_price_iqd: parseFloat(it.sell_price_iqd) || 0
            }));

            const banner = document.getElementById('purEditModeBanner');
            banner.classList.remove('hidden');
            banner.classList.add('flex');
            document.getElementById('purEditModeText').innerText = `دەستکاریکردنی وەسڵی ژمارە (${p.invoice_number}) — کۆمپانیای: ${p.company_name}`;
            document.getElementById('btnViewPurAddLabel').innerText = `دەستکاری (${p.invoice_number})`;
            document.getElementById('btnSubmitCleanPurchaseText').innerText = 'پاشەکەوتکردنی گۆڕانکارییەکان';

            togglePurchaseView('add');
            renderCleanPurCart();
        });
}

window.loadPurchasedReceiptsData = function() {
    fetch('api_purchases.php?action=get_overview')
        .then(r => r.json())
        .then(res => {
            if (res.status !== 'success') return;
            window.cleanPurState.companies = res.companies || [];
            window.cleanPurState.products = res.products || [];
            window.cleanPurState.purchases = res.purchases || [];
            window.cleanPurState.nextInvoice = res.next_invoice || '';

            const invInp = document.getElementById('purInvoiceNumber');
            if (invInp && window.cleanPurState.purchaseId === 0 && !invInp.value) {
                invInp.value = window.cleanPurState.nextInvoice;
            }

            const badge = document.getElementById('prTotalCountBadge');
            if (badge) badge.innerText = window.cleanPurState.purchases.length;

            syncPurchaseCurrencyWithSystem();
        });
};

function renderCleanPurchasesTable() {
    const tbody = document.getElementById('cleanPurchasesTableBody');
    if (!tbody) return;
    const q = (document.getElementById('prSearchInput')?.value || '').trim().toLowerCase();
    const typeFilter = document.getElementById('prTypeFilter')?.value || 'all';

    const list = window.cleanPurState.purchases.filter(p => {
        const matchQ = p.invoice_number.toLowerCase().includes(q) || p.company_name.toLowerCase().includes(q);
        const matchType = typeFilter === 'all' || p.purchase_type === typeFilter;
        return matchQ && matchType;
    });

    let sumTotal = 0, sumDebt = 0;
    list.forEach(p => {
        sumTotal += parseFloat(p.total_iqd) || 0;
        sumDebt += parseFloat(p.remaining_iqd) || 0;
    });

    const totDual = fmtPurDual(sumTotal);
    const debtDual = fmtPurDual(sumDebt);
    document.getElementById('prSumTotalDisplay').innerText = `${totDual.primary} (${totDual.secondary})`;
    document.getElementById('prSumDebtDisplay').innerText = `${debtDual.primary} (${debtDual.secondary})`;

    if (list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="py-16 text-center text-slate-400 font-bold">هیچ وەسڵێکی کڕین نەدۆزرایەوە</td></tr>`;
        return;
    }

    tbody.innerHTML = list.map(p => {
        const isDebt = p.purchase_type === 'debt' || parseFloat(p.remaining_iqd) > 0;
        const pTotal = fmtPurDual(p.total_iqd);
        const pPaid = fmtPurMoney(p.paid_iqd);
        const pRem = fmtPurMoney(p.remaining_iqd);

        return `
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
            <td class="py-2.5 px-3 text-center font-mono font-extrabold text-emerald-700 dark:text-emerald-400">${p.invoice_number}</td>
            <td class="py-2.5 px-3 font-bold text-slate-900 dark:text-white">🏢 ${p.company_name}</td>
            <td class="py-2.5 px-3 text-center">
                <span class="px-2.5 py-0.5 rounded-lg text-[11px] font-bold ${isDebt ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700'}">
                    ${isDebt ? '📒 قەرز' : '💵 نەقد'}
                </span>
            </td>
            <td class="py-2.5 px-3 text-center font-mono">${p.purchase_date}</td>
            <td class="py-2.5 px-3 text-center font-mono font-bold">${p.items_count} کاڵا (${parseFloat(p.total_qty)})</td>
            <td class="py-2.5 px-3 text-center font-mono font-bold">
                <div class="text-slate-900 dark:text-white">${pTotal.primary}</div>
                <div class="text-[10px] text-emerald-600">${pTotal.secondary}</div>
            </td>
            <td class="py-2.5 px-3 text-center font-mono font-bold text-blue-600">${pPaid}</td>
            <td class="py-2.5 px-3 text-center font-mono font-bold ${parseFloat(p.remaining_iqd) > 0 ? 'text-red-600' : 'text-slate-400'}">
                ${pRem}
            </td>
            <td class="py-2.5 px-3 text-center">
                <div class="flex items-center justify-center gap-1">
                    <button onclick="openCleanPurDetails(${p.id})" class="bg-blue-600 hover:bg-blue-500 text-white px-2.5 py-1 rounded-lg text-[11px] font-bold" title="بینینی ناوەڕۆک">👁️ بینین</button>
                    <button onclick="editSavedPurchaseReceipt(${p.id})" class="bg-amber-500 hover:bg-amber-600 text-white px-2.5 py-1 rounded-lg text-[11px] font-bold" title="دەستکاریکردنی وەسڵ">✏️ دەستکاری</button>
                    <button onclick="deleteCleanPurchase(${p.id})" class="bg-red-100 hover:bg-red-600 text-red-600 hover:text-white px-2 py-1 rounded-lg text-[11px] font-bold" title="سڕینەوە">🗑️</button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function filterPurCompanyDropdown(q) {
    document.getElementById('purCompanyId').value = '';
    const dd = document.getElementById('purCompanyDropdown');
    const query = (q || '').trim().toLowerCase();
    const list = window.cleanPurState.companies.filter(c => c.name.toLowerCase().includes(query));

    if (list.length === 0) {
        dd.innerHTML = `<div class="p-2.5 text-xs text-emerald-600 font-bold">✨ کۆمپانیای نوێ: "${q}" (خۆکارانە تۆمار دەکرێت)</div>`;
        dd.classList.remove('hidden');
        return;
    }
    dd.innerHTML = list.map(c => `
        <div onclick="document.getElementById('purCompanyId').value='${c.id}'; document.getElementById('purCompanyName').value='${c.name}'; document.getElementById('purCompanyDropdown').classList.add('hidden');"
            class="p-2.5 hover:bg-emerald-50 dark:hover:bg-slate-800 cursor-pointer border-b border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
            <span class="font-bold">🏢 ${c.name}</span>
            <span class="text-slate-400 font-mono">${c.phone || ''}</span>
        </div>
    `).join('');
    dd.classList.remove('hidden');
}

function filterCleanEntryProducts(q) {
    document.getElementById('entryProductId').value = '';
    const badge = document.getElementById('entryProdModeBadge');
    badge.innerText = '✨ کاڵای نوێ';
    badge.className = 'text-[10px] bg-amber-100 text-amber-800 px-2 py-0.2 rounded font-bold';
    document.getElementById('cleanAvgPreviewBox').classList.add('hidden');

    const dd = document.getElementById('entryProductDropdown');
    const query = (q || '').trim().toLowerCase();
    if (!query) { dd.classList.add('hidden'); return; }

    const matches = window.cleanPurState.products.filter(p =>
        p.name.toLowerCase().includes(query) || (p.barcode && p.barcode.toLowerCase().includes(query))
    );

    if (matches.length === 0) {
        dd.innerHTML = `<div class="p-2.5 text-xs text-amber-700 font-bold">➕ کاڵای نوێ: "${q}" (ڕاستەوخۆ زیاد دەکرێت بۆ لیستی کاڵاکان)</div>`;
        dd.classList.remove('hidden');
        return;
    }

    dd.innerHTML = matches.map(p => `
        <div onclick='selectCleanEntryProduct(${JSON.stringify(p)})'
            class="p-2.5 hover:bg-emerald-50 dark:hover:bg-slate-800 cursor-pointer border-b border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
            <div>
                <div class="font-bold text-slate-900 dark:text-white">${p.name}</div>
                <div class="text-[11px] text-slate-500">بارکۆد: ${p.barcode || '---'} | کۆگا: <b class="text-emerald-600">${parseFloat(p.stock)}</b></div>
            </div>
            <div class="text-left font-mono">
                <div class="font-bold">کڕین: ${fmtPurMoney(p.buy_price_iqd)}</div>
                <div class="text-[11px] text-emerald-600">فرۆشتن: ${fmtPurMoney(p.sell_price_iqd)}</div>
            </div>
        </div>
    `).join('');
    dd.classList.remove('hidden');
}

function selectCleanEntryProduct(p) {
    const rate = window.POS_CONFIG.usdRate || 1500;
    const cur = document.getElementById('entryCurrency').value;

    document.getElementById('entryProductId').value = p.id;
    document.getElementById('entryProductName').value = p.name;
    document.getElementById('entryBarcode').value = p.barcode || '';
    document.getElementById('entryBuyPrice').value = cur === 'USD' ? Number((p.buy_price_iqd / rate).toFixed(2)) : Math.round(p.buy_price_iqd);
    document.getElementById('entrySellPrice').value = cur === 'USD' ? Number((p.sell_price_iqd / rate).toFixed(2)) : Math.round(p.sell_price_iqd);

    const badge = document.getElementById('entryProdModeBadge');
    badge.innerText = `🔄 ئەپدەیتی کاڵا (لە کۆگا: ${parseFloat(p.stock)})`;
    badge.className = 'text-[10px] bg-emerald-100 text-emerald-800 px-2 py-0.2 rounded font-bold';

    document.getElementById('entryProductDropdown').classList.add('hidden');
    document.getElementById('entryQty').focus();
    updateCleanAvgPreview();
}

function updateCleanAvgPreview() {
    const pid = parseInt(document.getElementById('entryProductId').value) || 0;
    const box = document.getElementById('cleanAvgPreviewBox');
    const txt = document.getElementById('cleanAvgPreviewText');
    if (!pid) { box.classList.add('hidden'); return; }

    const prod = window.cleanPurState.products.find(p => parseInt(p.id) === pid);
    if (!prod) { box.classList.add('hidden'); return; }

    const rate = window.POS_CONFIG.usdRate || 1500;
    const cur = document.getElementById('entryCurrency').value;
    const newQty = parseFloat(document.getElementById('entryQty').value) || 0;
    const buyInp = parseFloat(document.getElementById('entryBuyPrice').value) || 0;
    const newBuyIQD = cur === 'USD' ? (buyInp * rate) : buyInp;

    const oldStock = Math.max(0, parseFloat(prod.stock) || 0);
    const oldBuyIQD = parseFloat(prod.buy_price_iqd) || 0;
    const totalQty = oldStock + newQty;
    const avgBuyIQD = totalQty > 0 ? ((oldStock * oldBuyIQD) + (newQty * newBuyIQD)) / totalQty : newBuyIQD;

    const fmt = (iqd) => cur === 'USD' ? '$' + (iqd / rate).toFixed(2) : Math.round(iqd).toLocaleString() + ' د.ع';
    txt.innerHTML = `پێشتر: <b>${oldStock} دانە</b> (${fmt(oldBuyIQD)}) + ئێستا: <b>${newQty} دانە</b> (${fmt(newBuyIQD)}) ⬅️ کۆی دانە: <b>${totalQty}</b> | تێکڕای نرخی نوێ = <b class="text-emerald-600 text-sm">${fmt(avgBuyIQD)}</b>`;
    box.classList.remove('hidden');
}

function addItemToCleanPurCart() {
    const pid = parseInt(document.getElementById('entryProductId').value) || 0;
    const name = document.getElementById('entryProductName').value.trim();
    const barcode = document.getElementById('entryBarcode').value.trim();
    const qty = parseFloat(document.getElementById('entryQty').value) || 0;
    const cur = document.getElementById('entryCurrency').value;
    const buyInp = parseFloat(document.getElementById('entryBuyPrice').value) || 0;
    const sellInp = parseFloat(document.getElementById('entrySellPrice').value) || 0;

    if (!name || qty <= 0 || buyInp <= 0) {
        alert('تکایە ناوی کاڵا، دانە و نرخی کڕین بنووسە!');
        return;
    }

    const rate = window.POS_CONFIG.usdRate || 1500;
    const buyIQD = cur === 'USD' ? (buyInp * rate) : buyInp;
    const sellIQD = cur === 'USD' ? (sellInp > 0 ? sellInp * rate : buyIQD) : (sellInp > 0 ? sellInp : buyIQD);

    const existingProd = window.cleanPurState.products.find(p =>
        (pid > 0 && parseInt(p.id) === pid) ||
        (barcode !== '' && p.barcode === barcode) ||
        p.name.toLowerCase() === name.toLowerCase()
    );

    const oldStock = existingProd ? (parseFloat(existingProd.stock) || 0) : 0;
    const oldBuyIQD = existingProd ? (parseFloat(existingProd.buy_price_iqd) || 0) : 0;
    const effOld = Math.max(0, oldStock);
    const avgBuyIQD = (effOld + qty) > 0 ? ((effOld * oldBuyIQD) + (qty * buyIQD)) / (effOld + qty) : buyIQD;

    window.cleanPurState.cart.push({
        product_id: existingProd ? existingProd.id : null,
        barcode: barcode || (existingProd ? existingProd.barcode : ''),
        item_name: name,
        qty: qty,
        old_stock: oldStock,
        old_buy_price_iqd: oldBuyIQD,
        buy_price_iqd: buyIQD,
        avg_buy_price_iqd: avgBuyIQD,
        sell_price_iqd: sellIQD
    });

    document.getElementById('entryProductId').value = '';
    document.getElementById('entryProductName').value = '';
    document.getElementById('entryBarcode').value = '';
    document.getElementById('entryQty').value = '1';
    document.getElementById('entryBuyPrice').value = '';
    document.getElementById('entrySellPrice').value = '';
    document.getElementById('cleanAvgPreviewBox').classList.add('hidden');
    document.getElementById('entryProductName').focus();

    renderCleanPurCart();
}

function updateCartRowPrice(idx, field, val) {
    const num = parseFloat(val) || 0;
    const rate = window.POS_CONFIG.usdRate || 1500;
    const isUSD = window.POS_CONFIG.currency === 'USD';
    const iqdVal = isUSD ? (num * rate) : num;

    if (field === 'buy') {
        window.cleanPurState.cart[idx].buy_price_iqd = iqdVal;
    } else if (field === 'sell') {
        window.cleanPurState.cart[idx].sell_price_iqd = iqdVal;
    } else if (field === 'qty') {
        window.cleanPurState.cart[idx].qty = Math.max(0.01, num);
    }
    renderCleanPurCart();
}

function renderCleanPurCart() {
    const tbody = document.getElementById('cleanPurCartBody');
    if (!tbody) return;
    const rate = window.POS_CONFIG.usdRate || 1500;
    const isUSD = window.POS_CONFIG.currency === 'USD';

    if (window.cleanPurState.cart.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="py-16 text-center text-slate-400 font-bold">📦 هیچ کاڵایەک داخڵی وەسڵەکە نەکراوە</td></tr>`;
        recalcCleanPurTotals();
        return;
    }

    tbody.innerHTML = window.cleanPurState.cart.map((it, idx) => {
        const effOld = Math.max(0, parseFloat(it.old_stock) || 0);
        const avgIQD = (effOld + it.qty) > 0 ? ((effOld * it.old_buy_price_iqd) + (it.qty * it.buy_price_iqd)) / (effOld + it.qty) : it.buy_price_iqd;
        it.avg_buy_price_iqd = avgIQD;
        const rowTotalIQD = it.qty * it.buy_price_iqd;

        const buyInputVal = isUSD ? Number((it.buy_price_iqd / rate).toFixed(2)) : Math.round(it.buy_price_iqd);
        const sellInputVal = isUSD ? Number((it.sell_price_iqd / rate).toFixed(2)) : Math.round(it.sell_price_iqd);
        const avgDual = fmtPurDual(avgIQD);
        const rowDual = fmtPurDual(rowTotalIQD);

        return `
        <tr class="bg-emerald-50/30 dark:bg-slate-800/30 hover:bg-emerald-50/60 transition">
            <td class="py-2 px-3 text-center font-mono font-bold">${idx + 1}</td>
            <td class="py-2 px-3 font-bold text-slate-900 dark:text-white">${it.item_name}</td>
            <td class="py-2 px-3 text-center font-mono text-[11px] text-slate-500">
                پێشتر: <b>${it.old_stock}</b> دانە (${fmtPurMoney(it.old_buy_price_iqd)})
            </td>
            <td class="py-2 px-3 text-center">
                <input type="number" step="any" value="${it.qty}"
                    onchange="updateCartRowPrice(${idx}, 'qty', this.value)"
                    class="w-20 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1 text-center font-mono font-extrabold">
            </td>
            <td class="py-2 px-3 text-center">
                <input type="number" step="any" value="${buyInputVal}"
                    onchange="updateCartRowPrice(${idx}, 'buy', this.value)"
                    class="w-28 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1 text-center font-mono font-bold text-emerald-700 dark:text-emerald-400">
            </td>
            <td class="py-2 px-3 text-center font-mono bg-amber-50/60 dark:bg-amber-950/20">
                <div class="font-extrabold text-amber-800 dark:text-amber-300">${avgDual.primary}</div>
                <div class="text-[10px] text-slate-500">${avgDual.secondary}</div>
            </td>
            <td class="py-2 px-3 text-center">
                <input type="number" step="any" value="${sellInputVal}"
                    onchange="updateCartRowPrice(${idx}, 'sell', this.value)"
                    class="w-28 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1 text-center font-mono font-bold">
            </td>
            <td class="py-2 px-3 text-center font-mono">
                <div class="font-extrabold text-slate-900 dark:text-white">${rowDual.primary}</div>
                <div class="text-[10px] text-emerald-600">${rowDual.secondary}</div>
            </td>
            <td class="py-2 px-3 text-center">
                <button onclick="window.cleanPurState.cart.splice(${idx},1); renderCleanPurCart();" class="text-red-500 hover:bg-red-50 p-1 rounded">🗑️</button>
            </td>
        </tr>`;
    }).join('');

    recalcCleanPurTotals();
}

function setCleanPurchaseType(type) {
    window.cleanPurState.purchaseType = type;
    window.cleanPurState.manualPaid = false;
    document.getElementById('purTypeBtn-cash').className = type === 'cash'
        ? "py-1.5 rounded-md text-xs font-extrabold bg-emerald-600 text-white shadow-sm"
        : "py-1.5 rounded-md text-xs font-extrabold text-slate-600 dark:text-slate-400";
    document.getElementById('purTypeBtn-debt').className = type === 'debt'
        ? "py-1.5 rounded-md text-xs font-extrabold bg-red-600 text-white shadow-sm"
        : "py-1.5 rounded-md text-xs font-extrabold text-slate-600 dark:text-slate-400";
    recalcCleanPurTotals();
}

function recalcCleanPurTotals() {
    const rate = window.POS_CONFIG.usdRate || 1500;
    const subtotalIQD = window.cleanPurState.cart.reduce((s, i) => s + (i.qty * i.buy_price_iqd), 0);
    const discountIQD = window.cleanPurState.discountIQD || 0;
    const netIQD = Math.max(0, subtotalIQD - discountIQD);

    if (!window.cleanPurState.manualPaid) {
        window.cleanPurState.paidIQD = window.cleanPurState.purchaseType === 'cash' ? netIQD : 0;
        document.getElementById('cleanPurPaidIQD').value = Math.round(window.cleanPurState.paidIQD);
        document.getElementById('cleanPurPaidUSD').value = (window.cleanPurState.paidIQD / rate).toFixed(2);
    }

    const paidIQD = Math.min(netIQD, Math.max(0, window.cleanPurState.paidIQD || 0));
    const remIQD = Math.max(0, netIQD - paidIQD);

    const subDual = fmtPurDual(subtotalIQD);
    const netDual = fmtPurDual(netIQD);
    const remDual = fmtPurDual(remIQD);

    document.getElementById('cleanPurSubtotalPrimary').innerText = subDual.primary;
    document.getElementById('cleanPurSubtotalSecondary').innerText = subDual.secondary;

    document.getElementById('cleanPurNetPrimary').innerText = netDual.primary;
    document.getElementById('cleanPurNetSecondary').innerText = netDual.secondary;

    document.getElementById('cleanPurRemPrimary').innerText = remDual.primary;
    document.getElementById('cleanPurRemSecondary').innerText = remDual.secondary;
}

function onCleanPurDiscount(cur) {
    const rate = window.POS_CONFIG.usdRate || 1500;
    if (cur === 'IQD') {
        const v = parseFloat(document.getElementById('cleanPurDiscountIQD').value) || 0;
        window.cleanPurState.discountIQD = v;
        document.getElementById('cleanPurDiscountUSD').value = (v / rate).toFixed(2);
    } else {
        const v = parseFloat(document.getElementById('cleanPurDiscountUSD').value) || 0;
        window.cleanPurState.discountIQD = Math.round(v * rate);
        document.getElementById('cleanPurDiscountIQD').value = window.cleanPurState.discountIQD;
    }
    recalcCleanPurTotals();
}

function onCleanPurPaid(cur) {
    window.cleanPurState.manualPaid = true;
    const rate = window.POS_CONFIG.usdRate || 1500;
    if (cur === 'IQD') {
        const v = parseFloat(document.getElementById('cleanPurPaidIQD').value) || 0;
        window.cleanPurState.paidIQD = v;
        document.getElementById('cleanPurPaidUSD').value = (v / rate).toFixed(2);
    } else {
        const v = parseFloat(document.getElementById('cleanPurPaidUSD').value) || 0;
        window.cleanPurState.paidIQD = Math.round(v * rate);
        document.getElementById('cleanPurPaidIQD').value = window.cleanPurState.paidIQD;
    }
    recalcCleanPurTotals();
}

function submitCleanPurchaseReceipt() {
    const companyName = document.getElementById('purCompanyName').value.trim();
    if (!companyName) { alert('تکایە ناوی کۆمپانیا بنووسە!'); return; }
    if (window.cleanPurState.cart.length === 0) { alert('تکایە کاڵا داخڵی وەسڵەکە بکە!'); return; }

    fetch('api_purchases.php?action=save_purchase', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            purchase_id: window.cleanPurState.purchaseId,
            company_id: document.getElementById('purCompanyId').value,
            company_name: companyName,
            invoice_number: document.getElementById('purInvoiceNumber').value.trim(),
            purchase_type: window.cleanPurState.purchaseType,
            purchase_date: document.getElementById('purDateInput').value,
            discount_iqd: window.cleanPurState.discountIQD,
            paid_iqd: window.cleanPurState.paidIQD,
            usd_rate: window.POS_CONFIG.usdRate || 1500,
            items: window.cleanPurState.cart
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            startNewPurchaseForm();
            togglePurchaseView('list');
            if (typeof window.refreshAllPurchaseModules === 'function') window.refreshAllPurchaseModules();
        } else {
            alert('هەڵە: ' + res.message);
        }
    });
}

function openCleanPurDetails(id) {
    fetch(`api_purchases.php?action=get_purchase_details&id=${id}`)
        .then(r => r.json())
        .then(res => {
            if (res.status !== 'success') return;
            const p = res.purchase;
            document.getElementById('cleanPurDetailTitle').innerText = `وەسڵی ژمارە: ${p.invoice_number} — کۆمپانیا: ${p.company_name}`;
            document.getElementById('cleanPurDetailMeta').innerText = `بەروار: ${p.purchase_date} | کۆی وەسڵ: ${fmtPurMoney(p.total_iqd)} | ماوەی قەرز: ${fmtPurMoney(p.remaining_iqd)}`;
            document.getElementById('btnEditFromDetailModal').onclick = () => editSavedPurchaseReceipt(p.id);

            document.getElementById('cleanPurDetailBody').innerHTML = res.items.map((it, i) => `
                <tr>
                    <td class="py-2 px-3 font-mono">${i + 1}</td>
                    <td class="py-2 px-3 font-bold">${it.item_name}</td>
                    <td class="py-2 px-3 text-center font-mono">${parseFloat(it.old_stock)}</td>
                    <td class="py-2 px-3 text-center font-mono">${fmtPurMoney(it.old_buy_price_iqd)}</td>
                    <td class="py-2 px-3 text-center font-mono font-bold text-emerald-600">${parseFloat(it.qty)}</td>
                    <td class="py-2 px-3 text-center font-mono">${fmtPurMoney(it.buy_price_iqd)}</td>
                    <td class="py-2 px-3 text-center font-mono font-extrabold text-amber-700">${fmtPurMoney(it.avg_buy_price_iqd)}</td>
                    <td class="py-2 px-3 text-center font-mono font-bold">${fmtPurMoney(it.total_iqd)}</td>
                </tr>
            `).join('');
            const modal = document.getElementById('cleanPurDetailModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
}

function deleteCleanPurchase(id) {
    if (!confirm('ئایا دڵنیایت لە سڕینەوەی ئەم وەسڵە؟')) return;
    fetch('api_purchases.php?action=delete_purchase', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ purchase_id: id })
    }).then(() => window.refreshAllPurchaseModules());
}

document.addEventListener('click', (e) => {
    if (!e.target.closest('#purCompanyName') && !e.target.closest('#purCompanyDropdown')) {
        document.getElementById('purCompanyDropdown')?.classList.add('hidden');
    }
    if (!e.target.closest('#entryProductName') && !e.target.closest('#entryProductDropdown')) {
        document.getElementById('entryProductDropdown')?.classList.add('hidden');
    }
});

renderCleanPurCart();
window.loadPurchasedReceiptsData();
</script>