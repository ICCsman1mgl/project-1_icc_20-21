<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$pdo = getConnection();   // <<< TAMBAHKAN INI

$nis = $_GET['nis'] ?? '';

if (!$nis) {
    echo json_encode(['status' => 'error']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, nama, kelas FROM anggota WHERE nis = ?");
$stmt->execute([$nis]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if ($data) {
    echo json_encode([
        'status' => 'found',
        'data' => $data
    ]);
} else {
    echo json_encode([
        'status' => 'not_found'
    ]);
}
