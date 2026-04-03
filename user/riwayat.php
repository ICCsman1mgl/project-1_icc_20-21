

<?php
require_once '../config/database.php';
requireLogin();

$pageTitle = 'Riwayat Peminjaman';
$cssPath = '../assets/css/style.css';
$jsPath = '../assets/js/script.js';

include '../includes/header.php';

$pdo = getConnection();
$userId = $_SESSION['user_id'];

// Parameter filter dan pagination
$status = isset($_GET['status']) ? cleanInput($_GET['status']) : '';
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$whereClause = "WHERE t.user_id = ?";
$params = [$userId];

if ($status) {
    $whereClause .= " AND t.status = ?";
    $params[] = $status;
}

if ($search) {
    $whereClause .= " AND (b.judul LIKE ? OR b.pengarang LIKE ? OR t.kode_transaksi LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
}

// Total transaksi
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM transaksi t 
                            JOIN buku b ON t.buku_id = b.id 
                            $whereClause");
$totalStmt->execute($params);
$totalTransaksi = $totalStmt->fetchColumn();
$totalPages = ceil($totalTransaksi / $limit);

// Data riwayat

$riwayatStmt = $pdo->prepare("SELECT t.*, b.judul, b.pengarang, b.cover,
                              DATEDIFF(CURDATE(), t.tanggal_kembali_rencana) as hari_terlambat,
                              DATEDIFF(t.tanggal_kembali_rencana, CURDATE()) as hari_tersisa
                              FROM transaksi t 
                              JOIN buku b ON t.buku_id = b.id 
                              $whereClause 
                              ORDER BY t.created_at DESC 
                              LIMIT $limit OFFSET $offset");
$riwayatStmt->execute($params);
$riwayatList = $riwayatStmt->fetchAll();

// Statistik user
$stats = [
    'total' => $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE user_id = ?"),
    'dipinjam' => $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE user_id = ? AND status = 'dipinjam'"),
    'dikembalikan' => $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE user_id = ? AND status = 'dikembalikan'"),
    'terlambat' => $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE user_id = ? AND status = 'dipinjam' AND tanggal_kembali_rencana < CURDATE()")
];

foreach ($stats as $key => $stmt) {
    $stmt->execute([$userId]);
    $stats[$key] = $stmt->fetchColumn();
}
?>


<?php include '../includes/navbar.php'; ?>

<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-1">
                <i class="bi bi-clock-history me-2"></i>
                Riwayat Peminjaman
            </h2>
            <p class="text-muted">Lihat seluruh aktivitas peminjaman Anda</p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="stat-number"><?= $stats['total'] ?></h3>
                            <p class="stat-label">Total Peminjaman</p>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-graph-up"></i>
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
            <div class="card stat-card <?= $stats['terlambat'] > 0 ? 'bg-danger' : 'bg-info' ?> text-white">
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
    </div>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <div class="search-box">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Cari judul buku, pengarang, atau kode transaksi..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <option value="dipinjam" <?= ($status == 'dipinjam') ? 'selected' : '' ?>>Sedang Dipinjam</option>
                        <option value="dikembalikan" <?= ($status == 'dikembalikan') ? 'selected' : '' ?>>Sudah Dikembalikan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-2"></i>Filter
                    </button>
                    <a href="riwayat.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Alert untuk buku terlambat -->
    <?php if ($stats['terlambat'] > 0): ?>
    <div class="alert alert-danger" role="alert">
        <h6 class="alert-heading">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Perhatian!
        </h6>
        <p class="mb-0">
            Anda memiliki <strong><?= $stats['terlambat'] ?></strong> buku yang sudah terlambat dikembalikan. 
            Segera kembalikan untuk menghindari denda tambahan.
        </p>
    </div>
    <?php endif; ?>

    <!-- Riwayat Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-list me-2"></i>
                Daftar Riwayat Peminjaman
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($riwayatList)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-clock-history" style="font-size: 4rem; color: #dee2e6;"></i>
                    <h4 class="text-muted mt-3">Belum Ada Riwayat</h4>
                    <p class="text-muted">Anda belum pernah meminjam buku. Silakan cari buku yang ingin dipinjam.</p>
                    <a href="katalog.php" class="btn btn-primary">
                        <i class="bi bi-search me-2"></i>Cari Buku
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Kode Transaksi</th>
                                <th>Buku</th>
                                <th>Tanggal Pinjam</th>
                                <th>Tanggal Kembali</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($riwayatList as $riwayat): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold"><?= htmlspecialchars($riwayat['kode_transaksi']) ?></span>
                                    <br><small class="text-muted">
                                        <i class="bi bi-calendar me-1"></i>
                                        <?= date('d/m/Y H:i', strtotime($riwayat['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($riwayat['cover']): ?>
                                            <img src="/LibraryManagement/proses/uploads/<?= htmlspecialchars($riwayat['cover']) ?>" 
                                                 class="book-cover me-3" alt="Cover">
                                        <?php else: ?>
                                            <div class="book-cover bg-light d-flex align-items-center justify-content-center me-3">
                                                <i class="bi bi-book text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($riwayat['judul']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($riwayat['pengarang']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($riwayat['tanggal_pinjam'])) ?>
                                </td>
                                <td>
                                    <div>
                                        <strong>Rencana:</strong> <?= date('d/m/Y', strtotime($riwayat['tanggal_kembali_rencana'])) ?>
                                        <?php if ($riwayat['tanggal_kembali_aktual']): ?>
                                            <br><span class="text-success">
                                                <strong>Aktual:</strong> <?= date('d/m/Y', strtotime($riwayat['tanggal_kembali_aktual'])) ?>
                                            </span>
                                        <?php elseif ($riwayat['status'] == 'dipinjam'): ?>
                                            <?php if ($riwayat['hari_terlambat'] > 0): ?>
                                                <br><span class="text-danger">
                                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                                    Terlambat <?= $riwayat['hari_terlambat'] ?> hari
                                                </span>
                                            <?php elseif ($riwayat['hari_tersisa'] <= 3): ?>
                                                <br><span class="text-warning">
                                                    <i class="bi bi-clock me-1"></i>
                                                    <?= $riwayat['hari_tersisa'] ?> hari lagi
                                                </span>
                                            <?php else: ?>
                                                <br><span class="text-success">
                                                    <i class="bi bi-check-circle me-1"></i>
                                                    <?= $riwayat['hari_tersisa'] ?> hari lagi
                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = '';
                                    $statusIcon = '';
                                    $statusText = '';
                                    
                                    if ($riwayat['status'] == 'dipinjam') {
                                        if ($riwayat['hari_terlambat'] > 0) {
                                            $statusClass = 'bg-danger';
                                            $statusIcon = 'bi-exclamation-triangle';
                                            $statusText = 'Terlambat';
                                        } else {
                                            $statusClass = 'bg-warning';
                                            $statusIcon = 'bi-clock';
                                            $statusText = 'Dipinjam';
                                        }
                                    } else {
                                        $statusClass = 'bg-success';
                                        $statusIcon = 'bi-check-circle';
                                        $statusText = 'Dikembalikan';
                                    }
                                    ?>
                                    <span class="badge <?= $statusClass ?>">
                                        <i class="<?= $statusIcon ?> me-1"></i><?= $statusText ?>
                                    </span>
                                    
                                    <?php if ($riwayat['denda'] > 0): ?>
                                        <br><small class="text-danger">
                                            <i class="bi bi-currency-dollar me-1"></i>
                                            Denda: Rp <?= number_format($riwayat['denda'], 0, ',', '.') ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-outline-info btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#detailModal<?= $riwayat['id'] ?>"
                                            title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal Detail -->
                            <div class="modal fade" id="detailModal<?= $riwayat['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Detail Transaksi</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-4 text-center">
                                                    <?php if ($riwayat['cover']): ?>
                                                        <img src="/LibraryManagement/proses/uploads/<?= htmlspecialchars($riwayat['cover']) ?>" 
                                                             class="book-cover-large mb-3" alt="Cover">
                                                    <?php else: ?>
                                                        <div class="book-cover-large bg-light d-flex align-items-center justify-content-center mb-3 mx-auto">
                                                            <i class="bi bi-book text-muted" style="font-size: 3rem;"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <h6><?= htmlspecialchars($riwayat['judul']) ?></h6>
                                                    <p class="text-muted"><?= htmlspecialchars($riwayat['pengarang']) ?></p>
                                                </div>

                                               <div class="col-md-8">
    <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">
        <i class="bi bi-receipt"></i> Informasi Transaksi
    </h5>
    <table class="table table-sm table-borderless">
        <tr>
            <td width="40%">Kode Transaksi</td>
            <td><strong><?= htmlspecialchars($riwayat['kode_transaksi']) ?></strong></td>
        </tr>
        <tr>
            <td>Tanggal Pinjam</td>
            <td><?= date('d F Y', strtotime($riwayat['tanggal_pinjam'])) ?></td>
        </tr>
        <tr>
            <td>Tanggal Kembali Rencana</td>
            <td><?= date('d F Y', strtotime($riwayat['tanggal_kembali_rencana'])) ?></td>
        </tr>
        <tr>
            <td>Status</td>
            <td><span class="badge <?= $statusClass ?>"><?= $statusText ?></span></td>
        </tr>
        <?php if ($riwayat['catatan']): ?>
        <tr>
            <td>Catatan</td>
            <td><?= htmlspecialchars($riwayat['catatan']) ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td>Dibuat</td>
            <td><?= date('d F Y H:i', strtotime($riwayat['created_at'])) ?></td>
        </tr>
    </table>
</div>

                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
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


<?php include '../includes/footer.php'; ?>
