<?php
session_start();
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit;
}
$rol = $_SESSION['rol'] ?? '';
// DEBUG: Token'ı ekrana yazdır
// Teknik kontrol: Token, kullanıcı ve rol bilgisi detaylı göster
$kullanici_id = $_SESSION['kullanici_id'] ?? '';
$paythor_token = $_SESSION['paythor_token'] ?? '';
// Eğer satıcı ise ve session'da token yoksa veritabanından çek
if ($rol === 'satici' && empty($paythor_token) && !empty($kullanici_id)) {
    require_once 'db.php';
    $stmt = $pdo->prepare("SELECT paythor_token FROM kullanicilar WHERE id = ?");
    $stmt->execute([$kullanici_id]);
    $paythor_token = $stmt->fetchColumn() ?: '';
}
// E-posta session'da yoksa veritabanından çek ve session'a yaz
if (empty($_SESSION['eposta']) && !empty($_SESSION['kullanici_id'])) {
    require_once 'db.php';
    $stmt = $pdo->prepare("SELECT eposta FROM kullanicilar WHERE id = ?");
    $stmt->execute([$_SESSION['kullanici_id']]);
    $kullanici_eposta = $stmt->fetchColumn();
    if ($kullanici_eposta) {
        $_SESSION['eposta'] = $kullanici_eposta;
    } else {
        $kullanici_eposta = '';
    }
} else {
    $kullanici_eposta = $_SESSION['eposta'] ?? '';
}
// ...DEBUG kutusu kaldırıldı...
include 'header.php';
$sepet = isset($_SESSION['sepet']) ? $_SESSION['sepet'] : [];

