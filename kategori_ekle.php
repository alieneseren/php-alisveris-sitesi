<?php
session_start();
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit;
}
if ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'yonetici') {
    header('Location: index.php');
    exit;
}
require_once 'db.php';
$hata = '';
$basari = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kategori_adi = trim($_POST['kategori_adi'] ?? '');
    if ($kategori_adi) {
        $stmt = $pdo->prepare('INSERT INTO kategoriler (kategori_adi) VALUES (?)');
        if ($stmt->execute([$kategori_adi])) {
            $basari = 'Kategori başarıyla eklendi!';
        } else {
            $hata = 'Kategori eklenirken hata oluştu!';
        }
    } else {
        $hata = 'Kategori adı boş olamaz!';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kategori Ekle</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .kategori-form { max-width: 400px; margin: 60px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 16px #0001; padding: 32px; }
        .kategori-form h2 { text-align: center; margin-bottom: 24px; }
        .kategori-form label { display: block; margin-bottom: 6px; font-weight: 500; }
        .kategori-form input { width: 100%; padding: 10px; margin-bottom: 18px; border-radius: 6px; border: 1px solid #ccc; }
        .kategori-form button { width: 100%; padding: 10px; background: #219150; color: #fff; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; }
        .kategori-form .mesaj { text-align: center; margin-bottom: 16px; }
        .kategori-form .mesaj.hata { color: #c00; }
        .kategori-form .mesaj.basari { color: #090; }
    </style>
</head>
<body>
    <form class="kategori-form" method="post">
        <h2>Kategori Ekle</h2>
        <?php if ($hata): ?><div class="mesaj hata"><?php echo $hata; ?></div><?php endif; ?>
        <?php if ($basari): ?><div class="mesaj basari"><?php echo $basari; ?></div><?php endif; ?>
        <label for="kategori_adi">Kategori Adı</label>
        <input type="text" name="kategori_adi" id="kategori_adi" required>
        <button type="submit">Ekle</button>
        <a href="admin.php" style="display:block;margin-top:18px;text-align:center;color:#4361ee;font-weight:500;text-decoration:underline;">Admin Paneline Dön</a>
    </form>
</body>
</html>
