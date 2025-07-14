<?php
require_once 'db.php';
header('Content-Type: application/json');
$kategoriler = $pdo->query("SELECT id, kategori_adi FROM kategoriler ORDER BY kategori_adi ASC")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($kategoriler);
