<?php
require_once '../../config/database.php';
requireAdmin();

$pageTitle = 'Edit Buku';
$cssPath = '../../assets/css/style.css';
$jsPath = '../../assets/js/script.js';

// Ambil ID buku dari parameter
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    $_SESSION['error'] = 'ID buku tidak valid';
    header('Location: ../dashboard.php');
    exit();
}

$pdo = getConnection();

// Ambil data buku
$stmt = $pdo->prepare("SELECT * FROM buku WHERE id = ?");
$stmt->execute([$id]);
$buku = $stmt->fetch();

if (!$buku) {
    $_SESSION['error'] = 'Buku tidak ditemukan';
    header('Location: ../dashboard.php');
    exit();
}

// Ambil data kategori
$kategoris = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();

include '../../includes/header.php';
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
            <a class="nav-link active text-white" href="tambah.php">
                <i class="bi bi-plus-circle"></i> Tambah Buku
            </a>
            <a class="nav-link text-white-50" href="../anggota/index.php">
                <i class="bi bi-people"></i> Kelola Anggota
            </a>
            <a class="nav-link text-white-50" href="../transaksi/index.php">
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
                            <h2 class="mb-1">Edit Buku</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item">Kelola Buku</li>
                                    <li class="breadcrumb-item active">Edit Buku</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-pencil me-2"></i>
                                Edit Informasi Buku
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                                </div>
                            <?php endif; ?>

                            <form action="../../proses/buku_edit.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                                <input type="hidden" name="id" value="<?= $buku['id'] ?>">
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="kode_buku" class="form-label">Kode Buku *</label>
                                        <input type="text" class="form-control" id="kode_buku" name="kode_buku" 
                                               value="<?= htmlspecialchars($buku['kode_buku']) ?>" required>
                                        <div class="invalid-feedback">
                                            Kode buku harus diisi.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="isbn" class="form-label">ISBN</label>
                                        <input type="text" class="form-control" id="isbn" name="isbn" 
                                               value="<?= htmlspecialchars($buku['isbn']) ?>" placeholder="978-602-xxx-xxx-x">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="judul" class="form-label">Judul Buku *</label>
                                    <input type="text" class="form-control" id="judul" name="judul" 
                                           value="<?= htmlspecialchars($buku['judul']) ?>" placeholder="Masukkan judul buku" required>
                                    <div class="invalid-feedback">
                                        Judul buku harus diisi.
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="pengarang" class="form-label">Pengarang *</label>
                                        <input type="text" class="form-control" id="pengarang" name="pengarang" 
                                               value="<?= htmlspecialchars($buku['pengarang']) ?>" placeholder="Nama pengarang" required>
                                        <div class="invalid-feedback">
                                            Pengarang harus diisi.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="penerbit" class="form-label">Penerbit</label>
                                        <input type="text" class="form-control" id="penerbit" name="penerbit" 
                                               value="<?= htmlspecialchars($buku['penerbit']) ?>" placeholder="Nama penerbit">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="tahun_terbit" class="form-label">Tahun Terbit</label>
                                        <input type="number" class="form-control" id="tahun_terbit" name="tahun_terbit" 
                                               value="<?= $buku['tahun_terbit'] ?>" min="1900" max="<?= date('Y') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="kategori_id" class="form-label">Kategori *</label>
                                        <select class="form-select" id="kategori_id" name="kategori_id" required>
                                            <option value="">Pilih Kategori</option>
                                            <?php foreach ($kategoris as $kategori): ?>
                                                <option value="<?= $kategori['id'] ?>" <?= ($kategori['id'] == $buku['kategori_id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($kategori['nama_kategori']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Kategori harus dipilih.
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="lokasi_rak" class="form-label">Lokasi Rak</label>
                                        <input type="text" class="form-control" id="lokasi_rak" name="lokasi_rak" 
                                               value="<?= htmlspecialchars($buku['lokasi_rak']) ?>" placeholder="A1-01">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="jumlah_total" class="form-label">Jumlah Total *</label>
                                        <input type="number" class="form-control" id="jumlah_total" name="jumlah_total" 
                                               value="<?= $buku['jumlah_total'] ?>" min="1" required>
                                        <div class="invalid-feedback">
                                            Jumlah buku minimal 1.
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="jumlah_tersedia" class="form-label">Jumlah Tersedia</label>
                                        <input type="number" class="form-control" id="jumlah_tersedia" name="jumlah_tersedia" 
                                               value="<?= $buku['jumlah_tersedia'] ?>" min="0" readonly>
                                        <div class="form-text">Akan disesuaikan otomatis</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="tersedia" <?= ($buku['status'] == 'tersedia') ? 'selected' : '' ?>>Tersedia</option>
                                            <option value="tidak_tersedia" <?= ($buku['status'] == 'tidak_tersedia') ? 'selected' : '' ?>>Tidak Tersedia</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="deskripsi" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" 
                                              placeholder="Deskripsi singkat tentang buku..."><?= htmlspecialchars($buku['deskripsi']) ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="cover" class="form-label">Cover Buku</label>
                                    <input type="file" class="form-control" id="cover" name="cover" 
                                           accept="image/*" data-preview="cover-preview">
                                    <div class="form-text">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Format: JPG, PNG, GIF. Maksimal 2MB. Kosongkan jika tidak ingin mengubah cover.
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-2"></i>
                                        Update Buku
                                    </button>
                                    <a href="../dashboard.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left me-2"></i>
                                        Kembali
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Preview Card -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-eye me-2"></i>
                                Cover Saat Ini
                            </h5>
                        </div>
                        <div class="card-body text-center">
                            <?php if ($buku['cover']): ?>
                                <img id="cover-current" src="../../uploads/<?= htmlspecialchars($buku['cover']) ?>" 
                                     alt="Cover Saat Ini" class="book-cover-large mb-3">
                            <?php else: ?>
                                <div id="cover-placeholder" class="book-cover-large bg-light d-flex align-items-center justify-content-center mb-3 mx-auto">
                                    <div class="text-center text-muted">
                                        <i class="bi bi-image" style="font-size: 3rem;"></i>
                                        <p class="mt-2 mb-0">Tidak ada cover</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <img id="cover-preview" src="" alt="Preview Cover" 
                                 class="book-cover-large mb-3" style="display: none;">
                            
                            <p class="text-muted small">
                                <i class="bi bi-lightbulb me-1"></i>
                                Cover yang baik akan menarik minat pembaca
                            </p>
                        </div>
                    </div>

                    <!-- Info Card -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-info-circle text-info me-2"></i>
                                Informasi Peminjaman
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php
                            // Ambil statistik peminjaman
                            $stmt = $pdo->prepare("SELECT COUNT(*) as total_dipinjam FROM transaksi WHERE buku_id = ?");
                            $stmt->execute([$buku['id']]);
                            $totalDipinjam = $stmt->fetchColumn();
                            
                            $stmt = $pdo->prepare("SELECT COUNT(*) as sedang_dipinjam FROM transaksi WHERE buku_id = ? AND status = 'dipinjam'");
                            $stmt->execute([$buku['id']]);
                            $sedangDipinjam = $stmt->fetchColumn();
                            ?>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h4 class="text-primary"><?= $totalDipinjam ?></h4>
                                    <small class="text-muted">Total Peminjaman</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-warning"><?= $sedangDipinjam ?></h4>
                                    <small class="text-muted">Sedang Dipinjam</small>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center">
                                <small class="text-muted">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    Ditambahkan: <?= date('d/m/Y', strtotime($buku['created_at'])) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Preview cover image
document.getElementById('cover').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('cover-preview');
    const current = document.getElementById('cover-current');
    const placeholder = document.getElementById('cover-placeholder');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (current) current.style.display = 'none';
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
        if (current) current.style.display = 'block';
        if (placeholder) placeholder.style.display = 'flex';
    }
});

// Auto-update jumlah tersedia ketika jumlah total berubah
document.getElementById('jumlah_total').addEventListener('input', function() {
    const jumlahTotal = parseInt(this.value) || 0;
    const sedangDipinjam = <?= $sedangDipinjam ?>;
    const jumlahTersedia = Math.max(0, jumlahTotal - sedangDipinjam);
    
    document.getElementById('jumlah_tersedia').value = jumlahTersedia;
});
</script>

<?php include '../../includes/footer.php'; ?>
