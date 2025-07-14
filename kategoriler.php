<?php
session_start();
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db.php';
$stmt = $pdo->query("SELECT * FROM kategoriler ORDER BY kategori_adi ASC");
$kategoriler = $stmt->fetchAll(PDO::FETCH_ASSOC);
$urunler = [];
if (isset($_GET['kategori_id']) && is_numeric($_GET['kategori_id'])) {
    $kategori_id = intval($_GET['kategori_id']);
    $stmt = $pdo->prepare("SELECT u.*, m.magaza_adi FROM urunler u JOIN urun_kategorileri uk ON u.id = uk.urun_id JOIN magazalar m ON u.magaza_id = m.id WHERE uk.kategori_id = ? AND u.durum = 'aktif' ORDER BY u.olusturma_tarihi DESC");
    $stmt->execute([$kategori_id]);
    $urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
include 'header.php';
?>
    <h2 class="mb-4">Kategoriler</h2>
    <div class="row mb-4">
        <?php foreach ($kategoriler as $kategori): ?>
            <div class="col-md-3 col-sm-6 mb-2">
                <a href="kategoriler.php?kategori_id=<?php echo $kategori['id']; ?>" class="btn btn-outline-primary w-100">
                    <?php echo htmlspecialchars($kategori['kategori_adi']); ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if (isset($kategori_id)): ?>
        <h4 class="mb-3">"<?php echo htmlspecialchars($kategoriler[array_search($kategori_id, array_column($kategoriler, 'id'))]['kategori_adi']); ?>" Kategorisindeki Ürünler</h4>
        <?php if (count($urunler) === 0): ?>
            <div class="alert alert-warning">Bu kategoride ürün yok.</div>
        <?php else: ?>
            <div class="row">
            <?php foreach ($urunler as $urun): ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <a href="urun_detay.php?id=<?php echo $urun['id']; ?>">
                                <h5 class="card-title"><?php echo htmlspecialchars($urun['urun_adi']); ?></h5>
                            </a>
                            <ul class="list-unstyled mb-0">
                                <li><strong>Mağaza:</strong> <?php echo htmlspecialchars($urun['magaza_adi']); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <a href="index.php" class="btn btn-secondary mt-3">Anasayfa</a>
<?php include 'footer.php'; ?>
