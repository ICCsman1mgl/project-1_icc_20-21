<?php
require_once '../config/database.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $transaksi_id = (int)$_POST['transaksi_id'];
    $tanggal_kembali_aktual = cleanInput($_POST['tanggal_kembali_aktual']);
    $denda = (int)$_POST['denda'];
    $catatan = cleanInput($_POST['catatan']);
    
    // Validasi required fields
    if (empty($transaksi_id) || empty($tanggal_kembali_aktual)) {
        $_SESSION['error'] = 'Harap lengkapi semua field yang wajib diisi!';
        header('Location: ../admin/transaksi/kembali.php');
        exit();
    }
    
    try {
        $pdo = getConnection();
        $pdo->beginTransaction();
        
        // Ambil data transaksi
        $stmt = $pdo->prepare("SELECT t.*, u.nama_lengkap, b.judul 
                               FROM transaksi t 
                               JOIN users u ON t.user_id = u.id 
                               JOIN buku b ON t.buku_id = b.id 
                               WHERE t.id = ? AND t.status = 'dipinjam'");
        $stmt->execute([$transaksi_id]);
        $transaksi = $stmt->fetch();
        
        if (!$transaksi) {
            throw new Exception('Transaksi tidak ditemukan atau sudah dikembalikan!');
        }
        
        // Validasi tanggal kembali aktual tidak boleh sebelum tanggal pinjam
        if ($tanggal_kembali_aktual < $transaksi['tanggal_pinjam']) {
            throw new Exception('Tanggal kembali aktual tidak boleh sebelum tanggal pinjam!');
        }
        
        // Hitung denda otomatis jika ada keterlambatan
        $tglKembaliRencana = new DateTime($transaksi['tanggal_kembali_rencana']);
        $tglKembaliAktual = new DateTime($tanggal_kembali_aktual);
        
        $hariTerlambat = 0;
        $dendaOtomatis = 0;
        
        if ($tglKembaliAktual > $tglKembaliRencana) {
            $hariTerlambat = $tglKembaliRencana->diff($tglKembaliAktual)->days;
            $dendaOtomatis = $hariTerlambat * 1000; // Rp 1000 per hari
        }
        
        // Gunakan denda otomatis jika lebih besar dari input manual
        $dendaFinal = max($denda, $dendaOtomatis);
        
        // Update transaksi
        $stmt = $pdo->prepare("UPDATE transaksi SET 
                               tanggal_kembali_aktual = ?, 
                               denda = ?, 
                               status = 'dikembalikan', 
                               catatan = CONCAT(COALESCE(catatan, ''), ?, ' | Dikembalikan oleh admin pada ', NOW()),
                               updated_at = NOW()
                               WHERE id = ?");
        
        $catatanUpdate = !empty($catatan) ? " | Catatan pengembalian: " . $catatan : "";
        $stmt->execute([$tanggal_kembali_aktual, $dendaFinal, $catatanUpdate, $transaksi_id]);
        
        // Insert record denda jika ada
        if ($dendaFinal > 0) {
            $stmt = $pdo->prepare("INSERT INTO denda (
                transaksi_id, jumlah_hari, tarif_per_hari, total_denda, status
            ) VALUES (?, ?, 1000, ?, 'belum_bayar')");
            
            $stmt->execute([$transaksi_id, $hariTerlambat, $dendaFinal]);
        }
        
        // Update jumlah tersedia buku
        $stmt = $pdo->prepare("UPDATE buku SET jumlah_tersedia = jumlah_tersedia + 1 WHERE id = ?");
        $stmt->execute([$transaksi['buku_id']]);
        
        $pdo->commit();
        
        $successMessage = 'Buku "' . $transaksi['judul'] . '" dari ' . $transaksi['nama_lengkap'] . ' berhasil dikembalikan!';
        
        if ($dendaFinal > 0) {
            $successMessage .= ' Denda: Rp ' . number_format($dendaFinal, 0, ',', '.');
        }
        
        $_SESSION['success'] = $successMessage;
        header('Location: ../admin/transaksi/index.php');
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = $e->getMessage();
        header('Location: ../admin/transaksi/kembali.php');
        exit();
    }
} else {
    header('Location: ../admin/transaksi/kembali.php');
    exit();
}
?>
