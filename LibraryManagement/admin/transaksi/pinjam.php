<?php
require_once '../../config/database.php';
requireAdmin();

$pageTitle = 'Pinjam Buku';
$cssPath = '../../assets/css/style.css';
$jsPath = '../../assets/js/script.js';

include '../../includes/header.php';

$pdo = getConnection();

// Ambil data anggota aktif
$anggotaList = $pdo->query("SELECT id, nama_lengkap, username FROM users WHERE role = 'user' AND status = 'aktif' ORDER BY nama_lengkap")->fetchAll();

// Ambil data buku tersedia
$bukuList = $pdo->query("SELECT id, kode_buku, judul, pengarang, cover, jumlah_tersedia FROM buku WHERE status = 'tersedia' AND jumlah_tersedia > 0 ORDER BY judul")->fetchAll();
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
                            <h2 class="mb-1">Pinjam Buku</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="index.php">Transaksi</a></li>
                                    <li class="breadcrumb-item active">Pinjam Buku</li>
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

            <!-- Form Peminjaman -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-box-arrow-up me-2"></i>
                                Form Peminjaman Buku
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="../../proses/pinjam_buku.php" method="POST" class="needs-validation" novalidate>
                                <input type="hidden" name="anggota_id" id="anggota_id">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="kode_transaksi" class="form-label">Kode Transaksi *</label>
                                        <input type="text" class="form-control" id="kode_transaksi" name="kode_transaksi"
                                            value="<?= generateCode('TRX', 8) ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="tanggal_pinjam" class="form-label">Tanggal Pinjam *</label>
                                        <input type="date" class="form-control" id="tanggal_pinjam" name="tanggal_pinjam"
                                            value="<?= date('Y-m-d') ?>" required>
                                        <div class="invalid-feedback">
                                            Tanggal pinjam harus diisi.
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">NIS</label>
                                    <input type="text" id="nis_input" class="form-control" placeholder="Scan atau masukkan NIS">
                                </div>
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">Pilih Anggota *</label>
                                    <select class="form-select" id="user_id" name="user_id" required>
                                        <option value="">-- Pilih Anggota --</option>
                                        <?php foreach ($anggotaList as $anggota): ?>
                                            <option value="<?= $anggota['id'] ?>">
                                                <?= htmlspecialchars($anggota['nama_lengkap']) ?> (<?= htmlspecialchars($anggota['username']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Anggota harus dipilih.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="buku_id" class="form-label">Pilih Buku *</label>
                                    <select class="form-select" id="buku_id" name="buku_id" required>
                                        <option value="">-- Pilih Buku --</option>
                                        <?php foreach ($bukuList as $buku): ?>
                                            <option value="<?= $buku['id'] ?>" data-stok="<?= $buku['jumlah_tersedia'] ?>">
                                                [<?= htmlspecialchars($buku['kode_buku']) ?>] <?= htmlspecialchars($buku['judul']) ?> - <?= htmlspecialchars($buku['pengarang']) ?> (Tersedia: <?= $buku['jumlah_tersedia'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Buku harus dipilih.
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="tanggal_kembali_rencana" class="form-label">Tanggal Kembali Rencana *</label>
                                        <input type="date" class="form-control" id="tanggal_kembali_rencana" name="tanggal_kembali_rencana"
                                            value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                                        <div class="invalid-feedback">
                                            Tanggal kembali harus diisi.
                                        </div>
                                        <div class="form-text">Default: 7 hari dari tanggal pinjam</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lama_pinjam" class="form-label">Lama Pinjam</label>
                                        <input type="text" class="form-control" id="lama_pinjam" readonly>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="catatan" class="form-label">Catatan</label>
                                    <textarea class="form-control" id="catatan" name="catatan" rows="3"
                                        placeholder="Catatan tambahan (opsional)..."></textarea>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-2"></i>
                                        Proses Peminjaman
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

                <!-- Info Card -->
                <div class="col-md-4">
                    <!-- Preview Anggota -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-person me-2"></i>
                                Info Anggota
                            </h6>
                        </div>
                        <div class="card-body" id="anggota-info">
                            <div class="text-center text-muted">
                                <i class="bi bi-person-circle" style="font-size: 3rem;"></i>
                                <p class="mt-2 mb-0">Pilih anggota untuk melihat info</p>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Buku -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-book me-2"></i>
                                Info Buku
                            </h6>
                        </div>
                        <div class="card-body" id="buku-info">
                            <div class="text-center text-muted">
                                <i class="bi bi-book" style="font-size: 3rem;"></i>
                                <p class="mt-2 mb-0">Pilih buku untuk melihat info</p>
                            </div>
                        </div>
                    </div>

                    <!-- Aturan Peminjaman -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-info-circle text-info me-2"></i>
                                Aturan Peminjaman
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <small>Maksimal 3 buku per anggota</small>
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <small>Lama pinjam maksimal 14 hari</small>
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <small>Denda Rp 1.000/hari untuk keterlambatan</small>
                                </li>
                                <li class="mb-0">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <small>Anggota harus dalam status aktif</small>
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
    document.getElementById('nis_input').addEventListener('change', function() {

        const nis = this.value;

        if (!nis) return;

        fetch('/LibraryManagement/proses/get_anggota_by_nis.php?nis=' + nis)
            .then(res => res.json())
            .then(result => {
                if (result.status === 'found') {
                    document.getElementById('anggota_id').value = result.data.id;
                } else {
                    alert('NIS tidak ditemukan');
                    document.getElementById('anggota_id').value = '';
                }
            })
            .catch(err => console.error(err));

    });

    // Hitung lama pinjam otomatis
    function hitungLamaPinjam() {
        const tanggalPinjam = new Date(document.getElementById('tanggal_pinjam').value);
        const tanggalKembali = new Date(document.getElementById('tanggal_kembali_rencana').value);

        if (tanggalPinjam && tanggalKembali && tanggalKembali >= tanggalPinjam) {
            const timeDiff = tanggalKembali.getTime() - tanggalPinjam.getTime();
            const dayDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
            document.getElementById('lama_pinjam').value = dayDiff + ' hari';
        } else {
            document.getElementById('lama_pinjam').value = '';
        }
    }

    // Event listeners
    document.getElementById('tanggal_pinjam').addEventListener('change', hitungLamaPinjam);
    document.getElementById('tanggal_kembali_rencana').addEventListener('change', hitungLamaPinjam);

    // Update tanggal kembali otomatis ketika tanggal pinjam berubah
    document.getElementById('tanggal_pinjam').addEventListener('change', function() {
        const tanggalPinjam = new Date(this.value);
        if (tanggalPinjam) {
            const tanggalKembali = new Date(tanggalPinjam);
            tanggalKembali.setDate(tanggalKembali.getDate() + 7);
            document.getElementById('tanggal_kembali_rencana').value = tanggalKembali.toISOString().split('T')[0];
            hitungLamaPinjam();
        }
    });

    // Load info anggota
    document.getElementById('user_id').addEventListener('change', function() {
        const userId = this.value;
        const infoDiv = document.getElementById('anggota-info');

        if (userId) {
            // AJAX request untuk mengambil info anggota
            fetch(`../../proses/get_anggota_info.php?id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        infoDiv.innerHTML = `
                        <div class="text-center">
                            ${data.foto ? 
                                `<img src="/LibraryManagement/proses/uploads/${data.foto}" class="profile-photo-large mb-2" alt="Foto">` :
                                `<div class="profile-photo-large bg-light d-flex align-items-center justify-content-center mb-2 mx-auto">
                                    <i class="bi bi-person text-muted" style="font-size: 3rem;"></i>
                                 </div>`
                            }
                            <h6>${data.nama_lengkap}</h6>
                            <p class="text-muted mb-2">${data.username}</p>
                            <small class="text-muted">${data.email}</small>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6">
                                <strong>${data.total_pinjam}</strong>
                                <br><small class="text-muted">Total Pinjam</small>
                            </div>
                            <div class="col-6">
                                <strong class="text-warning">${data.sedang_pinjam}</strong>
                                <br><small class="text-muted">Sedang Pinjam</small>
                            </div>
                        </div>
                    `;

                        // Cek apakah anggota sudah mencapai batas maksimal
                        if (data.sedang_pinjam >= 3) {
                            infoDiv.innerHTML += `
                            <div class="alert alert-warning mt-2 mb-0">
                                <small><i class="bi bi-exclamation-triangle me-1"></i>
                                Anggota sudah mencapai batas maksimal peminjaman</small>
                            </div>
                        `;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    infoDiv.innerHTML = '<div class="text-danger">Error loading data</div>';
                });
        } else {
            infoDiv.innerHTML = `
            <div class="text-center text-muted">
                <i class="bi bi-person-circle" style="font-size: 3rem;"></i>
                <p class="mt-2 mb-0">Pilih anggota untuk melihat info</p>
            </div>
        `;
        }
    });

    // Load info buku
    document.getElementById('buku_id').addEventListener('change', function() {
        const bukuId = this.value;
        const infoDiv = document.getElementById('buku-info');

        if (bukuId) {
            // AJAX request untuk mengambil info buku
            fetch(`../../proses/get_buku_info.php?id=${bukuId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        infoDiv.innerHTML = `
                        <div class="text-center">
                            ${data.cover ? 
                                `<img src="/LibraryManagement/proses/uploads/${data.cover}" class="book-cover-large mb-2" alt="Cover">` :
                                `<div class="book-cover-large bg-light d-flex align-items-center justify-content-center mb-2 mx-auto">
                                    <i class="bi bi-book text-muted" style="font-size: 3rem;"></i>
                                 </div>`
                            }
                            <h6>${data.judul}</h6>
                            <p class="text-muted mb-1">${data.pengarang}</p>
                            <small class="text-muted">[${data.kode_buku}]</small>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6">
                                <strong>${data.jumlah_total}</strong>
                                <br><small class="text-muted">Total</small>
                            </div>
                            <div class="col-6">
                                <strong class="text-success">${data.jumlah_tersedia}</strong>
                                <br><small class="text-muted">Tersedia</small>
                            </div>
                        </div>
                    `;

                        if (data.jumlah_tersedia <= 0) {
                            infoDiv.innerHTML += `
                            <div class="alert alert-danger mt-2 mb-0">
                                <small><i class="bi bi-exclamation-circle me-1"></i>
                                Buku sedang tidak tersedia</small>
                            </div>
                        `;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    infoDiv.innerHTML = '<div class="text-danger">Error loading data</div>';
                });
        } else {
            infoDiv.innerHTML = `
            <div class="text-center text-muted">
                <i class="bi bi-book" style="font-size: 3rem;"></i>
                <p class="mt-2 mb-0">Pilih buku untuk melihat info</p>
            </div>
        `;
        }
    });

    // Validasi maksimal 14 hari
    document.getElementById('tanggal_kembali_rencana').addEventListener('change', function() {
        const tanggalPinjam = new Date(document.getElementById('tanggal_pinjam').value);
        const tanggalKembali = new Date(this.value);

        if (tanggalPinjam && tanggalKembali) {
            const timeDiff = tanggalKembali.getTime() - tanggalPinjam.getTime();
            const dayDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));

            if (dayDiff > 14) {
                alert('Lama peminjaman maksimal 14 hari');
                const maxDate = new Date(tanggalPinjam);
                maxDate.setDate(maxDate.getDate() + 14);
                this.value = maxDate.toISOString().split('T')[0];
            }
            hitungLamaPinjam();
        }
    });

    // Initialize
    hitungLamaPinjam();
</script>

<?php include '../../includes/footer.php'; ?>