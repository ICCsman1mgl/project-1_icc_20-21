<?php
require_once '../config/database.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
        header('Location: ../admin/buku/tambah.php');
        exit();
    }
    
    try {
        $pdo = getConnection();
        
        // Cek apakah kode buku sudah ada
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM buku WHERE kode_buku = ?");
        $stmt->execute([$kode_buku]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = 'Kode buku sudah digunakan! Gunakan kode yang lain.';
            header('Location: ../admin/buku/tambah.php');
            exit();
        }
        
        // Handle upload cover
        $coverFilename = null;
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadFile($_FILES['cover'], 'covers');
            if ($uploadResult['success']) {
                $coverFilename = $uploadResult['filename'];
            } else {
                $_SESSION['error'] = 'Error upload cover: ' . $uploadResult['message'];
                header('Location: ../admin/buku/tambah.php');
                exit();
            }
        }
        
        // Insert data buku
        $stmt = $pdo->prepare("INSERT INTO buku (
            kode_buku, judul, pengarang, penerbit, tahun_terbit, isbn, 
            kategori_id, jumlah_total, jumlah_tersedia, cover, deskripsi, 
            lokasi_rak, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $kode_buku, $judul, $pengarang, $penerbit, $tahun_terbit, $isbn,
            $kategori_id, $jumlah_total, $jumlah_total, $coverFilename, $deskripsi,
            $lokasi_rak, $status
        ]);
        
        $_SESSION['success'] = 'Buku "' . $judul . '" berhasil ditambahkan!';
        header('Location: ../admin/dashboard.php');
        exit();
        
    } catch (PDOException $e) {
        // Hapus file yang sudah diupload jika ada error
        if ($coverFilename && file_exists('../uploads/' . $coverFilename)) {
            unlink('../uploads/' . $coverFilename);
        }
        
        $_SESSION['error'] = 'Gagal menambah buku: ' . $e->getMessage();
        header('Location: ../admin/buku/tambah.php');
        exit();
    }
} else {
    header('Location: ../admin/buku/tambah.php');
    exit();
}
?>
