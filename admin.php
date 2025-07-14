
<?php
session_start();
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db.php';
if ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'yonetici') {
    header('Location: index.php');
    exit;
}
// Kullanıcıları çek
$kullanicilar = $pdo->query("SELECT * FROM kullanicilar ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
// Ürünleri çek
$urunler = $pdo->query("SELECT * FROM urunler ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
// Siparişleri çek
$siparisler = $pdo->query("SELECT * FROM siparisler ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
include 'header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-11">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Admin Paneli</h2>
            <a href="index.php" class="btn btn-secondary btn-sm">Anasayfa</a>
        </div>
        <div class="mb-4">
            <h4>Kullanıcılar</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle bg-white">
                    <thead class="table-light">
                        <tr><th>ID</th><th>Ad</th><th>E-posta</th><th>Rol</th><th>Oluşturulma</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kullanicilar as $k): ?>
                        <tr>
                            <td><?php echo $k['id']; ?></td>
                            <td><?php echo htmlspecialchars($k['ad']); ?></td>
                            <td><?php echo htmlspecialchars($k['eposta']); ?></td>
                            <td><?php echo htmlspecialchars($k['rol']); ?></td>
                            <td><?php echo $k['olusturma_tarihi']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="margin-top:18px;">
                <a href="kategori_ekle.php" class="btn btn-success">Yeni Kategori Ekle</a>
            </div>
        </div>
        <div class="mb-4">
            <h4>Ürünler</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle bg-white">
                    <thead class="table-light">
                        <tr><th>ID</th><th>Ad</th><th>Fiyat</th><th>Stok</th><th>Durum</th><th>Oluşturulma</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($urunler as $u): ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td><?php echo htmlspecialchars($u['urun_adi']); ?></td>
                            <td><?php echo number_format($u['fiyat'],2); ?> TL</td>
                            <td><?php echo $u['stok']; ?></td>
                            <td><?php echo htmlspecialchars($u['durum']); ?></td>
                            <td><?php echo $u['olusturma_tarihi']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mb-4">
            <h4>Siparişler</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle bg-white">
                    <thead class="table-light">
                        <tr><th>ID</th><th>Kullanıcı ID</th><th>Toplam Tutar</th><th>Durum</th><th>Oluşturulma</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($siparisler as $s): ?>
                        <tr>
                            <td><?php echo $s['id']; ?></td>
                            <td><?php echo $s['kullanici_id']; ?></td>
                            <td><?php echo number_format($s['toplam_tutar'],2); ?> TL</td>
                            <td><?php echo htmlspecialchars($s['durum']); ?></td>
                            <td><?php echo $s['olusturma_tarihi']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
