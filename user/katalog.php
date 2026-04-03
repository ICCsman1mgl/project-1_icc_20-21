<?php
require_once '../config/database.php';
requireLogin();

$pageTitle = 'Katalog Buku';
$cssPath = '../assets/css/style.css';
$jsPath = '../assets/js/script.js';

include '../includes/header.php';

$pdo = getConnection();

// Parameter pencarian dan filter
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$kategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$sortBy = isset($_GET['sort']) ? cleanInput($_GET['sort']) : 'terbaru';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Ambil data kategori
$kategoris = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();

// Build query dengan filter
$whereClause = "WHERE b.status = 'tersedia' AND b.jumlah_tersedia > 0";
$params = [];

if ($search) {
    $whereClause .= " AND (b.judul LIKE ? OR b.pengarang LIKE ? OR b.penerbit LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
}

if ($kategori) {
    $whereClause .= " AND b.kategori_id = ?";
    $params[] = $kategori;
}

// Sorting
$orderClause = "";
switch ($sortBy) {
    case 'judul':
        $orderClause = "ORDER BY b.judul ASC";
        break;
    case 'pengarang':
        $orderClause = "ORDER BY b.pengarang ASC";
        break;
    case 'tahun':
        $orderClause = "ORDER BY b.tahun_terbit DESC";
        break;
    case 'populer':
        $orderClause = "ORDER BY total_dipinjam DESC, b.created_at DESC";
        break;
    default: // terbaru
        $orderClause = "ORDER BY b.created_at DESC";
        break;
}

// Total buku
$totalQuery = "SELECT COUNT(*) FROM buku b 
               LEFT JOIN kategori k ON b.kategori_id = k.id 
               $whereClause";
$totalStmt = $pdo->prepare($totalQuery);
$totalStmt->execute($params);
$totalBuku = $totalStmt->fetchColumn();
$totalPages = ceil($totalBuku / $limit);

// Data buku dengan statistik peminjaman
$bukuQuery = "SELECT b.*, k.nama_kategori,
              COUNT(t.id) as total_dipinjam,
              COUNT(CASE WHEN t.status = 'dipinjam' THEN 1 END) as sedang_dipinjam
              FROM buku b 
              LEFT JOIN kategori k ON b.kategori_id = k.id 
              LEFT JOIN transaksi t ON b.id = t.buku_id
              $whereClause 
              GROUP BY b.id 
              $orderClause 
              LIMIT $limit OFFSET $offset";

$bukuStmt = $pdo->prepare($bukuQuery);
$bukuStmt->execute($params);
$bukuList = $bukuStmt->fetchAll();

// Cek buku yang sedang dipinjam user
$userId = $_SESSION['user_id'];
$bukuDipinjamUser = $pdo->prepare("SELECT buku_id FROM transaksi WHERE user_id = ? AND status = 'dipinjam'");
$bukuDipinjamUser->execute([$userId]);
$bukuDipinjamUser = array_column($bukuDipinjamUser->fetchAll(), 'buku_id');

// Cek jumlah buku yang sedang dipinjam user
$jumlahDipinjamUser = count($bukuDipinjamUser);
?>

<?php include '../includes/navbar.php'; ?>

