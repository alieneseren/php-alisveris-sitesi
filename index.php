<?php
session_start();
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit;
}
include 'header.php';
?>
<div class="modern-layout-flex">
  <aside class="modern-sidebar">
    <nav class="modern-sidebar-nav">
      <ul>
        <li><a href="urunler.php">Tüm Ürünleri Gör</a></li>
        <!-- Kategori menüsü sidebar'dan kaldırıldı -->
        <?php if ($_SESSION['rol'] === 'musteri' || $_SESSION['rol'] === 'yonetici' || $_SESSION['rol'] === 'admin'): ?>
          <li><a href="siparislerim.php">Siparişlerim</a></li>
        <?php endif; ?>
        <?php if ($_SESSION['rol'] === 'satici' || $_SESSION['rol'] === 'yonetici' || $_SESSION['rol'] === 'admin'): ?>
          <li><a href="magazalarim.php">Mağazalarım</a></li>
          <li><a href="magaza_ac.php">Mağaza Aç</a></li>
          <li><a href="urun_ekle.php">Ürün Ekle</a></li>
          <li><a href="urunlerim.php">Ürünlerim</a></li>
          <li><a href="magaza_siparisleri.php">Mağaza Siparişleri</a></li>
        <?php endif; ?>
        <?php if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'yonetici'): ?>
          <li><a href="kullanicilar.php">Kullanıcılar</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </aside>
  <main class="modern-main-content">
    <div class="modern-box glassmorphism shadow-lg p-4 mb-4 mt-4 animate-fade-in">
      <h2 class="modern-title mb-2">Hoşgeldiniz, <?php echo htmlspecialchars($_SESSION['ad']); ?>!</h2>
      <div class="modern-role mb-3">
        <span class="modern-badge">
          Rolünüz: <?php 
            if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'yonetici') {
              echo 'Yönetici';
            } elseif ($_SESSION['rol'] === 'satici') {
              echo 'Satıcı';
            } else {
              echo 'Müşteri';
            }
          ?>
        </span>
      </div>
      <div class="modern-divider"></div>
      <h3 class="modern-subtitle mt-3 mb-3">Öne Çıkan Ürünler</h3>
      <?php
      require_once 'db.php';
      $stmt = $pdo->query("SELECT urunler.*, magazalar.magaza_adi, kullanicilar.ad AS satici_adi FROM urunler JOIN magazalar ON urunler.magaza_id = magazalar.id JOIN kullanicilar ON magazalar.kullanici_id = kullanicilar.id WHERE urunler.durum = 'aktif' ORDER BY urunler.olusturma_tarihi DESC");
      $urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);
      // Ürün görsellerini çekmek için ürün id'lerini topla
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
      ?>
      <div class="modern-featured-products">
        <div id="urunler-listesi">
        <?php if (count($urunler) === 0): ?>
          <div class="modern-alert warning mb-4">Hiç ürün bulunamadı.</div>
        <?php else: ?>
          <div class="modern-grid" style="display: flex; flex-wrap: wrap; gap: 2rem;">
          <?php foreach ($urunler as $i => $urun): ?>
            <div class="modern-featured-card" style="flex: 1 1 calc(50% - 2rem); max-width: calc(50% - 2rem); min-width: 320px; background: #f9f9fb; padding: 1.5rem 2rem; margin-bottom: 2rem; border-radius: 24px; box-shadow: 0 4px 16px rgba(67,97,238,0.08); display: flex; align-items: flex-start; gap: 1.2rem;">
              <img src="<?php echo isset($urun_gorselleri[$urun['id']]) ? htmlspecialchars($urun_gorselleri[$urun['id']]) : '/default-product.png'; ?>" alt="<?php echo htmlspecialchars($urun['urun_adi']); ?>" style="width:80px;height:80px;object-fit:cover;border-radius:16px;margin-right:1.2rem;background:#fff;box-shadow:0 2px 8px rgba(67,97,238,0.06);">
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
                <a href="urun_detay.php?id=<?php echo $urun['id']; ?>" class="btn-main" style="margin-top:1rem;">İncele</a>
              </div>
            </div>
          <?php endforeach; ?>
          </div>
        <?php endif; ?>
        </div>
      </div>
    </div>
  </main>
</div>
<?php include 'footer.php'; ?>
