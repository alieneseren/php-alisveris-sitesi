<?php
session_start();
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit;
}
include 'header.php';
$sepet = isset($_SESSION['sepet']) ? $_SESSION['sepet'] : [];
?>
<div class="modern-box glassmorphism shadow-lg p-4 mb-4 mt-4 animate-fade-in">
  <h2 class="modern-title mb-4">Sepetim</h2>
  <?php if (empty($sepet)): ?>
    <div class="modern-alert warning mb-4">Sepetinizde ürün yok.</div>
  <?php else: ?>
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
  <?php endif; ?>
</div>
<script>
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
