

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
$stmt = $pdo->prepare("SELECT s.*, su.adet, su.birim_fiyat, u.urun_adi, m.magaza_adi, s.kullanici_id as musteri_id FROM siparisler s JOIN siparis_urunleri su ON s.id = su.siparis_id JOIN urunler u ON su.urun_id = u.id JOIN magazalar m ON u.magaza_id = m.id WHERE m.kullanici_id = ? ORDER BY s.olusturma_tarihi DESC");
$stmt->execute([$kullanici_id]);
$siparisler = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sipariş durumu güncelleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['siparis_id'], $_POST['durum'])) {
    $siparis_id = intval($_POST['siparis_id']);
    $durum = $_POST['durum'];
    $stmt = $pdo->prepare("UPDATE siparisler SET durum = ? WHERE id = ?");
    $stmt->execute([$durum, $siparis_id]);
    echo "<script>window.location='magaza_siparisleri.php';</script>";
    exit;
}
include 'header.php';
?>
<div class="modern-container">
    <div class="modern-content">
        <div class="modern-flex-between mb-3">
            <h2 class="modern-title">Mağaza Siparişleri</h2>
            <a href="index.php" class="modern-btn-secondary">Anasayfa</a>
        </div>
        <?php if (count($siparisler) === 0): ?>
            <div class="modern-alert-info">Henüz sipariş yok.</div>
        <?php else: ?>
            <div class="modern-table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Sipariş No</th>
                            <th>Ürün</th>
                            <th>Mağaza</th>
                            <th>Müşteri ID</th>
                            <th>Adet</th>
                            <th>Birim Fiyat</th>
                            <th>Toplam Tutar</th>
                            <th>Durum</th>
                            <th>Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($siparisler as $siparis): ?>
                        <tr>
                            <td><?php echo $siparis['id']; ?></td>
                            <td><?php echo htmlspecialchars($siparis['urun_adi']); ?></td>
                            <td><?php echo htmlspecialchars($siparis['magaza_adi']); ?></td>
                            <td><?php echo $siparis['musteri_id']; ?></td>
                            <td><?php echo $siparis['adet']; ?></td>
                            <td><?php echo number_format($siparis['birim_fiyat'],2); ?> TL</td>
                            <td><?php echo number_format($siparis['toplam_tutar'],2); ?> TL</td>
                            <td>
                                <form action="magaza_siparisleri.php" method="post" class="modern-flex-row gap-2 mb-0">
                                    <input type="hidden" name="siparis_id" value="<?php echo $siparis['id']; ?>">
                                    <select name="durum" class="modern-select">
                                        <option value="yeni" <?php if($siparis['durum']==='yeni') echo 'selected'; ?>>yeni</option>
                                        <option value="hazırlanıyor" <?php if($siparis['durum']==='hazırlanıyor') echo 'selected'; ?>>hazırlanıyor</option>
                                        <option value="kargoda" <?php if($siparis['durum']==='kargoda') echo 'selected'; ?>>kargoda</option>
                                        <option value="tamamlandı" <?php if($siparis['durum']==='tamamlandı') echo 'selected'; ?>>tamamlandı</option>
                                        <option value="iptal" <?php if($siparis['durum']==='iptal') echo 'selected'; ?>>iptal</option>
                                    </select>
                                    <button type="submit" class="modern-btn-primary">Güncelle</button>
                                </form>
                            </td>
                            <td><?php echo $siparis['olusturma_tarihi']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include 'footer.php'; ?>
