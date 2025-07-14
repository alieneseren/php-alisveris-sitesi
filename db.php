<?php
$host = "localhost";
$dbname = "pazaryeri";
$username = "root"; // phpMyAdmin varsayılan kullanıcı adı
$password = "";     // Şifreniz (genellikle localde boş olur)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Hata yakalama modu
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>
