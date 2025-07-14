<?php
session_start();
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db.php';
if ($_SESSION['rol'] !== 'satici') {
    header('Location: index.php');
    exit;
}
$kullanici_id = $_SESSION['kullanici_id'];
$stmt = $pdo->prepare("SELECT u.*, m.magaza_adi FROM urunler u JOIN magazalar m ON u.magaza_id = m.id WHERE m.kullanici_id = ? ORDER BY u.olusturma_tarihi DESC");
$stmt->execute([$kullanici_id]);
$urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);
$alert = null;
if (isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    $urun_id = intval($_GET['sil']);
    $stmt = $pdo->prepare("DELETE FROM urunler WHERE id = ?");
    if ($stmt->execute([$urun_id])) {
        $alert = ['type' => 'success', 'msg' => 'Ürün silindi!'];
    } else {
        $alert = ['type' => 'danger', 'msg' => 'Silme hatası!'];
    }
    echo "<script>window.location='urunlerim.php';</script>";
    exit;
}
include 'header.php';
?>
<div class="modern-layout-flex">
  <aside class="modern-sidebar">
    <nav class="modern-sidebar-nav">
      <ul>
        <li><a href="urunler.php">Tüm Ürünleri Gör</a></li>
        <li><a href="kategoriler.php">Kategoriler</a></li>
        <li><a href="magazalarim.php">Mağazalarım</a></li>
        <li><a href="magaza_ac.php">Mağaza Aç</a></li>
        <li><a href="urun_ekle.php">Ürün Ekle</a></li>
        <li><a href="urunlerim.php">Ürünlerim</a></li>
        <li><a href="magaza_siparisleri.php">Mağaza Siparişleri</a></li>
      </ul>
    </nav>
  </aside>
  <main class="modern-main-content">
    <div class="modern-box glassmorphism shadow-lg p-4 mb-4 mt-4 animate-fade-in">
      <div class="modern-flex-between mb-3">
        <h2 class="modern-title mb-0">Ürünlerim</h2>
        <div>
          <a href="urun_ekle.php" class="btn-main btn-sm">Yeni Ürün Ekle</a>
          <a href="urun_gorsel_ekle.php" class="btn-secondary btn-sm">Görsel Ekle</a>
        </div>
      </div>
      <?php if ($alert): ?>
        <div class="modern-alert <?php echo $alert['type']; ?> mb-3"><?php echo $alert['msg']; ?></div>
      <?php endif; ?>
      <?php if (count($urunler) === 0): ?>
        <div class="modern-alert info">Henüz ürününüz yok.</div>
      <?php else: ?>
        <div class="modern-table-responsive">
          <table class="modern-table">
            <thead>
              <tr>
                <th>Ürün Adı</th>
                <th>Mağaza</th>
                <th>Oluşturulma</th>
                <th>İşlemler</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($urunler as $urun): ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($urun['urun_adi']); ?></strong></td>
                <td><?php echo htmlspecialchars($urun['magaza_adi']); ?></td>
                <td><?php echo $urun['olusturma_tarihi']; ?></td>
                <td>
                  <a href="urun_guncelle.php?id=<?php echo $urun['id']; ?>" class="btn-secondary btn-sm">Düzenle</a>
                  <a href="urunlerim.php?sil=<?php echo $urun['id']; ?>" class="btn-main btn-sm" onclick="return confirm('Silmek istediğinize emin misiniz?')">Sil</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </main>
</div>
<?php include 'footer.php'; ?>
