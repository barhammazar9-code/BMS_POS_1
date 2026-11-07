<?php require_once 'db.php'; ?>
<div class="max-w-2xl mx-auto mt-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
        <div class="flex items-center gap-3 border-b border-slate-200 dark:border-slate-800 pb-4 mb-6">
            <span class="text-3xl">⚙️</span>
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">بەشی سێتینگ و ڕێکخستن</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">گۆڕینی زانیارییە گشتییەکانی مارکێت و نرخی دۆلار</p>
            </div>
        </div>

        <form onsubmit="saveGeneralSettings(event)" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">ناوی دوکان / مارکێت</label>
                <input type="text" id="settingShopName" value="<?= htmlspecialchars($app_settings['shop_name']) ?>" required
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-2.5 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">نرخی ١٠٠ دۆلار بە دینار</label>
                <input type="number" id="settingUsdRate" value="<?= round($app_settings['usd_rate'] * 100) ?>" required
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-2.5 text-slate-900 dark:text-white font-mono focus:outline-none focus:border-emerald-500">
            </div>

            <div id="settingsAlert" class="hidden bg-emerald-50 dark:bg-emerald-500/20 border border-emerald-500 text-emerald-700 dark:text-emerald-300 px-4 py-2.5 rounded-xl text-sm font-semibold">
                ڕێکخستنەکان بە سەرکەوتوویی نوێکرانەوە!
            </div>

            <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-6 py-2.5 rounded-xl transition shadow-sm">
                پاشەکەوتکردنی گۆڕانکارییەکان
            </button>
        </form>
    </div>
</div>

<script>
function saveGeneralSettings(e) {
    e.preventDefault();
    const shopName = document.getElementById('settingShopName').value;
    const usdRate = document.getElementById('settingUsdRate').value;

    const fd = new FormData();
    fd.append('action', 'update_settings');
    fd.append('shop_name', shopName);
    fd.append('usd_rate', usdRate);

    fetch('api_update.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('headerShopName').innerText = data.shop_name;
                window.POS_CONFIG.usdRate = parseFloat(data.usd_rate);
                refreshCurrencyUI();
                const alertBox = document.getElementById('settingsAlert');
                alertBox.classList.remove('hidden');
                setTimeout(() => alertBox.classList.add('hidden'), 3000);
            }
        });
}
</script>