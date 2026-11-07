<?php
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        header("Location: index.php");
        exit;
    } else {
        $error = "ناوی بەکارهێنەر یان وشەی نهێنی هەڵەیە!";
    }
}
?>
<!DOCTYPE html>
<html lang="ku" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>چوونەژوورەوە - <?= htmlspecialchars($app_settings['shop_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Vazirmatn', sans-serif; }</style>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">

    <div class="bg-slate-800 border border-slate-700 w-full max-w-md rounded-2xl shadow-2xl p-8">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-emerald-500/20 text-emerald-400 rounded-2xl flex items-center justify-center mx-auto mb-4 text-3xl font-bold">
                🛒
            </div>
            <h1 class="text-2xl font-bold text-white"><?= htmlspecialchars($app_settings['shop_name']) ?></h1>
            <p class="text-slate-400 text-sm mt-1">سیستەمی کاشێر و بەڕێوەبردنی فرۆشتن</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-300 px-4 py-3 rounded-xl mb-6 text-sm text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-slate-300 text-sm mb-2">ناوی بەکارهێنەر</label>
                <input type="text" name="username" required autofocus
                    class="w-full bg-slate-900 border border-slate-700 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-slate-300 text-sm mb-2">وشەی نهێنی</label>
                <input type="password" name="password" required
                    class="w-full bg-slate-900 border border-slate-700 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-emerald-500">
            </div>
            <button type="submit"
                class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-emerald-600/20">
                چوونەژوورەوە
            </button>
        </form>
    </div>

</body>
</html>