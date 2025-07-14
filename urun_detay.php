<?php
require_once 'db.php';
session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    include 'header.php';
    echo '<div class="modern-alert danger">Geçersiz ürün!</div>';
    include 'footer.php';
    exit;
}

$urun_id = intval($_GET['id']);

// Ürün detay sorgusu
$stmt = $pdo->prepare("SELECT urunler.*, magazalar.magaza_adi, kullanicilar.ad AS satici_adi FROM urunler JOIN magazalar ON urunler.magaza_id = magazalar.id JOIN kullanicilar ON magazalar.kullanici_id = kullanicilar.id WHERE urunler.id = ?");
$stmt->execute([$urun_id]);
$urun = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$urun) {
    include 'header.php';
    echo '<div class="modern-alert danger">Ürün bulunamadı!</div>';
    include 'footer.php';
    exit;
}

// Ürün görselleri
$stmt = $pdo->prepare("SELECT * FROM urun_gorselleri WHERE urun_id = ?");
$stmt->execute([$urun_id]);
$gorseller = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($gorseller)) {
    $gorseller = [['gorsel_url' => '/default-product.png']];
}

// Yorumlar
$stmt = $pdo->prepare("SELECT uy.ad, y.puan, y.yorum, y.olusturma_tarihi FROM urun_yorumlari y JOIN kullanicilar uy ON y.kullanici_id = uy.id WHERE y.urun_id = ? ORDER BY y.olusturma_tarihi DESC");
$stmt->execute([$urun_id]);
$yorumlar = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>
<link rel="stylesheet" href="style.css?v=2">
<style>
.product-detail-flex {
  display: flex;
  flex-wrap: wrap;
  gap: 2.5rem;
  margin-top: 2.5rem;
  justify-content: center;
}
.product-gallery-box {
  background: #fff;
  border-radius: 24px;
  box-shadow: 0 8px 32px rgba(67,97,238,0.10);
  padding: 2rem 2rem 1rem 2rem;
  min-width: 320px;
  max-width: 420px;
  flex: 1 1 340px;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.product-gallery-box .main-image img {
  width: 100%;
  max-width: 320px;
  max-height: 320px;
  border-radius: 18px;
  box-shadow: 0 4px 16px rgba(67,97,238,0.08);
  background: #f8f9fa;
}
.thumbnail-list {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
}
.thumbnail {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 10px;
  border: 2px solid #eee;
  cursor: pointer;
  transition: border 0.2s;
}
.thumbnail:hover {
  border: 2px solid var(--primary);
}
.product-info-box {
  background: #fff;
  border-radius: 24px;
  box-shadow: 0 8px 32px rgba(67,97,238,0.10);
  padding: 2rem 2.5rem;
  min-width: 320px;
  max-width: 520px;
  flex: 1 1 340px;
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
}
.product-title {
  font-size: 2rem;
  font-weight: 800;
  color: var(--primary);
  margin-bottom: 0.5rem;
}
.product-meta {
  display: flex;
  gap: 1.5rem;
  font-size: 1.1rem;
  color: var(--dark);
}
.product-price {
  font-size: 2.2rem;
  font-weight: 900;
  color: var(--accent);
  margin-bottom: 0.5rem;
}
.product-stock {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}
.product-variants, .product-campaigns, .product-extra-services {
  margin-bottom: 0.5rem;
}
.product-campaigns .campaign-box {
  background: var(--success);
  color: #fff;
  border-radius: 10px;
  padding: 0.5rem 1rem;
  display: inline-block;
  margin-right: 0.5rem;
  margin-bottom: 0.3rem;
  font-size: 1rem;
  font-weight: 600;
}
.product-actions {
  margin: 1rem 0 0.5rem 0;
}
.btn-main {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: #fff;
  font-weight: 700;
  font-size: 1.2rem;
  border: none;
  border-radius: 50px;
  padding: 0.8rem 2.5rem;
  box-shadow: 0 4px 16px rgba(67,97,238,0.10);
  cursor: pointer;
  transition: background 0.2s, transform 0.2s;
}
.btn-main:hover {
  background: linear-gradient(135deg, var(--secondary), var(--primary));
  transform: translateY(-2px) scale(1.03);
}
.btn-main.disabled {
  background: #eee;
  color: #aaa;
  cursor: not-allowed;
}
.product-extra-services label {
  display: inline-block;
  margin-right: 1.2rem;
  color: var(--primary-dark);
  font-size: 1rem;
}
.product-created-date {
  color: #888;
  font-size: 0.95rem;
}
.product-description {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 4px 16px rgba(67,97,238,0.08);
  padding: 1.5rem 2rem;
  margin: 2rem 0 1.5rem 0;
}
.product-other-sellers {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 4px 16px rgba(67,97,238,0.08);
  padding: 1.5rem 2rem;
  margin-bottom: 2rem;
}
.other-seller-box {
  display: flex;
  align-items: center;
  gap: 1.2rem;
  margin-bottom: 0.7rem;
  padding-bottom: 0.7rem;
  border-bottom: 1px solid #eee;
}
.other-seller-box:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}
.seller-name {
  font-weight: 700;
  color: var(--primary-dark);
}
.seller-rating {
  background: var(--success);
  color: #fff;
  border-radius: 8px;
  padding: 0.2rem 0.7rem;
  font-size: 1rem;
  margin-left: 0.5rem;
}
.seller-price {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--accent);
}
.btn-secondary {
  background: var(--primary-dark);
  color: #fff;
  border-radius: 50px;
  padding: 0.5rem 1.5rem;
  border: none;
  font-weight: 600;
  margin-left: auto;
  transition: background 0.2s;
}
.btn-secondary:hover {
  background: var(--accent);
}
.product-comments {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 4px 16px rgba(67,97,238,0.08);
  padding: 1.5rem 2rem;
  margin-bottom: 2rem;
}
.comment-list {
  list-style: none;
  padding: 0;
  margin: 0 0 1.5rem 0;
}
.comment-item {
  border-bottom: 1px solid #eee;
  padding: 1rem 0;
}
.comment-item:last-child {
  border-bottom: none;
}
.comment-header {
  display: flex;
  align-items: center;
  gap: 1.2rem;
  margin-bottom: 0.3rem;
}
.comment-user {
  font-weight: 700;
  color: var(--primary-dark);
}
.comment-rating {
  background: var(--success);
  color: #fff;
  border-radius: 8px;
  padding: 0.2rem 0.7rem;
  font-size: 1rem;
}
.comment-date {
  color: #888;
  font-size: 0.95rem;
}
.comment-body {
  color: #222;
  font-size: 1.05rem;
  margin-left: 0.2rem;
}
.comment-form-box {
  margin-top: 1.5rem;
  background: #f8f9fa;
  border-radius: 12px;
  padding: 1.2rem 1.5rem;
}
@media (max-width: 900px) {
  .product-detail-flex {
    flex-direction: column;
    gap: 1.5rem;
    align-items: stretch;
  }
  .product-gallery-box, .product-info_box {
    max-width: 100%;
    min-width: 0;
    padding: 1.2rem 1rem;
  }
}
</style>
<div class="product-detail-flex">
  <div class="product-gallery-box">
    <div class="main-image">
      <img src="<?php echo htmlspecialchars($gorseller[0]['gorsel_url']); ?>" alt="Ürün görseli">
    </div>
    <?php if (count($gorseller) > 1): ?>
    <div class="thumbnail-list">
      <?php foreach ($gorseller as $gorsel): ?>
        <img src="<?php echo htmlspecialchars($gorsel['gorsel_url']); ?>" alt="Görsel" class="thumbnail">
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
  <div class="product-info-box">
    <h1 class="product-title"><?php echo htmlspecialchars($urun['urun_adi']); ?></h1>
    <div class="product-meta">
      <span class="product-seller">Satıcı: <b><?php echo htmlspecialchars($urun['satici_adi']); ?></b></span>
      <span class="product-store">Mağaza: <b><?php echo htmlspecialchars($urun['magaza_adi']); ?></b></span>
    </div>
    <div class="product-price"><?php echo number_format($urun['fiyat'],2); ?> TL</div>
    <div class="product-stock">
      <?php if ($urun['stok'] > 0): ?>
        <span class="in-stock">Stokta: <?php echo $urun['stok']; ?></span>
      <?php else: ?>
        <span class="out-of-stock">Stokta yok</span>
      <?php endif; ?>
    </div>
    <div class="product-variants">
      <span class="variant-label">Renk:</span>
      <span class="variant-value">Gri</span>
    </div>
    <div class="product-campaigns">
      <div class="campaign-box">Seçili Küçük Ev Aletlerinde Sepette %5 Net İndirim</div>
      <div class="campaign-box">300 TL üzeri kargo bedava</div>
    </div>
    <div class="product-actions">
      <?php if (isset($_SESSION['kullanici_id']) && $urun['stok'] > 0): ?>
        <button class="btn-main add-to-cart-btn" data-urun-id="<?php echo $urun_id; ?>">Sepete Ekle</button>
      <?php else: ?>
        <button class="btn-main disabled" disabled>Sepete Ekle</button>
      <?php endif; ?>
    </div>
    <div id="cart-notification" class="modern-alert success" style="display:none;position:fixed;top:32px;left:50%;transform:translateX(-50%);z-index:9999;min-width:320px;text-align:center;">Ürün sepete eklendi!</div>
    <div class="product-extra-services">
      <label><input type="checkbox" disabled> Kazaen Zarar Sigortası (64,00 TL)</label>
      <label><input type="checkbox" disabled> +1 Yıl Ek Garanti (102,00 TL)</label>
    </div>
    <div class="product-created-date">
      <small>Oluşturulma: <?php echo $urun['olusturma_tarihi']; ?></small>
    </div>
  </div>
