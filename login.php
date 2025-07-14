<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$error = '';
$success = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $eposta = trim($_POST['eposta']);
    $sifre = $_POST['sifre'];
    $stmt = $pdo->prepare("SELECT * FROM kullanicilar WHERE eposta = ?");
    $stmt->execute([$eposta]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($sifre, $user['sifre'])) {
        $_SESSION['kullanici_id'] = $user['id'];
        $_SESSION['ad'] = $user['ad'];
        $_SESSION['rol'] = $user['rol'];
        header("Location: index.php");
        exit;
    } else {
        $error = 'E-posta veya şifre hatalı!';
    }
}
?>

<link rel="stylesheet" href="style.css">
<div class="auth-container">
    <div class="auth-image">
        <h2>Hoş Geldiniz!</h2>
        <p>Modern ve güvenli pazaryeri platformuna giriş yapın.<br>Hesabınız yoksa hemen kaydolun!</p>
    </div>
    <div class="auth-form">
        <div class="auth-switch">
            <button class="active" onclick="switchForm('login')">Giriş Yap</button>
            <button onclick="switchForm('register')">Kayıt Ol</button>
        </div>
        <h1>Giriş Yap</h1>
        <p>Hesabınıza erişmek için bilgilerinizi girin.</p>
        <?php if ($error): ?>
            <div class="alert alert-danger" style="margin-bottom:16px;"> <?= $error ?> </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success" style="margin-bottom:16px;"> <?= $success ?> </div>
        <?php endif; ?>
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="eposta">E-posta</label>
                <input type="email" id="eposta" name="eposta" class="form-control" required>
            </div>
            <div class="form-group" style="position:relative;">
                <label for="sifre">Şifre</label>
                <input type="password" id="sifre" name="sifre" class="form-control" required>
                <span class="password-toggle" onclick="togglePassword('sifre', this)">&#128065;</span>
            </div>
            <button type="submit" class="btn btn-primary">Giriş Yap</button>
        </form>
        <div class="auth-link">
            Hesabınız yok mu? <a href="register.php" onclick="event.preventDefault();switchForm('register')">Kayıt Ol</a>
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
