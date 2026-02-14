<?php
require_once '../../config/database.php';
requireAdmin();

$pageTitle = 'Kembalikan Buku';
$cssPath = '../../assets/css/style.css';
$jsPath = '../../assets/js/script.js';

include '../../includes/header.php';

$pdo = getConnection();

// Jika ada ID transaksi dari parameter (untuk pengembalian langsung)
$transaksiId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$selectedTransaksi = null;

if ($transaksiId) {
    $stmt = $pdo->prepare("SELECT t.*, u.nama_lengkap, b.judul, b.pengarang, b.cover 
                           FROM transaksi t 
                           JOIN users u ON t.user_id = u.id 
                           JOIN buku b ON t.buku_id = b.id 
                           WHERE t.id = ? AND t.status = 'dipinjam'");
    $stmt->execute([$transaksiId]);
    $selectedTransaksi = $stmt->fetch();
}

// Ambil daftar transaksi yang belum dikembalikan
$transaksiAktif = $pdo->query("SELECT t.*, u.nama_lengkap, b.judul, b.pengarang, b.cover,
                               DATEDIFF(CURDATE(), t.tanggal_kembali_rencana) as hari_terlambat
                               FROM transaksi t 
                               JOIN users u ON t.user_id = u.id 
                               JOIN buku b ON t.buku_id = b.id 
                               WHERE t.status = 'dipinjam' 
                               ORDER BY t.tanggal_kembali_rencana ASC")->fetchAll();
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
                            <h2 class="mb-1">Kembalikan Buku</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="index.php">Transaksi</a></li>
                                    <li class="breadcrumb-item active">Kembalikan Buku</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert Messages -->
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

            <div class="row">
                <!-- Form Pengembalian -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-box-arrow-down me-2"></i>
                                Form Pengembalian Buku
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="../../proses/kembali_buku.php" method="POST" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="transaksi_id" class="form-label">Pilih Transaksi *</label>
                                    <select class="form-select" id="transaksi_id" name="transaksi_id" required>
                                        <option value="">-- Pilih Transaksi --</option>
                                        <?php foreach ($transaksiAktif as $transaksi): ?>
                                            <option value="<?= $transaksi['id'] ?>" 
                                                    <?= ($selectedTransaksi && $selectedTransaksi['id'] == $transaksi['id']) ? 'selected' : '' ?>
                                                    data-kode="<?= htmlspecialchars($transaksi['kode_transaksi']) ?>"
                                                    data-anggota="<?= htmlspecialchars($transaksi['nama_lengkap']) ?>"
                                                    data-buku="<?= htmlspecialchars($transaksi['judul']) ?>"
                                                    data-pengarang="<?= htmlspecialchars($transaksi['pengarang']) ?>"
                                                    data-cover="<?= htmlspecialchars($transaksi['cover']) ?>"
                                                    data-tgl-pinjam="<?= $transaksi['tanggal_pinjam'] ?>"
                                                    data-tgl-kembali="<?= $transaksi['tanggal_kembali_rencana'] ?>"
                                                    data-terlambat="<?= $transaksi['hari_terlambat'] ?>">
                                                [<?= htmlspecialchars($transaksi['kode_transaksi']) ?>] 
                                                <?= htmlspecialchars($transaksi['nama_lengkap']) ?> - 
                                                <?= htmlspecialchars($transaksi['judul']) ?>
                                                <?php if ($transaksi['hari_terlambat'] > 0): ?>
                                                    <span class="text-danger">(Terlambat <?= $transaksi['hari_terlambat'] ?> hari)</span>
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Transaksi harus dipilih.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="tanggal_kembali_aktual" class="form-label">Tanggal Kembali Aktual *</label>
                                    <input type="date" class="form-control" id="tanggal_kembali_aktual" name="tanggal_kembali_aktual" 
                                           value="<?= date('Y-m-d') ?>" required>
                                    <div class="invalid-feedback">
                                        Tanggal kembali aktual harus diisi.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="denda" class="form-label">Denda</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" id="denda" name="denda" 
                                               value="0" min="0" readonly>
                                    </div>
                                    <div class="form-text">Denda akan dihitung otomatis berdasarkan keterlambatan</div>
                                </div>

                                <div class="mb-4">
                                    <label for="catatan" class="form-label">Catatan</label>
                                    <textarea class="form-control" id="catatan" name="catatan" rows="3" 
                                              placeholder="Catatan kondisi buku atau lainnya..."></textarea>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-circle me-2"></i>
                                        Proses Pengembalian
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="bi bi-arrow-clockwise me-2"></i>
                                        Reset
                                    </button>
                                    <a href="index.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left me-2"></i>
                                        Kembali
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Info Transaksi -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                Detail Transaksi
                            </h5>
                        </div>
                        <div class="card-body" id="detail-transaksi">
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-arrow-left-right" style="font-size: 3rem;"></i>
                                <p class="mt-2 mb-0">Pilih transaksi untuk melihat detail</p>
                            </div>
                        </div>
                    </div>

                    <!-- Aturan Pengembalian -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-exclamation-circle text-warning me-2"></i>
                                Aturan Pengembalian
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <small>Periksa kondisi buku sebelum menerima</small>
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <small>Denda Rp 1.000/hari untuk keterlambatan</small>
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <small>Catat kondisi buku jika ada kerusakan</small>
                                </li>
                                <li class="mb-0">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <small>Konfirmasi pembayaran denda jika ada</small>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Buku yang Dipinjam -->
            <?php if (!empty($transaksiAktif)): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-list me-2"></i>
                        Daftar Buku yang Sedang Dipinjam
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
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
                                <?php foreach ($transaksiAktif as $transaksi): ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold"><?= htmlspecialchars($transaksi['kode_transaksi']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($transaksi['nama_lengkap']) ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($transaksi['cover']): ?>
                                                <img src="/LibraryManagement/proses/uploads/<?= htmlspecialchars($transaksi['cover']) ?>" 
                                                     class="book-cover me-2" alt="Cover">
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
                                    <td><?= date('d/m/Y', strtotime($transaksi['tanggal_pinjam'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($transaksi['tanggal_kembali_rencana'])) ?></td>
                                    <td>
                                        <?php if ($transaksi['hari_terlambat'] > 0): ?>
                                            <span class="badge bg-danger">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                Terlambat <?= $transaksi['hari_terlambat'] ?> hari
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">
                                                <i class="bi bi-clock me-1"></i>Dipinjam
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-success btn-sm btn-pilih-transaksi" 
                                                data-id="<?= $transaksi['id'] ?>">
                                            <i class="bi bi-check-circle me-1"></i>
                                            Kembalikan
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Function untuk menampilkan detail transaksi
function tampilkanDetailTransaksi(option) {
    const detailDiv = document.getElementById('detail-transaksi');
    
    if (option && option.value) {
        const kode = option.getAttribute('data-kode');
        const anggota = option.getAttribute('data-anggota');
        const buku = option.getAttribute('data-buku');
        const pengarang = option.getAttribute('data-pengarang');
        const cover = option.getAttribute('data-cover');
        const tglPinjam = option.getAttribute('data-tgl-pinjam');
        const tglKembali = option.getAttribute('data-tgl-kembali');
        const terlambat = parseInt(option.getAttribute('data-terlambat'));
        
        const denda = Math.max(0, terlambat) * 1000;
        document.getElementById('denda').value = denda;
        
        detailDiv.innerHTML = `
            <div class="row">
                <div class="col-md-4 text-center">
                    ${cover ? 
                        `<img src="/LibraryManagement/proses/uploads/${cover}" class="book-cover-large mb-2" alt="Cover">` :
                        `<div class="book-cover-large bg-light d-flex align-items-center justify-content-center mb-2 mx-auto">
                            <i class="bi bi-book text-muted" style="font-size: 3rem;"></i>
                         </div>`
                    }
                </div>
                <div class="col-md-8">
                    <h6>Informasi Transaksi</h6>
                    <table class="table table-sm">
                        <tr>
                            <td>Kode Transaksi</td>
                            <td><strong>${kode}</strong></td>
                        </tr>
                        <tr>
                            <td>Anggota</td>
                            <td>${anggota}</td>
                        </tr>
                        <tr>
                            <td>Buku</td>
                            <td><strong>${buku}</strong><br><small class="text-muted">${pengarang}</small></td>
                        </tr>
                        <tr>
                            <td>Tanggal Pinjam</td>
                            <td>${new Date(tglPinjam).toLocaleDateString('id-ID')}</td>
                        </tr>
                        <tr>
                            <td>Tanggal Kembali</td>
                            <td>${new Date(tglKembali).toLocaleDateString('id-ID')}</td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>
                                ${terlambat > 0 ? 
                                    `<span class="badge bg-danger">Terlambat ${terlambat} hari</span>` :
                                    `<span class="badge bg-warning">Dipinjam</span>`
                                }
                            </td>
                        </tr>
                        ${denda > 0 ? `
                        <tr>
                            <td>Denda</td>
                            <td><strong class="text-danger">Rp ${denda.toLocaleString('id-ID')}</strong></td>
                        </tr>
                        ` : ''}
                    </table>
                </div>
            </div>
        `;
    } else {
        document.getElementById('denda').value = 0;
        detailDiv.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="bi bi-arrow-left-right" style="font-size: 3rem;"></i>
                <p class="mt-2 mb-0">Pilih transaksi untuk melihat detail</p>
            </div>
        `;
    }
}

// Event listener untuk select transaksi
document.getElementById('transaksi_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    tampilkanDetailTransaksi(selectedOption);
});

// Event listener untuk tombol pilih transaksi dari tabel
document.querySelectorAll('.btn-pilih-transaksi').forEach(button => {
    button.addEventListener('click', function() {
        const transaksiId = this.getAttribute('data-id');
        const selectElement = document.getElementById('transaksi_id');
        
        // Set selected option
        selectElement.value = transaksiId;
        
        // Trigger change event
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        tampilkanDetailTransaksi(selectedOption);
        
        // Scroll to form
        document.querySelector('.card').scrollIntoView({ behavior: 'smooth' });
    });
});

// Validasi tanggal kembali tidak boleh sebelum tanggal pinjam
document.getElementById('tanggal_kembali_aktual').addEventListener('change', function() {
    const selectElement = document.getElementById('transaksi_id');
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    
    if (selectedOption && selectedOption.value) {
        const tglPinjam = new Date(selectedOption.getAttribute('data-tgl-pinjam'));
        const tglKembaliAktual = new Date(this.value);
        
        if (tglKembaliAktual < tglPinjam) {
            alert('Tanggal kembali tidak boleh sebelum tanggal pinjam');
            this.value = new Date().toISOString().split('T')[0];
        }
    }
});

// Initialize jika ada transaksi terpilih
document.addEventListener('DOMContentLoaded', function() {
    const selectElement = document.getElementById('transaksi_id');
    if (selectElement.value) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        tampilkanDetailTransaksi(selectedOption);
    }
});
</script>

<?php include '../../includes/footer.php'; ?>
