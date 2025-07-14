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
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kullanici_id = $_SESSION['kullanici_id'];
    $magaza_adi = trim($_POST['magaza_adi']);
    $magaza_aciklamasi = trim($_POST['magaza_aciklamasi']);
    $magaza_logo = trim($_POST['magaza_logo']);
    $olusturma_tarihi = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("INSERT INTO magazalar (kullanici_id, magaza_adi, magaza_aciklamasi, magaza_logo, olusturma_tarihi) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$kullanici_id, $magaza_adi, $magaza_aciklamasi, $magaza_logo, $olusturma_tarihi])) {
        echo "<script>alert('Mağaza başarıyla açıldı!'); window.location='magazalarim.php';</script>";
    } else {
        echo "<script>alert('Mağaza açılırken hata oluştu!'); window.location='magaza_ac.php';</script>";
    }
}
include 'header.php';
?>
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm mt-5">
                <div class="card-body">
                    <h3 class="card-title mb-4 text-center">Mağaza Aç</h3>
                    <form action="magaza_ac.php" method="post">
                        <div class="mb-3">
                            <label class="form-label">Mağaza Adı</label>
                            <input type="text" name="magaza_adi" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mağaza Açıklaması</label>
                            <textarea name="magaza_aciklamasi" class="form-control" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mağaza Logo (URL)</label>
                            <input type="text" name="magaza_logo" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-success w-100">Mağaza Aç</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <a href="index.php" class="btn btn-secondary mt-3">Anasayfa</a>
<?php include 'footer.php'; ?>
