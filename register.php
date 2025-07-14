
<?php
require_once 'db.php';
$error = '';
$success = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ad = trim($_POST['ad']);
    $eposta = trim($_POST['eposta']);
    $sifre = password_hash($_POST['sifre'], PASSWORD_DEFAULT);
    $rol = $_POST['rol'];
    $olusturma_tarihi = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("SELECT id FROM kullanicilar WHERE eposta = ?");
    $stmt->execute([$eposta]);
    if ($stmt->rowCount() > 0) {
        $error = 'Bu e-posta ile zaten kayıtlı bir kullanıcı var!';
    } else {
        $stmt = $pdo->prepare("INSERT INTO kullanicilar (ad, eposta, sifre, rol, olusturma_tarihi) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$ad, $eposta, $sifre, $rol, $olusturma_tarihi])) {
            $success = 'Kayıt başarılı! Giriş yapabilirsiniz.';
        } else {
            $error = 'Kayıt sırasında hata oluştu!';
        }
    }
}
?>
<link rel="stylesheet" href="style.css">
<div class="auth-container">
    <div class="auth-image">
        <h2>Kayıt Ol</h2>
        <p>Modern ve güvenli pazaryeri platformuna katılın.<br>Zaten hesabınız varsa giriş yapın!</p>
    </div>
    <div class="auth-form">
        <div class="auth-switch">
            <button onclick="switchForm('login')">Giriş Yap</button>
            <button class="active" onclick="switchForm('register')">Kayıt Ol</button>
        </div>
        <h1>Kayıt Ol</h1>
        <p>Hesabınızı oluşturmak için bilgilerinizi girin.</p>
        <?php if ($error): ?>
            <div class="alert alert-danger" style="margin-bottom:16px;"> <?= $error ?> </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success" style="margin-bottom:16px;"> <?= $success ?> </div>
        <?php endif; ?>
        <form action="register.php" method="post">
            <div class="form-group">
                <label for="ad">Ad Soyad</label>
                <input type="text" id="ad" name="ad" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="eposta">E-posta</label>
                <input type="email" id="eposta" name="eposta" class="form-control" required>
            </div>
            <div class="form-group" style="position:relative;">
                <label for="sifre">Şifre</label>
                <input type="password" id="sifre" name="sifre" class="form-control" required>
                <span class="password-toggle" onclick="togglePassword('sifre', this)">&#128065;</span>
            </div>
            <div class="form-group">
                <label for="rol">Rol</label>
                <select id="rol" name="rol" class="form-control" required>
                    <option value="musteri">Müşteri</option>
                    <option value="satici">Satıcı</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Kayıt Ol</button>
        </form>
        <div class="auth-link">
            Zaten hesabınız var mı? <a href="login.php" onclick="event.preventDefault();switchForm('login')">Giriş Yap</a>
        </div>
    </div>
</div>
<script>
function togglePassword(id, el) {
  const input = document.getElementById(id);
  if (input.type === 'password') {
    input.type = 'text';
    el.style.color = '#4361ee';
  } else {
    input.type = 'password';
    el.style.color = '#999';
  }
}

function switchForm(target) {
  const authContainer = document.querySelector('.auth-container');
  authContainer.classList.add('fade-out');
  setTimeout(function() {
    if(target === 'register') {
      window.location.href = 'register.php';
    } else {
      window.location.href = 'login.php';
    }
  }, 350);
}
</script>
<style>
.fade-out {
  animation: fadeOutAnim 0.35s forwards;
}
@keyframes fadeOutAnim {
  to { opacity: 0; transform: scale(0.98); }
}
</style>
