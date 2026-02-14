<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_transaksi = cleanInput($_POST['kode_transaksi']);
    $user_id = (int)$_POST['user_id'];
    $buku_id = (int)$_POST['buku_id'];
    $tanggal_pinjam = cleanInput($_POST['tanggal_pinjam']);
    $tanggal_kembali_rencana = cleanInput($_POST['tanggal_kembali_rencana']);
    $catatan = cleanInput($_POST['catatan']);
    $redirect = isset($_POST['redirect']) ? cleanInput($_POST['redirect']) : 'admin';
    
    // Determine who is making the request
    $isAdmin = isAdmin();
    $currentUserId = $_SESSION['user_id'];
    
    // If not admin, ensure user can only create their own transactions
    if (!$isAdmin && $user_id != $currentUserId) {
        $_SESSION['error'] = 'Akses ditolak!';
        header('Location: ../user/katalog.php');
        exit();
    }
    
    // Validasi required fields
    if (empty($kode_transaksi) || empty($user_id) || empty($buku_id) || empty($tanggal_pinjam) || empty($tanggal_kembali_rencana)) {
        $_SESSION['error'] = 'Harap lengkapi semua field yang wajib diisi!';
        $redirectUrl = $isAdmin ? '../admin/transaksi/pinjam.php' : '../user/pinjam.php?buku_id=' . $buku_id;
        header('Location: ' . $redirectUrl);
        exit();
    }
    
    // Validasi tanggal
    $tglPinjam = new DateTime($tanggal_pinjam);
    $tglKembali = new DateTime($tanggal_kembali_rencana);
    $today = new DateTime();
    
    if ($tglPinjam > $tglKembali) {
        $_SESSION['error'] = 'Tanggal kembali tidak boleh sebelum tanggal pinjam!';
        $redirectUrl = $isAdmin ? '../admin/transaksi/pinjam.php' : '../user/pinjam.php?buku_id=' . $buku_id;
        header('Location: ' . $redirectUrl);
        exit();
    }
    
    $lamaPinjam = $tglPinjam->diff($tglKembali)->days;
    if ($lamaPinjam > 14) {
        $_SESSION['error'] = 'Lama peminjaman maksimal 14 hari!';
        $redirectUrl = $isAdmin ? '../admin/transaksi/pinjam.php' : '../user/pinjam.php?buku_id=' . $buku_id;
        header('Location: ' . $redirectUrl);
        exit();
    }
    
    try {
        $pdo = getConnection();
        $pdo->beginTransaction();
        
        // Cek apakah kode transaksi sudah ada
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE kode_transaksi = ?");
        $stmt->execute([$kode_transaksi]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('Kode transaksi sudah digunakan! Refresh halaman untuk mendapat kode baru.');
        }
        
        // Cek status anggota
        $stmt = $pdo->prepare("SELECT status, nama_lengkap FROM users WHERE id = ? AND role = 'user'");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new Exception('Anggota tidak ditemukan!');
        }
        
        if ($user['status'] !== 'aktif') {
            throw new Exception('Anggota sedang tidak aktif!');
        }
        
        // Cek jumlah buku yang sedang dipinjam user
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE user_id = ? AND status = 'dipinjam'");
        $stmt->execute([$user_id]);
        $jumlahDipinjam = $stmt->fetchColumn();
        
        if ($jumlahDipinjam >= 3) {
            throw new Exception('Anggota sudah mencapai batas maksimal peminjaman (3 buku)!');
        }
        
        // Cek apakah user sudah meminjam buku ini
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE user_id = ? AND buku_id = ? AND status = 'dipinjam'");
        $stmt->execute([$user_id, $buku_id]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('Anggota sudah meminjam buku ini!');
        }
        
        // Cek ketersediaan buku
        $stmt = $pdo->prepare("SELECT judul, jumlah_tersedia, status FROM buku WHERE id = ?");
        $stmt->execute([$buku_id]);
        $buku = $stmt->fetch();
        
        if (!$buku) {
            throw new Exception('Buku tidak ditemukan!');
        }
        
        if ($buku['status'] !== 'tersedia') {
            throw new Exception('Buku sedang tidak tersedia!');
        }
        
        if ($buku['jumlah_tersedia'] <= 0) {
            throw new Exception('Stok buku habis!');
        }
        
        // Insert transaksi
        $stmt = $pdo->prepare("INSERT INTO transaksi (
            kode_transaksi, user_id, buku_id, tanggal_pinjam, tanggal_kembali_rencana, 
            status, catatan, created_by
        ) VALUES (?, ?, ?, ?, ?, 'dipinjam', ?, ?)");
        
        $stmt->execute([
            $kode_transaksi, $user_id, $buku_id, $tanggal_pinjam, $tanggal_kembali_rencana,
            $catatan, $currentUserId
        ]);
        
        // Update jumlah tersedia buku
        $stmt = $pdo->prepare("UPDATE buku SET jumlah_tersedia = jumlah_tersedia - 1 WHERE id = ?");
        $stmt->execute([$buku_id]);
        
        $pdo->commit();
        
        $_SESSION['success'] = 'Peminjaman buku "' . $buku['judul'] . '" untuk ' . $user['nama_lengkap'] . ' berhasil diproses!';
        
        // Redirect based on user type
        if ($redirect === 'user') {
            header('Location: ../user/riwayat.php');
        } else {
            header('Location: ../admin/transaksi/index.php');
        }
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = $e->getMessage();
        
        if ($redirect === 'user') {
            header('Location: ../user/pinjam.php?buku_id=' . $buku_id);
        } else {
            header('Location: ../admin/transaksi/pinjam.php');
        }
        exit();
    }
} else {
    header('Location: ../index.php');
    exit();
}
?>
