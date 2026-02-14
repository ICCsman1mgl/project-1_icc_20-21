<?php
require_once '../config/database.php';
requireAdmin();

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $bukuId = (int)$_GET['id'];
    
    try {
        $pdo = getConnection();
        
        // Ambil data buku
        $stmt = $pdo->prepare("SELECT * FROM buku WHERE id = ?");
        $stmt->execute([$bukuId]);
        $buku = $stmt->fetch();
        
        if (!$buku) {
            echo json_encode(['success' => false, 'message' => 'Buku tidak ditemukan']);
            exit();
        }
        
        echo json_encode([
            'success' => true,
            'kode_buku' => $buku['kode_buku'],
            'judul' => $buku['judul'],
            'pengarang' => $buku['pengarang'],
            'cover' => $buku['cover'],
            'jumlah_total' => $buku['jumlah_total'],
            'jumlah_tersedia' => $buku['jumlah_tersedia']
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error database: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID buku tidak diberikan']);
}
?>
