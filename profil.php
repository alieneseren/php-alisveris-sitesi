<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit;
}
$kullanici_id = $_SESSION['kullanici_id'];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ad = trim($_POST['ad']);
    $eposta = trim($_POST['eposta']);
    $rol = $_SESSION['rol'];
    $stmt = $pdo->prepare("UPDATE kullanicilar SET ad = ?, eposta = ? WHERE id = ?");
    if ($stmt->execute([$ad, $eposta, $kullanici_id])) {
        $_SESSION['ad'] = $ad;
        echo "<script>alert('Profil güncellendi!'); window.location='profil.php';</script>";
    } else {
        echo "<script>alert('Güncelleme hatası!'); window.location='profil.php';</script>";
    }
}
$stmt = $pdo->prepare("SELECT * FROM kullanicilar WHERE id = ?");
$stmt->execute([$kullanici_id]);
$kullanici = $stmt->fetch(PDO::FETCH_ASSOC);
include 'header.php';
?>
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm mt-5">
                <div class="card-body">
                    <h3 class="card-title mb-4 text-center">Profilim</h3>
                    <form action="profil.php" method="post">
                        <div class="mb-3">
                            <label class="form-label">Ad Soyad</label>
                            <input type="text" name="ad" class="form-control" value="<?php echo htmlspecialchars($kullanici['ad']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">E-posta</label>
                            <input type="email" name="eposta" class="form-control" value="<?php echo htmlspecialchars($kullanici['eposta']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rol</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($kullanici['rol']); ?>" disabled>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Güncelle</button>
                    </form>
                    </form>
                    <form action="logout.php" method="post" style="margin-top:24px;">
                        <button type="submit" class="btn btn-danger w-100" style="font-weight:600;letter-spacing:0.5px;">Çıkış Yap</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php include 'footer.php'; ?>
