<?php
session_start();
if (!isset($_SESSION['sepet'])) {
    $_SESSION['sepet'] = [];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['get_count'])) {
        $count = array_sum($_SESSION['sepet']);
        echo json_encode(['count' => $count]);
        exit;
    }
    if (isset($_POST['urun_id']) && is_numeric($_POST['urun_id'])) {
        $urun_id = intval($_POST['urun_id']);
        $yeni_eklendi = false;
        if (isset($_SESSION['sepet'][$urun_id])) {
            $_SESSION['sepet'][$urun_id]++;
            $yeni_eklendi = false;
        } else {
            $_SESSION['sepet'][$urun_id] = 1;
            $yeni_eklendi = true;
        }
        $count = array_sum($_SESSION['sepet']);
        echo json_encode([
            'success' => true,
            'count' => $count,
            'yeni_eklendi' => $yeni_eklendi
        ]);
        exit;
    }
    echo json_encode(['success' => false]);
    exit;
}
echo json_encode(['success' => false]);
