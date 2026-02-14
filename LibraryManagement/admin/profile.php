<?php
require_once '../config/database.php';
requireLogin();
requireAdmin();

$pdo = getConnection();

// Ambil data admin yang sedang login
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch();

if (!$admin) {
    $_SESSION['error'] = 'Data admin tidak ditemukan!';
    header('Location: dashboard.php');
    exit();
}
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar (optional for future use) -->
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-person-gear me-2"></i>Profil Admin</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
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
                            <?php if (!empty($admin['foto'])): ?>
                            <img src="../uploads/<?= htmlspecialchars($admin['foto']); ?>?v=<?= time(); ?>"
     alt="Profile"
     class="rounded-circle"
     style="width:150px;height:150px;object-fit:cover;">


                            <?php else: ?>
                                <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" 
                                     style="width: 150px; height: 150px;">
                                    <i class="bi bi-person" style="font-size: 4rem; color: white;"></i>
                                </div>
                            <?php endif; ?>
                            
                            <h4><?= htmlspecialchars($admin['nama_lengkap']) ?></h4>
                            <p class="text-muted"><?= ucfirst($admin['role']) ?></p>
                            <p class="text-muted"><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($admin['email']) ?></p>
                            <p class="text-muted"><i class="bi bi-person-badge me-2"></i><?= htmlspecialchars($admin['username']) ?></p>
                            
                            <span class="badge <?= $admin['status'] === 'aktif' ? 'bg-success' : 'bg-danger' ?>">
                                <?= ucfirst($admin['status']) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Statistik</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            // Statistik admin
                          $stmt = $pdo->prepare("
    SELECT 
        (SELECT COUNT(*) FROM transaksi WHERE created_by = ?) AS transaksi_dibuat,
        (SELECT COUNT(*) FROM buku) AS buku_ditambah,
        (SELECT COUNT(*) FROM users WHERE role = 'user') AS anggota_ditambah
");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();

                            ?>
                            
                            <div class="row text-center">
                                <div class="col-12 mb-2">
                                    <div class="border-bottom pb-2">
                                        <h5 class="mb-0"><?= $stats['transaksi_dibuat'] ?></h5>
                                        <small class="text-muted">Transaksi Diproses</small>
                                    </div>
                                </div>
                                <div class="col-12 mb-2">
                                    <div class="border-bottom pb-2">
                                        <h5 class="mb-0"><?= $stats['buku_ditambah'] ?></h5>
                                        <small class="text-muted">Buku Ditambahkan</small>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <h5 class="mb-0"><?= $stats['anggota_ditambah'] ?></h5>
                                    <small class="text-muted">Anggota Ditambahkan</small>
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
                            <form action="../proses/admin_profile_update.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= $admin['id'] ?>">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="username" class="form-label">Username *</label>
                                        <input type="text" class="form-control" id="username" name="username" 
                                               value="<?= htmlspecialchars($admin['username']) ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?= htmlspecialchars($admin['email']) ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="nama_lengkap" class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                           value="<?= htmlspecialchars($admin['nama_lengkap']) ?>" required>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="telepon" class="form-label">Telepon</label>
                                        <input type="text" class="form-control" id="telepon" name="telepon" 
                                               value="<?= htmlspecialchars($admin['telepon']) ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="foto" class="form-label">Foto Profil</label>
                                        <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                                        <small class="text-muted">Format: JPG, PNG, GIF (Max: 2MB)</small>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="alamat" class="form-label">Alamat</label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="3"><?= htmlspecialchars($admin['alamat']) ?></textarea>
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
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>