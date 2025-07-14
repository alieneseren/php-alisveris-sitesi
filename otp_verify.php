<?php
// Paythor OTP doğrulama fonksiyonu
function paythor_otp_verify($target, $otp) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://dev-api.paythor.com/otp/verify', // TEST ortamı için dev-api
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode([
            "target" => $target,
            "otp" => $otp
        ]),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

// Örnek kullanım (formdan POST ile target ve otp gelirse)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['target'], $_POST['otp'])) {
    $sonuc = paythor_otp_verify($_POST['target'], $_POST['otp']);
    header('Content-Type: application/json');
    echo $sonuc;
    exit;
}
?>
<!-- Basit bir test formu -->
<form method="post" style="max-width:400px;margin:40px auto;padding:24px;background:#f8f8ff;border-radius:12px;box-shadow:0 2px 12px #e0e0e0;">
  <h3>Paythor OTP Doğrulama</h3>
  <input type="email" name="target" placeholder="E-posta veya telefon" required style="width:100%;margin-bottom:12px;padding:8px;">
  <input type="text" name="otp" placeholder="OTP Kodu" required style="width:100%;margin-bottom:12px;padding:8px;">
  <button type="submit" style="width:100%;padding:10px 0;background:#4361ee;color:#fff;border:none;border-radius:6px;font-weight:600;">Doğrula</button>
</form>
