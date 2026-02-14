<?php
require_once '../../config/database.php';
requireAdmin();

$pageTitle = 'Kelola Anggota';
$cssPath = '../../assets/css/style.css';
$jsPath = '../../assets/js/script.js';
$includeDataTables = true;

include '../../includes/header.php';

$pdo = getConnection();

// Ambil data anggota dengan pagination
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$whereClause = "WHERE role = 'user'";
$params = [];

if ($search) {
    $whereClause .= " AND (nama_lengkap LIKE ? OR username LIKE ? OR email LIKE ?)";
    $searchParam = "%$search%";
    $params = [$searchParam, $searchParam, $searchParam];
}

// Total anggota
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM users $whereClause");
$totalStmt->execute($params);
$totalAnggota = $totalStmt->fetchColumn();
$totalPages = ceil($totalAnggota / $limit);

// Data anggota
$anggotaStmt = $pdo->prepare("SELECT * FROM users $whereClause ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
$anggotaStmt->execute($params);
$anggotaList = $anggotaStmt->fetchAll();
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
            <a class="nav-link active text-white" href="index.php">
                <i class="bi bi-people"></i> Kelola Anggota
            </a>
            <a class="nav-link text-white-50" href="../transaksi/index.php">
                <i class="bi bi-arrow-left-right"></i> Transaksi
            </a>
            <a class="nav-link text-white-50" href="../laporan/index.php">
                <i class="bi bi-file-earmark-text"></i> Laporan
            </a>
            <a class="nav-link" href="/admin/anggota/import.php">
                <i class="fas fa-file-import">Import Anggota</i>
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
                            <h2 class="mb-1">Kelola Anggota</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Kelola Anggota</li>
                                </ol>
                            </nav>
                        </div>
                        <div>
                            <a href="tambah.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>
                                Tambah Anggota
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stat-card bg-primary text-white">
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
                    <?php
                    $aktifStmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user' AND status = 'aktif'");
                    $anggotaAktif = $aktifStmt->fetchColumn();
                    ?>
                    <div class="card stat-card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="stat-number"><?= $anggotaAktif ?></h3>
                                    <p class="stat-label">Anggota Aktif</p>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-person-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <?php
                    $pinjamStmt = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM transaksi WHERE status = 'dipinjam'");
                    $sedangPinjam = $pinjamStmt->fetchColumn();
                    ?>
                    <div class="card stat-card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="stat-number"><?= $sedangPinjam ?></h3>
                                    <p class="stat-label">Sedang Pinjam</p>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-book-half"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <?php
                    $bulanIniStmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
                    $anggotaBaru = $bulanIniStmt->fetchColumn();
                    ?>
                    <div class="card stat-card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="stat-number"><?= $anggotaBaru ?></h3>
                                    <p class="stat-label">Anggota Baru</p>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-person-plus"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-6">
                            <div class="search-box">
                                <i class="bi bi-search search-icon"></i>
                                <input type="text" class="form-control" name="search"
                                    placeholder="Cari nama, username, atau email..."
                                    value="<?= htmlspecialchars($search) ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-2"></i>Cari
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
                    <?= $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Data Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-list me-2"></i>
                        Daftar Anggota
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($anggotaList)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-people" style="font-size: 4rem; color: #dee2e6;"></i>
                            <h4 class="text-muted mt-3">Belum Ada Anggota</h4>
                            <p class="text-muted">Silakan tambahkan anggota baru untuk memulai.</p>
                            <a href="tambah.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Tambah Anggota Pertama
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-custom" id="anggotaTable">
                                <thead>
                                    <tr>
                                        <th>Foto</th>
                                        <th>Informasi Anggota</th>
                                        <th>Kontak</th>
                                        <th>Status</th>
                                        <th>Terdaftar</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($anggotaList as $anggota): ?>
                                        <tr>
                                            <td>
                                                <?php if ($anggota['foto']): ?>
                                                    <img src="/LibraryManagement/proses/uploads/<?= htmlspecialchars($anggota['foto']) ?>"
                                                        alt="Foto <?= htmlspecialchars($anggota['nama_lengkap']) ?>"
                                                        class="profile-photo">
                                                <?php else: ?>
                                                    <div class="profile-photo bg-light d-flex align-items-center justify-content-center">
                                                        <i class="bi bi-person text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div>
                                                    <h6 class="mb-1"><?= htmlspecialchars($anggota['nama_lengkap']) ?></h6>
                                                    <small class="text-muted">
                                                        <i class="bi bi-person me-1"></i><?= htmlspecialchars($anggota['username']) ?>
                                                    </small>
                                                    <?php if ($anggota['alamat']): ?>
                                                        <br><small class="text-muted">
                                                            <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($anggota['alamat']) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <small>
                                                        <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($anggota['email']) ?>
                                                    </small>
                                                    <?php if ($anggota['telepon']): ?>
                                                        <br><small>
                                                            <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($anggota['telepon']) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($anggota['status'] == 'aktif'): ?>
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle me-1"></i>Aktif
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">
                                                        <i class="bi bi-x-circle me-1"></i>Non-aktif
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small><?= date('d/m/Y', strtotime($anggota['created_at'])) ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="edit.php?id=<?= $anggota['id'] ?>"
                                                        class="btn btn-outline-primary"
                                                        data-bs-toggle="tooltip" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="hapus.php?id=<?= $anggota['id'] ?>"
                                                        class="btn btn-outline-danger btn-delete"
                                                        data-name="<?= htmlspecialchars($anggota['nama_lengkap']) ?>"
                                                        data-bs-toggle="tooltip" title="Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
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
                                            <a class="page-link" href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                                                <i class="bi bi-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
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