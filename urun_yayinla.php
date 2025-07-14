<?php
require_once 'db.php';
session_start();
if (!isset($_SESSION['kullanici_id']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'yonetici')) {
    header('Location: index.php');
    exit;
}
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("UPDATE urunler SET durum = 'aktif' WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    header('Location: admin.php');
    exit;
}
?>
