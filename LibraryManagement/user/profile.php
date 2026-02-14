<?php
require_once '../config/database.php';
requireLogin();

$pdo = getConnection();

// Ambil data user yang sedang login
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error'] = 'Data user tidak ditemukan!';
    header('Location: dashboard.php');
    exit();
}

// Ambil statistik peminjaman user
$stmt = $pdo->prepare("SELECT 
    COUNT(*) as total_pinjam,
    COUNT(CASE WHEN status = 'dipinjam' THEN 1 END) as sedang_pinjam,
    COUNT(CASE WHEN status = 'dikembalikan' THEN 1 END) as sudah_kembali,
    COALESCE(SUM(denda), 0) as total_denda
    FROM transaksi WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-person-gear me-2"></i>Profil Saya</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Beranda</a></li>
                        <li class="breadcrumb-item active">Profil</li>
                    </ol>
                </nav>
            </div>

            <?php include '../includes/alerts.php'; ?>

            <div class="row">
                <!-- Profile Info -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <?php if (!empty($user['foto'])): ?>
                                <img src="../uploads/<?= htmlspecialchars($user['foto']) ?>" 
                                     alt="Profile" class="rounded-circle mb-3" 
                                     style="width: 150px; height: 150px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" 
                                     style="width: 150px; height: 150px;">
                                    <i class="bi bi-person" style="font-size: 4rem; color: white;"></i>
                                </div>
                            <?php endif; ?>
                            
                            <h4><?= htmlspecialchars($user['nama_lengkap']) ?></h4>
                            <p class="text-muted">Anggota Perpustakaan</p>
                            <p class="text-muted"><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($user['email']) ?></p>
                            <p class="text-muted"><i class="bi bi-person-badge me-2"></i><?= htmlspecialchars($user['username']) ?></p>
                            
                            <span class="badge <?= $user['status'] === 'aktif' ? 'bg-success' : 'bg-danger' ?>">
                                <?= ucfirst($user['status']) ?>
                            </span>
                            
                            <p class="text-muted mt-2">
                                <small>Bergabung: <?= date('d/m/Y', strtotime($user['created_at'])) ?></small>
                            </p>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Statistik Peminjaman</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-12 mb-2">
                                    <div class="border-bottom pb-2">
                                        <h5 class="mb-0 text-primary"><?= $stats['total_pinjam'] ?></h5>
                                        <small class="text-muted">Total Peminjaman</small>
                                    </div>
                                </div>
                                <div class="col-12 mb-2">
                                    <div class="border-bottom pb-2">
                                        <h5 class="mb-0 text-warning"><?= $stats['sedang_pinjam'] ?></h5>
                                        <small class="text-muted">Sedang Dipinjam</small>
                                    </div>
                                </div>
                                <div class="col-12 mb-2">
                                    <div class="border-bottom pb-2">
                                        <h5 class="mb-0 text-success"><?= $stats['sudah_kembali'] ?></h5>
                                        <small class="text-muted">Sudah Dikembalikan</small>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <h5 class="mb-0 text-danger">Rp <?= number_format($stats['total_denda'], 0, ',', '.') ?></h5>
                                    <small class="text-muted">Total Denda</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Form -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Edit Profil</h5>
                        </div>
                        <div class="card-body">
                            <form action="../proses/user_profile_update.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="username" class="form-label">Username *</label>
                                        <input type="text" class="form-control" id="username" name="username" 
                                               value="<?= htmlspecialchars($user['username']) ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?= htmlspecialchars($user['email']) ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="nama_lengkap" class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                           value="<?= htmlspecialchars($user['nama_lengkap']) ?>" required>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="telepon" class="form-label">Telepon</label>
                                        <input type="text" class="form-control" id="telepon" name="telepon" 
                                               value="<?= htmlspecialchars($user['telepon']) ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="foto" class="form-label">Foto Profil</label>
                                        <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                                        <small class="text-muted">Format: JPG, PNG, GIF (Max: 2MB)</small>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="alamat" class="form-label">Alamat</label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="3"><?= htmlspecialchars($user['alamat']) ?></textarea>
                                </div>

                                <hr>
                                <h6>Ubah Password (Opsional)</h6>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">Password Baru</label>
                                        <input type="password" class="form-control" id="password" name="password" 
                                               placeholder="Kosongkan jika tidak ingin mengubah">
                                        <small class="text-muted">Minimal 6 karakter</small>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                               placeholder="Ulangi password baru">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="dashboard.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-2"></i>Kembali
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-2"></i>Update Profil
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Borrowed Books -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Buku Yang Sedang Dipinjam</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $stmt = $pdo->prepare("SELECT t.*, b.judul, b.pengarang, b.cover, b.kode_buku
                                FROM transaksi t
                                JOIN buku b ON t.buku_id = b.id
                                WHERE t.user_id = ? AND t.status = 'dipinjam'
                                ORDER BY t.tanggal_kembali_rencana ASC");
                            $stmt->execute([$_SESSION['user_id']]);
                            $currentBooks = $stmt->fetchAll();
                            ?>
                            
                            <?php if (empty($currentBooks)): ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-book text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2">Tidak ada buku yang sedang dipinjam</p>
                                    <a href="katalog.php" class="btn btn-primary">
                                        <i class="bi bi-search me-2"></i>Cari Buku
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($currentBooks as $book): ?>
                                        <?php
                                        $isOverdue = strtotime($book['tanggal_kembali_rencana']) < time();
                                        $daysLeft = floor((strtotime($book['tanggal_kembali_rencana']) - time()) / 86400);
                                        ?>
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card h-100 <?= $isOverdue ? 'border-danger' : ($daysLeft <= 3 ? 'border-warning' : '') ?>">
                                                <div class="card-body">
                                                    <h6 class="card-title"><?= htmlspecialchars($book['judul']) ?></h6>
                                                    <p class="card-text">
                                                        <small class="text-muted">
                                                            <strong>Pengarang:</strong> <?= htmlspecialchars($book['pengarang']) ?><br>
                                                            <strong>Kode:</strong> <?= htmlspecialchars($book['kode_buku']) ?><br>
                                                            <strong>Dipinjam:</strong> <?= date('d/m/Y', strtotime($book['tanggal_pinjam'])) ?><br>
                                                            <strong>Harus kembali:</strong> <?= date('d/m/Y', strtotime($book['tanggal_kembali_rencana'])) ?>
                                                        </small>
                                                    </p>
                                                    
                                                    <?php if ($isOverdue): ?>
                                                        <span class="badge bg-danger">Terlambat <?= abs($daysLeft) ?> hari</span>
                                                    <?php elseif ($daysLeft <= 3): ?>
                                                        <span class="badge bg-warning text-dark">Jatuh tempo <?= $daysLeft ?> hari lagi</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success"><?= $daysLeft ?> hari lagi</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>