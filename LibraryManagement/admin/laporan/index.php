<?php
require_once '../../config/database.php';
requireAdmin();

$pageTitle = 'Laporan';
$cssPath = '../../assets/css/style.css';
$jsPath = '../../assets/js/script.js';
$includeCharts = true;

include '../../includes/header.php';

$pdo = getConnection();

// Parameter filter
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('n');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');
$jenis_laporan = isset($_GET['jenis']) ? cleanInput($_GET['jenis']) : 'transaksi';

// Data statistik umum
$totalBuku = $pdo->query("SELECT COUNT(*) FROM buku")->fetchColumn();
$totalAnggota = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$bukuDipinjam = $pdo->query("SELECT COUNT(*) FROM transaksi WHERE status = 'dipinjam'")->fetchColumn();
$totalTransaksi = $pdo->query("SELECT COUNT(*) FROM transaksi")->fetchColumn();

// Data transaksi bulanan
$stmt = $pdo->prepare("SELECT 
    COUNT(*) as total_transaksi,
    COUNT(CASE WHEN status = 'dipinjam' THEN 1 END) as sedang_dipinjam,
    COUNT(CASE WHEN status = 'dikembalikan' THEN 1 END) as sudah_dikembalikan,
    COUNT(CASE WHEN status = 'dipinjam' AND tanggal_kembali_rencana < CURDATE() THEN 1 END) as terlambat
    FROM transaksi 
    WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?");
$stmt->execute([$bulan, $tahun]);
$statsBulanan = $stmt->fetch();

// Data untuk chart transaksi harian dalam bulan
$stmt = $pdo->prepare("SELECT 
    DAY(created_at) as hari,
    COUNT(*) as jumlah
    FROM transaksi 
    WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?
    GROUP BY DAY(created_at)
    ORDER BY hari");
$stmt->execute([$bulan, $tahun]);
$chartHarian = $stmt->fetchAll();

// Data buku populer
$bukuPopuler = $pdo->query("SELECT 
    b.judul, b.pengarang, b.cover,
    COUNT(t.id) as total_dipinjam
    FROM buku b 
    LEFT JOIN transaksi t ON b.id = t.buku_id 
    GROUP BY b.id 
    ORDER BY total_dipinjam DESC 
    LIMIT 10")->fetchAll();

// Data anggota aktif
$anggotaAktif = $pdo->query("SELECT 
    u.nama_lengkap, u.username,
    COUNT(t.id) as total_pinjam
    FROM users u 
    LEFT JOIN transaksi t ON u.id = t.user_id 
    WHERE u.role = 'user' AND u.status = 'aktif'
    GROUP BY u.id 
    ORDER BY total_pinjam DESC 
    LIMIT 10")->fetchAll();

// Data keterlambatan
$stmt = $pdo->prepare("SELECT 
    u.nama_lengkap, b.judul,
    t.tanggal_kembali_rencana,
    DATEDIFF(CURDATE(), t.tanggal_kembali_rencana) as hari_terlambat
    FROM transaksi t
    JOIN users u ON t.user_id = u.id
    JOIN buku b ON t.buku_id = b.id
    WHERE t.status = 'dipinjam' AND t.tanggal_kembali_rencana < CURDATE()
    ORDER BY hari_terlambat DESC");
$stmt->execute();
$dataKeterlambatan = $stmt->fetchAll();
?>

<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar bg-primary text-white" style="width: 250px; min-height: 100vh;">
        <div class="p-3">
            <h5><i class="bi bi-speedometer2 me-2"></i>Admin Panel</h5>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link text-white-50" href="../dashboard.php">
                <i class="bi bi-house-door"></i> Dashboard
            </a>
            <a class="nav-link text-white-50" href="../buku/tambah.php">
                <i class="bi bi-plus-circle"></i> Tambah Buku
            </a>
            <a class="nav-link text-white-50" href="../anggota/index.php">
                <i class="bi bi-people"></i> Kelola Anggota
            </a>
            <a class="nav-link text-white-50" href="../transaksi/index.php">
                <i class="bi bi-arrow-left-right"></i> Transaksi
            </a>
            <a class="nav-link active text-white" href="index.php">
                <i class="bi bi-file-earmark-text"></i> Laporan
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-grow-1">
        <?php include '../../includes/navbar.php'; ?>
        
        <div class="container-fluid p-4">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">Laporan Perpustakaan</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Laporan</li>
                                </ol>
                            </nav>
                        </div>
                        <div>
                            <button class="btn btn-success" onclick="printReport()">
                                <i class="bi bi-printer me-2"></i>
                                Cetak Laporan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="jenis" class="form-label">Jenis Laporan</label>
                            <select class="form-select" id="jenis" name="jenis">
                                <option value="transaksi" <?= ($jenis_laporan == 'transaksi') ? 'selected' : '' ?>>Transaksi</option>
                                <option value="buku" <?= ($jenis_laporan == 'buku') ? 'selected' : '' ?>>Buku Populer</option>
                                <option value="anggota" <?= ($jenis_laporan == 'anggota') ? 'selected' : '' ?>>Anggota Aktif</option>
                                <option value="keterlambatan" <?= ($jenis_laporan == 'keterlambatan') ? 'selected' : '' ?>>Keterlambatan</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="bulan" class="form-label">Bulan</label>
                            <select class="form-select" id="bulan" name="bulan">
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?= $i ?>" <?= ($bulan == $i) ? 'selected' : '' ?>>
                                        <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="tahun" class="form-label">Tahun</label>
                            <select class="form-select" id="tahun" name="tahun">
                                <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
                                    <option value="<?= $i ?>" <?= ($tahun == $i) ? 'selected' : '' ?>><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-funnel me-2"></i>Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistik Umum -->
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
                    <div class="card stat-card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="stat-number"><?= $totalTransaksi ?></h3>
                                    <p class="stat-label">Total Transaksi</p>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-graph-up"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Laporan Berdasarkan Jenis -->
            <?php if ($jenis_laporan == 'transaksi'): ?>
                <!-- Chart Transaksi Harian -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-graph-up me-2"></i>
                                    Transaksi Harian - <?= date('F Y', mktime(0, 0, 0, $bulan, 1, $tahun)) ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="chartHarian" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Statistik Bulanan</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h4 class="text-primary"><?= $statsBulanan['total_transaksi'] ?></h4>
                                        <small class="text-muted">Total Transaksi</small>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-warning"><?= $statsBulanan['sedang_dipinjam'] ?></h4>
                                        <small class="text-muted">Sedang Dipinjam</small>
                                    </div>
                                    <div class="col-6 mt-3">
                                        <h4 class="text-success"><?= $statsBulanan['sudah_dikembalikan'] ?></h4>
                                        <small class="text-muted">Sudah Dikembalikan</small>
                                    </div>
                                    <div class="col-6 mt-3">
                                        <h4 class="text-danger"><?= $statsBulanan['terlambat'] ?></h4>
                                        <small class="text-muted">Terlambat</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif ($jenis_laporan == 'buku'): ?>
                <!-- Buku Populer -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-star me-2"></i>
                            Buku Paling Populer
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Ranking</th>
                                        <th>Buku</th>
                                        <th>Pengarang</th>
                                        <th>Total Dipinjam</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bukuPopuler as $index => $buku): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary fs-6"><?= $index + 1 ?></span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($buku['cover']): ?>
                                                    <img src="../../uploads/<?= htmlspecialchars($buku['cover']) ?>" 
                                                         class="book-cover me-2" alt="Cover">
                                                <?php else: ?>
                                                    <div class="book-cover bg-light d-flex align-items-center justify-content-center me-2">
                                                        <i class="bi bi-book text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <strong><?= htmlspecialchars($buku['judul']) ?></strong>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($buku['pengarang']) ?></td>
                                        <td>
                                            <span class="badge bg-success"><?= $buku['total_dipinjam'] ?> kali</span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($jenis_laporan == 'anggota'): ?>
                <!-- Anggota Aktif -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-people me-2"></i>
                            Anggota Paling Aktif
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Ranking</th>
                                        <th>Nama Anggota</th>
                                        <th>Username</th>
                                        <th>Total Peminjaman</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($anggotaAktif as $index => $anggota): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary fs-6"><?= $index + 1 ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($anggota['nama_lengkap']) ?></td>
                                        <td><?= htmlspecialchars($anggota['username']) ?></td>
                                        <td>
                                            <span class="badge bg-success"><?= $anggota['total_pinjam'] ?> buku</span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($jenis_laporan == 'keterlambatan'): ?>
                <!-- Data Keterlambatan -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                            Data Keterlambatan Pengembalian
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($dataKeterlambatan)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                                <h5 class="mt-3">Tidak Ada Keterlambatan</h5>
                                <p class="text-muted">Semua buku telah dikembalikan tepat waktu!</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Anggota</th>
                                            <th>Buku</th>
                                            <th>Tgl Jatuh Tempo</th>
                                            <th>Keterlambatan</th>
                                            <th>Denda</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dataKeterlambatan as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['nama_lengkap']) ?></td>
                                            <td><?= htmlspecialchars($item['judul']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($item['tanggal_kembali_rencana'])) ?></td>
                                            <td>
                                                <span class="badge bg-danger"><?= $item['hari_terlambat'] ?> hari</span>
                                            </td>
                                            <td>
                                                <strong class="text-danger">
                                                    Rp <?= number_format($item['hari_terlambat'] * 1000, 0, ',', '.') ?>
                                                </strong>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Chart untuk transaksi harian
<?php if ($jenis_laporan == 'transaksi'): ?>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('chartHarian').getContext('2d');
    
    // Prepare data
    const chartData = <?= json_encode($chartHarian) ?>;
    const daysInMonth = new Date(<?= $tahun ?>, <?= $bulan ?>, 0).getDate();
    
    const labels = [];
    const data = [];
    
    for (let i = 1; i <= daysInMonth; i++) {
        labels.push(i);
        const found = chartData.find(item => item.hari == i);
        data.push(found ? found.jumlah : 0);
    }
    
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
                },
                x: {
                    title: {
                        display: true,
                        text: 'Tanggal'
                    }
                }
            },
            plugins: {
                legend: {
                    display: true
                },
                title: {
                    display: true,
                    text: 'Transaksi Harian'
                }
            }
        }
    });
});
<?php endif; ?>

// Function untuk print laporan
function printReport() {
    window.print();
}

// Print styles
const printStyles = `
    @media print {
        .sidebar, .navbar, .btn, .breadcrumb { display: none !important; }
        .container-fluid { margin: 0 !important; padding: 0 !important; }
        .card { border: 1px solid #ddd !important; margin-bottom: 20px !important; }
        .stat-card { background: #f8f9fa !important; color: #000 !important; }
        body { -webkit-print-color-adjust: exact; }
    }
`;

// Inject print styles
const styleSheet = document.createElement('style');
styleSheet.textContent = printStyles;
document.head.appendChild(styleSheet);
</script>

<?php include '../../includes/footer.php'; ?>
