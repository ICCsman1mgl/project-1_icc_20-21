<?php
require_once '../config/database.php';
requireLogin();

$pageTitle = 'Pinjam Buku';
$cssPath = '../assets/css/style.css';
$jsPath = '../assets/js/script.js';

include '../includes/header.php';

$pdo = getConnection();
$userId = $_SESSION['user_id'];

// Cek buku yang dipilih dari parameter
$bukuId = isset($_GET['buku_id']) ? (int)$_GET['buku_id'] : 0;
$selectedBuku = null;

if ($bukuId) {
    $stmt = $pdo->prepare("SELECT b.*, k.nama_kategori 
                           FROM buku b 
                           LEFT JOIN kategori k ON b.kategori_id = k.id 
                           WHERE b.id = ? AND b.status = 'tersedia' AND b.jumlah_tersedia > 0");
    $stmt->execute([$bukuId]);
    $selectedBuku = $stmt->fetch();
}

// Cek jumlah buku yang sedang dipinjam user
$stmt = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE user_id = ? AND status = 'dipinjam'");
$stmt->execute([$userId]);
$jumlahDipinjam = $stmt->fetchColumn();

// Cek apakah user sudah meminjam buku ini
$sudahDipinjam = false;
if ($bukuId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE user_id = ? AND buku_id = ? AND status = 'dipinjam'");
    $stmt->execute([$userId, $bukuId]);
    $sudahDipinjam = $stmt->fetchColumn() > 0;
}

// Ambil data user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Ambil daftar buku tersedia (untuk dropdown jika tidak ada yang dipilih)
$bukuTersedia = $pdo->query("SELECT id, kode_buku, judul, pengarang FROM buku WHERE status = 'tersedia' AND jumlah_tersedia > 0 ORDER BY judul")->fetchAll();
?>

<?php include '../includes/navbar.php'; ?>

<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-bookmark-plus me-2"></i>
                        Pinjam Buku
                    </h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="katalog.php">Katalog</a></li>
                            <li class="breadcrumb-item active">Pinjam Buku</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="katalog.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Katalog
                    </a>
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

    <!-- Validasi Batas Peminjaman -->
    <?php if ($jumlahDipinjam >= 3): ?>
        <div class="alert alert-warning" role="alert">
            <h6 class="alert-heading">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Batas Peminjaman Tercapai
            </h6>
            <p class="mb-0">
                Anda sudah mencapai batas maksimal peminjaman (3 buku).
                Silakan kembalikan buku yang sudah dipinjam untuk dapat meminjam buku lain.
                <a href="riwayat.php" class="alert-link">Lihat riwayat peminjaman</a>
            </p>
        </div>
    <?php elseif ($sudahDipinjam): ?>
        <div class="alert alert-info" role="alert">
            <h6 class="alert-heading">
                <i class="bi bi-info-circle me-2"></i>
                Buku Sudah Dipinjam
            </h6>
            <p class="mb-0">
                Anda sudah meminjam buku ini dan belum mengembalikannya.
                Silakan pilih buku lain atau <a href="riwayat.php" class="alert-link">lihat status peminjaman</a>.
            </p>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Form Peminjaman -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-form-check me-2"></i>
                        Form Peminjaman Buku
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($jumlahDipinjam >= 3 || $sudahDipinjam): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Tidak Dapat Meminjam</h5>
                            <p class="text-muted">
                                <?php if ($jumlahDipinjam >= 3): ?>
                                    Anda sudah mencapai batas maksimal peminjaman
                                <?php else: ?>
                                    Buku ini sudah Anda pinjam
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <form action="../proses/pinjam_buku.php" method="POST" class="needs-validation" novalidate>

                            <input type="hidden" name="user_id" value="<?= $userId ?>">
                            <input type="hidden" name="redirect" value="user">

                            <!-- Column Anggota -->
                            <input type="hidden" name="anggota_id" id="anggota_id">



                            <!-- Info Peminjam -->
                            <div class="mb-3">
                                <label class="form-label">NIS</label>
                                <input type="text" id="nis_input" class="form-control" placeholder="Scan atau masukkan NIS" required>
                            </div>

                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">Informasi Peminjam</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong><?= htmlspecialchars($user['nama_lengkap']) ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars($user['username']) ?></small>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
                                            <br><small class="text-success">Sedang meminjam: <?= $jumlahDipinjam ?>/3 buku</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="kode_transaksi" class="form-label">Kode Transaksi</label>
                                    <input type="text" class="form-control" id="kode_transaksi" name="kode_transaksi"
                                        value="<?= generateCode('TRX', 8) ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="tanggal_pinjam" class="form-label">Tanggal Pinjam</label>
                                    <input type="date" class="form-control" id="tanggal_pinjam" name="tanggal_pinjam"
                                        value="<?= date('Y-m-d') ?>" readonly>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="buku_id" class="form-label">Pilih Buku *</label>
                                <select class="form-select" id="buku_id" name="buku_id" required
                                    <?= $selectedBuku ? 'disabled' : '' ?>>
                                    <?php if ($selectedBuku): ?>
                                        <option value="<?= $selectedBuku['id'] ?>" selected>
                                            [<?= htmlspecialchars($selectedBuku['kode_buku']) ?>]
                                            <?= htmlspecialchars($selectedBuku['judul']) ?> -
                                            <?= htmlspecialchars($selectedBuku['pengarang']) ?>
                                        </option>
                                        <input type="hidden" name="buku_id" value="<?= $selectedBuku['id'] ?>">
                                    <?php else: ?>
                                        <option value="">-- Pilih Buku yang Ingin Dipinjam --</option>
                                        <?php foreach ($bukuTersedia as $buku): ?>
                                            <option value="<?= $buku['id'] ?>">
                                                [<?= htmlspecialchars($buku['kode_buku']) ?>]
                                                <?= htmlspecialchars($buku['judul']) ?> -
                                                <?= htmlspecialchars($buku['pengarang']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
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
                                    <div class="form-text">Maksimal 14 hari dari tanggal pinjam</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="lama_pinjam" class="form-label">Lama Pinjam</label>
                                    <input type="text" class="form-control" id="lama_pinjam" readonly>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="catatan" class="form-label">Catatan (Opsional)</label>
                                <textarea class="form-control" id="catatan" name="catatan" rows="3"
                                    placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                            </div>

                            <!-- Persetujuan -->
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="setuju" required>
                                    <label class="form-check-label" for="setuju">
                                        Saya setuju dengan <a href="#" data-bs-toggle="modal" data-bs-target="#aturanModal">
                                            aturan peminjaman
                                        </a> yang berlaku
                                    </label>
                                    <div class="invalid-feedback">
                                        Anda harus menyetujui aturan peminjaman.
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-bookmark-plus me-2"></i>
                                    Ajukan Peminjaman
                                </button>
                                <a href="katalog.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>
                                    Kembali
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Info Buku dan Aturan -->
        <div class="col-md-4">
            <!-- Detail Buku -->
            <?php if ($selectedBuku): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-book me-2"></i>
                            Detail Buku
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <?php if ($selectedBuku['cover']): ?>
                                <img src="/LibraryManagement/proses/uploads/<?= htmlspecialchars($selectedBuku['cover']) ?>"
                                    class="book-cover-large" alt="Cover">
                            <?php else: ?>
                                <div class="book-cover-large bg-light d-flex align-items-center justify-content-center mx-auto">
                                    <i class="bi bi-book text-muted" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <h6><?= htmlspecialchars($selectedBuku['judul']) ?></h6>
                        <p class="text-muted mb-2"><?= htmlspecialchars($selectedBuku['pengarang']) ?></p>

                        <table class="table table-sm table-borderless">
                            <tr>
                                <td>Kode</td>
                                <td><code><?= htmlspecialchars($selectedBuku['kode_buku']) ?></code></td>
                            </tr>
                            <tr>
                                <td>Kategori</td>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($selectedBuku['nama_kategori']) ?></span></td>
                            </tr>
                            <tr>
                                <td>Tersedia</td>
                                <td><span class="badge bg-success"><?= $selectedBuku['jumlah_tersedia'] ?></span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

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
                            <small>Maksimal meminjam 3 buku sekaligus</small>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <small>Lama peminjaman maksimal 14 hari</small>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <small>Denda keterlambatan Rp 1.000/hari</small>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <small>Buku harus dikembalikan dalam kondisi baik</small>
                        </li>
                        <li class="mb-0">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <small>Perpanjangan dapat dilakukan maksimal 1x</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Aturan -->
<div class="modal fade" id="aturanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Aturan Peminjaman Buku</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Ketentuan Umum:</h6>
                <ol>
                    <li>Setiap anggota maksimal meminjam 3 buku sekaligus</li>
                    <li>Masa peminjaman maksimal 14 hari kalender</li>
                    <li>Buku harus dikembalikan tepat waktu</li>
                    <li>Perpanjangan dapat dilakukan maksimal 1 kali</li>
                </ol>

                <h6>Denda dan Sanksi:</h6>
                <ol>
                    <li>Keterlambatan pengembalian: Rp 1.000 per hari</li>
                    <li>Kehilangan atau kerusakan buku akan dikenakan ganti rugi</li>
                    <li>Anggota yang menunggak denda tidak dapat meminjam buku baru</li>
                </ol>

                <h6>Kewajiban Peminjam:</h6>
                <ol>
                    <li>Menjaga kondisi buku dengan baik</li>
                    <li>Tidak meminjamkan kepada orang lain</li>
                    <li>Mengembalikan buku sesuai jadwal</li>
                    <li>Melaporkan jika ada kerusakan atau kehilangan</li>
                </ol>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Saya Mengerti</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Lookup anggota berdasarkan NIS
    document.getElementById('nis_input').addEventListener('change', function() {
        let nis = this.value;

        fetch('/LibraryManagement/proses/get_anggota_by_nis.php?nis=' + nis)
            .then(response => response.json())
            .then(result => {
                if (result.status === 'found') {
                    document.getElementById('anggota_id').value = result.data.id;
                    alert("Anggota ditemukan: " + result.data.nama);
                } else {
                    alert("NIS tidak ditemukan");
                    document.getElementById('anggota_id').value = '';
                }
            });
    });

    // Hitung lama pinjam otomatis
    function hitungLamaPinjam() {
        const tanggalPinjam = new Date(document.getElementById('tanggal_pinjam').value);
        const tanggalKembali = new Date(document.getElementById('tanggal_kembali_rencana').value);

        if (tanggalPinjam && tanggalKembali && tanggalKembali >= tanggalPinjam) {
            const timeDiff = tanggalKembali.getTime() - tanggalPinjam.getTime();
            const dayDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
            document.getElementById('lama_pinjam').value = dayDiff + ' hari';

            // Validasi maksimal 14 hari
            if (dayDiff > 14) {
                alert('Lama peminjaman maksimal 14 hari');
                const maxDate = new Date(tanggalPinjam);
                maxDate.setDate(maxDate.getDate() + 14);
                document.getElementById('tanggal_kembali_rencana').value = maxDate.toISOString().split('T')[0];
                hitungLamaPinjam(); // Recalculate
            }
        } else {
            document.getElementById('lama_pinjam').value = '';
        }
    }

    // Event listeners
    document.getElementById('tanggal_kembali_rencana').addEventListener('change', hitungLamaPinjam);

    // Set minimum date untuk tanggal kembali (besok)
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    document.getElementById('tanggal_kembali_rencana').min = tomorrow.toISOString().split('T')[0];

    // Set maximum date (14 hari dari sekarang)
    const maxDate = new Date(today);
    maxDate.setDate(maxDate.getDate() + 14);
    document.getElementById('tanggal_kembali_rencana').max = maxDate.toISOString().split('T')[0];

    // Initialize calculation
    hitungLamaPinjam();
</script>

<?php include '../includes/footer.php'; ?>