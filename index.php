<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ku" dir="rtl" class="light">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($app_settings['shop_name']) ?> - داشبۆرد</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Vazirmatn', sans-serif; }
        .tab-active {
            background-color: #ffffff;
            color: #059669;
            border-top: 3px solid #10b981;
            box-shadow: 0 -2px 8px rgba(0,0,0,0.04);
        }
        .dark .tab-active {
            background-color: #0f172a;
            color: #34d399;
            border-top: 3px solid #10b981;
        }
    </style>
</head>
<body class="bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-100 h-screen flex flex-col overflow-hidden select-none transition-colors duration-200">

    <!-- بەشی سەرەوە (Top Header) -->
    <header class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 px-4 py-2 flex items-center justify-between shadow-sm shrink-0">
        
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/20 border border-emerald-200 dark:border-emerald-500/40 flex items-center justify-center text-xl">
                🏪
            </div>
            <div>
                <h1 id="headerShopName" class="font-bold text-base text-slate-900 dark:text-white leading-tight">
                    <?= htmlspecialchars($app_settings['shop_name']) ?>
                </h1>
                <span class="text-xs text-slate-500 dark:text-slate-400">کاشێر: <?= htmlspecialchars($_SESSION['full_name']) ?></span>
            </div>
        </div>

        <div class="flex items-center gap-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-4 py-1.5 rounded-xl text-xs">
            <div class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-bold">
                <span>📅</span>
                <span id="liveDay">...</span>
            </div>
            <span class="text-slate-300 dark:text-slate-700">|</span>
            <div id="liveDate" class="text-slate-700 dark:text-slate-200 font-mono font-semibold">0000/00/00</div>
            <span class="text-slate-300 dark:text-slate-700">|</span>
            <div id="liveClock" class="text-amber-600 dark:text-amber-400 font-mono font-bold">00:00:00</div>
        </div>

        <div class="flex items-center gap-2.5">
            <div class="flex items-center bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-1 gap-2">
                <span class="text-xs text-slate-500 dark:text-slate-400">100$ =</span>
                <span id="displayRate" class="font-bold text-emerald-600 dark:text-emerald-400 font-mono text-sm">
                    <?= number_format($app_settings['usd_rate'] * 100) ?>
                </span>
                <span class="text-xs text-slate-500 dark:text-slate-400">د.ع</span>
                <button onclick="openRateModal()" class="text-xs bg-amber-50 hover:bg-amber-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-slate-700 px-2 py-0.5 rounded-lg font-semibold transition">
                    ✏️ گۆڕین
                </button>
            </div>

            <div class="flex bg-slate-100 dark:bg-slate-950 p-1 rounded-xl border border-slate-200 dark:border-slate-800">
                <button id="btnIQD" onclick="setSystemCurrency('IQD')" class="px-2.5 py-1 rounded-lg text-xs font-bold transition">
                    دینار (د.ع)
                </button>
                <button id="btnUSD" onclick="setSystemCurrency('USD')" class="px-2.5 py-1 rounded-lg text-xs font-bold transition">
                    دۆلار ($)
                </button>
            </div>

            <button onclick="toggleTheme()" id="themeToggleBtn"
                class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-amber-300 px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <span id="themeIcon">🌙</span>
                <span id="themeText">تاریک</span>
            </button>

            <a href="logout.php" class="bg-red-50 hover:bg-red-100 dark:bg-red-500/10 dark:hover:bg-red-500/20 border border-red-200 dark:border-red-500/30 text-red-600 dark:text-red-400 px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1">
                <span>چوونەدەرەوە</span>
                <span>🚪</span>
            </a>
        </div>
    </header>

    <!-- شریتی تابەکان -->
    <div class="bg-slate-200/80 dark:bg-slate-900 border-b border-slate-300 dark:border-slate-800 px-2 pt-1.5 flex items-center gap-1 overflow-x-auto shrink-0" id="tabsBar">
        <button onclick="switchTab('dashboard')" id="tab-btn-dashboard"
            class="tab-active px-4 py-1.5 rounded-t-xl text-xs font-bold flex items-center gap-2 transition border-x border-slate-300 dark:border-slate-800">
            <span>🏠</span>
            <span>داشبۆرد</span>
        </button>
    </div>

    <!-- ناوەڕۆکی تابەکان -->
    <main class="flex-1 relative overflow-hidden bg-slate-100 dark:bg-slate-950" id="tabsContent">
        
        <div id="tab-panel-dashboard" class="h-full w-full p-6 overflow-y-auto">
            <div class="max-w-6xl mx-auto">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">بەشەکانی سیستەم</h2>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">کلیک لە هەر بەشێک بکە بۆ کردنەوەی لە تابی سەربەخۆدا</p>
                    </div>
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 px-4 py-2 rounded-xl text-sm shadow-sm">
                        دراوی چالاک: <span class="font-bold text-emerald-600 dark:text-emerald-400 currency-label"></span>
                    </div>
                </div>

                <!-- کارتی بەشەکان (١٠ بەشی ڕێکخراو) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    
                    <!-- ١. بەشی فرۆشتن -->
                    <div onclick="openTab('sales', 'بەشی فرۆشتن', '🛒', 'sales.php')"
                        class="cursor-pointer group bg-white dark:bg-slate-900 hover:border-emerald-500 border-2 border-slate-200 dark:border-slate-800 rounded-2xl p-4 transition-all duration-200 shadow-sm hover:shadow-md">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition">
                            🛒
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white mb-1">بەشی فرۆشتن (کاشێر)</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">فرۆشتنی خێرا بە بارکۆد، نەقد، قەرز، قیست و زەمان</p>
                    </div>

                    <!-- ٢. وەسڵە فرۆشراوەکان -->
                    <div onclick="openTab('sold_receipts', 'وەسڵە فرۆشراوەکان', '🧾', 'sold_receipts.php')"
                        class="cursor-pointer group bg-white dark:bg-slate-900 hover:border-blue-500 border-2 border-slate-200 dark:border-slate-800 rounded-2xl p-4 transition-all duration-200 shadow-sm hover:shadow-md">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition">
                            🧾
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white mb-1">وەسڵە فرۆشراوەکان</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">بینینی وەسڵەکانی فرۆشتن و گەڕاندنەوەی کاڵا بۆ کۆگا</p>
                    </div>

                    <!-- ٣. وەسڵە کڕدراوەکان -->
                    <div onclick="openTab('purchased_receipts', 'وەسڵە کڕدراوەکان', '📥', 'purchased_receipts.php')"
                        class="cursor-pointer group bg-white dark:bg-slate-900 hover:border-amber-500 border-2 border-slate-200 dark:border-slate-800 rounded-2xl p-4 transition-all duration-200 shadow-sm hover:shadow-md">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition">
                            📥
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white mb-1">وەسڵە کڕدراوەکان</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">زیادکردنی وەسڵی کڕین و دەستکاریکردنی وەسڵی کڕدراو</p>
                    </div>

                    <!-- ٤. لیستی کاڵاکان -->
                    <div onclick="openTab('products_list', 'لیستی کاڵاکان', '📦', 'products_list.php')"
                        class="cursor-pointer group bg-white dark:bg-slate-900 hover:border-teal-500 border-2 border-slate-200 dark:border-slate-800 rounded-2xl p-4 transition-all duration-200 shadow-sm hover:shadow-md">
                        <div class="w-12 h-12 rounded-2xl bg-teal-50 dark:bg-teal-500/20 text-teal-600 dark:text-teal-400 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition">
                            📦
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white mb-1">لیستی کاڵاکان (کۆگا)</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">نرخی تاک، جوملە، تایبەت، کۆمپانیا، براند و پۆل</p>
                    </div>
                    <!-- پۆل و براندەکان -->
                    <div onclick="openTab('categories_brands', 'پۆل و براندەکان', '🏷️', 'categories_brands.php')"
                        class="cursor-pointer group bg-white dark:bg-slate-900 hover:border-violet-500 border-2 border-slate-200 dark:border-slate-800 rounded-2xl p-4 transition-all duration-200 shadow-sm hover:shadow-md">
                        <div class="w-12 h-12 rounded-2xl bg-violet-50 dark:bg-violet-500/20 text-violet-600 dark:text-violet-400 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition">
                            🏷️
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white mb-1">پۆل و براندەکان</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">زیادکردنی پۆل (بەش) و براند (مارکە) بۆ کاڵاکان</p>
                    </div>

                    <!-- ٥. لیستی کڕیاران -->
                    <div onclick="openTab('customers_list', 'لیستی کڕیاران', '👥', 'customers_list.php')"
                        class="cursor-pointer group bg-white dark:bg-slate-900 hover:border-cyan-500 border-2 border-slate-200 dark:border-slate-800 rounded-2xl p-4 transition-all duration-200 shadow-sm hover:shadow-md">
                        <div class="w-12 h-12 rounded-2xl bg-cyan-50 dark:bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition">
                            👥
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white mb-1">لیستی کڕیاران</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">بەڕێوەبردنی کڕیاران، ژمارە تەلەفۆن، کەفیل و باڵانس</p>
                    </div>

                    <!-- ٦. بەشی قەرزەکان (کڕیاران) -->
                    <div onclick="openTab('customer_debts', 'بەشی قەرزەکان', '📒', 'customer_debts.php')"
                        class="cursor-pointer group bg-white dark:bg-slate-900 hover:border-orange-500 border-2 border-slate-200 dark:border-slate-800 rounded-2xl p-4 transition-all duration-200 shadow-sm hover:shadow-md">
                        <div class="w-12 h-12 rounded-2xl bg-orange-50 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition">
                            📒
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white mb-1">بەشی قەرزەکان</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">لیستی وەسڵە قەرزەکانی کڕیاران و وەرگرتنەوەی قەرز</p>
                    </div>

                    <!-- ٧. بەشی قیستەکان (کڕیاران) -->
                    <div onclick="openTab('customer_installments', 'بەشی قیستەکان', '📅', 'customer_installments.php')"
                        class="cursor-pointer group bg-white dark:bg-slate-900 hover:border-pink-500 border-2 border-slate-200 dark:border-slate-800 rounded-2xl p-4 transition-all duration-200 shadow-sm hover:shadow-md">
                        <div class="w-12 h-12 rounded-2xl bg-pink-50 dark:bg-pink-500/20 text-pink-600 dark:text-pink-400 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition">
                            📅
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white mb-1">بەشی قیستەکان</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">بەڕێوەبردنی وەسڵەکانی قیست و وەرگرتنی قیستی مانگانە</p>
                    </div>

                    <!-- ٨. لیستی کۆمپانیاکان -->
                    <div onclick="openTab('companies_list', 'لیستی کۆمپانیاکان', '🏢', 'companies_list.php')"
                        class="cursor-pointer group bg-white dark:bg-slate-900 hover:border-indigo-500 border-2 border-slate-200 dark:border-slate-800 rounded-2xl p-4 transition-all duration-200 shadow-sm hover:shadow-md">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition">
                            🏢
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white mb-1">لیستی کۆمپانیاکان</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">تۆمارکردنی کۆمپانیاکان و کۆی گشتی کڕینەکانیان</p>
                    </div>

                    <!-- ٩. قەرزی کۆمپانیاکان -->
                    <div onclick="openTab('company_debts', 'قەرزی کۆمپانیاکان', '💳', 'company_debts.php')"
                        class="cursor-pointer group bg-white dark:bg-slate-900 hover:border-rose-500 border-2 border-slate-200 dark:border-slate-800 rounded-2xl p-4 transition-all duration-200 shadow-sm hover:shadow-md">
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition">
                            💳
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white mb-1">قەرزی کۆمپانیاکان</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">بینینی وەسڵە قەرزەکانی کۆمپانیاکان و دانەوەی قەرز</p>
                    </div>

                    <!-- ١٠. بەشی سێتینگ -->
                    <div onclick="openTab('settings', 'بەشی سێتینگ', '⚙️', 'settings.php')"
                        class="cursor-pointer group bg-white dark:bg-slate-900 hover:border-purple-500 border-2 border-slate-200 dark:border-slate-800 rounded-2xl p-4 transition-all duration-200 shadow-sm hover:shadow-md">
                        <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-500/20 text-purple-600 dark:text-purple-400 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition">
                            ⚙️
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white mb-1">بەشی سێتینگ</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">گۆڕینی ناوی دوکان، نرخی دۆلار و ڕێکخستنی سیستەم</p>
                    </div>

                </div>
            </div>
        </div>

    </main>

    <!-- مۆداڵی گۆڕینی خێرای نرخی دۆلار -->
    <div id="rateModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl w-full max-w-sm p-6 shadow-2xl">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">گۆڕینی نرخی دۆلار</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">نرخی ١٠٠ دۆلار بە دینار بنووسە (نموونە: 150000)</p>
            <input type="number" id="newRateInput"
                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-2.5 text-slate-900 dark:text-white font-mono text-lg mb-4 focus:outline-none focus:border-emerald-500">
            <div class="flex justify-end gap-2">
                <button onclick="closeRateModal()" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-sm font-semibold">پاشگەزبوونەوە</button>
                <button onclick="saveNewRate()" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm">پاشەکەوتکردن</button>
            </div>
        </div>
        <!-- ١١. بەشی گەرەنتی و سیریال -->
                    <div onclick="openTab('warranties', 'گەرەنتی و سیریال', '🛡️', 'warranties.php')"
                        class="cursor-pointer group bg-white dark:bg-slate-900 hover:border-sky-500 border-2 border-slate-200 dark:border-slate-800 rounded-2xl p-4 transition-all duration-200 shadow-sm hover:shadow-md">
                        <div class="w-12 h-12 rounded-2xl bg-sky-50 dark:bg-sky-500/20 text-sky-600 dark:text-sky-400 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition">
                            🛡️
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white mb-1">بەشی گەرەنتی و سیریال</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">گەڕان بەدوای کاڵای گەرەنتی بە سیریال، بەروار، کاڵا و کڕیار</p>
                    </div>

                    <!-- ١٢. بەشی ئاگادارییەکان -->
                    <div onclick="openTab('alerts_center', 'بەشی ئاگادارییەکان', '🔔', 'alerts_center.php')"
                        class="cursor-pointer group bg-white dark:bg-slate-900 hover:border-red-500 border-2 border-slate-200 dark:border-slate-800 rounded-2xl p-4 transition-all duration-200 shadow-sm hover:shadow-md">
                        <div class="w-12 h-12 rounded-2xl bg-red-50 dark:bg-red-500/20 text-red-600 dark:text-red-400 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition">
                            🔔
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white mb-1">بەشی ئاگادارییەکان</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">ئاگاداری قەرز، قیست، کاڵای مەیوو، کەمبوون و بەسەرچوون</p>
                    </div>
    </div>

    <script>
        window.POS_CONFIG = {
            currency: "<?= $app_settings['active_currency'] ?>",
            usdRate: <?= floatval($app_settings['usd_rate']) ?>
        };

        const openTabs = {};
        let currentTab = 'dashboard';

        window.refreshAllPurchaseModules = function() {
            if (typeof window.loadWarrantiesPageData === 'function') window.loadWarrantiesPageData();
            if (typeof window.loadAlertsCenterData === 'function') window.loadAlertsCenterData();
            if (typeof window.loadCategoriesBrandsPageData === 'function') window.loadCategoriesBrandsPageData();
            if (typeof window.loadPurchasedReceiptsData === 'function') window.loadPurchasedReceiptsData();
            if (typeof window.loadProductsPageData === 'function') window.loadProductsPageData();
            if (typeof window.loadCompaniesPageData === 'function') window.loadCompaniesPageData();
            if (typeof window.loadCompanyDebtsPageData === 'function') window.loadCompanyDebtsPageData();
            if (typeof window.loadCustomersPageData === 'function') window.loadCustomersPageData();
            if (typeof window.loadCustomerDebtsPageData === 'function') window.loadCustomerDebtsPageData();
            if (typeof window.loadCustomerInstallmentsPageData === 'function') window.loadCustomerInstallmentsPageData();
            if (typeof window.loadSoldReceipts === 'function') window.loadSoldReceipts();
        };

        function applyTheme(theme) {
            const html = document.documentElement;
            const icon = document.getElementById('themeIcon');
            const text = document.getElementById('themeText');
            if (theme === 'dark') {
                html.classList.add('dark');
                icon.innerText = '☀️';
                text.innerText = 'سپی';
            } else {
                html.classList.remove('dark');
                icon.innerText = '🌙';
                text.innerText = 'تاریک';
            }
            localStorage.setItem('pos_theme', theme);
        }

        function toggleTheme() {
            const isDark = document.documentElement.classList.contains('dark');
            applyTheme(isDark ? 'light' : 'dark');
        }

        applyTheme(localStorage.getItem('pos_theme') || 'light');

        const kurdishDays = ['یەکشەممە', 'دووشەممە', 'سێشەممە', 'چوارشەممە', 'پێنجشەممە', 'هەینی', 'شەممە'];
        function updateClock() {
            const now = new Date();
            document.getElementById('liveDay').innerText = kurdishDays[now.getDay()];
            const yyyy = now.getFullYear();
            const mm = String(now.getMonth() + 1).padStart(2, '0');
            const dd = String(now.getDate()).padStart(2, '0');
            document.getElementById('liveDate').innerText = `${yyyy}/${mm}/${dd}`;
            document.getElementById('liveClock').innerText = now.toLocaleTimeString('en-US');
        }
        setInterval(updateClock, 1000);
        updateClock();

        function formatMoney(amountInIQD) {
            if (window.POS_CONFIG.currency === 'USD') {
                const usd = amountInIQD / window.POS_CONFIG.usdRate;
                return '$' + usd.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            } else {
                return Math.round(amountInIQD).toLocaleString('en-US') + ' د.ع';
            }
        }

        function refreshCurrencyUI() {
            const isUSD = window.POS_CONFIG.currency === 'USD';
            const btnIQD = document.getElementById('btnIQD');
            const btnUSD = document.getElementById('btnUSD');

            if (isUSD) {
                btnUSD.className = "px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-600 text-white shadow";
                btnIQD.className = "px-2.5 py-1 rounded-lg text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-slate-900";
            } else {
                btnIQD.className = "px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-600 text-white shadow";
                btnUSD.className = "px-2.5 py-1 rounded-lg text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-slate-900";
            }

            document.getElementById('displayRate').innerText = Math.round(window.POS_CONFIG.usdRate * 100).toLocaleString('en-US');

            document.querySelectorAll('[data-iqd]').forEach(el => {
                const iqdValue = parseFloat(el.getAttribute('data-iqd')) || 0;
                el.innerText = formatMoney(iqdValue);
            });

            document.querySelectorAll('.currency-label').forEach(el => {
                el.innerText = isUSD ? 'دۆلار ($)' : 'دینار (د.ع)';
            });

            if (typeof renderCart === 'function') renderCart();
            window.refreshAllPurchaseModules();
        }

        function setSystemCurrency(cur) {
            window.POS_CONFIG.currency = cur;
            refreshCurrencyUI();
            const formData = new FormData();
            formData.append('action', 'toggle_currency');
            formData.append('currency', cur);
            fetch('api_update.php', { method: 'POST', body: formData });
        }

        function openRateModal() {
            document.getElementById('newRateInput').value = Math.round(window.POS_CONFIG.usdRate * 100);
            document.getElementById('rateModal').classList.remove('hidden');
            document.getElementById('rateModal').classList.add('flex');
            document.getElementById('newRateInput').focus();
        }
        function closeRateModal() {
            document.getElementById('rateModal').classList.add('hidden');
            document.getElementById('rateModal').classList.remove('flex');
        }
        function saveNewRate() {
            const val = parseFloat(document.getElementById('newRateInput').value);
            if (!val || val <= 0) return;

            const formData = new FormData();
            formData.append('action', 'update_rate');
            formData.append('rate', val);

            fetch('api_update.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        window.POS_CONFIG.usdRate = parseFloat(data.usd_rate);
                        refreshCurrencyUI();
                        closeRateModal();
                    }
                });
        }

        function openTab(tabId, title, icon, url) {
            if (openTabs[tabId]) {
                switchTab(tabId);
                return;
            }

            const tabsBar = document.getElementById('tabsBar');
            const tabBtn = document.createElement('div');
            tabBtn.id = `tab-btn-${tabId}`;
            tabBtn.className = "px-4 py-1.5 rounded-t-xl text-xs font-bold flex items-center gap-2 cursor-pointer transition border-x border-slate-300 dark:border-slate-800 bg-slate-300/60 dark:bg-slate-800/60 text-slate-700 dark:text-slate-300";
            tabBtn.innerHTML = `
                <div onclick="switchTab('${tabId}')" class="flex items-center gap-1.5">
                    <span>${icon}</span>
                    <span>${title}</span>
                </div>
                <button onclick="closeTab('${tabId}', event)" class="w-4 h-4 rounded-full hover:bg-red-500/20 hover:text-red-600 flex items-center justify-center text-[10px] transition" title="داخستنی تاب">
                    ✕
                </button>
            `;
            tabsBar.appendChild(tabBtn);

            const tabsContent = document.getElementById('tabsContent');
            const panel = document.createElement('div');
            panel.id = `tab-panel-${tabId}`;
            panel.className = "h-full w-full overflow-hidden hidden p-3";
            panel.innerHTML = `<div class="text-center text-slate-500 mt-10">بارکردنی ${title}...</div>`;
            tabsContent.appendChild(panel);

            openTabs[tabId] = true;
            switchTab(tabId);

            fetch(url)
                .then(res => res.text())
                .then(html => {
                    panel.innerHTML = html;
                    panel.querySelectorAll('script').forEach(oldScript => {
                        const newScript = document.createElement('script');
                        newScript.text = oldScript.text;
                        document.body.appendChild(newScript).parentNode.removeChild(newScript);
                    });
                    refreshCurrencyUI();
                });
        }

        function switchTab(tabId) {
            document.querySelectorAll('[id^="tab-panel-"]').forEach(p => p.classList.add('hidden'));
            document.querySelectorAll('[id^="tab-btn-"]').forEach(b => {
                b.classList.remove('tab-active');
                b.classList.add('bg-slate-300/60', 'dark:bg-slate-800/60', 'text-slate-700', 'dark:text-slate-300');
            });

            const activePanel = document.getElementById(`tab-panel-${tabId}`);
            const activeBtn = document.getElementById(`tab-btn-${tabId}`);
            if (activePanel && activeBtn) {
                activePanel.classList.remove('hidden');
                activeBtn.classList.add('tab-active');
                activeBtn.classList.remove('bg-slate-300/60', 'dark:bg-slate-800/60', 'text-slate-700', 'dark:text-slate-300');
                currentTab = tabId;
                window.refreshAllPurchaseModules();
            }
        }

        function closeTab(tabId, event) {
            event.stopPropagation();
            const btn = document.getElementById(`tab-btn-${tabId}`);
            const panel = document.getElementById(`tab-panel-${tabId}`);
            if (btn) btn.remove();
            if (panel) panel.remove();
            delete openTabs[tabId];

            if (currentTab === tabId) {
                const remaining = Object.keys(openTabs);
                if (remaining.length > 0) {
                    switchTab(remaining[remaining.length - 1]);
                } else {
                    switchTab('dashboard');
                }
            }
        }

        refreshCurrencyUI();
    </script>
</body>
</html>