<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-1">
                <i class="bi bi-search me-2"></i>
                Katalog Buku
            </h2>
            <p class="text-muted">Temukan dan pinjam buku yang Anda inginkan</p>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <div class="search-box">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Cari judul, pengarang, atau penerbit..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="kategori">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($kategoris as $kat): ?>
                            <option value="<?= $kat['id'] ?>" <?= ($kategori == $kat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kat['nama_kategori']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="sort">
                        <option value="terbaru" <?= ($sortBy == 'terbaru') ? 'selected' : '' ?>>Terbaru</option>
                        <option value="judul" <?= ($sortBy == 'judul') ? 'selected' : '' ?>>Judul A-Z</option>
                        <option value="pengarang" <?= ($sortBy == 'pengarang') ? 'selected' : '' ?>>Pengarang A-Z</option>
                        <option value="tahun" <?= ($sortBy == 'tahun') ? 'selected' : '' ?>>Tahun Terbaru</option>
                        <option value="populer" <?= ($sortBy == 'populer') ? 'selected' : '' ?>>Paling Populer</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="katalog.php" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-clockwise me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Info -->
    <div class="row mb-3">
        <div class="col-md-6">
            <p class="text-muted mb-0">
                Menampilkan <?= min($totalBuku, $limit) ?> dari <?= $totalBuku ?> buku
                <?php if ($search || $kategori): ?>
                    <span class="fw-bold">
                        <?php if ($search): ?>
                            untuk "<?= htmlspecialchars($search) ?>"
                        <?php endif; ?>
                        <?php if ($kategori): ?>
                            <?php
                            $namaKategori = array_filter($kategoris, function($k) use ($kategori) {
                                return $k['id'] == $kategori;
                            });
                            $namaKategori = reset($namaKategori);
                            ?>
                            dalam kategori "<?= htmlspecialchars($namaKategori['nama_kategori']) ?>"
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
            </p>
        </div>
        <div class="col-md-6 text-end">
            <div class="d-flex align-items-center justify-content-end">
                <span class="me-2 text-muted">
                    <i class="bi bi-book-half me-1"></i>
                    Sedang Dipinjam: <?= $jumlahDipinjamUser ?>/3
                </span>
                <?php if ($jumlahDipinjamUser >= 3): ?>
                    <span class="badge bg-warning">Batas Maksimal</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Alert untuk batas peminjaman -->
    <?php if ($jumlahDipinjamUser >= 3): ?>
    <div class="alert alert-warning" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Perhatian!</strong> Anda sudah mencapai batas maksimal peminjaman (3 buku). 
        Kembalikan buku yang sudah dipinjam untuk dapat meminjam buku lain.
    </div>
    <?php endif; ?>

    <!-- Book Grid -->
    <?php if (empty($bukuList)): ?>
        <div class="text-center py-5">
            <i class="bi bi-search" style="font-size: 5rem; color: #dee2e6;"></i>
            <h4 class="text-muted mt-3">Tidak Ada Buku Ditemukan</h4>
            <p class="text-muted">Coba ubah kata kunci pencarian atau filter yang Anda gunakan</p>
            <a href="katalog.php" class="btn btn-primary">
                <i class="bi bi-arrow-clockwise me-2"></i>Lihat Semua Buku
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($bukuList as $buku): ?>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100 book-card">
                    <div class="position-relative">
                        <div class="text-center pt-3">
                            <?php if ($buku['cover']): ?>
                              <img src="/LibraryManagement/proses/uploads/<?= htmlspecialchars($buku['cover']) ?>"
     class="book-cover-large" alt="Cover" style="cursor: pointer;">

                                   
                            <?php else: ?>
                                <div class="book-cover-large bg-light d-flex align-items-center justify-content-center mx-auto"
                                     style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#bookModal<?= $buku['id'] ?>">
                                    <i class="bi bi-book text-muted" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Status Badge -->
                        <?php if (in_array($buku['id'], $bukuDipinjamUser)): ?>
                            <span class="position-absolute top-0 end-0 badge bg-warning m-2">
                                Sedang Dipinjam
                            </span>
                        <?php elseif ($buku['jumlah_tersedia'] <= 0): ?>
                            <span class="position-absolute top-0 end-0 badge bg-danger m-2">
                                Tidak Tersedia
                            </span>
                        <?php elseif ($buku['total_dipinjam'] > 5): ?>
                            <span class="position-absolute top-0 end-0 badge bg-primary m-2">
                                <i class="bi bi-star me-1"></i>Populer
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title mb-2" style="height: 2.4em; overflow: hidden;">
                            <?= htmlspecialchars($buku['judul']) ?>
                        </h6>
                        <p class="card-text text-muted small mb-2"><?= htmlspecialchars($buku['pengarang']) ?></p>
                        
                        <?php if ($buku['nama_kategori']): ?>
                            <span class="badge bg-light text-dark mb-2"><?= htmlspecialchars($buku['nama_kategori']) ?></span>
                        <?php endif; ?>
                        
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Tersedia: <?= $buku['jumlah_tersedia'] ?>
                                </small>
                                <?php if ($buku['total_dipinjam'] > 0): ?>
                                    <small class="text-primary">
                                        <i class="bi bi-graph-up me-1"></i>
                                        <?= $buku['total_dipinjam'] ?>x dipinjam
                                    </small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-grid gap-1">
                                <button class="btn btn-primary btn-sm" 
                                        data-bs-toggle="modal" data-bs-target="#bookModal<?= $buku['id'] ?>">
                                    <i class="bi bi-eye me-1"></i>Lihat Detail
                                </button>
                                
                                <?php if (in_array($buku['id'], $bukuDipinjamUser)): ?>
                                    <span class="btn btn-warning btn-sm disabled">
                                        <i class="bi bi-clock me-1"></i>Sedang Dipinjam
                                    </span>
                                <?php elseif ($buku['jumlah_tersedia'] <= 0): ?>
                                    <span class="btn btn-secondary btn-sm disabled">
                                        <i class="bi bi-x-circle me-1"></i>Tidak Tersedia
                                    </span>
                                <?php elseif ($jumlahDipinjamUser >= 3): ?>
                                    <span class="btn btn-secondary btn-sm disabled">
                                        <i class="bi bi-exclamation-triangle me-1"></i>Batas Tercapai
                                    </span>
                                <?php else: ?>
                                    <a href="pinjam.php?buku_id=<?= $buku['id'] ?>" class="btn btn-success btn-sm">
                                        <i class="bi bi-bookmark-plus me-1"></i>Pinjam Buku
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Detail Buku -->
                <div class="modal fade" id="bookModal<?= $buku['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Detail Buku</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-4 text-center">
                                        <?php if ($buku['cover']): ?>
                                            <img src="/LibraryManagement/proses/uploads/<?= htmlspecialchars($buku['cover']) ?>" 
                                                 class="book-cover-large mb-3" alt="Cover">
                                        <?php else: ?>
                                            <div class="book-cover-large bg-light d-flex align-items-center justify-content-center mb-3 mx-auto">
                                                <i class="bi bi-book text-muted" style="font-size: 4rem;"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-8">
                                        <h4><?= htmlspecialchars($buku['judul']) ?></h4>
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <td width="30%">Kode Buku</td>
                                                <td><strong><?= htmlspecialchars($buku['kode_buku']) ?></strong></td>
                                            </tr>
                                            <tr>
                                                <td>Pengarang</td>
                                                <td><?= htmlspecialchars($buku['pengarang']) ?></td>
                                            </tr>
                                            <?php if ($buku['penerbit']): ?>
                                            <tr>
                                                <td>Penerbit</td>
                                                <td><?= htmlspecialchars($buku['penerbit']) ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if ($buku['tahun_terbit']): ?>
                                            <tr>
                                                <td>Tahun Terbit</td>
                                                <td><?= $buku['tahun_terbit'] ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <td>Kategori</td>
                                                <td>
                                                    <span class="badge bg-primary"><?= htmlspecialchars($buku['nama_kategori']) ?></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Ketersediaan</td>
                                                <td>
                                                    <span class="badge bg-success"><?= $buku['jumlah_tersedia'] ?> dari <?= $buku['jumlah_total'] ?> tersedia</span>
                                                </td>
                                            </tr>
                                            <?php if ($buku['lokasi_rak']): ?>
                                            <tr>
                                                <td>Lokasi Rak</td>
                                                <td><code><?= htmlspecialchars($buku['lokasi_rak']) ?></code></td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if ($buku['isbn']): ?>
                                            <tr>
                                                <td>ISBN</td>
                                                <td><?= htmlspecialchars($buku['isbn']) ?></td>
                                            </tr>
                                            <?php endif; ?>
                                        </table>
                                        
                                        <?php if ($buku['deskripsi']): ?>
                                        <div class="mt-3">
                                            <h6>Deskripsi</h6>
                                            <p class="text-muted"><?= nl2br(htmlspecialchars($buku['deskripsi'])) ?></p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                
                                <?php if (in_array($buku['id'], $bukuDipinjamUser)): ?>
                                    <span class="btn btn-warning disabled">
                                        <i class="bi bi-clock me-1"></i>Sedang Dipinjam
                                    </span>
                                <?php elseif ($buku['jumlah_tersedia'] <= 0): ?>
                                    <span class="btn btn-secondary disabled">
                                        <i class="bi bi-x-circle me-1"></i>Tidak Tersedia
                                    </span>
                                <?php elseif ($jumlahDipinjamUser >= 3): ?>
                                    <span class="btn btn-secondary disabled">
                                        <i class="bi bi-exclamation-triangle me-1"></i>Batas Tercapai
                                    </span>
                                <?php else: ?>
                                    <a href="pinjam.php?buku_id=<?= $buku['id'] ?>" class="btn btn-success">
                                        <i class="bi bi-bookmark-plus me-1"></i>Pinjam Buku
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="/app/buku.php?page=<?= $i ?>"
><?= $search ? '&search=' . urlencode($search) : '' ?><?= $kategori ? '&kategori=' . $kategori : '' ?><?= $sortBy ? '&sort=' . urlencode($sortBy) : '' ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $kategori ? '&kategori=' . $kategori : '' ?><?= $sortBy ? '&sort=' . urlencode($sortBy) : '' ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link"href="/app/buku.php?page=<?= $i ?>"
<?= $search ? '&search=' . urlencode($search) : '' ?><?= $kategori ? '&kategori=' . $kategori : '' ?><?= $sortBy ? '&sort=' . urlencode($sortBy) : '' ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.book-card {
    transition: transform 0.2s ease-in-out;
}

.book-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.book-cover-large {
    cursor: pointer;
    transition: transform 0.2s ease-in-out;
}

.book-cover-large:hover {
    transform: scale(1.05);
}
</style>

<?php include '../includes/footer.php'; ?>
