<?php

require_once '../config/database.php';
requireAdmin();

$pageTitle = 'Dashboard Admin';
$cssPath = '../assets/css/style.css';
$jsPath = '../assets/js/script.js';
$includeCharts = true;

include '../includes/header.php';

// Ambil statistik
$pdo = getConnection();

// Total buku
$totalBuku = $pdo->query("SELECT COUNT(*) FROM buku")->fetchColumn();

// Total anggota
$totalAnggota = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();

// Buku dipinjam
$bukuDipinjam = $pdo->query("SELECT COUNT(*) FROM transaksi WHERE status = 'dipinjam'")->fetchColumn();

// Transaksi hari ini
$transaksiHariIni = $pdo->query("SELECT COUNT(*) FROM transaksi WHERE DATE(created_at) = CURDATE()")->fetchColumn();

// Buku akan jatuh tempo (3 hari ke depan)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE status = 'dipinjam' AND DATEDIFF(tanggal_kembali_rencana, CURDATE()) <= 3 AND DATEDIFF(tanggal_kembali_rencana, CURDATE()) >= 0");
$stmt->execute();
$bukuJatuhTempo = $stmt->fetchColumn();

// Buku terlambat
$bukuTerlambat = $pdo->query("SELECT COUNT(*) FROM transaksi WHERE status = 'dipinjam' AND tanggal_kembali_rencana < CURDATE()")->fetchColumn();

