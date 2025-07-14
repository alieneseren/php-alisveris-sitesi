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
<div class="header-flexbar-left" style="display:flex;align-items:center;">
    <a class="logo-main" href="index.php">
        <img src="/logo.svg" alt="Pazaryeri">
    </a>
</div>
<nav class="header-flexbar-right" style="margin-left:2rem;">
    <ul class="nav-menu nav-flex">
        <li><a href="index.php">Anasayfa</a></li>
        <li class="dropdown" id="kategori-dropdown">
          <a href="#" class="dropdown-toggle" id="kategoriDropdownBtn">Kategoriler <span style="font-size:1.1em;">▼</span></a>
        </li>
        <?php if (isset($_SESSION['kullanici_id'])): ?>
            <li><a href="profil.php">Profilim</a></li>
            <?php if (isset($_SESSION['rol']) && ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'yonetici')): ?>
                <li><a href="admin.php">Admin Paneli</a></li>
            <?php endif; ?>
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
<!-- Kategori menüsü headerın hemen altında ve tam ortada -->
<div style="width:100%;display:flex;justify-content:center;position:relative;z-index:1002;">
  <ul class="dropdown-menu" id="kategoriDropdownMenu" style="display:none;position:absolute;top:calc(100% + 12px);left:0;right:0;margin:0 auto;z-index:1002;background:#fff;box-shadow:0 8px 32px rgba(60,60,60,0.18);border-radius:16px;padding:0.5rem 0;min-width:320px;max-width:400px;">
    <?php
    require_once "db.php";
    $stmt = $pdo->prepare("SELECT * FROM kategoriler WHERE ust_kategori_id IS NULL ORDER BY kategori_adi LIMIT 11");
    $stmt->execute();
    $ana_kategoriler = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $ana_index = 0;
    foreach ($ana_kategoriler as $ana) {
        echo '<li class="dropdown-submenu" style="position:relative;">';
        echo '<a href="#" class="dropdown-item ana-kategori" data-index="'.$ana_index.'" style="font-weight:600;padding:8px 18px;">'.htmlspecialchars($ana['kategori_adi']).'</a>';
        $stmt2 = $pdo->prepare("SELECT * FROM kategoriler WHERE ust_kategori_id = ? ORDER BY kategori_adi");
        $stmt2->execute([$ana['id']]);
        $alt_kategoriler = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        if ($alt_kategoriler) {
            echo '<ul class="dropdown-menu-sub" style="position:absolute;left:100%;top:0;background:#f8faff;box-shadow:0 2px 8px rgba(67,97,238,0.08);border-radius:10px;padding:0.5rem 0;min-width:180px;display:none;">';
            foreach ($alt_kategoriler as $alt) {
                echo '<li><a href="urunler.php?kategori_id='.$alt['id'].'" class="dropdown-item" style="padding:8px 18px;">'.htmlspecialchars($alt['kategori_adi']).'</a></li>';
            }
            echo '</ul>';
        }
        echo '</li>';
        $ana_index++;
    }
    ?>
  </ul>
</div>
    </div>
</header>
<div class="container">
