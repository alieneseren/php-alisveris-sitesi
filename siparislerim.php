<?php
session_start();
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db.php';
$kullanici_id = $_SESSION['kullanici_id'];
$stmt = $pdo->prepare("SELECT s.*, su.adet, su.birim_fiyat, u.urun_adi FROM siparisler s JOIN siparis_urunleri su ON s.id = su.siparis_id JOIN urunler u ON su.urun_id = u.id WHERE s.kullanici_id = ? ORDER BY s.olusturma_tarihi DESC");
$stmt->execute([$kullanici_id]);
$siparisler = $stmt->fetchAll(PDO::FETCH_ASSOC);
include 'header.php';
?>
    <h2 class="mb-4">Siparişlerim</h2>
    <?php if (count($siparisler) === 0): ?>
        <div class="alert alert-info">Henüz siparişiniz yok.</div>
    <?php else: ?>
        <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
            <tr>
                <th>Sipariş No</th>
                <th>Ürün</th>
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
                <td><?php echo $siparis['adet']; ?></td>
                <td><?php echo number_format($siparis['birim_fiyat'],2); ?> TL</td>
                <td><?php echo number_format($siparis['toplam_tutar'],2); ?> TL</td>
                <td><?php echo htmlspecialchars($siparis['durum']); ?></td>
                <td><?php echo $siparis['olusturma_tarihi']; ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
    <a href="index.php" class="btn btn-secondary mt-3">Anasayfa</a>
<?php include 'footer.php'; ?>
