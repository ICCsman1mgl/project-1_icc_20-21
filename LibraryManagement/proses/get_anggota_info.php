<?php
require_once '../config/database.php';
requireAdmin();

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $userId = (int)$_GET['id'];
    
    try {
        $pdo = getConnection();
        
        // Ambil data anggota
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'user'");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Anggota tidak ditemukan']);
            exit();
        }
        
        // Ambil statistik peminjaman
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE user_id = ?");
        $stmt->execute([$userId]);
        $totalPinjam = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE user_id = ? AND status = 'dipinjam'");
        $stmt->execute([$userId]);
        $sedangPinjam = $stmt->fetchColumn();
        
        echo json_encode([
            'success' => true,
            'nama_lengkap' => $user['nama_lengkap'],
            'username' => $user['username'],
            'email' => $user['email'],
            'foto' => $user['foto'],
            'total_pinjam' => $totalPinjam,
            'sedang_pinjam' => $sedangPinjam
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error database: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID anggota tidak diberikan']);
}
?>
