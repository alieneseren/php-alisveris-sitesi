<?php
require_once 'db.php';
session_start();
if (!isset($_SESSION['kullanici_id']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'yonetici')) {
    header('Location: index.php');
    exit;
}
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM kullanicilar WHERE id = ? AND (rol = 'musteri' OR rol = 'satici')");
    $stmt->execute([$_GET['id']]);
}
header('Location: admin.php');
exit;
?>
