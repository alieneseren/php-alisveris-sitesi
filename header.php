<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pazaryeri</title>
    <link rel="stylesheet" href="style.css">
    <script src="main.js?v=2" defer></script>
</head>
<body>
<header class="header header-flexbar">
    <div class="header-flexbar-inner">
        <div class="header-flexbar-center">
            <a class="logo-main" href="index.php">
                <img src="/logo.svg" alt="Pazaryeri">
            </a>
        </div>
        <nav class="header-flexbar-right">
            <ul class="nav-menu nav-flex">
                <li><a href="index.php">Anasayfa</a></li>
                <li class="dropdown" id="kategori-dropdown">
                  <a href="#" class="dropdown-toggle" id="kategoriDropdownBtn">Kategoriler <span style="font-size:1.1em;">▼</span></a>
                  <ul class="dropdown-menu" id="kategoriDropdownMenu" style="display:none;position:absolute;z-index:999;background:#fff;box-shadow:0 4px 16px rgba(67,97,238,0.10);border-radius:12px;padding:0.5rem 0;min-width:180px;">
                    <!-- Kategoriler JS ile yüklenecek -->
                  </ul>
                </li>
                <?php if (isset($_SESSION['kullanici_id'])): ?>
                    <li><a href="profil.php">Profilim</a></li>
                    <li>
                        <a href="sepet.php" class="cart-link" style="position:relative;display:inline-block;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24"><path fill="#4B4BFF" d="M7 20a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm10 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7.16 18h9.68c.8 0 1.51-.48 1.8-1.21l3.24-7.56A1 1 0 0 0 21 8H6.21l-.94-2.36A2 2 0 0 0 3.42 4H1a1 1 0 1 0 0 2h2.42l3.6 9.03-1.35 2.44C4.52 18.37 5.48 20 7 20zm12.24-8-2.88 6.72a.25.25 0 0 1-.23.14H7.16l1.1-2h7.72a1 1 0 1 0 0-2H9.53l-.76-2H19.24z"/></svg>
                            <span id="cart-count" style="position:absolute;top:-6px;right:-8px;background:#ff3b3b;color:#fff;font-size:13px;padding:2px 7px;border-radius:12px;min-width:22px;text-align:center;display:none;">0</span>
                        </a>
                    </li>
                <?php else: ?>
                    <li><a href="login.php">Giriş Yap</a></li>
                    <li><a href="register.php">Kayıt Ol</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<div class="container">
