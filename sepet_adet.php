<?php
session_start();
if (!isset($_SESSION['kullanici_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Giriş gerekli']);
    exit;
}
if (!isset($_POST['urun_id']) || !isset($_POST['adet'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Eksik veri']);
    exit;
}
$urun_id = intval($_POST['urun_id']);
$adet = intval($_POST['adet']);
if ($adet < 1) $adet = 1;
if (!isset($_SESSION['sepet'][$urun_id])) {
    echo json_encode(['success' => false, 'message' => 'Ürün sepette yok']);
    exit;
}
// Stok kontrolü
require_once 'db.php';
$stmt = $pdo->prepare("SELECT stok FROM urunler WHERE id = ?");
$stmt->execute([$urun_id]);
$urun = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$urun) {
    echo json_encode(['success' => false, 'message' => 'Ürün bulunamadı']);
    exit;
}
if ($adet > $urun['stok']) {
    echo json_encode(['success' => false, 'message' => 'Stok yetersiz']);
    exit;
}
$_SESSION['sepet'][$urun_id] = $adet;
// Yeni toplamı döndür
$fiyat = 0;
$stmt = $pdo->prepare("SELECT fiyat FROM urunler WHERE id = ?");
$stmt->execute([$urun_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) $fiyat = $row['fiyat'];
$ara_toplam = $fiyat * $adet;
// Sepet toplamı
$toplam = 0;
foreach ($_SESSION['sepet'] as $uid => $adet2) {
    $stmt = $pdo->prepare("SELECT fiyat FROM urunler WHERE id = ?");
    $stmt->execute([$uid]);
    $row2 = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row2) $toplam += $row2['fiyat'] * $adet2;
}
echo json_encode([
    'success' => true,
    'adet' => $adet,
    'ara_toplam' => number_format($ara_toplam,2),
    'toplam' => number_format($toplam,2)
]);
