
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
// Kullanıcının ürünlerini çek
$kullanici_id = $_SESSION['kullanici_id'];
$stmt = $pdo->prepare("SELECT u.*, m.magaza_adi FROM urunler u JOIN magazalar m ON u.magaza_id = m.id WHERE m.kullanici_id = ? ORDER BY u.olusturma_tarihi DESC");
$stmt->execute([$kullanici_id]);
$urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Görsel ekleme işlemi
$alert = null;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $urun_id = intval($_POST['urun_id']);
    $ana_gorsel = isset($_POST['ana_gorsel']) ? 1 : 0;
    $gorsel_url = null;
    // Dosya yükleme öncelikli
    if (isset($_FILES['gorsel_dosya']) && $_FILES['gorsel_dosya']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['gorsel_dosya']['tmp_name'];
        $name = basename($_FILES['gorsel_dosya']['name']);
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed)) {
            $new_name = uniqid('urun_').'.'.$ext;
            $target = __DIR__ . '/gorseller/' . $new_name;
            if (move_uploaded_file($tmp_name, $target)) {
                $gorsel_url = 'gorseller/'.$new_name;
            }
        }
    } else if (!empty($_POST['gorsel_url'])) {
        $gorsel_url = trim($_POST['gorsel_url']);
    }
    if ($gorsel_url) {
        if ($ana_gorsel) {
            $stmt = $pdo->prepare("UPDATE urun_gorselleri SET ana_gorsel = 0 WHERE urun_id = ?");
            $stmt->execute([$urun_id]);
        }
        $stmt = $pdo->prepare("INSERT INTO urun_gorselleri (urun_id, gorsel_url, ana_gorsel) VALUES (?, ?, ?)");
        if ($stmt->execute([$urun_id, $gorsel_url, $ana_gorsel])) {
            $alert = ['type' => 'success', 'msg' => 'Görsel eklendi!'];
        } else {
            $alert = ['type' => 'danger', 'msg' => 'Görsel eklenirken hata!'];
        }
    } else {
        $alert = ['type' => 'danger', 'msg' => 'Geçerli bir görsel seçiniz!'];
    }
    echo "<script>window.location='urun_gorsel_ekle.php';</script>";
    exit;
}
include 'header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0">Ürün Görseli Ekle</h4>
            </div>
            <div class="card-body">
                <?php if ($alert): ?>
                    <div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show" role="alert">
                        <?php echo $alert['msg']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <form action="urun_gorsel_ekle.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Görsel Dosya Yükle</label>
                        <input type="file" name="gorsel_dosya" class="form-control" accept="image/*">
                        <div class="form-text">Yalnızca jpg, jpeg, png, gif, webp dosyaları.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ürün</label>
                        <select name="urun_id" class="form-select" required>
                            <?php foreach ($urunler as $urun): ?>
                                <option value="<?php echo $urun['id']; ?>"><?php echo htmlspecialchars($urun['urun_adi'] . ' (' . $urun['magaza_adi'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Görsel URL</label>
                        <input type="text" name="gorsel_url" class="form-control" id="gorselUrlInput">
                        <div class="form-text">Alternatif olarak bir görsel dosyası da seçebilirsiniz.</div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="ana_gorsel" value="1" id="anaGorsel">
                        <label class="form-check-label" for="anaGorsel">Ana Görsel Olsun</label>
                    </div>
                    <button type="submit" class="btn btn-info w-100 text-white">Görsel Ekle</button>
                </form>
                <script>
                // Dosya seçildiyse görsel url zorunlu olmasın
                document.addEventListener('DOMContentLoaded', function() {
                  var fileInput = document.querySelector('input[name="gorsel_dosya"]');
                  var urlInput = document.getElementById('gorselUrlInput');
                  var form = fileInput.closest('form');
                  form.addEventListener('submit', function(e) {
                    if (fileInput.files.length > 0) {
                      urlInput.removeAttribute('required');
                    } else {
                      urlInput.setAttribute('required', 'required');
                    }
                  });
                });
                </script>
                </form>
            </div>
        </div>
        <a href="index.php" class="btn btn-link">Anasayfa</a>
    </div>
</div>
<?php include 'footer.php'; ?>
