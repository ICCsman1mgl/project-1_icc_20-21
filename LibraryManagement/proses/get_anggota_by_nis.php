<?php
// proses/get_anggota_by_nis.php

header('Content-Type: application/json; charset=utf-8');

// 1) Load config project
require_once __DIR__ . '/../config/database.php';

// 2) Adapter: kalau config kamu punya PDO tapi namanya beda
if (!isset($pdo) || !($pdo instanceof PDO)) {
    if (isset($db) && $db instanceof PDO) {
        $pdo = $db;
    } elseif (isset($conn) && $conn instanceof PDO) {
        $pdo = $conn;
    }
}

// 3) Fallback: kalau config kamu MySQLi, bikin PDO khusus endpoint ini
if (!isset($pdo) || !($pdo instanceof PDO)) {
    try {
        $pdo = new PDO(
            "mysql:host=localhost;dbname=perpustakaan;charset=utf8mb4",
            "root",
            ""
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Koneksi DB gagal: ' . $e->getMessage()
        ]);
        exit;
    }
}

// 4) Ambil NIS
$nis = isset($_GET['nis']) ? trim($_GET['nis']) : '';
if ($nis === '') {
    echo json_encode(['status' => 'invalid', 'message' => 'NIS kosong']);
    exit;
}

// 5) Query data anggota
$stmt = $pdo->prepare("SELECT nis, nama, alamat, no_hp FROM anggota WHERE nis = ? LIMIT 1");
$stmt->execute([$nis]);
$row = $stmt->fetch();

if ($row) {
    echo json_encode(['status' => 'found', 'data' => $row]);
} else {
    echo json_encode(['status' => 'not_found']);
}
