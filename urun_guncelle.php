<?php
session_start();
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db.php';
if ($_SESSION['rol'] !== 'satici' && $_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'yonetici') {
    header('Location: index.php');
    exit;
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Geçersiz ürün!');
}
$urun_id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM urunler WHERE id = ?");
$stmt->execute([$urun_id]);
$urun = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$urun) {
    die('Ürün bulunamadı!');
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $urun_adi = trim($_POST['urun_adi']);
    $urun_aciklamasi = trim($_POST['urun_aciklamasi']);
    $fiyat = floatval($_POST['fiyat']);
    $stok = intval($_POST['stok']);
    $durum = $_POST['durum'];
    $stmt = $pdo->prepare("UPDATE urunler SET urun_adi=?, urun_aciklamasi=?, fiyat=?, stok=?, durum=? WHERE id=?");
    if ($stmt->execute([$urun_adi, $urun_aciklamasi, $fiyat, $stok, $durum, $urun_id])) {
        echo "<script>alert('Ürün güncellendi!'); window.location='urunlerim.php';</script>";
        exit;
    } else {
        echo "<script>alert('Güncelleme hatası!'); window.location='urunlerim.php';</script>";
        exit;
    }
}
include 'header.php';
?>
<div class="modern-layout-flex">
  <aside class="modern-sidebar">
    <nav class="modern-sidebar-nav">
      <ul>
        <li><a href="urunler.php">Tüm Ürünleri Gör</a></li>
        <!-- Kategoriler linki sidebar'dan kaldırıldı -->
        <li><a href="siparislerim.php">Siparişlerim</a></li>
        <li><a href="profil.php">Profilim</a></li>
      </ul>
    </nav>
  </aside>
  <main class="modern-main-content">
    <div class="modern-box glassmorphism shadow-lg p-4 mb-4 mt-4 animate-fade-in">
      <h2 class="modern-title mb-4">Ürün Güncelle</h2>
      <form action="urun_guncelle.php?id=<?php echo $urun_id; ?>" method="post" class="modern-form">
        <label>Ürün Adı:
          <input type="text" name="urun_adi" value="<?php echo htmlspecialchars($urun['urun_adi']); ?>" required>
        </label>
        <label>Ürün Açıklaması:
          <textarea name="urun_aciklamasi" required><?php echo htmlspecialchars($urun['urun_aciklamasi']); ?></textarea>
        </label>
        <label>Fiyat:
          <input type="number" step="0.01" name="fiyat" value="<?php echo $urun['fiyat']; ?>" required>
        </label>
        <label>Stok:
          <input type="number" name="stok" value="<?php echo $urun['stok']; ?>" required>
        </label>
        <label>Durum:
          <select name="durum" required>
            <option value="aktif" <?php if($urun['durum']==='aktif') echo 'selected'; ?>>Aktif</option>
            <option value="pasif" <?php if($urun['durum']==='pasif') echo 'selected'; ?>>Pasif</option>
          </select>
        </label>
        <button type="submit" class="btn-main mt-3">Güncelle</button>
      </form>
      <div class="mt-3">
        <a href="urunlerim.php" class="btn-secondary">Ürünlerime Dön</a>
      </div>
    </div>
  </main>
</div>
<?php include 'footer.php'; ?>