</div>
<div class="product-description">
  <h2>Açıklama</h2>
  <p><?php echo nl2br(htmlspecialchars($urun['urun_aciklamasi'])); ?></p>
// Diğer satıcılar bölümü kaldırıldı veya devre dışı bırakıldı. Gereksiz PHP endif ve bloklar temizlendi.
<div class="product-comments">
  <h2>Yorumlar</h2>
  <?php if (!$yorumlar): ?>
    <div class="modern-alert warning">Henüz yorum yok.</div>
  <?php else: ?>
    <ul class="comment-list">
    <?php foreach ($yorumlar as $yorum): ?>
      <li class="comment-item">
        <div class="comment-header">
          <span class="comment-user"><?php echo htmlspecialchars($yorum['ad']); ?></span>
          <span class="comment-rating">Puan: <?php echo $yorum['puan']; ?></span>
          <span class="comment-date"><?php echo $yorum['olusturma_tarihi']; ?></span>
        </div>
        <div class="comment-body"><?php echo nl2br(htmlspecialchars($yorum['yorum'])); ?></div>
      </li>
    <?php endforeach; ?>
    </ul>
  <?php endif; ?>
  <?php if (isset($_SESSION['kullanici_id'])): ?>
    <div class="comment-form-box">
      <h3>Yorum Ekle</h3>
      <form action="yorum_ekle.php" method="post">
        <input type="hidden" name="urun_id" value="<?php echo $urun_id; ?>">
        <label>Puan:
          <select name="puan" required>
            <option value="5">5</option>
            <option value="4">4</option>
            <option value="3">3</option>
            <option value="2">2</option>
            <option value="1">1</option>
          </select>
        </label>
        <label>Yorum:
          <textarea name="yorum" required></textarea>
        </label>
        <button type="submit" class="btn-main">Gönder</button>
      </form>
    </div>
  <?php endif; ?>
