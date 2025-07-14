
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
// Kategorileri çek
$stmt = $pdo->query("SELECT * FROM kategoriler ORDER BY kategori_adi ASC");
$kategoriler = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Kullanıcının mağazalarını getir
$kullanici_id = $_SESSION['kullanici_id'];
$stmt = $pdo->prepare("SELECT * FROM magazalar WHERE kullanici_id = ?");
$stmt->execute([$kullanici_id]);
$magazalar = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Ürün ekleme işlemi
$alert = null;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $magaza_id = $_POST['magaza_id'];
    $urun_adi = trim($_POST['urun_adi']);
    $urun_aciklamasi = trim($_POST['urun_aciklamasi']);
    $fiyat = floatval($_POST['fiyat']);
    $stok = intval($_POST['stok']);
    $durum = $_POST['durum'];
    $olusturma_tarihi = date('Y-m-d H:i:s');
    $kategori_idler = isset($_POST['kategori_id']) ? $_POST['kategori_id'] : [];
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO urunler (magaza_id, urun_adi, urun_aciklamasi, fiyat, stok, durum, olusturma_tarihi) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$magaza_id, $urun_adi, $urun_aciklamasi, $fiyat, $stok, $durum, $olusturma_tarihi]);
        $urun_id = $pdo->lastInsertId();
        foreach ($kategori_idler as $kategori_id) {
            $stmt = $pdo->prepare("INSERT INTO urun_kategorileri (urun_id, kategori_id) VALUES (?, ?)");
            $stmt->execute([$urun_id, $kategori_id]);
        }
        // Görsel yükleme
        if (isset($_FILES['urun_gorsel']) && $_FILES['urun_gorsel']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['urun_gorsel']['tmp_name'];
            $name = basename($_FILES['urun_gorsel']['name']);
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (in_array($ext, $allowed)) {
                $new_name = uniqid('urun_').'.'.$ext;
                $target = __DIR__ . '/gorseller/' . $new_name;
                if (move_uploaded_file($tmp_name, $target)) {
                    $stmt = $pdo->prepare("INSERT INTO urun_gorselleri (urun_id, gorsel_url) VALUES (?, ?)");
                    $stmt->execute([$urun_id, 'gorseller/'.$new_name]);
                }
            }
        }
        $pdo->commit();
        $alert = ['type' => 'success', 'msg' => 'Ürün, kategoriler ve görsel başarıyla eklendi!'];
    } catch (Exception $e) {
        $pdo->rollBack();
        $alert = ['type' => 'danger', 'msg' => 'Ürün eklenirken hata oluştu!'];
    }
}
include 'header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Ürün Ekle</h4>
            </div>
            <div class="card-body">
                <?php if ($alert): ?>
                    <div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show" role="alert">
                        <?php echo $alert['msg']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <form action="urun_ekle.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Ürün Görseli</label>
                        <input type="file" name="urun_gorsel" class="form-control" accept="image/*">
                        <div class="form-text">Yalnızca jpg, jpeg, png, gif, webp dosyaları.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mağaza</label>
                        <select name="magaza_id" class="form-select" required>
                            <?php foreach ($magazalar as $magaza): ?>
                                <option value="<?php echo $magaza['id']; ?>"><?php echo htmlspecialchars($magaza['magaza_adi']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ürün Adı</label>
                        <input type="text" name="urun_adi" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ürün Açıklaması</label>
                        <textarea name="urun_aciklamasi" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fiyat</label>
                        <input type="number" step="0.01" name="fiyat" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stok</label>
                        <input type="number" name="stok" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select name="durum" class="form-select" required>
                            <option value="aktif">Aktif</option>
                            <option value="pasif">Pasif</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategoriler</label>
                        <select name="kategori_id[]" class="form-select" multiple required>
                            <?php foreach ($kategoriler as $kategori): ?>
                                <option value="<?php echo $kategori['id']; ?>"><?php echo htmlspecialchars($kategori['kategori_adi']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Birden fazla kategori seçebilirsiniz.</div>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Ürün Ekle</button>
                </form>
            </div>
        </div>
        <a href="index.php" class="btn btn-link">Anasayfa</a>
    </div>
</div>
<?php include 'footer.php'; ?>
