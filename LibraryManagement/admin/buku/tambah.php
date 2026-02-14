<?php
require_once '../../config/database.php';
requireAdmin();

$pageTitle = 'Tambah Buku';
$cssPath = '../../assets/css/style.css';
$jsPath = '../../assets/js/script.js';

include '../../includes/header.php';

// Ambil data kategori
$pdo = getConnection();
$kategoris = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
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
                            <h2 class="mb-1">Tambah Buku Baru</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item">Kelola Buku</li>
                                    <li class="breadcrumb-item active">Tambah Buku</li>
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
                                <i class="bi bi-book me-2"></i>
                                Informasi Buku
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

                            <form action="../../proses/buku_tambah.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="kode_buku" class="form-label">Kode Buku *</label>
                                        <input type="text" class="form-control" id="kode_buku" name="kode_buku" 
                                               value="<?= generateCode('BK', 6) ?>" required>
                                        <div class="invalid-feedback">
                                            Kode buku harus diisi.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="isbn" class="form-label">ISBN</label>
                                        <input type="text" class="form-control" id="isbn" name="isbn" 
                                               placeholder="978-602-xxx-xxx-x">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="judul" class="form-label">Judul Buku *</label>
                                    <input type="text" class="form-control" id="judul" name="judul" 
                                           placeholder="Masukkan judul buku" required>
                                    <div class="invalid-feedback">
                                        Judul buku harus diisi.
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="pengarang" class="form-label">Pengarang *</label>
                                        <input type="text" class="form-control" id="pengarang" name="pengarang" 
                                               placeholder="Nama pengarang" required>
                                        <div class="invalid-feedback">
                                            Pengarang harus diisi.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="penerbit" class="form-label">Penerbit</label>
                                        <input type="text" class="form-control" id="penerbit" name="penerbit" 
                                               placeholder="Nama penerbit">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="tahun_terbit" class="form-label">Tahun Terbit</label>
                                        <input type="number" class="form-control" id="tahun_terbit" name="tahun_terbit" 
                                               min="1900" max="<?= date('Y') ?>" placeholder="<?= date('Y') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="kategori_id" class="form-label">Kategori *</label>
                                        <select class="form-select" id="kategori_id" name="kategori_id" required>
                                            <option value="">Pilih Kategori</option>
                                            <?php foreach ($kategoris as $kategori): ?>
                                                <option value="<?= $kategori['id'] ?>"><?= htmlspecialchars($kategori['nama_kategori']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Kategori harus dipilih.
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="lokasi_rak" class="form-label">Lokasi Rak</label>
                                        <input type="text" class="form-control" id="lokasi_rak" name="lokasi_rak" 
                                               placeholder="A1-01">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="jumlah_total" class="form-label">Jumlah Buku *</label>
                                        <input type="number" class="form-control" id="jumlah_total" name="jumlah_total" 
                                               min="1" value="1" required>
                                        <div class="invalid-feedback">
                                            Jumlah buku minimal 1.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="tersedia">Tersedia</option>
                                            <option value="tidak_tersedia">Tidak Tersedia</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="deskripsi" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" 
                                              placeholder="Deskripsi singkat tentang buku..."></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="cover" class="form-label">Cover Buku</label>
                                    <input type="file" class="form-control" id="cover" name="cover" 
                                           accept="image/*" data-preview="cover-preview">
                                    <div class="form-text">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Format: JPG, PNG, GIF. Maksimal 2MB.
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-2"></i>
                                        Simpan Buku
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="bi bi-arrow-clockwise me-2"></i>
                                        Reset
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
                                Preview Cover
                            </h5>
                        </div>
                        <div class="card-body text-center">
                            <img id="cover-preview" src="" alt="Preview Cover" 
                                 class="book-cover-large mb-3" style="display: none;">
                            <div id="cover-placeholder" class="book-cover-large bg-light d-flex align-items-center justify-content-center mb-3 mx-auto">
                                <div class="text-center text-muted">
                                    <i class="bi bi-image" style="font-size: 3rem;"></i>
                                    <p class="mt-2 mb-0">Upload cover buku</p>
                                </div>
                            </div>
                            <p class="text-muted small">
                                <i class="bi bi-lightbulb me-1"></i>
                                Cover yang baik akan menarik minat pembaca
                            </p>
                        </div>
                    </div>

                    <!-- Tips Card -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-lightbulb text-warning me-2"></i>
                                Tips Menambah Buku
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <small>Pastikan kode buku unik</small>
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <small>Lengkapi informasi ISBN jika ada</small>
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <small>Pilih kategori yang sesuai</small>
                                </li>
                                <li class="mb-0">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <small>Upload cover dengan kualitas baik</small>
                                </li>
                            </ul>
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
    const placeholder = document.getElementById('cover-placeholder');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
        placeholder.style.display = 'flex';
    }
});

// Auto-generate kode buku
document.getElementById('judul').addEventListener('input', function() {
    const judul = this.value;
    if (judul.length > 0) {
        // Auto-generate lokasi rak berdasarkan kategori dan huruf pertama judul
        const firstLetter = judul.charAt(0).toUpperCase();
        document.getElementById('lokasi_rak').placeholder = `${firstLetter}1-01`;
    }
});
</script>


