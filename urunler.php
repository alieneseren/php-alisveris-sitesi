<?php
session_start();
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db.php';
// Ürünleri ve görsellerini çek
$urunler = [];
if (isset($_GET['kategori_id']) && is_numeric($_GET['kategori_id'])) {
  $kategori_id = intval($_GET['kategori_id']);
  $stmt = $pdo->prepare("SELECT u.*, m.magaza_adi, k.kategori_adi, (SELECT ad FROM kullanicilar WHERE id = m.kullanici_id) as satici_adi FROM urunler u JOIN urun_kategorileri uk ON u.id = uk.urun_id JOIN magazalar m ON u.magaza_id = m.id JOIN kategoriler k ON uk.kategori_id = k.id WHERE uk.kategori_id = ? AND u.durum = 'aktif' ORDER BY u.olusturma_tarihi DESC");
  $stmt->execute([$kategori_id]);
  $urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
  $stmt = $pdo->query("SELECT urunler.*, magazalar.magaza_adi, kullanicilar.ad AS satici_adi FROM urunler JOIN magazalar ON urunler.magaza_id = magazalar.id JOIN kullanicilar ON magazalar.kullanici_id = kullanicilar.id WHERE urunler.durum = 'aktif' ORDER BY urunler.olusturma_tarihi DESC");
  $urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$urun_gorselleri = [];
if (count($urunler) > 0) {
  $ids = array_column($urunler, 'id');
  $placeholders = implode(',', array_fill(0, count($ids), '?'));
  $stmtG = $pdo->prepare("SELECT urun_id, gorsel_url FROM urun_gorselleri WHERE urun_id IN ($placeholders) GROUP BY urun_id");
  $stmtG->execute($ids);
  foreach ($stmtG->fetchAll(PDO::FETCH_ASSOC) as $gorsel) {
    $urun_gorselleri[$gorsel['urun_id']] = $gorsel['gorsel_url'];
  }
}
include 'header.php';
?>
<div class="modern-layout-flex">
  <aside class="modern-sidebar">
    <nav class="modern-sidebar-nav">
      <ul>
        <li><a href="urunler.php">Tüm Ürünleri Gör</a></li>
        <!-- Kategori menüsü sidebar'dan kaldırıldı -->
        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] !== 'musteri'): ?>
          <li><a href="magazalarim.php">Mağazalarım</a></li>
          <li><a href="magaza_ac.php">Mağaza Aç</a></li>
          <li><a href="urun_ekle.php">Ürün Ekle</a></li>
          <li><a href="urunlerim.php">Ürünlerim</a></li>
          <li><a href="magaza_siparisleri.php">Mağaza Siparişleri</a></li>
        <?php endif; ?>
      </ul>
      <!-- Kategori menüsü sidebar'dan kaldırıldı -->
    </nav>
  </aside>
  <main class="modern-main-content">
    <div class="modern-box glassmorphism shadow-lg p-4 mb-4 mt-4 animate-fade-in">
      <h2 class="modern-title mb-4">Ürünler</h2>
      <?php if (count($urunler) === 0): ?>
        <div class="modern-alert warning mb-4">Hiç ürün bulunamadı.</div>
      <?php else: ?>
        <div class="modern-grid" style="display: flex; flex-wrap: wrap; gap: 2rem;">
        <?php foreach ($urunler as $urun): ?>
          <div class="modern-featured-card product-card" style="flex: 1 1 calc(50% - 2rem); max-width: calc(50% - 2rem); min-width: 320px; background: #f9f9fb; padding: 1.5rem 2rem; margin-bottom: 2rem; border-radius: 24px; box-shadow: 0 4px 16px rgba(67,97,238,0.08); display: flex; align-items: flex-start; gap: 1.2rem;">
            <img src="<?php echo isset($urun_gorselleri[$urun['id']]) ? htmlspecialchars($urun_gorselleri[$urun['id']]) : '/default-product.png'; ?>" alt="<?php echo htmlspecialchars($urun['urun_adi']); ?>" class="product-card-img" style="width:80px;height:80px;object-fit:cover;border-radius:16px;margin-right:1.2rem;background:#fff;box-shadow:0 2px 8px rgba(67,97,238,0.06);">
            <div style="flex:1;">
              <div class="modern-featured-title" style="font-size:1.25rem;font-weight:700;color:#3a2fd6;margin-bottom:0.3rem;">
                <?php echo htmlspecialchars($urun['urun_adi']); ?>
              </div>
              <div class="modern-featured-desc" style="margin-bottom:0.5rem;color:#222;">
                <?php echo nl2br(htmlspecialchars($urun['urun_aciklamasi'])); ?>
              </div>
              <div class="modern-featured-meta" style="font-size:1.05rem;color:#444;">
                <span><b>Fiyat:</b> <?php echo number_format($urun['fiyat'],2); ?> TL</span><br>
                <span><b>Stok:</b> <?php echo $urun['stok']; ?></span><br>
                <span><b>Mağaza:</b> <?php echo htmlspecialchars($urun['magaza_adi']); ?></span><br>
                <span><b>Satıcı:</b> <?php echo htmlspecialchars($urun['satici_adi']); ?></span><br>
                <span><small class="modern-muted">Oluşturulma: <?php echo $urun['olusturma_tarihi']; ?></small></span>
              </div>
              <div style="display:flex;gap:0.7rem;margin-top:1rem;align-items:center;">
                <a href="urun_detay.php?id=<?php echo $urun['id']; ?>" class="btn-main">İncele</a>
                <?php if ($urun['stok'] > 0): ?>
                  <button class="btn-main add-to-cart-btn" data-urun-id="<?php echo $urun['id']; ?>">Sepete Ekle</button>
                <?php else: ?>
                  <button class="btn-main disabled" disabled>Sepete Ekle</button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>
</div>
<?php include 'footer.php'; ?>
