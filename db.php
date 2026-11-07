<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "pos_system";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("هەڵە لە پەیوەستبوون بە داتابەیس: " . $e->getMessage());
}

// هێنانی ڕێکخستنەکان (ناوی دوکان و نرخی دۆلار)
$stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
$app_settings = $stmt->fetch(PDO::FETCH_ASSOC);
?>