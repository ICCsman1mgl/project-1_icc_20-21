<?php
require_once 'config/database.php';

// Redirect berdasarkan status login
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/dashboard.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid vh-100 d-flex align-items-center">
        <div class="row w-100">
            <div class="col-md-6 d-flex align-items-center justify-content-center">
                <div class="text-center">
                    <i class="bi bi-book text-primary" style="font-size: 5rem;"></i>
                    <h1 class="display-4 fw-bold text-primary">Perpustakaan Digital</h1>
                    <p class="lead text-muted">Sistem manajemen perpustakaan yang modern dan efisien</p>
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="bi bi-book-fill text-primary mb-2" style="font-size: 2rem;"></i>
                                    <h6>Kelola Buku</h6>
                                    <small class="text-muted">Manajemen koleksi buku yang lengkap</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="bi bi-people-fill text-success mb-2" style="font-size: 2rem;"></i>
                                    <h6>Kelola Anggota</h6>
                                    <small class="text-muted">Manajemen data anggota perpustakaan</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="bi bi-arrow-left-right text-warning mb-2" style="font-size: 2rem;"></i>
                                    <h6>Transaksi</h6>
                                    <small class="text-muted">Peminjaman dan pengembalian buku</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 d-flex align-items-center justify-content-center">
                <div class="card shadow" style="width: 400px;">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0">
                            <i class="bi bi-shield-lock me-2"></i>
                            Login Sistem
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="proses/login_proses.php" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Masuk
                            </button>
                        </form>
                        
                        <hr>
                       
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
