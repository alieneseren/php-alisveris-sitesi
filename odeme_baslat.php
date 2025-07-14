<?php
session_start();
header('Content-Type: application/json');

// Sepet ve kullanıcı bilgileri
$rol = $_SESSION['rol'] ?? '';
$paythor_token = $_SESSION['paythor_token'] ?? '';
// Eğer satıcı ise ve session'da token yoksa veritabanından çek
$kullanici_id = $_SESSION['kullanici_id'] ?? '';
if ($rol === 'satici' && empty($paythor_token) && !empty($kullanici_id)) {
    require_once 'db.php';
    $stmt = $pdo->prepare("SELECT paythor_token FROM kullanicilar WHERE id = ?");
    $stmt->execute([$kullanici_id]);
    $paythor_token = $stmt->fetchColumn() ?: '';
}
$sepet = $_SESSION['sepet'] ?? [];

// Müşteri bilgileri POST ile geliyorsa al
$input = json_decode(file_get_contents('php://input'), true);
$ad = $input['ad'] ?? ($_SESSION['ad'] ?? null);
$soyad = $input['soyad'] ?? ($_SESSION['soyad'] ?? null);
$eposta = $input['eposta'] ?? ($_SESSION['eposta'] ?? null);
$phone = $input['telefon'] ?? ($_SESSION['telefon'] ?? '5533929072');

// Müşteri ise satıcı tokenı, satıcı ise kendi tokenı
$satici_token = null;
if ($rol === 'musteri' && !empty($sepet)) {
    require_once 'db.php';
    reset($sepet);
    $ilk_urun_id = key($sepet);
    $stmt = $pdo->prepare("SELECT u.magaza_id, m.kullanici_id FROM urunler u JOIN magazalar m ON u.magaza_id = m.id WHERE u.id = ?");
    $stmt->execute([$ilk_urun_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $satici_id = $row['kullanici_id'] ?? null;
    if ($satici_id) {
        $stmt2 = $pdo->prepare("SELECT paythor_token FROM kullanicilar WHERE id = ?");
        $stmt2->execute([$satici_id]);
        $satici_token = $stmt2->fetchColumn();
    }
}
// Token ve debug log
$token = ($rol === 'satici') ? $paythor_token : $satici_token;
$debug_info = [
    'token' => $token,
    'rol' => $rol,
    'kullanici_id' => $_SESSION['kullanici_id'] ?? null,
    'eposta' => $eposta,
    'ad' => $ad,
    'soyad' => $soyad,
    'sepet' => $sepet
];
file_put_contents(__DIR__.'/paythor_token_log.txt', date('Y-m-d H:i:s')." - odeme_baslat.php Debug: ".json_encode($debug_info, JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND);
if (!$token) {
    echo json_encode(['success' => false, 'message' => 'API token bulunamadı.', 'debug' => $debug_info]);
    exit;
}

// Sepet toplamı
$toplam = 0;
require_once 'db.php';

$cart = [];
foreach ($sepet as $urun_id => $adet) {
    $stmt = $pdo->prepare("SELECT urun_adi, fiyat FROM urunler WHERE id = ?");
    $stmt->execute([$urun_id]);
    $urun = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$urun) continue;
    $ara_toplam = $urun['fiyat'] * $adet;
    $toplam += $ara_toplam;
$cart[] = [
    'id' => (string)$urun_id,
    'name' => $urun['urun_adi'],
    'type' => 'product',
    'price' => number_format($urun['fiyat'], 2, '.', ''),
    'quantity' => (int)$adet
];
}

// Ad ve soyadı ayır
$ad = trim($ad);
$soyad = trim($soyad);
$first_name = $ad;
$last_name = $soyad;
// last_name en az 2 karakter olmalı
if (strlen($last_name) < 2) $last_name = 'Musteri';

// Telefon zorunlu ve string olmalı
$phone = (string)preg_replace('/[^0-9]/', '', $phone);
if (strlen($phone) < 10) $phone = '5000000000';

// API isteği (örnek isteğe uygun)
$api_url = 'https://dev-api.paythor.com/payment/create';

$data = [
    'payment' => [
        'amount' => number_format($toplam, 2, '.', ''),
        'currency' => 'TRY',
        'buyer_fee' => '0',
        'method' => 'creditcard',
        'merchant_reference' => 'ORDER-' . uniqid()
    ],
    'payer' => [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $eposta,
        'phone' => $phone,
        'address' => [
            'line_1' => '123 Main St',
            'city' => 'Istanbul',
            'state' => 'Istanbul',
            'postal_code' => '07050',
            'country' => 'TR'
        ],
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
    ],
    'order' => [
        'cart' => $cart,
        'shipping' => [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone,
            'email' => $eposta,
            'address' => [
                'line_1' => '123 Main St',
                'city' => 'Istanbul',
                'state' => 'Istanbul',
                'postal_code' => '07050',
                'country' => 'TR'
            ]
        ],
        'invoice' => [
            'id' => 'cart_hash_' . uniqid(),
            'first_name' => $first_name,
            'last_name' => $last_name,
            'price' => number_format($toplam, 2, '.', ''),
            'quantity' => 1
        ]
    ]
];

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200 && $response) {
    $json = json_decode($response, true);
    if (isset($json['status']) && $json['status'] === 'success' && isset($json['data']['payment_link'])) {
        echo json_encode(['success' => true, 'payment_link' => $json['data']['payment_link']]);
        exit;
    } else {
        echo json_encode([
            'success' => false,
            'message' => $json['message'] ?? 'Ödeme linki alınamadı.',
            'http_code' => $http_code,
            'api_response' => $json,
            'request' => $data
        ]);
        exit;
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'API bağlantı hatası.',
        'http_code' => $http_code,
        'api_response' => $response,
        'request' => $data
    ]);
    exit;
}
