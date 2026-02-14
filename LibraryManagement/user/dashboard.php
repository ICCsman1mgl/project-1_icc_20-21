

<?php
require_once '../config/database.php';
requireLogin();

$pageTitle = 'Dashboard Anggota';
$cssPath = '../assets/css/style.css';
$jsPath = '../assets/js/script.js';

include '../includes/header.php';

$pdo = getConnection();
$userId = $_SESSION['user_id'];

// Statistik untuk user
$totalPinjam = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE user_id = ?");
$totalPinjam->execute([$userId]);
$totalPinjam = $totalPinjam->fetchColumn();

$sedangPinjam = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE user_id = ? AND status = 'dipinjam'");
$sedangPinjam->execute([$userId]);
$sedangPinjam = $sedangPinjam->fetchColumn();

$sudahKembali = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE user_id = ? AND status = 'dikembalikan'");
$sudahKembali->execute([$userId]);
$sudahKembali = $sudahKembali->fetchColumn();

$terlambat = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE user_id = ? AND status = 'dipinjam' AND tanggal_kembali_rencana < CURDATE()");
$terlambat->execute([$userId]);
$terlambat = $terlambat->fetchColumn();

// Buku yang sedang dipinjam
$bukuDipinjam = $pdo->prepare("SELECT t.*, b.judul, b.pengarang, b.cover, 
                               DATEDIFF(CURDATE(), t.tanggal_kembali_rencana) as hari_terlambat,
                               DATEDIFF(t.tanggal_kembali_rencana, CURDATE()) as hari_tersisa
                               FROM transaksi t 
                               JOIN buku b ON t.buku_id = b.id 
                               WHERE t.user_id = ? AND t.status = 'dipinjam' 
                               ORDER BY t.tanggal_kembali_rencana ASC");
$bukuDipinjam->execute([$userId]);
$bukuDipinjam = $bukuDipinjam->fetchAll();

// Buku rekomendasi (buku populer yang belum dipinjam user)
$rekomendasi = $pdo->prepare("SELECT b.*, COUNT(t.id) as total_dipinjam 
                             FROM buku b 
                             LEFT JOIN transaksi t ON b.id = t.buku_id 
                             WHERE b.status = 'tersedia' AND b.jumlah_tersedia > 0
                             AND b.id NOT IN (
                                 SELECT DISTINCT buku_id FROM transaksi 
                                 WHERE user_id = ? AND status = 'dipinjam'
                             )
                             GROUP BY b.id 
                             ORDER BY total_dipinjam DESC, b.created_at DESC 
                             LIMIT 6");
$rekomendasi->execute([$userId]);
$rekomendasi = $rekomendasi->fetchAll();

// Riwayat terakhir
$riwayatTerakhir = $pdo->prepare("SELECT t.*, b.judul, b.pengarang, b.cover 
                                 FROM transaksi t 
                                 JOIN buku b ON t.buku_id = b.id 
                                 WHERE t.user_id = ? 
                                 ORDER BY t.created_at DESC 
                                 LIMIT 5");
$riwayatTerakhir->execute([$userId]);
$riwayatTerakhir = $riwayatTerakhir->fetchAll();
?>

<?php include '../includes/navbar.php'; ?>

<div class="container-fluid p-4">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <i class="bi bi-house-heart me-2"></i>
                                Selamat Datang, <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>!
                            </h2>
                            <p class="mb-0 opacity-75">
                                <i class="bi bi-calendar3 me-1"></i>
                                <?= date('l, d F Y') ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="katalog.php" class="btn btn-light">
                                    <i class="bi bi-search me-1"></i>Cari Buku
                                </a>
                                <a href="riwayat.php" class="btn btn-outline-light">
                                    <i class="bi bi-clock-history me-1"></i>Riwayat
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="stat-number"><?= $totalPinjam ?></h3>
                            <p class="stat-label">Total Peminjaman</p>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-book"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="stat-number"><?= $sedangPinjam ?></h3>
                            <p class="stat-label">Sedang Dipinjam</p>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-book-half"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="stat-number"><?= $sudahKembali ?></h3>
                            <p class="stat-label">Sudah Dikembalikan</p>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card <?= $terlambat > 0 ? 'bg-danger' : 'bg-info' ?> text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="stat-number"><?= $terlambat ?></h3>
                            <p class="stat-label">Terlambat</p>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert untuk buku terlambat -->
    <?php if ($terlambat > 0): ?>
    <div class="alert alert-danger" role="alert">
        <h6 class="alert-heading">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Perhatian!
        </h6>
        <p class="mb-0">
            Anda memiliki <strong><?= $terlambat ?></strong> buku yang sudah terlambat dikembalikan. 
            Segera kembalikan untuk menghindari denda tambahan.
        </p>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Buku yang Sedang Dipinjam -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-book-half me-2"></i>
                        Buku yang Sedang Dipinjam
                    </h5>
                    <?php if ($sedangPinjam >= 3): ?>
                        <span class="badge bg-warning">Batas Maksimal Tercapai</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($bukuDipinjam)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-book" style="font-size: 3rem; color: #dee2e6;"></i>
                            <h5 class="text-muted mt-3">Tidak Ada Buku yang Dipinjam</h5>
                            <p class="text-muted">Silakan cari dan pinjam buku yang Anda inginkan</p>
                            <a href="katalog.php" class="btn btn-primary">
                                <i class="bi bi-search me-2"></i>Cari Buku
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($bukuDipinjam as $buku): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border-left-<?= $buku['hari_terlambat'] > 0 ? 'danger' : ($buku['hari_tersisa'] <= 3 ? 'warning' : 'primary') ?>">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-3">
                                                <?php if ($buku['cover']): ?>
                                                    <img src="/LibraryManagement/proses/uploads/<?= htmlspecialchars($buku['cover']) ?>" 
                                                         class="book-cover" alt="Cover">
                                                <?php else: ?>
                                                    <div class="book-cover bg-light d-flex align-items-center justify-content-center">
                                                        <i class="bi bi-book text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-9">
                                                <h6 class="card-title mb-1"><?= htmlspecialchars($buku['judul']) ?></h6>
                                                <p class="card-text text-muted small mb-2"><?= htmlspecialchars($buku['pengarang']) ?></p>
                                                
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="bi bi-calendar me-1"></i>
                                                        Pinjam: <?= date('d/m/Y', strtotime($buku['tanggal_pinjam'])) ?>
                                                    </small>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="bi bi-calendar-check me-1"></i>
                                                        Kembali: <?= date('d/m/Y', strtotime($buku['tanggal_kembali_rencana'])) ?>
                                                    </small>
                                                </div>
                                                
                                                <?php if ($buku['hari_terlambat'] > 0): ?>
                                                    <span class="badge bg-danger">
                                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                                        Terlambat <?= $buku['hari_terlambat'] ?> hari
                                                    </span>
                                                <?php elseif ($buku['hari_tersisa'] <= 3): ?>
                                                    <span class="badge bg-warning">
                                                        <i class="bi bi-clock me-1"></i>
                                                        <?= $buku['hari_tersisa'] ?> hari lagi
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle me-1"></i>
                                                        <?= $buku['hari_tersisa'] ?> hari lagi
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Rekomendasi Buku -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-star me-2"></i>
                        Rekomendasi Buku Untuk Anda
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($rekomendasi)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-search" style="font-size: 3rem; color: #dee2e6;"></i>
                            <h5 class="text-muted mt-3">Tidak Ada Rekomendasi</h5>
                            <p class="text-muted">Silakan lihat katalog untuk menemukan buku menarik</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($rekomendasi as $buku): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="text-center pt-3">
                                        <?php if ($buku['cover']): ?>
                                         <img src="/LibraryManagement/proses/uploads/<?= htmlspecialchars($buku['cover']) ?>"
     class="book-cover-large" alt="Cover">

                                        <?php else: ?>
                                            <div class="book-cover-large bg-light d-flex align-items-center justify-content-center mx-auto">
                                                <i class="bi bi-book text-muted" style="font-size: 3rem;"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body text-center">
                                        <h6 class="card-title"><?= htmlspecialchars($buku['judul']) ?></h6>
                                        <p class="card-text text-muted small"><?= htmlspecialchars($buku['pengarang']) ?></p>
                                        <div class="mb-2">
                                            <small class="text-success">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Tersedia: <?= $buku['jumlah_tersedia'] ?>
                                            </small>
                                            <?php if ($buku['total_dipinjam'] > 0): ?>
                                                <br><small class="text-primary">
                                                    <i class="bi bi-star me-1"></i>
                                                    Dipinjam <?= $buku['total_dipinjam'] ?> kali
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <a href="katalog.php?search=<?= urlencode($buku['judul']) ?>" class="btn btn-primary btn-sm">
                                            <i class="bi bi-eye me-1"></i>Lihat Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Profile Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-person me-2"></i>
                        Profil Saya
                    </h6>
                </div>
               <div class="card-body text-center">
    <?php if (!empty($_SESSION['foto'])): ?>
      <img src="/LibraryManagement/proses/uploads/<?= htmlspecialchars($_SESSION['foto']) ?>"
     class="profile-photo-large" alt="Foto Profil">


    <?php else: ?>
        <div class="profile-photo-large bg-light d-flex align-items-center justify-content-center mb-3 mx-auto">
            <i class="bi bi-person text-muted" style="font-size: 3rem;"></i>
        </div>
                    <?php endif; ?>
                    <h6><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></h6>
                    <p class="text-muted mb-3"><?= htmlspecialchars($_SESSION['email']) ?></p>
                    <a href="profile.php" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i>Edit Profil
                    </a>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i>
                        Statistik Cepat
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Batas Pinjam:</span>
                        <span class="fw-bold"><?= $sedangPinjam ?>/3 buku</span>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar <?= $sedangPinjam >= 3 ? 'bg-danger' : 'bg-primary' ?>" 
                             style="width: <?= ($sedangPinjam / 3) * 100 ?>%"></div>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Pinjam:</span>
                        <span class="fw-bold"><?= $totalPinjam ?> buku</span>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <span>Status:</span>
                        <span class="badge <?= $terlambat > 0 ? 'bg-danger' : 'bg-success' ?>">
                            <?= $terlambat > 0 ? 'Ada Keterlambatan' : 'Baik' ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Riwayat Terakhir -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        Aktivitas Terakhir
                    </h6>
                    <a href="riwayat.php" class="btn btn-sm btn-outline-primary">
                        Lihat Semua
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($riwayatTerakhir)): ?>
                        <div class="text-center py-3">
                            <i class="bi bi-clock-history text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2 mb-0">Belum ada aktivitas</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($riwayatTerakhir as $index => $riwayat): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-2">
                                <?php if ($riwayat['cover']): ?>
                                    <img src="\LibraryManagement\proses\uploads\<?= htmlspecialchars($riwayat['cover']) ?>" 
                                         class="book-cover" alt="Cover">
                                <?php else: ?>
                                    <div class="book-cover bg-light d-flex align-items-center justify-content-center">
                                        <i class="bi bi-book text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 small"><?= htmlspecialchars($riwayat['judul']) ?></h6>
                                <small class="text-muted">
                                    <?= htmlspecialchars($riwayat['pengarang']) ?>
                                </small>
                                <br>
                                <small class="text-muted">
                                    <i class="bi bi-calendar me-1"></i>
                                    <?= date('d/m/Y', strtotime($riwayat['created_at'])) ?>
                                </small>
                                <span class="badge badge-sm <?= $riwayat['status'] == 'dikembalikan' ? 'bg-success' : 'bg-warning' ?>">
                                    <?= ucfirst($riwayat['status']) ?>
                                </span>
                            </div>
                        </div>
                        <?php if ($index < count($riwayatTerakhir) - 1): ?>
                            <hr class="my-2">
                        <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