// Müşteri ise: Sepetteki ilk ürünün satıcısının paythor_token'ı
$satici_token = null;
if ($rol === 'musteri' && !empty($sepet)) {
    require_once 'db.php';
    reset($sepet);
    $ilk_urun_id = key($sepet);
    $stmt = $pdo->prepare("SELECT u.magaza_id, m.kullanici_id FROM urunler u JOIN magazalar m ON u.magaza_id = m.id WHERE u.id = ?");
    $stmt->execute([$ilk_urun_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $satici_id = $row['kullanici_id'] ?? null;
    if ($satici_id) {
        // Token veritabanında varsa, doğrudan kullan
        $stmt2 = $pdo->prepare("SELECT paythor_token FROM kullanicilar WHERE id = ?");
        $stmt2->execute([$satici_id]);
        $satici_token = $stmt2->fetchColumn();
        // Eğer token boşsa, API'yi bağlaması istenir
    }
}

// Ödeme butonu ve yönlendirme (Sepetim kutusunun altında, sadece "Ödeme Yap" olarak)
?>
<div class="modern-box glassmorphism shadow-lg p-4 mb-4 mt-4 animate-fade-in">
  <h2 class="modern-title mb-4">Sepetim</h2>

  <?php if ($rol === 'satici'): ?>
    <div class="modern-alert warning mb-4" style="position:sticky;top:0;z-index:9999;text-align:center;">
      <?php if (empty($paythor_token)): ?>
        API bağlı değil! <a href="paythor_login.php" class="btn-main" style="margin-left:12px;">APİ'yi Bağla</a>
      <?php else: ?>
        API tokenınız mevcut. <a href="paythor_login.php" class="btn-main" style="margin-left:12px;background:#ff9800;">API Tokenı Yenile</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
  <?php if ($rol === 'musteri' && !empty($sepet) && empty($satici_token)): ?>
    <div class="modern-alert danger mb-4" style="position:sticky;top:0;z-index:9999;text-align:center;">Bu ürünün satıcısı ödeme API'sini bağlamamış. Satıcı API'si olmadan ödeme yapılamaz.</div>
  <?php endif; ?>
  <?php if (empty($sepet)): ?>
    <div class="modern-alert warning mb-4">Sepetinizde ürün yok.</div>
  <?php else: ?>
    <form id="odeme-bilgileri-form">
      <h3 class="mb-2" style="font-size:1.15rem;font-weight:600;">Müşteri Bilgileri</h3>
      <div style="display:flex;gap:12px;margin-bottom:18px;flex-wrap:wrap;">
        <input type="text" id="musteri-ad" name="ad" class="form-control" placeholder="Ad" required style="min-width:120px;" value="<?php echo htmlspecialchars($_SESSION['ad'] ?? ''); ?>">
        <input type="text" id="musteri-soyad" name="soyad" class="form-control" placeholder="Soyad" required style="min-width:120px;" value="<?php echo htmlspecialchars($_SESSION['soyad'] ?? ''); ?>">
        <input type="email" id="musteri-eposta" name="eposta" class="form-control" placeholder="E-posta" required style="min-width:180px;" value="<?php echo htmlspecialchars($kullanici_eposta); ?>">
        <input type="text" id="musteri-telefon" name="telefon" class="form-control" placeholder="Telefon" required style="min-width:120px;" value="<?php echo htmlspecialchars($_SESSION['telefon'] ?? ''); ?>">
      </div>
    </form>
    <table class="modern-table">
      <thead>
        <tr>
          <th>Ürün</th>
          <th>Fiyat</th>
          <th>Toplam</th>
          <th>Adet</th>
        </tr>
      </thead>
      <tbody>
        <?php
        require_once 'db.php';
        $toplam = 0;
        foreach ($sepet as $urun_id => $adet):
          $stmt = $pdo->prepare("SELECT urun_adi, fiyat FROM urunler WHERE id = ?");
          $stmt->execute([$urun_id]);
          $urun = $stmt->fetch(PDO::FETCH_ASSOC);
          if (!$urun) continue;
          $ara_toplam = $urun['fiyat'] * $adet;
          $toplam += $ara_toplam;
        ?>
        <tr data-urun-id="<?php echo $urun_id; ?>">
          <td><?php echo htmlspecialchars($urun['urun_adi']); ?></td>
          <td><?php echo number_format($urun['fiyat'],2); ?> TL</td>
          <td><?php echo number_format($ara_toplam,2); ?> TL</td>
          <td>
            <div class="adet-controls">
              <button class="adet-btn adet-azalt mini-btn" data-urun-id="<?php echo $urun_id; ?>" title="Azalt">
                <span class="azalt-ikon">-</span>
              </button>
              <span class="adet-value" style="display:inline-block;min-width:22px;text-align:center;font-weight:600;font-size:1rem;"><?php echo $adet; ?></span>
              <button class="adet-btn adet-arttir mini-btn" data-urun-id="<?php echo $urun_id; ?>" title="Arttır">+</button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4" style="text-align:right;"><b>Toplam:</b></td>
          <td><b><?php echo number_format($toplam,2); ?> TL</b></td>
        </tr>
      </tfoot>
    </table>
    <div style="text-align:right;margin-top:2.5rem;">
      <button id="devam-et-btn" class="btn-main" <?php echo ($rol === 'musteri' && empty($satici_token)) || ($rol === 'satici' && empty($paythor_token)) ? 'disabled' : ''; ?>>Devam Et</button>
      <button id="paythor-odeme-btn" class="btn-main" style="display:none;" disabled>Ödeme Yap</button>
    </div>
    <!-- Adres ve ek bilgi formu kutusu (başta gizli) -->
    <div id="adres-form-kutusu" class="modern-box glassmorphism shadow-lg p-4 mb-4 animate-fade-in" style="display:none;max-width:600px;margin:32px auto 0 auto;">
      <h3 class="modern-title mb-3">Teslimat ve Fatura Bilgileri</h3>
      <form id="adres-bilgi-formu">
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
          <input type="text" id="adres-adres" name="adres" class="form-control" placeholder="Adres" required style="min-width:220px;flex:1;">
          <input type="text" id="adres-il" name="il" class="form-control" placeholder="İl" required style="min-width:120px;">
          <input type="text" id="adres-ilce" name="ilce" class="form-control" placeholder="İlçe" required style="min-width:120px;">
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:12px;">
          <input type="text" id="adres-posta" name="posta_kodu" class="form-control" placeholder="Posta Kodu" style="min-width:100px;">
          <input type="text" id="adres-aciklama" name="aciklama" class="form-control" placeholder="Ek Açıklama (isteğe bağlı)" style="min-width:180px;flex:1;">
        </div>
        <div style="text-align:right;margin-top:18px;">
          <button type="button" id="adres-iptal-btn" class="btn-main" style="background:#bbb;color:#222;">İptal</button>
          <button type="submit" id="adres-onayla-btn" class="btn-main" style="margin-left:12px;">Onayla ve Ödeme Adımına Geç</button>
        </div>
      </form>
    </div>
  <?php endif; ?>
<!-- Kapanışlar ve fazlalıklar düzeltildi -->
<script>
// PHP değişkenlerini JS'ye aktar
var rol = <?php echo json_encode($rol); ?>;
var saticiToken = <?php echo json_encode($satici_token); ?>;
var paythorToken = <?php echo json_encode($paythor_token); ?>;

// Sepetten ürün silme (AJAX) ve adet arttır/azalt
document.addEventListener('DOMContentLoaded', function() {

  // Adet arttır/azalt
  document.querySelectorAll('.adet-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var urunId = this.getAttribute('data-urun-id');
      var row = this.closest('tr');
      var span = row.querySelector('.adet-value');
      var adet = parseInt(span.textContent);
      if (this.classList.contains('adet-arttir')) {
        adet++;
      } else if (this.classList.contains('adet-azalt')) {
        adet--;
      }
      if (adet < 1) {
        // 0 olursa ürünü sepetten kaldır
        fetch('sepet_sil.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'urun_id=' + encodeURIComponent(urunId)
        })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            var row = document.querySelector('tr[data-urun-id="' + urunId + '"]');
            if (row) row.remove();
            if (document.querySelectorAll('.adet-btn').length === 0) {
              location.reload();
            }
          } else {
            alert(data.message || 'Silme işlemi başarısız.');
          }
        });
        return;
      }
      // Adet güncelleme (adet >= 1)
      fetch('sepet_adet.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'urun_id=' + encodeURIComponent(urunId) + '&adet=' + adet
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          span.textContent = data.adet;
          // Ara toplam ve genel toplamı güncelle
          row.querySelector('td:nth-child(3)').textContent = data.ara_toplam + ' TL';
          document.querySelector('tfoot td:last-child b').textContent = data.toplam + ' TL';
        } else {
          alert(data.message || 'Adet güncellenemedi.');
        }
      });
    });
  });



