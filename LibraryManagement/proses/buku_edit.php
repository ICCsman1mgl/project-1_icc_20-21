<?php
require_once '../config/database.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int)$_POST['id'];
    $kode_buku = cleanInput($_POST['kode_buku']);
    $judul = cleanInput($_POST['judul']);
    $pengarang = cleanInput($_POST['pengarang']);
    $penerbit = cleanInput($_POST['penerbit']);
    $tahun_terbit = !empty($_POST['tahun_terbit']) ? (int)$_POST['tahun_terbit'] : null;
    $isbn = cleanInput($_POST['isbn']);
    $kategori_id = (int)$_POST['kategori_id'];
    $jumlah_total = (int)$_POST['jumlah_total'];
    $lokasi_rak = cleanInput($_POST['lokasi_rak']);
    $status = cleanInput($_POST['status']);
    $deskripsi = cleanInput($_POST['deskripsi']);
    
    // Validasi required fields
    if (empty($kode_buku) || empty($judul) || empty($pengarang) || empty($kategori_id) || $jumlah_total <= 0) {
        $_SESSION['error'] = 'Harap lengkapi semua field yang wajib diisi!';
        header('Location: ../admin/buku/edit.php?id=' . $id);
        exit();
    }
    
    try {
        $pdo = getConnection();
        
        // Ambil data buku lama
        $stmt = $pdo->prepare("SELECT * FROM buku WHERE id = ?");
        $stmt->execute([$id]);
        $bukuLama = $stmt->fetch();
        
        if (!$bukuLama) {
            $_SESSION['error'] = 'Buku tidak ditemukan!';
            header('Location: ../admin/dashboard.php');
            exit();
        }
        
        // Cek apakah kode buku sudah ada (kecuali untuk buku ini sendiri)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM buku WHERE kode_buku = ? AND id != ?");
        $stmt->execute([$kode_buku, $id]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = 'Kode buku sudah digunakan! Gunakan kode yang lain.';
            header('Location: ../admin/buku/edit.php?id=' . $id);
            exit();
        }
        
        // Hitung jumlah yang sedang dipinjam
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE buku_id = ? AND status = 'dipinjam'");
        $stmt->execute([$id]);
        $sedangDipinjam = $stmt->fetchColumn();
        
        // Validasi jumlah total tidak boleh kurang dari yang sedang dipinjam
        if ($jumlah_total < $sedangDipinjam) {
            $_SESSION['error'] = 'Jumlah total tidak boleh kurang dari jumlah yang sedang dipinjam (' . $sedangDipinjam . ')!';
            header('Location: ../admin/buku/edit.php?id=' . $id);
            exit();
        }
        
        // Hitung jumlah tersedia baru
        $jumlah_tersedia = $jumlah_total - $sedangDipinjam;
        
        // Handle upload cover baru
        $coverFilename = $bukuLama['cover']; // Keep old cover by default
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadFile($_FILES['cover'], 'covers');
            if ($uploadResult['success']) {
                // Hapus cover lama jika ada
                if ($bukuLama['cover'] && file_exists('../uploads/' . $bukuLama['cover'])) {
                    unlink('../uploads/' . $bukuLama['cover']);
                }
                $coverFilename = $uploadResult['filename'];
            } else {
                $_SESSION['error'] = 'Error upload cover: ' . $uploadResult['message'];
                header('Location: ../admin/buku/edit.php?id=' . $id);
                exit();
            }
        }
        
        // Update data buku
        $stmt = $pdo->prepare("UPDATE buku SET 
            kode_buku = ?, judul = ?, pengarang = ?, penerbit = ?, tahun_terbit = ?, 
            isbn = ?, kategori_id = ?, jumlah_total = ?, jumlah_tersedia = ?, 
            cover = ?, deskripsi = ?, lokasi_rak = ?, status = ?, updated_at = NOW()
            WHERE id = ?");
        
        $stmt->execute([
            $kode_buku, $judul, $pengarang, $penerbit, $tahun_terbit, $isbn,
            $kategori_id, $jumlah_total, $jumlah_tersedia, $coverFilename, $deskripsi,
            $lokasi_rak, $status, $id
        ]);
        
        $_SESSION['success'] = 'Buku "' . $judul . '" berhasil diperbarui!';
        header('Location: ../admin/dashboard.php');
        exit();
        
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Gagal memperbarui buku: ' . $e->getMessage();
        header('Location: ../admin/buku/edit.php?id=' . $id);
        exit();
    }
} else {
    header('Location: ../admin/dashboard.php');
    exit();
}
?>