// Data untuk chart
$chartData = $pdo->query("
    SELECT DATE(created_at) as tanggal, COUNT(*) as jumlah 
    FROM transaksi 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
    GROUP BY DATE(created_at) 
    ORDER BY tanggal
")->fetchAll();

// Transaksi terbaru
$transaksiTerbaru = $pdo->query("
    SELECT t.*, u.nama_lengkap, b.judul, b.cover 
    FROM transaksi t 
    JOIN users u ON t.user_id = u.id 
    JOIN buku b ON t.buku_id = b.id 
    ORDER BY t.created_at DESC 
    LIMIT 5
")->fetchAll();

// Buku populer
$bukuPopuler = $pdo->query("
    SELECT b.*, COUNT(t.id) as total_dipinjam 
    FROM buku b 
    LEFT JOIN transaksi t ON b.id = t.buku_id 
    GROUP BY b.id 
    ORDER BY total_dipinjam DESC 
    LIMIT 5
")->fetchAll();
?>

<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar bg-primary text-white" style="width: 250px; min-height: 100vh;">
        <div class="p-3">
            <h5><i class="bi bi-speedometer2 me-2"></i>Admin Panel</h5>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link active text-white" href="dashboard.php">
                <i class="bi bi-house-door"></i> Dashboard
            </a>
            <a class="nav-link text-white-50" href="buku/tambah.php">
                <i class="bi bi-plus-circle"></i> Tambah Buku
            </a>
            <a class="nav-link text-white-50" href="anggota/index.php">
                <i class="bi bi-people"></i> Kelola Anggota
            </a>
            <a class="nav-link text-white-50" href="transaksi/index.php">
                <i class="bi bi-arrow-left-right"></i> Transaksi
            </a>
            <a class="nav-link text-white-50" href="laporan/index.php">
                <i class="bi bi-file-earmark-text"></i> Laporan
            </a>
            <a class="nav-link" href="/LibraryManagement/admin/anggota/import.php">
                <i class="fas fa-file-import"></i> Import Anggota
            </a>
            <a class="nav-link text-white-50" href="transaksi/scan_nis.php">
                <i class="bi bi-qr-code-scan"></i> Scan NIS (QR)
            </a>

        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-grow-1">
        <?php include '../includes/navbar.php'; ?>

        <div class="container-fluid p-4">
            <!-- Welcome Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">Selamat Datang, <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>!</h2>
                            <p class="text-muted mb-0">
                                <i class="bi bi-calendar3 me-1"></i>
                                <?= date('l, d F Y') ?>
                            </p>
                        </div>
                        <div>
                            <button class="btn btn-primary" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                            </button>
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
                                    <h3 class="stat-number"><?= $totalBuku ?></h3>
                                    <p class="stat-label">Total Buku</p>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-book"></i>
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
                                    <h3 class="stat-number"><?= $totalAnggota ?></h3>
                                    <p class="stat-label">Total Anggota</p>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-people"></i>
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
                                    <h3 class="stat-number"><?= $bukuDipinjam ?></h3>
                                    <p class="stat-label">Buku Dipinjam</p>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-book-half"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="stat-number"><?= $transaksiHariIni ?></h3>
                                    <p class="stat-label">Transaksi Hari Ini</p>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-graph-up"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert Cards -->
            <?php if ($bukuJatuhTempo > 0 || $bukuTerlambat > 0): ?>
                <div class="row mb-4">
                    <?php if ($bukuJatuhTempo > 0): ?>
                        <div class="col-md-6">
                            <div class="alert alert-warning" role="alert">
                                <h6 class="alert-heading">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Perhatian!
                                </h6>
                                <p class="mb-0">
                                    Ada <strong><?= $bukuJatuhTempo ?></strong> buku yang akan jatuh tempo dalam 3 hari ke depan.
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($bukuTerlambat > 0): ?>
                        <div class="col-md-6">
                            <div class="alert alert-danger" role="alert">
                                <h6 class="alert-heading">
                                    <i class="bi bi-exclamation-circle me-2"></i>
                                    Terlambat!
                                </h6>
                                <p class="mb-0">
                                    Ada <strong><?= $bukuTerlambat ?></strong> buku yang sudah terlambat dikembalikan.
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Charts and Recent Activities -->
            <div class="row mb-4">
                <!-- Chart -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-graph-up me-2"></i>
                                Statistik Transaksi (7 Hari Terakhir)
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="transactionChart" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-lightning me-2"></i>
                                Aksi Cepat
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="buku/tambah.php" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>
                                    Tambah Buku
                                </a>
                                <a href="anggota/tambah.php" class="btn btn-success">
                                    <i class="bi bi-person-plus me-2"></i>
                                    Tambah Anggota
                                </a>
                                <a href="transaksi/pinjam.php" class="btn btn-warning">
                                    <i class="bi bi-box-arrow-up me-2"></i>
                                    Pinjam Buku
                                </a>
                                <a href="transaksi/kembali.php" class="btn btn-info">
                                    <i class="bi bi-box-arrow-down me-2"></i>
                                    Kembalikan Buku
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions and Popular Books -->
            <div class="row">
                <!-- Recent Transactions -->
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-clock-history me-2"></i>
                                Transaksi Terbaru
                            </h5>
                            <a href="transaksi/index.php" class="btn btn-sm btn-outline-primary">
                                Lihat Semua
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($transaksiTerbaru)): ?>
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mt-2">Belum ada transaksi</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Anggota</th>
                                                <th>Buku</th>
                                                <th>Tanggal</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($transaksiTerbaru as $transaksi): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="me-2">
                                                                <i class="bi bi-person-circle" style="font-size: 1.5rem;"></i>
                                                            </div>
                                                            <div>
                                                                <strong><?= htmlspecialchars($transaksi['nama_lengkap']) ?></strong>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <?php if (!empty($transaksi['cover'])): ?>
                                                                <img src="/LibraryManagement/proses/uploads/<?= htmlspecialchars($transaksi['cover']) ?>"
                                                                    class="book-cover me-2" alt="<?= htmlspecialchars($transaksi['judul']) ?>">
                                                            <?php else: ?>
                                                                <div class="book-cover me-2 bg-light d-flex align-items-center justify-content-center">
                                                                    <i class="bi bi-book text-muted"></i>
                                                                </div>
                                                            <?php endif; ?>

                                                            <div>
                                                                <small class="fw-bold"><?= htmlspecialchars($transaksi['judul']) ?></small>
                                                            </div>
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <small><?= date('d/m/Y', strtotime($transaksi['created_at'])) ?></small>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $badgeClass = '';
                                                        switch ($transaksi['status']) {
                                                            case 'dipinjam':
                                                                $badgeClass = 'bg-warning';
                                                                break;
                                                            case 'dikembalikan':
                                                                $badgeClass = 'bg-success';
                                                                break;
                                                            case 'terlambat':
                                                                $badgeClass = 'bg-danger';
                                                                break;
                                                        }
                                                        ?>
                                                        <span class="badge <?= $badgeClass ?>"><?= ucfirst($transaksi['status']) ?></span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Popular Books -->
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-star me-2"></i>
                                Buku Populer
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($bukuPopuler)): ?>
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-book" style="font-size: 3rem;"></i>
                                    <p class="mt-2">Belum ada data</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($bukuPopuler as $index => $buku): ?>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="me-3">
                                            <span class="badge bg-primary fs-6"><?= $index + 1 ?></span>
                                        </div>
                                        <div class="me-3">
                                            <?php if ($buku['cover']): ?>
                                                <img src="/LibraryManagement/proses/uploads/<?= htmlspecialchars($buku['cover']) ?>"
                                                    alt="<?= htmlspecialchars($buku['judul']) ?>">

                                            <?php else: ?>
                                                <div class="book-cover bg-light d-flex align-items-center justify-content-center">
                                                    <i class="bi bi-book text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?= htmlspecialchars($buku['judul']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($buku['pengarang']) ?></small>
                                            <br>
                                            <small class="text-primary">
                                                <i class="bi bi-graph-up me-1"></i>
                                                <?= $buku['total_dipinjam'] ?> kali dipinjam
                                            </small>
                                        </div>
                                    </div>
                                    <?php if ($index < count($bukuPopuler) - 1): ?>
                                        <hr class="my-2">
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Chart configuration
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('transactionChart').getContext('2d');

        const chartData = <?= json_encode($chartData) ?>;
        const labels = chartData.map(item => {
            const date = new Date(item.tanggal);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short'
            });
        });
        const data = chartData.map(item => item.jumlah);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Transaksi',
                    data: data,
                    borderColor: 'rgb(13, 110, 253)',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    });
</script>

<?php include '../includes/footer.php'; ?>