</div>
<div class="product-back" style="text-align:center;margin:2rem 0;">
  <a href="urunler.php" class="btn btn-outline">&larr; Ürünlere Dön</a>
  <a href="index.php" class="btn btn-outline">Anasayfa</a>
</div>
<script>
// Sepete ilk eklemede bildirim göster, sonraki eklemelerde gösterme
let cartAddFirst = true;
document.addEventListener('DOMContentLoaded', function() {
  const addToCartBtn = document.querySelector('.add-to-cart-btn');
  if (addToCartBtn) {
    // Önce eski event'ı kaldır (olası duplicate için)
    addToCartBtn.onclick = null;
    let isProcessing = false;
    addToCartBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopImmediatePropagation();
      if (isProcessing) return;
      isProcessing = true;
      addToCartBtn.classList.add('loading');
      const urunId = this.getAttribute('data-urun-id');
      fetch('sepet_ekle.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'urun_id=' + encodeURIComponent(urunId) + '&adet=1'
      })
      .then(r => r.json())
      .then(data => {
        addToCartBtn.classList.remove('loading');
        isProcessing = false;
        if (data.success) {
          // Eğer "yeni_eklendi" varsa ve true ise sadece ilk kez ekleniyor demektir
          if (cartAddFirst && (typeof data.yeni_eklendi === 'undefined' || data.yeni_eklendi === true)) {
            const notif = document.getElementById('cart-notification');
            notif.style.display = 'block';
            notif.style.opacity = 1;
            setTimeout(() => {
              notif.style.transition = 'opacity 0.5s';
              notif.style.opacity = 0;
              setTimeout(() => notif.style.display = 'none', 500);
            }, 1800);
            cartAddFirst = false;
          }
          if (window.updateCartCount) window.updateCartCount(data.sepet_urun_sayisi);
        } else {
          alert(data.message || 'Sepete eklenemedi.');
        }
      })
      .catch(() => {
        addToCartBtn.classList.remove('loading');
        isProcessing = false;
      });
    });
  }
</script>
<?php include 'footer.php'; ?>