// Adres/bilgi adımı ve ödeme adımı yönetimi
var devamEtBtn = document.getElementById('devam-et-btn');
var odemeBtn = document.getElementById('paythor-odeme-btn');
var adresFormKutusu = document.getElementById('adres-form-kutusu');
var adresFormu = document.getElementById('adres-bilgi-formu');
var adresIptalBtn = document.getElementById('adres-iptal-btn');
var adresOnaylaBtn = document.getElementById('adres-onayla-btn');
var adresBilgileri = null;

if (devamEtBtn) {
  devamEtBtn.addEventListener('click', function() {
    devamEtBtn.style.display = 'none';
    if (adresFormKutusu) adresFormKutusu.style.display = 'block';
  });
}
if (adresIptalBtn) {
  adresIptalBtn.addEventListener('click', function() {
    if (adresFormKutusu) adresFormKutusu.style.display = 'none';
    if (devamEtBtn) devamEtBtn.style.display = '';
    if (odemeBtn) {
      odemeBtn.style.display = 'none';
      odemeBtn.disabled = true;
    }
  });
}
if (adresFormu) {
  adresFormu.addEventListener('submit', function(e) {
    e.preventDefault();
    // Adres bilgilerini topla
    adresBilgileri = {
      adres: document.getElementById('adres-adres').value.trim(),
      il: document.getElementById('adres-il').value.trim(),
      ilce: document.getElementById('adres-ilce').value.trim(),
      posta_kodu: document.getElementById('adres-posta').value.trim(),
      aciklama: document.getElementById('adres-aciklama').value.trim()
    };
    if (adresFormKutusu) adresFormKutusu.style.display = 'none';
    if (odemeBtn) {
      odemeBtn.style.display = '';
      odemeBtn.disabled = false;
    }
  });
}

