<?php
session_start();
if (!isset($_SESSION['kullanici_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Giriş gerekli']);
    exit;
}
if (!isset($_POST['urun_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Eksik veri']);
    exit;
}
$urun_id = intval($_POST['urun_id']);
if (isset($_SESSION['sepet'][$urun_id])) {
    unset($_SESSION['sepet'][$urun_id]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ürün zaten yok']);
}
