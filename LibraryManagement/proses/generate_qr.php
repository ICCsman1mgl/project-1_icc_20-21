<?php
require_once '../config/database.php';
require_once '../assets/qrcode/phpqrcode.php';


$id = $_GET['id'] ?? '';

if (!$id) {
    exit('ID tidak ditemukan');
}

$pdo = getConnection();

$stmt = $pdo->prepare("SELECT nis FROM anggota WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    exit('Anggota tidak ditemukan');
}

$nis = $data['nis'];

header('Content-Type: image/png');
QRcode::png($nis, false, QR_ECLEVEL_H, 6);
