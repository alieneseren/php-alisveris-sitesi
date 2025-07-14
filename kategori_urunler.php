<?php
require_once 'db.php';
if (!isset($_GET['kategori_id']) || !is_numeric($_GET['kategori_id'])) {
    echo '<div class="modern-alert warning mb-4">Kategori seçilmedi.</div>';
    exit;
}
$kategori_id = intval($_GET['kategori_id']);


$stmt = $pdo->prepare("SELECT u.*, m.magaza_adi, k.kategori_adi, (SELECT gorsel_url FROM urun_gorselleri WHERE urun_id = u.id LIMIT 1) as gorsel_url FROM urunler u JOIN urun_kategorileri uk ON u.id = uk.urun_id JOIN magazalar m ON u.magaza_id = m.id JOIN kategoriler k ON uk.kategori_id = k.id WHERE uk.kategori_id = ? AND u.durum = 'aktif' ORDER BY u.olusturma_tarihi DESC");
$stmt->execute([$kategori_id]);
$urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($urunler) === 0) {
    echo '<div class="modern-alert warning mb-4">Bu kategoride ürün yok.</div>';
    exit;
}
foreach ($urunler as $urun): ?>
    <div class="modern-featured-card" style="display:flex;align-items:flex-start;gap:1.2rem;background:#f9f9fb;padding:1.5rem 2rem;margin-bottom:2rem;border-radius:24px;box-shadow:0 4px 16px rgba(67,97,238,0.08);">
      <img src="<?php echo $urun['gorsel_url'] ? htmlspecialchars($urun['gorsel_url']) : '/default-product.png'; ?>" alt="<?php echo htmlspecialchars($urun['urun_adi']); ?>" style="width:80px;height:80px;object-fit:cover;border-radius:16px;margin-right:1.2rem;background:#fff;box-shadow:0 2px 8px rgba(67,97,238,0.06);">
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
          <span><b>Satıcı:</b> <?php echo htmlspecialchars($urun['satici_adi'] ?? ''); ?></span><br>
          <span><small class="modern-muted">Oluşturulma: <?php echo $urun['olusturma_tarihi']; ?></small></span>
        </div>
        <a href="urun_detay.php?id=<?php echo $urun['id']; ?>" class="btn-main" style="margin-top:1rem;">İncele</a>
      </div>
    </div>
<?php endforeach; ?>
