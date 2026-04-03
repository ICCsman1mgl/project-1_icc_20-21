<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
requireLogin();

$nis = isset($_GET['nis']) ? trim((string)$_GET['nis']) : '';
if ($nis === '') {
    echo json_encode(['status' => 'invalid', 'message' => 'NIS kosong']);
    exit;
}

try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT 
            id,
            username AS nis,
            nama_lengkap AS nama,
            alamat,
            telepon AS no_hp,
            foto
        FROM users 
        WHERE role = 'user' AND username = ?
        LIMIT 1");
    $stmt->execute([$nis]);
    $row = $stmt->fetch();

    if ($row) {
        echo json_encode(['status' => 'found', 'data' => $row]);
    } else {
        echo json_encode(['status' => 'not_found']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan sistem.']);
}
