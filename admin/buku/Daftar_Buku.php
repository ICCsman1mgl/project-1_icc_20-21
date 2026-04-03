<?php
// 1. Sertakan file konfigurasi
include '../../config/database.php';
$pdo = getConnection();

// 2. Query data
$stmt = $pdo->prepare("
    SELECT id, kode_buku, judul, pengarang, penerbit, tahun_terbit, jumlah_tersedia, status 
    FROM buku
");
$stmt->execute();
$books = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daftar Buku</title>
    <link rel="stylesheet" href="../../assets/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8fafc;
        }
        .container {
            background: #fff;
            padding: 30px;
            margin: 30px auto;
            border-radius: 10px;
            max-width: 1200px;
            box-shadow: 0px 0px 15px rgba(0,0,0,.1);
        }
        h2 {
            font-weight: bold;
            color: #333;
        }
        .table {
            font-size: 14px;
        }
        .table thead {
            background-color: #343a40;
            color: #fff;
        }
        .table-hover tbody tr:hover {
            background-color: #f1f1f1;
        }
        .btn {
            font-size: 12px;
        }
        .no-data {
            font-style: italic;
            text-align: center;
            color: #777;
        }
        .btn-success {
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📚 Daftar Buku</h2>
    <a href="tambah.php" class="btn btn-success btn-sm mt-2">➕ Tambah Buku</a>
    <div class="table-responsive mt-3">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Judul Buku</th>
                    <th>Pengarang</th>
                    <th>Tahun Terbit</th>
                    <th>Stok Tersedia</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; if (count($books) > 0): ?>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlentities($book['judul']); ?></td>
                            <td><?= htmlentities($book['pengarang']); ?></td>
                            <td><?= htmlentities($book['tahun_terbit']); ?></td>
                            <td><?= htmlentities($book['jumlah_tersedia']); ?></td>
                            <td style="text-align: center;">
                                <a href="edit.php?id=<?= $book['id']; ?>"
                                   class="btn btn-warning btn-sm">Edit</a>
                                <a href="hapus.php?id=<?= $book['id']; ?>"
                                   class="btn btn-danger btn-sm"
                                   onClick="return confirm('Anda yakin?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="no-data">Belum ada data buku</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
