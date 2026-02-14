<?php
// Tentukan base URL berdasarkan role user
$baseUrl = '';
if (isset($_SESSION['role'])) {
    $baseUrl = ($_SESSION['role'] === 'admin') ? '/admin/' : '/user/';
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container-fluid">
        <!-- Brand -->
        <a class="navbar-brand" href="<?= $baseUrl ?>dashboard.php">
            <i class="bi bi-book me-2"></i>
            Perpustakaan Digital
        </a>

        <!-- Mobile toggle button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation items -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if (isAdmin()): ?>
                    <!-- Admin Navigation -->
                    <li class="nav-item">
                        <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : '' ?>"
                            href="/LibraryManagement/admin/dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-book me-1"></i>Kelola Buku
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="/LibraryManagement/admin/buku/Daftar_Buku.php">
                                    <i class="bi bi-list-ul me-2"></i>Daftar Buku
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/LibraryManagement/admin/buku/tambah.php">
                                    <i class="bi bi-plus-circle me-2"></i>Tambah Buku
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/LibraryManagement/admin/buku/edit.php">
                                    <i class="bi bi-pencil-square me-2"></i>Edit Buku
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/LibraryManagement/admin/buku/hapus.php">
                                    <i class="bi bi-trash me-2"></i>Hapus Buku
                                </a>
                            </li>
                        </ul>
                    </li>


            </ul>
            <<li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="bi bi-people me-1"></i>Kelola Anggota
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/LibraryManagement/admin/anggota/tambah.php">
                            <i class="bi bi-person-plus me-2"></i>Tambah Anggota
                        </a></li>
                    <li><a class="dropdown-item" href="/LibraryManagement/admin/anggota/index.php">
                            <i class="bi bi-people me-2"></i>Daftar Anggota
                        </a></li>
                </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-arrow-left-right me-1"></i>Transaksi
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/LibraryManagement/admin/transaksi/pinjam.php">
                                <i class="bi bi-box-arrow-up me-2"></i>Peminjaman
                            </a></li>
                        <li><a class="dropdown-item" href="/LibraryManagement/admin/transaksi/kembali.php">
                                <i class="bi bi-box-arrow-down me-2"></i>Pengembalian
                            </a></li>
                        <li><a class="dropdown-item" href="/LibraryManagement/admin/transaksi/index.php">
                                <i class="bi bi-list-ul me-2"></i>Riwayat Transaksi
                            </a></li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/LibraryManagement/admin/laporan/index.php">
                        <i class="bi bi-file-earmark-text me-1"></i>Laporan
                    </a>
                    <a class="nav-link" href="/LibraryManagement/admin/anggota/import.php">
                        <i class="fas fa-file-import"></i> Import Anggota
                    </a>

                </li>


            <?php else: ?>
                <!-- User Navigation -->
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : '' ?>"
                        href="/LibraryManagement/user/dashboard.php">
                        <i class="bi bi-house me-1"></i>Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'katalog.php') ? 'active' : '' ?>"
                        href="/LibraryManagement/user/katalog.php">
                        <i class="bi bi-search me-1"></i>Katalog Buku
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'riwayat.php') ? 'active' : '' ?>"
                        href="/LibraryManagement/user/riwayat.php">
                        <i class="bi bi-clock-history me-1"></i>Riwayat Pinjam
                    </a>
                </li>
            <?php endif; ?>
            </ul>

            <!-- User menu -->
            <ul class="navbar-nav">
                <!-- Search (untuk user) -->
                <?php if (!isAdmin()): ?>
                    <li class="nav-item me-3">
                        <form class="d-flex" action="/user/katalog.php" method="GET">
                            <div class="input-group">
                                <input class="form-control form-control-sm" type="search"
                                    name="search" placeholder="Cari buku..."
                                    value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                                <button class="btn btn-outline-light btn-sm" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>
                    </li>
                <?php endif; ?>

                <?php
                // Notifikasi jumlah
                $pdo = getConnection();
                $queryCount = "SELECT COUNT(*) as count 
               FROM transaksi
               WHERE status = 'dipinjam' 
               AND DATEDIFF(tanggal_kembali_rencana, CURDATE()) <= 3 
               AND DATEDIFF(tanggal_kembali_rencana, CURDATE()) >= 0";
                if (!isAdmin()) {
                    $queryCount .= " AND user_id = " . $_SESSION['user_id'];
                }
                $notifStmt = $pdo->prepare($queryCount);
                $notifStmt->execute();
                $notifCount = $notifStmt->fetch()['count'];

                ?>

                <!-- Icon Notifikasi -->
                <li class="nav-item dropdown me-3">
                    <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        <?php if ($notifCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $notifCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php if ($notifCount > 0): ?>
                            <li>
                                <h6 class="dropdown-header">Notifikasi Pengembalian</h6>
                            </li>
                            <?php
                            $queryDetail = "SELECT b.judul, t.tanggal_kembali_rencana
                            FROM transaksi t 
                            JOIN buku b ON t.buku_id = b.id
                            WHERE t.status = 'dipinjam' 
                            AND DATEDIFF(tanggal_kembali_rencana, CURDATE()) <= 3 
                            AND DATEDIFF(tanggal_kembali_rencana, CURDATE()) >= 0";
                            if (!isAdmin()) {
                                $queryDetail .= " AND t.user_id = " . $_SESSION['user_id'];
                            }
                            $queryDetail .= " LIMIT 5";

                            $detailStmt = $pdo->prepare($queryDetail);
                            $detailStmt->execute();
                            $notifications = $detailStmt->fetchAll();

                            foreach ($notifications as $notif): ?>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        Buku: <strong><?= htmlentities($notif['judul']); ?></strong><br />
                                        Jatuh Tempo: <strong><?= htmlentities($notif['tanggal_kembali_rencana']); ?></strong>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><span class="dropdown-item">Tidak ada notifikasi</span></li>
                        <?php endif; ?>
                    </ul>
                </li>


                <!-- User dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <?php if (!empty($_SESSION['foto'])): ?>
                            <img src="LibraryManagemet/proses/uploads/<?= htmlspecialchars($_SESSION['foto']) ?>"
                                alt="Profile" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                        <?php else: ?>
                            <i class="bi bi-person-circle me-2" style="font-size: 1.5rem;"></i>
                        <?php endif; ?>
                        <span class="d-none d-md-inline"><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <h6 class="dropdown-header">
                                <i class="bi bi-person me-2"></i><?= htmlspecialchars($_SESSION['nama_lengkap']) ?>
                                <br><small class="text-muted"><?= ucfirst($_SESSION['role']) ?></small>
                            </h6>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <?php if (isAdmin()): ?>
                            <li><a class="dropdown-item" href="/LibraryManagement/admin/profile.php">
                                    <i class="bi bi-person-gear me-2"></i>Profil
                                </a></li>
                            <li><a class="dropdown-item" href="/LibraryManagement/admin/settings.php">
                                    <i class="bi bi-gear me-2"></i>Pengaturan
                                </a></li>
                        <?php else: ?>
                            <li><a class="dropdown-item" href="/LibraryManagement/user/profile.php">
                                    <i class="bi bi-person-gear me-2"></i>Profil Saya
                                </a></li>
                        <?php endif; ?>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item text-danger" href="/LibraryManagement/logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                        </li>

                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>