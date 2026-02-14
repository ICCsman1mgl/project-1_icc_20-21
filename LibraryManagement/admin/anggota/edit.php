<?php
require_once '../../config/database.php';
requireAdmin();

$pageTitle = 'Edit Anggota';
$cssPath = '../../assets/css/style.css';
$jsPath = '../../assets/js/script.js';

// Ambil ID anggota dari parameter
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    $_SESSION['error'] = 'ID anggota tidak valid';
    header('Location: index.php');
    exit();
}

$pdo = getConnection();

// Ambil data anggota
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'user'");
$stmt->execute([$id]);
$anggota = $stmt->fetch();

if (!$anggota) {
    $_SESSION['error'] = 'Anggota tidak ditemukan';
    header('Location: index.php');
    exit();
}

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
                            <h2 class="mb-1">Edit Anggota</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="index.php">Kelola Anggota</a></li>
                                    <li class="breadcrumb-item active">Edit Anggota</li>
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
                                Edit Informasi Anggota
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

                            <form action="../../proses/anggota_edit.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                                <input type="hidden" name="id" value="<?= $anggota['id'] ?>">
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="username" class="form-label">Username *</label>
                                        <input type="text" class="form-control" id="username" name="username" 
                                               value="<?= htmlspecialchars($anggota['username']) ?>" required>
                                        <div class="invalid-feedback">
                                            Username harus diisi.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?= htmlspecialchars($anggota['email']) ?>" required>
                                        <div class="invalid-feedback">
                                            Email harus diisi dengan format yang valid.
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="nama_lengkap" class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                           value="<?= htmlspecialchars($anggota['nama_lengkap']) ?>" required>
                                    <div class="invalid-feedback">
                                        Nama lengkap harus diisi.
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="password" class="form-label">Password Baru</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password" name="password" 
                                                   placeholder="Kosongkan jika tidak ingin mengubah" minlength="6">
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">Kosongkan jika tidak ingin mengubah password</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                               placeholder="Ulangi password baru">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="alamat" class="form-label">Alamat</label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="3"><?= htmlspecialchars($anggota['alamat']) ?></textarea>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="telepon" class="form-label">Nomor Telepon</label>
                                        <input type="tel" class="form-control" id="telepon" name="telepon" 
                                               value="<?= htmlspecialchars($anggota['telepon']) ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="aktif" <?= ($anggota['status'] == 'aktif') ? 'selected' : '' ?>>Aktif</option>
                                            <option value="nonaktif" <?= ($anggota['status'] == 'nonaktif') ? 'selected' : '' ?>>Non-aktif</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="foto" class="form-label">Foto Anggota</label>
                                    <input type="file" class="form-control" id="foto" name="foto" 
                                           accept="image/*" data-preview="foto-preview">
                                    <div class="form-text">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Format: JPG, PNG, GIF. Maksimal 2MB. Kosongkan jika tidak ingin mengubah foto.
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-2"></i>
                                        Update Anggota
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

                <!-- Preview Card -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-eye me-2"></i>
                                Foto Saat Ini
                            </h5>
                        </div>
                        <div class="card-body text-center">
                            <?php if ($anggota['foto']): ?>
                                <img id="foto-current" src="/LibraryManagement/proses/uploads/<?= htmlspecialchars($anggota['foto']) ?>" 
                                     alt="Foto Saat Ini" class="profile-photo-large mb-3">
                            <?php else: ?>
                                <div id="foto-placeholder" class="profile-photo-large bg-light d-flex align-items-center justify-content-center mb-3 mx-auto">
                                    <div class="text-center text-muted">
                                        <i class="bi bi-person" style="font-size: 3rem;"></i>
                                        <p class="mt-2 mb-0">Tidak ada foto</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <img id="foto-preview" src="" alt="Preview Foto" 
                                 class="profile-photo-large mb-3" style="display: none;">
                        </div>
                    </div>

                    <!-- Statistics Card -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-graph-up text-info me-2"></i>
                                Statistik Peminjaman
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php
                            // Ambil statistik peminjaman anggota
                            $stmt = $pdo->prepare("SELECT COUNT(*) as total_pinjam FROM transaksi WHERE user_id = ?");
                            $stmt->execute([$anggota['id']]);
                            $totalPinjam = $stmt->fetchColumn();
                            
                            $stmt = $pdo->prepare("SELECT COUNT(*) as sedang_pinjam FROM transaksi WHERE user_id = ? AND status = 'dipinjam'");
                            $stmt->execute([$anggota['id']]);
                            $sedangPinjam = $stmt->fetchColumn();
                            
                            $stmt = $pdo->prepare("SELECT COUNT(*) as terlambat FROM transaksi WHERE user_id = ? AND status = 'terlambat'");
                            $stmt->execute([$anggota['id']]);
                            $terlambat = $stmt->fetchColumn();
                            ?>
                            <div class="row text-center">
                                <div class="col-4">
                                    <h4 class="text-primary"><?= $totalPinjam ?></h4>
                                    <small class="text-muted">Total Pinjam</small>
                                </div>
                                <div class="col-4">
                                    <h4 class="text-warning"><?= $sedangPinjam ?></h4>
                                    <small class="text-muted">Sedang Pinjam</small>
                                </div>
                                <div class="col-4">
                                    <h4 class="text-danger"><?= $terlambat ?></h4>
                                    <small class="text-muted">Terlambat</small>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center">
                                <small class="text-muted">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    Bergabung: <?= date('d/m/Y', strtotime($anggota['created_at'])) ?>
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
// Preview foto
document.getElementById('foto').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('foto-preview');
    const current = document.getElementById('foto-current');
    const placeholder = document.getElementById('foto-placeholder');
    
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

// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const password = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (password.type === 'password') {
        password.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        password.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
});

// Validasi konfirmasi password
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (password && password !== confirmPassword) {
        this.setCustomValidity('Password tidak sama');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php include '../../includes/footer.php'; ?>
