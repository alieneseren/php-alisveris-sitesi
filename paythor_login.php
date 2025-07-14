<?php
session_start();
// Her yeni giriş ekranı açılışında pending OTP session'larını sıfırla (kullanıcı sayfaya ilk geldiğinde)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    unset($_SESSION['pending_paythor_email']);
    unset($_SESSION['pending_paythor_password']);
}
// DEBUG: Giriş ekranında eski token'ı sıfırla
unset($_SESSION['paythor_token']);
unset($_SESSION['paythor_user_email']);
// Eğer zaten giriş yaptıysa yönlendir
if (isset($_SESSION['paythor_token']) && !empty($_SESSION['paythor_token'])) {
    header('Location: sepet.php');
    exit;
}

$hata = '';
$show_otp = false;
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $sifre = trim($_POST['sifre'] ?? '');
    $otp = trim($_POST['otp'] ?? '');
    $program_id = 1;
    $app_id = 102;
    $store_url = 'https://ornekmagaza.com';

    // Eğer OTP bekleniyorsa, yeni bir login isteği atılmasını engelle
    if (isset($_SESSION['pending_paythor_email']) && empty($otp)) {
        $show_otp = true;
        $email = $_SESSION['pending_paythor_email'];
        $sifre = $_SESSION['pending_paythor_password'] ?? '';
        $hata = '';
        $basarili = 'Giriş başarılı, lütfen e-posta adresinize gelen OTP kodunu girin.';
    } else if (!$email || !$sifre) {
        $hata = 'E-posta ve şifre zorunlu.';
    } else if (!$otp) {
        // İlk adım: e-posta/şifre ile giriş, OTP bekleniyor mu?
        $payload = json_encode([
            'auth_query' => [
                'auth_method' => 'email_password_panel',
                'email' => $email,
                'password' => $sifre,
                'program_id' => $program_id,
                'app_id' => $app_id,
                'store_url' => $store_url
            ]
        ]);
        $ch = curl_init('https://dev-api.paythor.com/auth/signin');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = json_decode($response, true);
        // DEBUG: API login yanıtını ekrana/loga yazdır
        file_put_contents(__DIR__.'/paythor_login_debug.txt', date('Y-m-d H:i:s')." - LOGIN RESPONSE: ".print_r($data, true)."\n", FILE_APPEND);
        echo '<div style="background:#222;color:#fff;padding:8px 12px;margin-bottom:12px;font-size:14px;border-radius:6px;max-width:600px;overflow-x:auto;">DEBUG: API Login Yanıtı:<br><pre style="color:#fff;">'.htmlspecialchars(print_r($data, true)).'</pre></div>';
        $token = $data['data']['token_string'] ?? $data['token'] ?? '';
        // login.html'deki gibi: Eğer status "validation" ise OTP istenecek, token varsa geçici olarak session'da tut
        if ($httpcode === 200 && isset($data['data']['status']) && $data['data']['status'] === 'validation') {
            $show_otp = true;
            $_SESSION['pending_paythor_email'] = $email;
            $_SESSION['pending_paythor_password'] = $sifre;
            $_SESSION['pending_paythor_token'] = $token; // TOKENI GEÇİCİ OLARAK SAKLA
            $hata = '';
            $basarili = 'Giriş başarılı, lütfen e-posta adresinize gelen OTP kodunu girin.';
        } else if ($httpcode === 200 && $token) {
            $_SESSION['paythor_token'] = $token;
            $_SESSION['paythor_user_email'] = $email;
            require_once 'db.php';
            if (isset($_SESSION['kullanici_id'])) {
                $kullanici_id = $_SESSION['kullanici_id'];
                $stmt = $pdo->prepare("UPDATE kullanicilar SET paythor_token = ? WHERE id = ?");
                $stmt->execute([$token, $kullanici_id]);
            }
            // Temizlik
            unset($_SESSION['pending_paythor_email']);
            unset($_SESSION['pending_paythor_password']);
            unset($_SESSION['pending_paythor_token']);
            header('Location: sepet.php');
            exit;
        } else if (($data['status'] ?? '') === 'otp_required' || strpos(strtolower($data['message'] ?? ''), 'otp') !== false) {
            $show_otp = true;
            $_SESSION['pending_paythor_email'] = $email;
            $_SESSION['pending_paythor_password'] = $sifre;
            $hata = 'OTP kodu gerekli. Lütfen e-posta veya telefonunuza gelen kodu girin.';
        } else {
            $hata = $data['message'] ?? 'API bağlantı hatası.';
        }
    } else {
        // OTP ile doğrulama
        $otp_payload = json_encode([
            'target' => $email,
            'otp' => $otp
        ]);
        $ch = curl_init('https://dev-api.paythor.com/otp/verify');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $otp_payload);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = json_decode($response, true);
        // DEBUG: OTP yanıtını logla
        file_put_contents(__DIR__.'/paythor_login_debug.txt', date('Y-m-d H:i:s')." - OTP RESPONSE: ".print_r($data, true)."\n", FILE_APPEND);
        // Tokenı pending_paythor_token'dan al (login.html mantığı)
        $token = $_SESSION['pending_paythor_token'] ?? '';
        if ($httpcode === 200 && ($data['status'] ?? '') === 'success' && $token) {
            // DEBUG: OTP sonrası eski token'ı sıfırla
            unset($_SESSION['paythor_token']);
            unset($_SESSION['paythor_user_email']);
            unset($_SESSION['pending_paythor_email']);
            unset($_SESSION['pending_paythor_password']);
            unset($_SESSION['pending_paythor_token']);
            // Token'ı session ve veritabanına kaydet
            file_put_contents(__DIR__.'/paythor_token_log.txt', date('Y-m-d H:i:s')." - Session: ".$token."\n", FILE_APPEND);
            echo '<div style="background:#222;color:#fff;padding:8px 12px;margin-bottom:12px;font-size:14px;border-radius:6px;">DEBUG: Alınan Token: <b>'.htmlspecialchars($token).'</b></div>';
            $_SESSION['paythor_token'] = $token;
            $_SESSION['paythor_user_email'] = $email;
            require_once 'db.php';
            if (isset($_SESSION['kullanici_id'])) {
                $kullanici_id = $_SESSION['kullanici_id'];
                $stmt = $pdo->prepare("UPDATE kullanicilar SET paythor_token = ? WHERE id = ?");
                $stmt->execute([$token, $kullanici_id]);
                // DEBUG: Veritabanı token'ı logla
                file_put_contents(__DIR__.'/paythor_token_log.txt', date('Y-m-d H:i:s')." - DB: ".$token." Kullanıcı: ".$kullanici_id."\n", FILE_APPEND);
            }
            header('Location: sepet.php');
            exit;
        } else if ($httpcode === 200 && ($data['status'] ?? '') === 'success') {
            // OTP başarılı ama token yoksa, ikinci OTP oluşmaması için tekrar login denemesi yapılmaz
            unset($_SESSION['pending_paythor_email']);
            unset($_SESSION['pending_paythor_password']);
            unset($_SESSION['pending_paythor_token']);
            $basarili = 'OTP doğrulandı, ancak API tokenı alınamadı. Lütfen tekrar giriş yapınız.';
            $show_otp = false;
            // Kullanıcıya tekrar login formunu göster
        } else {
            $show_otp = true;
            $hata = $data['message'] ?? 'OTP doğrulama başarısız.';
        }
    }
}
?><!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Paythor API Giriş</title>
    <link rel="stylesheet" href="style.css?v=2">
    <style>
    .api-login-box { max-width: 400px; margin: 60px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 16px #0001; padding: 32px 28px; }
    </style>
</head>
<body>
<div class="api-login-box">
    <h2 class="mb-4 text-center">Paythor API Giriş</h2>
    <?php if (!empty($hata)): ?>
        <div class="modern-alert danger mb-3"><?php echo htmlspecialchars($hata); ?></div>
    <?php endif; ?>
    <?php if (!empty($basarili)): ?>
        <div class="modern-alert success mb-3"><?php echo htmlspecialchars($basarili); ?></div>
    <?php endif; ?>
    <div class="mb-3 text-center">
        <span style="font-size:15px;color:#888;">Paythor hesabınız yok mu?</span><br>
        <a href="paythor_register.php" class="btn-main" style="margin-top:8px;display:inline-block;">Paythor Hesabı Aç</a>
        <br>
        <a href="sepet.php" class="btn-main" style="margin-top:12px;display:inline-block;background:#4caf50;">Sepete Dön</a>
    </div>
    <form method="post">
        <div class="mb-3">
            <label for="email">E-posta</label>
            <input type="email" name="email" id="email" class="form-control" required value="<?php echo htmlspecialchars($email); ?>" <?php if ($show_otp) echo 'readonly'; ?>>
        </div>
        <div class="mb-3">
            <label for="sifre">Şifre</label>
            <input type="password" name="sifre" id="sifre" class="form-control" required value="<?php echo $show_otp ? htmlspecialchars($sifre) : ''; ?>" <?php if ($show_otp) echo 'readonly'; ?>>
        </div>
        <?php if ($show_otp): ?>
        <div class="mb-3">
            <label for="otp">OTP Kodu</label>
            <input type="text" name="otp" id="otp" class="form-control" maxlength="6" required autocomplete="one-time-code">
        </div>
        <?php endif; ?>
        <button type="submit" class="btn-main w-100"><?php echo $show_otp ? 'OTP ile Doğrula' : 'Giriş Yap'; ?></button>
    </form>
</div>
</body>
</html>
