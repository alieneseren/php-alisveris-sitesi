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
$stmt = $pdo->prepare("SELECT * FROM magazalar WHERE kullanici_id = ?");
$stmt->execute([$kullanici_id]);
$magazalar = $stmt->fetchAll(PDO::FETCH_ASSOC);
include 'header.php';
?>
    <h2 class="mb-4">Mağazalarım</h2>
    <a href="magaza_ac.php" class="btn btn-success mb-3">Yeni Mağaza Aç</a>
    <?php if (count($magazalar) === 0): ?>
        <div class="alert alert-info">Henüz mağazanız yok.</div>
    <?php else: ?>
        <div class="row">
        <?php foreach ($magazalar as $magaza): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($magaza['magaza_adi']); ?></h5>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($magaza['magaza_aciklamasi'])); ?></p>
                        <?php if ($magaza['magaza_logo']): ?>
                            <img src="<?php echo htmlspecialchars($magaza['magaza_logo']); ?>" alt="Logo" class="img-thumbnail mb-2" style="max-width:100px;max-height:100px;">
                        <?php endif; ?>
                        <div><small class="text-muted">Oluşturulma: <?php echo $magaza['olusturma_tarihi']; ?></small></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <a href="index.php" class="btn btn-secondary mt-3">Anasayfa</a>
<?php include 'footer.php'; ?>
