<?php
require_once '../../config/database.php';
requireAdmin();

// Ambil ID anggota dari parameter
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    $_SESSION['error'] = 'ID anggota tidak valid';
    header('Location: index.php');
    exit();
}

$pdo = getConnection();

// Cek apakah anggota sedang meminjam buku
$stmt = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE user_id = ? AND status = 'dipinjam'");
$stmt->execute([$id]);
$sedangMeminjam = $stmt->fetchColumn();

if ($sedangMeminjam > 0) {
    $_SESSION['error'] = 'Anggota tidak dapat dihapus karena masih meminjam buku.';
    header('Location: index.php');
    exit();
}

// Ambil data anggota untuk mendapatkan file foto
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'user'");
$stmt->execute([$id]);
$anggota = $stmt->fetch();

if (!$anggota) {
    $_SESSION['error'] = 'Anggota tidak ditemukan';
    header('Location: index.php');
    exit();
}

try {
    // Hapus file foto jika ada
    if (!empty($anggota['foto'])) {
        $fotoPath = rtrim(UPLOAD_DIR, "/\\") . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $anggota['foto']);
        if (file_exists($fotoPath)) {
            unlink($fotoPath);
        }
    }
    
    // Hapus data anggota dari database
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['success'] = 'Anggota "' . $anggota['nama_lengkap'] . '" berhasil dihapus';
    
} catch (PDOException $e) {
    $_SESSION['error'] = 'Gagal menghapus anggota. Silakan coba lagi.';
}

header('Location: index.php');
exit();
?>
