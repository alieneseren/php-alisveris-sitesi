<?php
require_once 'db.php';
session_start();
if ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'yonetici') {
    header('Location: index.php');
    exit;
}
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM urunler WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    header('Location: admin.php');
    exit;
}
?>