if (odemeBtn) {
  odemeBtn.addEventListener('click', function() {
    odemeBtn.disabled = true;
    odemeBtn.textContent = 'Yönlendiriliyor...';
    // Müşteri bilgilerini ve adres bilgilerini topla
    var musteriAd = document.getElementById('musteri-ad').value.trim();
    var musteriSoyad = document.getElementById('musteri-soyad').value.trim();
    var musteriEposta = document.getElementById('musteri-eposta').value.trim();
    var musteriTelefon = document.getElementById('musteri-telefon').value.trim();
    var payload = {
      ad: musteriAd,
      soyad: musteriSoyad,
      eposta: musteriEposta,
      telefon: musteriTelefon,
      adres_bilgi: adresBilgileri
    };
    fetch('odeme_baslat.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
      if (data.success && data.payment_link) {
        window.open(data.payment_link, '_blank');
      } else {
        var hata = data.message || 'Ödeme linki alınamadı.';
        if (data.http_code) {
          hata += ' (HTTP: ' + data.http_code + ')';
        }
        if (data.api_response) {
          hata += '\nAPI: ' + (typeof data.api_response === 'object' ? JSON.stringify(data.api_response) : data.api_response);
        }
        var alertBox = document.createElement('div');
        alertBox.className = 'modern-alert danger mb-3';
        alertBox.style = 'margin-top:18px;max-width:600px;margin-left:auto;margin-right:auto;text-align:center;';
        alertBox.innerText = hata;
        var oldAlert = document.getElementById('odeme-hata-box');
        if (oldAlert) oldAlert.remove();
        alertBox.id = 'odeme-hata-box';
        var container = document.querySelector('.modern-box');
        if (container) container.insertBefore(alertBox, container.firstChild);
      }
      odemeBtn.disabled = false;
      odemeBtn.textContent = 'Ödeme Yap';
    })
    .catch(() => {
      var hata = 'Bağlantı hatası.';
      var alertBox = document.createElement('div');
      alertBox.className = 'modern-alert danger mb-3';
      alertBox.style = 'margin-top:18px;max-width:600px;margin-left:auto;margin-right:auto;text-align:center;';
      alertBox.innerText = hata;
      var oldAlert = document.getElementById('odeme-hata-box');
      if (oldAlert) oldAlert.remove();
      alertBox.id = 'odeme-hata-box';
      var container = document.querySelector('.modern-box');
      if (container) container.insertBefore(alertBox, container.firstChild);
      odemeBtn.disabled = false;
      odemeBtn.textContent = 'Ödeme Yap';
    });
  });
}

  // - butonunda çöp kutusu hover
  document.querySelectorAll('.adet-azalt').forEach(function(btn) {
    btn.addEventListener('mouseenter', function() {
      var row = this.closest('tr');
      var span = row.querySelector('.adet-value');
      var adet = parseInt(span.textContent);
      if (adet === 1) {
        this.classList.add('delete-hover');
        this.querySelector('.azalt-ikon').innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 6V4C7 2.89543 7.89543 2 9 2H15C16.1046 2 17 2.89543 17 4V6M4 6H20M19 6V20C19 21.1046 18.1046 22 17 22H7C5.89543 22 5 21.1046 5 20V6H19Z" stroke="#fff" stroke-width="2"/><path d="M10 11V17" stroke="#fff" stroke-width="2" stroke-linecap="round"/><path d="M14 11V17" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>';
      }
    });
    btn.addEventListener('mouseleave', function() {
      var row = this.closest('tr');
      var span = row.querySelector('.adet-value');
      var adet = parseInt(span.textContent);
      if (adet === 1) {
        this.classList.remove('delete-hover');
        this.querySelector('.azalt-ikon').textContent = '-';
      }
    });
  });
});
</script>
<style>
.mini-btn {
  font-size: 0.92rem;
  padding: 0.13rem 0.38rem;
  border-radius: 16px;
  margin: 0 1px;
  background: #eee;
  color: #333;
  border: none;
  box-shadow: 0 1px 4px rgba(67,97,238,0.04);
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
}
.mini-btn:hover {
  background: #e0e0e0;
}
.mini-btn.delete-hover {
  background: #ff3b3b !important;
  color: #fff !important;
}
.adet-controls {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 2px;
}
</style>
<?php include 'footer.php'; ?>
