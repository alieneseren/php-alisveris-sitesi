<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Paythor Hesabı Aç</title>
    <link rel="stylesheet" href="style.css?v=2">
    <style>
        body { background: #f8f9fa; }
        .register-formu { max-width: 420px; margin: 60px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 16px #0001; padding: 32px 28px; }
        .form-label { font-weight: 500; }
    </style>
</head>
<body>
    <div class="register-formu">
        <h2 class="mb-4 text-center">Paythor Hesabı Aç</h2>
        <div class="mb-3">
            <label for="registerFirstname" class="form-label">Ad</label>
            <input type="text" id="registerFirstname" class="form-control" placeholder="Ad" required>
        </div>
        <div class="mb-3">
            <label for="registerLastname" class="form-label">Soyad</label>
            <input type="text" id="registerLastname" class="form-control" placeholder="Soyad" required>
        </div>
        <div class="mb-3">
            <label for="registerUserPhone" class="form-label">Telefon (905551234567)</label>
            <input type="text" id="registerUserPhone" class="form-control" placeholder="905551234567" required>
        </div>
        <div class="mb-3">
            <label for="registerEmail" class="form-label">E-posta</label>
            <input type="email" id="registerEmail" class="form-control" placeholder="E-posta" required>
        </div>
        <div class="mb-3">
            <label for="registerSifre" class="form-label">Şifre</label>
            <input type="password" id="registerSifre" class="form-control" placeholder="Şifre" required>
        </div>
        <hr>
        <div class="mb-3">
            <label for="merchantName" class="form-label">Mağaza Adı</label>
            <input type="text" id="merchantName" class="form-control" placeholder="Mağaza Adı" required>
        </div>
        <div class="mb-3">
            <label for="merchantCompany" class="form-label">Şirket Adı</label>
            <input type="text" id="merchantCompany" class="form-control" placeholder="Şirket Adı" required>
        </div>
        <div class="mb-3">
            <label for="merchantEmail" class="form-label">Şirket E-posta</label>
            <input type="email" id="merchantEmail" class="form-control" placeholder="Şirket E-posta" required>
        </div>
        <div class="mb-3">
            <label for="merchantPhone" class="form-label">Şirket Telefon (905551234567)</label>
            <input type="text" id="merchantPhone" class="form-control" placeholder="905551234567" required>
        </div>
        <div class="mb-3">
            <label for="merchantWeb" class="form-label">Web Sitesi</label>
            <input type="text" id="merchantWeb" class="form-control" placeholder="https://example.com" required>
        </div>
        <div class="mb-3">
            <label for="merchantCountry" class="form-label">Ülke</label>
            <input type="text" id="merchantCountry" class="form-control" placeholder="tr" value="tr" required>
        </div>
        <div class="mb-3">
            <label for="merchantLang" class="form-label">Dil</label>
            <input type="text" id="merchantLang" class="form-control" placeholder="en_US" value="en_US" required>
        </div>
        <div class="mb-3">
            <label for="merchantProgramId" class="form-label">Program ID</label>
            <input type="number" id="merchantProgramId" class="form-control" placeholder="1" value="1" required>
        </div>
        <button onclick="registerOl()" class="btn-main w-100">Kayıt Ol</button>
        <div class="sonuc mt-4" id="registerSonuc"></div>
        <div class="mt-3 text-center">
            <a href="paythor_login.php">Zaten hesabın var mı? Giriş Yap</a><br>
            <a href="sepet.php" class="btn-main" style="margin-top:12px;display:inline-block;background:#4caf50;">Sepete Dön</a>
        </div>
    </div>
    <script>
        function registerOl() {
            const firstname = document.getElementById('registerFirstname').value;
            const lastname = document.getElementById('registerLastname').value;
            const userPhone = document.getElementById('registerUserPhone').value;
            const email = document.getElementById('registerEmail').value;
            const sifre = document.getElementById('registerSifre').value;
            const merchantName = document.getElementById('merchantName').value;
            const merchantCompany = document.getElementById('merchantCompany').value;
            const merchantEmail = document.getElementById('merchantEmail').value;
            const merchantPhone = document.getElementById('merchantPhone').value;
            const merchantWeb = document.getElementById('merchantWeb').value;
            const merchantCountry = document.getElementById('merchantCountry').value;
            const merchantLang = document.getElementById('merchantLang').value;
            const merchantProgramId = parseInt(document.getElementById('merchantProgramId').value, 10);

            if (!firstname || !lastname || !userPhone || !email || !sifre ||
                !merchantName || !merchantCompany || !merchantEmail || !merchantPhone ||
                !merchantWeb || !merchantCountry || !merchantLang || !merchantProgramId) {
                document.getElementById('registerSonuc').innerHTML = '<div class="modern-alert danger">Tüm alanları doldurun.</div>';
                return;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(merchantEmail)) {
                document.getElementById('registerSonuc').innerHTML = '<div class="modern-alert danger">Geçerli bir e-posta girin.</div>';
                return;
            }
            if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])/.test(sifre)) {
                document.getElementById('registerSonuc').innerHTML = '<div class="modern-alert danger">Şifre en az bir büyük harf, bir küçük harf, bir rakam ve bir özel karakter içermelidir.</div>';
                return;
            }
            if (sifre.length < 8) {
                document.getElementById('registerSonuc').innerHTML = '<div class="modern-alert danger">Şifre en az 8 karakter olmalı.</div>';
                return;
            }
            if (!/^https?:\/\/[\w\-]+(\.[\w\-]+)+.*$/.test(merchantWeb)) {
                document.getElementById('registerSonuc').innerHTML = '<div class="modern-alert danger">Geçerli bir web adresi girin (http veya https ile başlamalı).</div>';
                return;
            }
            document.getElementById('registerSonuc').innerHTML = '<div class="modern-alert info">Kayıt olunuyor...</div>';

            const raw = JSON.stringify({
                user: {
                    firstname: firstname,
                    lastname: lastname,
                    phone: userPhone,
                    email: email,
                    password: sifre
                },
                merchant: {
                    program_id: merchantProgramId,
                    name: merchantName,
                    company: merchantCompany,
                    email: merchantEmail,
                    phone: merchantPhone,
                    web: merchantWeb,
                    country: merchantCountry,
                    lang: merchantLang
                }
            });

            fetch("https://dev-api.paythor.com/auth/register/", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: raw
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(result => {
                if (result.status === 201 || result.status === 200) {
                    document.getElementById('registerSonuc').innerHTML = '<div class="modern-alert success">Kayıt başarılı! <a href="paythor_login.php">Giriş yap</a></div>';
                } else {
                    let hata = "Kayıt başarısız.";
                    if (result.body) {
                        if (result.body.details) {
                            if (Array.isArray(result.body.details)) {
                                hata = result.body.details.join("<br>");
                            } else {
                                hata = result.body.details;
                            }
                        } else if (result.body.error) {
                            hata = result.body.error;
                        } else if (result.body.message) {
                            hata = result.body.message;
                        } else if (typeof result.body === "string") {
                            hata = result.body;
                        }
                    }
                    document.getElementById('registerSonuc').innerHTML = '<div class="modern-alert danger">Hata: ' + hata + '</div>';
                }
            })
            .catch((err) => {
                document.getElementById('registerSonuc').innerHTML = '<div class="modern-alert danger">Sunucuya ulaşılamadı.</div>';
            });
        }
    </script>
</body>
</html>
