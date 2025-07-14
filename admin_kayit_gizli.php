<?php
session_start();
// Sadece belirli bir gizli anahtar ile erişim (ör: ?key=supersecret)
$gizli_anahtar = 'supersecret2025';
if (!isset($_GET['key']) || $_GET['key'] !== $gizli_anahtar) {
    http_response_code(404);
    exit('Sayfa bulunamadı.');
}
require_once 'db.php';
$hata = '';
$basari = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad = trim($_POST['ad'] ?? '');
    $eposta = trim($_POST['eposta'] ?? '');
    $sifre = $_POST['sifre'] ?? '';
    if ($ad && $eposta && $sifre) {
        $hashli_sifre = password_hash($sifre, PASSWORD_DEFAULT);
        $olusturma_tarihi = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare('INSERT INTO kullanicilar (ad, eposta, sifre, rol, olusturma_tarihi) VALUES (?, ?, ?, ?, ?)');
        $sonuc = $stmt->execute([$ad, $eposta, $hashli_sifre, 'yonetici', $olusturma_tarihi]);
        if ($sonuc) {
            $basari = 'Admin başarıyla kaydedildi!';
        } else {
            $hata = 'Kayıt sırasında hata oluştu.';
        }
    } else {
        $hata = 'Tüm alanları doldurun.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Gizli Admin Kayıt</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-kayit-form { max-width: 400px; margin: 60px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 16px #0001; padding: 32px; }
        .admin-kayit-form h2 { text-align: center; margin-bottom: 24px; }
        .admin-kayit-form label { display: block; margin-bottom: 6px; font-weight: 500; }
        .admin-kayit-form input { width: 100%; padding: 10px; margin-bottom: 18px; border-radius: 6px; border: 1px solid #ccc; }
        .admin-kayit-form button { width: 100%; padding: 10px; background: #1a73e8; color: #fff; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; }
        .admin-kayit-form .mesaj { text-align: center; margin-bottom: 16px; }
        .admin-kayit-form .mesaj.hata { color: #c00; }
        .admin-kayit-form .mesaj.basari { color: #090; }
    </style>
</head>
<body>
    <form class="admin-kayit-form" method="post">
        <h2>Admin Kayıt</h2>
        <?php if ($hata): ?><div class="mesaj hata"><?php echo $hata; ?></div><?php endif; ?>
        <?php if ($basari): ?><div class="mesaj basari"><?php echo $basari; ?></div><?php endif; ?>
        <label for="ad">Ad Soyad</label>
        <input type="text" name="ad" id="ad" required>
        <label for="eposta">E-posta</label>
        <input type="email" name="eposta" id="eposta" required>
        <label for="sifre">Şifre</label>
        <input type="password" name="sifre" id="sifre" required>
        <button type="submit">Admin Kaydet</button>
        <a href="login.php" style="display:block;margin-top:18px;text-align:center;color:#4361ee;font-weight:500;text-decoration:underline;">Giriş Sayfasına Dön</a>
    </form>
</body>
</html>
