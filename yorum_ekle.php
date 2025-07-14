<?php
session_start();
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $urun_id = intval($_POST['urun_id']);
    $kullanici_id = $_SESSION['kullanici_id'];
    $puan = intval($_POST['puan']);
    $yorum = trim($_POST['yorum']);
    $olusturma_tarihi = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("INSERT INTO urun_yorumlari (urun_id, kullanici_id, puan, yorum, olusturma_tarihi) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$urun_id, $kullanici_id, $puan, $yorum, $olusturma_tarihi])) {
        echo "<script>alert('Yorumunuz eklendi!'); window.location='urun_detay.php?id=".$urun_id."';</script>";
    } else {
        echo "<script>alert('Yorum eklenirken hata oluştu!'); window.location='urun_detay.php?id=".$urun_id."';</script>";
    }
}
?>
