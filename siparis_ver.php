<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Geçersiz ürün!');
}
$urun_id = intval($_GET['id']);

// Ürün bilgisi
$stmt = $pdo->prepare("SELECT * FROM urunler WHERE id = ? AND durum = 'aktif'");
$stmt->execute([$urun_id]);
$urun = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$urun) {
    die('Ürün bulunamadı veya satışta değil!');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $adet = intval($_POST['adet']);
    if ($adet < 1 || $adet > $urun['stok']) {
        echo "<script>alert('Geçersiz adet!'); window.location='siparis_ver.php?id=$urun_id';</script>";
        exit;
    }
    $kullanici_id = $_SESSION['kullanici_id'];
    $toplam_tutar = $adet * $urun['fiyat'];
    $olusturma_tarihi = date('Y-m-d H:i:s');
    $durum = 'yeni';

    // Sipariş oluştur
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO siparisler (kullanici_id, toplam_tutar, durum, olusturma_tarihi) VALUES (?, ?, ?, ?)");
        $stmt->execute([$kullanici_id, $toplam_tutar, $durum, $olusturma_tarihi]);
        $siparis_id = $pdo->lastInsertId();
        $stmt = $pdo->prepare("INSERT INTO siparis_urunleri (siparis_id, urun_id, adet, birim_fiyat) VALUES (?, ?, ?, ?)");
        $stmt->execute([$siparis_id, $urun_id, $adet, $urun['fiyat']]);
        // Stok güncelle
        $stmt = $pdo->prepare("UPDATE urunler SET stok = stok - ? WHERE id = ?");
        $stmt->execute([$adet, $urun_id]);
        $pdo->commit();
        echo "<script>alert('Siparişiniz başarıyla oluşturuldu!'); window.location='siparislerim.php';</script>";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>alert('Sipariş sırasında hata oluştu!'); window.location='siparis_ver.php?id=$urun_id';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sipariş Ver</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Sipariş Ver</h2>
    <p><strong>Ürün:</strong> <?php echo htmlspecialchars($urun['urun_adi']); ?></p>
    <p><strong>Fiyat:</strong> <?php echo number_format($urun['fiyat'],2); ?> TL</p>
    <p><strong>Stok:</strong> <?php echo $urun['stok']; ?></p>
    <form action="siparis_ver.php?id=<?php echo $urun_id; ?>" method="post">
        <label>Adet:</label>
        <input type="number" name="adet" min="1" max="<?php echo $urun['stok']; ?>" value="1" required><br>
        <button type="submit">Sipariş Ver</button>
    </form>
    <a href="urun_detay.php?id=<?php echo $urun_id; ?>">Ürün Detayına Dön</a>
</body>
</html>
