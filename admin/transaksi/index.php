<?php
require_once '../../config/database.php';
requireAdmin();

$pageTitle = 'Kelola Transaksi';
$cssPath = '../../assets/css/style.css';
$jsPath = '../../assets/js/script.js';
$includeDataTables = true;

include '../../includes/header.php';

$pdo = getConnection();

// Filter dan pagination
$status = isset($_GET['status']) ? cleanInput($_GET['status']) : '';
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$whereClause = "WHERE 1=1";
$params = [];

if ($status) {
    $whereClause .= " AND t.status = ?";
    $params[] = $status;
}

if ($search) {
    $whereClause .= " AND (u.nama_lengkap LIKE ? OR b.judul LIKE ? OR t.kode_transaksi LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
}

// Total transaksi
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM transaksi t 
                            JOIN users u ON t.user_id = u.id 
                            JOIN buku b ON t.buku_id = b.id 
                            $whereClause");
$totalStmt->execute($params);
$totalTransaksi = $totalStmt->fetchColumn();
$totalPages = ceil($totalTransaksi / $limit);

// Data transaksi
$transaksiStmt = $pdo->prepare("SELECT t.*, u.nama_lengkap, u.foto as foto_user, 
                                b.judul, b.cover, b.pengarang,
                                DATEDIFF(CURDATE(), t.tanggal_kembali_rencana) as hari_terlambat
                                FROM transaksi t 
                                JOIN users u ON t.user_id = u.id 
                                JOIN buku b ON t.buku_id = b.id 
                                $whereClause 
                                ORDER BY t.created_at DESC 
                                LIMIT $limit OFFSET $offset");
$transaksiStmt->execute($params);
$transaksiList = $transaksiStmt->fetchAll();

// Statistik
$stats = [
    'dipinjam' => $pdo->query("SELECT COUNT(*) FROM transaksi WHERE status = 'dipinjam'")->fetchColumn(),
    'dikembalikan' => $pdo->query("SELECT COUNT(*) FROM transaksi WHERE status = 'dikembalikan'")->fetchColumn(),
    'terlambat' => $pdo->query("SELECT COUNT(*) FROM transaksi WHERE status = 'dipinjam' AND tanggal_kembali_rencana < CURDATE()")->fetchColumn(),
    'hari_ini' => $pdo->query("SELECT COUNT(*) FROM transaksi WHERE DATE(created_at) = CURDATE()")->fetchColumn()
];
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
            <a class="nav-link active text-white" href="index.php">
                <i class="bi bi-arrow-left-right"></i> Transaksi
            </a>
            <a class="nav-link text-white-50" href="../laporan/index.php">
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
                            <h2 class="mb-1">Kelola Transaksi</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Transaksi</li>
                                </ol>
                            </nav>
                        </div>
                        <div>
                            <a href="pinjam.php" class="btn btn-primary me-2">
                                <i class="bi bi-box-arrow-up me-2"></i>
                                Pinjam Buku
                            </a>
                            <a href="kembali.php" class="btn btn-success">
                                <i class="bi bi-box-arrow-down me-2"></i>
                                Kembalikan Buku
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stat-card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="stat-number"><?= $stats['dipinjam'] ?></h3>
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
                                    <h3 class="stat-number"><?= $stats['dikembalikan'] ?></h3>
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
                    <div class="card stat-card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="stat-number"><?= $stats['terlambat'] ?></h3>
                                    <p class="stat-label">Terlambat</p>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-exclamation-triangle"></i>
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
                                    <h3 class="stat-number"><?= $stats['hari_ini'] ?></h3>
                                    <p class="stat-label">Transaksi Hari Ini</p>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-calendar-day"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <div class="search-box">
                                <i class="bi bi-search search-icon"></i>
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Cari anggota, buku, atau kode transaksi..." 
                                       value="<?= htmlspecialchars($search) ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="status">
                                <option value="">Semua Status</option>
                                <option value="dipinjam" <?= ($status == 'dipinjam') ? 'selected' : '' ?>>Dipinjam</option>
                                <option value="dikembalikan" <?= ($status == 'dikembalikan') ? 'selected' : '' ?>>Dikembalikan</option>
                                <option value="terlambat" <?= ($status == 'terlambat') ? 'selected' : '' ?>>Terlambat</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel me-2"></i>Filter
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise me-2"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Alerts -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Data Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-list me-2"></i>
                        Daftar Transaksi
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($transaksiList)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-arrow-left-right" style="font-size: 4rem; color: #dee2e6;"></i>
                            <h4 class="text-muted mt-3">Belum Ada Transaksi</h4>
                            <p class="text-muted">Transaksi akan muncul setelah ada aktivitas peminjaman.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-custom">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Anggota</th>
                                        <th>Buku</th>
                                        <th>Tgl Pinjam</th>
                                        <th>Tgl Kembali</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transaksiList as $transaksi): ?>
                                        <tr>
                                            <td>
                                                <span class="fw-bold"><?= htmlspecialchars($transaksi['kode_transaksi']) ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($transaksi['foto_user']): ?>
                                                        <img src="/LibraryManagement/proses/uploads/<?= htmlspecialchars($transaksi['foto_user']) ?>" 
                                                             class="profile-photo me-2" alt="Foto">
                                                    <?php else: ?>
                                                        <div class="profile-photo bg-light d-flex align-items-center justify-content-center me-2">
                                                            <i class="bi bi-person text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <small class="fw-bold"><?= htmlspecialchars($transaksi['nama_lengkap']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($transaksi['cover']): ?>
                                                        <img src="/LibraryManagement/proses/uploads/<?= htmlspecialchars($transaksi['cover']) ?>" 
                                                                                     class="book-cover-large" alt="Cover">
                                                    <?php else: ?>
                                                        <div class="book-cover bg-light d-flex align-items-center justify-content-center me-2">
                                                            <i class="bi bi-book text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <small class="fw-bold"><?= htmlspecialchars($transaksi['judul']) ?></small>
                                                        <br><small class="text-muted"><?= htmlspecialchars($transaksi['pengarang']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <small><?= date('d/m/Y', strtotime($transaksi['tanggal_pinjam'])) ?></small>
                                            </td>
                                            <td>
                                                <small>
                                                    <?= date('d/m/Y', strtotime($transaksi['tanggal_kembali_rencana'])) ?>
                                                    <?php if ($transaksi['tanggal_kembali_aktual']): ?>
                                                        <br><span class="text-success">
                                                            Dikembalikan: <?= date('d/m/Y', strtotime($transaksi['tanggal_kembali_aktual'])) ?>
                                                        </span>
                                                    <?php elseif ($transaksi['hari_terlambat'] > 0): ?>
                                                        <br><span class="text-danger">
                                                            Terlambat <?= $transaksi['hari_terlambat'] ?> hari
                                                        </span>
                                                    <?php endif; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = '';
                                                $statusIcon = '';
                                                switch ($transaksi['status']) {
                                                    case 'dipinjam':
                                                        if ($transaksi['hari_terlambat'] > 0) {
                                                            $statusClass = 'bg-danger';
                                                            $statusIcon = 'bi-exclamation-triangle';
                                                            $statusText = 'Terlambat';
                                                        } else {
                                                            $statusClass = 'bg-warning';
                                                            $statusIcon = 'bi-book-half';
                                                            $statusText = 'Dipinjam';
                                                        }
                                                        break;
                                                    case 'dikembalikan':
                                                        $statusClass = 'bg-success';
                                                        $statusIcon = 'bi-check-circle';
                                                        $statusText = 'Dikembalikan';
                                                        break;
                                                    case 'terlambat':
                                                        $statusClass = 'bg-danger';
                                                        $statusIcon = 'bi-exclamation-triangle';
                                                        $statusText = 'Terlambat';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?= $statusClass ?>">
                                                    <i class="<?= $statusIcon ?> me-1"></i><?= $statusText ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <?php if ($transaksi['status'] == 'dipinjam' || ($transaksi['status'] == 'dipinjam' && $transaksi['hari_terlambat'] > 0)): ?>
                                                        <a href="kembali.php?id=<?= $transaksi['id'] ?>" 
                                                           class="btn btn-outline-success" 
                                                           data-bs-toggle="tooltip" title="Kembalikan">
                                                            <i class="bi bi-box-arrow-down"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <button class="btn btn-outline-info btn-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#detailModal<?= $transaksi['id'] ?>"
                                                            title="Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>

                                                <!-- Modal Detail -->
                                                <div class="modal fade" id="detailModal<?= $transaksi['id'] ?>" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Detail Transaksi</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <h6>Informasi Transaksi</h6>
                                                                        <table class="table table-sm">
                                                                            <tr>
                                                                                <td>Kode Transaksi</td>
                                                                                <td><?= htmlspecialchars($transaksi['kode_transaksi']) ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Tanggal Pinjam</td>
                                                                                <td><?= date('d/m/Y', strtotime($transaksi['tanggal_pinjam'])) ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Tanggal Kembali</td>
                                                                                <td><?= date('d/m/Y', strtotime($transaksi['tanggal_kembali_rencana'])) ?></td>
                                                                            </tr>
                                                                            <?php if ($transaksi['tanggal_kembali_aktual']): ?>
                                                                            <tr>
                                                                                <td>Dikembalikan</td>
                                                                                <td><?= date('d/m/Y', strtotime($transaksi['tanggal_kembali_aktual'])) ?></td>
                                                                            </tr>
                                                                            <?php endif; ?>
                                                                            <tr>
                                                                                <td>Status</td>
                                                                                <td>
                                                                                    <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <h6>Informasi Buku</h6>
                                                                        <div class="text-center mb-3">
                                                                            <?php if ($transaksi['cover']): ?>
                                                                                <img src="/LibraryManagement/proses/uploads/<?= htmlspecialchars($transaksi['cover']) ?>" 
                                                                                     class="book-cover-large" alt="Cover">
                                                                            <?php else: ?>
                                                                                <div class="book-cover-large bg-light d-flex align-items-center justify-content-center mx-auto">
                                                                                    <i class="bi bi-book text-muted" style="font-size: 3rem;"></i>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                        <p class="mb-1"><strong><?= htmlspecialchars($transaksi['judul']) ?></strong></p>
                                                                        <p class="text-muted mb-0"><?= htmlspecialchars($transaksi['pengarang']) ?></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Pagination" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $status ? '&status=' . urlencode($status) : '' ?>">
                                                <i class="bi bi-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $status ? '&status=' . urlencode($status) : '' ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $status ? '&status=' . urlencode($status) : '' ?>">
                                                <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
