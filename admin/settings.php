<?php
require_once '../config/database.php';
requireLogin();
requireAdmin();

$pdo = getConnection();



if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'backup':
            $backupFile = __DIR__ . '/../backup/backup_' . date('Y-m-d-H-i-s') . '.sql';
            $command = "mysqldump -h localhost -u root perpustakaan > $backupFile";
            system($command);
            header("Location: settings.php?status=backup_done");
            exit;

        case 'export':
            $transactions = $pdo->query("
                SELECT kode_transaksi, user_id, buku_id, status, 
                       tanggal_pinjam, tanggal_kembali_rencana, tanggal_kembali_aktual, denda 
                FROM transaksi
            ")->fetchAll();

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename="export_transaksi_' . date('Y-m-d') . '.csv"');
            
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Kode Transaksi',
                'User ID',
                'Buku ID',
                'Status',
                'Tanggal Pinjam',
                'Tanggal Kembali Rencana',
                'Tanggal Kembali Aktual',
                'Denda'
            ]);

            foreach ($transactions as $trans) {
                fputcsv($out, $trans);
            }

            fclose($out);
            exit;

        case 'clear_cache':
            $cacheDir = __DIR__ . '/../cache/';
            if (is_dir($cacheDir)) {
                foreach (glob($cacheDir . "*") as $cacheFile) {
                    if (is_file($cacheFile)) {
                        unlink($cacheFile);
                    }
                }
            }
            header("Location: settings.php?status=cache_cleared");
            exit;

        case 'maintenance':
            file_put_contents(__DIR__ . '/maintenance.txt', '1');
            header("Location: settings.php?status=maintenance_on");
            exit;

        default:
            header("Location: settings.php");
            exit;
    }
}

// Ambil pengaturan sistem (simulasi, bisa dikembangkan dengan tabel settings)
$systemSettings = [
    'nama_perpustakaan' => 'Perpustakaan Digital',
    'alamat_perpustakaan' => 'Jl.H,Ali , Kampung Tengah Kramat Jati , Jakarta Timur',
    'telepon_perpustakaan' => '(+6287818894504)',
    'email_perpustakaan' => 'PerpusKy11@gmail.com',
    'max_pinjam_per_anggota' => 3,
    'lama_pinjam_default' => 14,
    'denda_per_hari' => 1000,
    'jam_operasional' => '08:00 - 17:00'
];

// Ambil statistik sistem
$stmt = $pdo->prepare("SELECT 
    (SELECT COUNT(*) FROM users WHERE role = 'user') as total_anggota,
    (SELECT COUNT(*) FROM buku) as total_buku,
    (SELECT COUNT(*) FROM transaksi) as total_transaksi,
    (SELECT COUNT(*) FROM transaksi WHERE status = 'dipinjam') as sedang_dipinjam
");
$stmt->execute();
$systemStats = $stmt->fetch();
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-gear me-2"></i>Pengaturan Sistem</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Pengaturan</li>
                    </ol>
                </nav>
            </div>

            <?php include '../includes/alerts.php'; ?>
			
			<?php
// Contoh Logic Action

?>


            <div class="row">
                <!-- System Information -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Informasi Sistem</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Nama Perpustakaan:</strong></td>
                                    <td><?= htmlspecialchars($systemSettings['nama_perpustakaan']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Alamat:</strong></td>
                                    <td><?= htmlspecialchars($systemSettings['alamat_perpustakaan']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Telepon:</strong></td>
                                    <td><?= htmlspecialchars($systemSettings['telepon_perpustakaan']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td><?= htmlspecialchars($systemSettings['email_perpustakaan']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Jam Operasional:</strong></td>
                                    <td><?= htmlspecialchars($systemSettings['jam_operasional']) ?></td>
                                </tr>
                            </table>
                            
                            <hr>
                            <h6>Aturan Peminjaman</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Max Pinjam per Anggota:</strong></td>
                                    <td><?= $systemSettings['max_pinjam_per_anggota'] ?> buku</td>
                                </tr>
                                <tr>
                                    <td><strong>Lama Pinjam Default:</strong></td>
                                    <td><?= $systemSettings['lama_pinjam_default'] ?> hari</td>
                                </tr>
                                <tr>
                                    <td><strong>Denda per Hari:</strong></td>
                                    <td>Rp <?= number_format($systemSettings['denda_per_hari'], 0, ',', '.') ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- System Statistics -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Statistik Sistem</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body">
                                            <h3><?= $systemStats['total_anggota'] ?></h3>
                                            <small>Total Anggota</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body">
                                            <h3><?= $systemStats['total_buku'] ?></h3>
                                            <small>Total Buku</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body">
                                            <h3><?= $systemStats['total_transaksi'] ?></h3>
                                            <small>Total Transaksi</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body">
                                            <h3><?= $systemStats['sedang_dipinjam'] ?></h3>
                                            <small>Sedang Dipinjam</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Actions -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Aksi Sistem</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary" onclick="backupDatabase()">
                                    <i class="bi bi-download me-2"></i>Backup Database
                                </button>
                                <button class="btn btn-outline-info" onclick="exportData()">
                                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>Export Data
                                </button>
                                <button class="btn btn-outline-secondary" onclick="clearCache()">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Clear Cache
                                </button>
                                <hr>
                                <button class="btn btn-outline-danger" onclick="showMaintenanceMode()">
                                    <i class="bi bi-exclamation-triangle me-2"></i>Mode Maintenance
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Logs -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Log Aktivitas Terbaru</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            // Ambil log aktivitas terbaru dari transaksi
                            $stmt = $pdo->prepare("SELECT 
                                t.*, u.nama_lengkap as nama_user, b.judul as judul_buku,
                                admin.nama_lengkap as nama_admin
                                FROM transaksi t
                                JOIN users u ON t.user_id = u.id
                                JOIN buku b ON t.buku_id = b.id
                                LEFT JOIN users admin ON t.created_by = admin.id
                                ORDER BY t.created_at DESC
                                LIMIT 10
                            ");
                            $stmt->execute();
                            $recentActivities = $stmt->fetchAll();
                            ?>
                            
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Waktu</th>
                                            <th>Aktivitas</th>
                                            <th>User</th>
                                            <th>Admin</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentActivities as $activity): ?>
                                        <tr>
                                            <td>
                                                <small><?= date('d/m/Y H:i', strtotime($activity['created_at'])) ?></small>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($activity['judul_buku']) ?></strong><br>
                                                <small class="text-muted"><?= $activity['kode_transaksi'] ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($activity['nama_user']) ?></td>
                                            <td><?= htmlspecialchars($activity['nama_admin'] ?: '-') ?></td>
                                            <td>
                                                <span class="badge <?= $activity['status'] === 'dipinjam' ? 'bg-warning' : 'bg-success' ?>">
                                                    <?= ucfirst($activity['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-start mt-3">
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function backupDatabase() {
    if (confirm("Anda yakin ingin melakukan backup database?")) {
        window.location.href = "settings.php?action=backup";
    }
}
function exportData() {
    if (confirm("Anda yakin ingin export data?")) {
        window.location.href = "settings.php?action=export";
    }
}
function clearCache() {
    if (confirm("Anda yakin ingin menghapus cache?")) {
        window.location.href = "settings.php?action=clear_cache";
    }
}
function showMaintenanceMode() {
    if (confirm("Anda yakin ingin masuk ke mode maintenance?")) {
        window.location.href = "settings.php?action=maintenance";
    }
}
</script>


<?php include '../includes/footer.php'; ?>