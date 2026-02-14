<?php
require_once '../../config/database.php';
requireAdmin();

// Ambil ID buku dari parameter
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    $_SESSION['error'] = 'ID buku tidak valid';
    header('Location: ../dashboard.php');
    exit();
}

$pdo = getConnection();

// Cek apakah buku sedang dipinjam
$stmt = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE buku_id = ? AND status = 'dipinjam'");
$stmt->execute([$id]);
$sedangDipinjam = $stmt->fetchColumn();

if ($sedangDipinjam > 0) {
    $_SESSION['error'] = 'Buku tidak dapat dihapus karena sedang dipinjam oleh anggota.';
    header('Location: ../dashboard.php');
    exit();
}

// Ambil data buku untuk mendapatkan file cover
$stmt = $pdo->prepare("SELECT * FROM buku WHERE id = ?");
$stmt->execute([$id]);
$buku = $stmt->fetch();

if (!$buku) {
    $_SESSION['error'] = 'Buku tidak ditemukan';
    header('Location: ../dashboard.php');
    exit();
}

try {
    // Jika cover tidak kosong
    if (!empty($buku['cover'])) {
        $coverPath = '../../uploads/' . $buku['cover']; 
        // Cek apakah file memang ada sebelum dihapus
        if (file_exists($coverPath)) {
            unlink($coverPath);
        }
    }

    // Hapus data buku dari database
    $stmt = $pdo->prepare("DELETE FROM buku WHERE id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['success'] = 'Buku "' . htmlspecialchars($buku['judul']) . '" berhasil dihapus';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Gagal menghapus buku: ' . $e->getMessage();
}

header('Location: ../dashboard.php');
exit();
?>
