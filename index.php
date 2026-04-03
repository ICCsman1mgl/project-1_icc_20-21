<?php
require_once 'config/database.php';

$pageTitle = 'Beranda';
$pageDescription = 'Platform perpustakaan digital untuk mengelola buku, anggota, dan transaksi peminjaman secara cepat dan aman.';
$canonicalUrl = currentUrl();

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
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> - Sistem Perpustakaan</title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <a class="visually-hidden-focusable" href="#main-content">Lewati ke konten utama</a>
    <main id="main-content" class="container-fluid min-vh-100 d-flex align-items-center py-4">
        <div class="row w-100">
            <section class="col-md-6 d-flex align-items-center justify-content-center" aria-label="Ringkasan layanan perpustakaan">
                <div class="text-center px-3">
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
            </section>
            <section class="col-md-6 d-flex align-items-center justify-content-center" aria-label="Form login sistem">
                <div class="card shadow w-100" style="max-width: 420px;">
                    <div class="card-header bg-primary text-white text-center">
                        <h2 class="h4 mb-0">
                            <i class="bi bi-shield-lock me-2"></i>
                            Login Sistem
                        </h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle me-2" aria-hidden="true"></i>
                                <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="proses/login_proses.php" method="POST" novalidate>
                            <?= csrfField() ?>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person" aria-hidden="true"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" autocomplete="username" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock" aria-hidden="true"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Tampilkan atau sembunyikan password">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Masuk
                            </button>
                        </form>
                        
                        <hr>
                        <p class="text-center text-muted mb-0">
                            Akses admin untuk mengelola koleksi dan anggota, akses pengguna untuk mencari dan meminjam buku.
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <script nonce="<?= htmlspecialchars(cspNonce(), ENT_QUOTES, 'UTF-8') ?>" src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script nonce="<?= htmlspecialchars(cspNonce(), ENT_QUOTES, 'UTF-8') ?>" src="assets/js/script.js"></script>
</body>
</html>
