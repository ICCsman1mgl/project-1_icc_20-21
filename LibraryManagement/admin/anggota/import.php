<?php
// import.php
// Import anggota dari CSV ke tabel anggota (berdasarkan NIS)

// Wajib: koneksi PDO dari project kamu
// Sesuaikan path-nya:
require_once __DIR__ . '/../../config/database.php'; // kalau file PDO kamu ada di config/database.php

// Pastikan $pdo tersedia dari file di atas.
// Kalau di file kamu nama variabelnya beda, sesuaikan.
// Fallback: kalau project ternyata pakai MySQLi, bikin PDO khusus untuk import
if (!isset($pdo) || !($pdo instanceof PDO)) {
    try {
        $pdo = new PDO(
            "mysql:host=localhost;dbname=perpustakaan;charset=utf8mb4",
            "root",
            ""
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        die("Koneksi PDO gagal: " . $e->getMessage());
    }
}


function normalizeHeader(string $h): string {
    $h = trim(strtolower($h));
    $h = str_replace(["\xEF\xBB\xBF", " "], ["", "_"], $h); // hapus BOM + spasi jadi underscore
    return $h;
}

function toNullIfEmpty($v) {
    $v = is_string($v) ? trim($v) : $v;
    return ($v === '' || $v === null) ? null : $v;
}

$message = '';
$stats = ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => 0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
        $message = 'Upload CSV gagal.';
    } else {
        $tmp = $_FILES['csv']['tmp_name'];

        $fh = fopen($tmp, 'r');
        if (!$fh) {
            $message = 'File CSV tidak bisa dibaca.';
        } else {
            // Baca header
            $headerRaw = fgetcsv($fh);
            if (!$headerRaw) {
                $message = 'CSV kosong atau header tidak valid.';
            } else {
                $headers = array_map('normalizeHeader', $headerRaw);

                // Minimal wajib ada nis
                $nisIndex = array_search('nis', $headers, true);
                if ($nisIndex === false) {
                    $message = "Header CSV wajib punya kolom: nis";
                } else {
                    // Map kolom opsional
                    $map = [
                        'nis' => $nisIndex,
                        'nama' => array_search('nama', $headers, true),
                        'no_hp' => array_search('no_hp', $headers, true),
                        'alamat' => array_search('alamat', $headers, true),
                    ];

                    // Mode:
                    // - insert_only: kalau NIS sudah ada -> skip
                    // - upsert: kalau NIS sudah ada -> update data non-null
                    $mode = ($_POST['mode'] ?? 'insert_only') === 'upsert' ? 'upsert' : 'insert_only';

                    // Siapkan statement
                    // Pakai INSERT IGNORE supaya duplikat NIS tidak error (butuh UNIQUE(nis) sudah ada)
                    $stmtInsert = $pdo->prepare("
                        INSERT IGNORE INTO anggota (nis, nama, no_hp, alamat)
                        VALUES (:nis, :nama, :no_hp, :alamat)
                    ");

                    $stmtUpdate = $pdo->prepare("
                        UPDATE anggota
                        SET
                            nama = COALESCE(:nama, nama),
                            no_hp = COALESCE(:no_hp, no_hp),
                            alamat = COALESCE(:alamat, alamat)
                        WHERE nis = :nis
                    ");

                    $pdo->beginTransaction();
                    try {
                        while (($row = fgetcsv($fh)) !== false) {
                            $nis = isset($row[$map['nis']]) ? trim($row[$map['nis']]) : '';
                            if ($nis === '') {
                                $stats['skipped']++;
                                continue;
                            }

                            $payload = [
                                ':nis' => $nis,
                                ':nama' => ($map['nama'] !== false && isset($row[$map['nama']])) ? toNullIfEmpty($row[$map['nama']]) : null,
                                ':no_hp' => ($map['no_hp'] !== false && isset($row[$map['no_hp']])) ? toNullIfEmpty($row[$map['no_hp']]) : null,
                                ':alamat' => ($map['alamat'] !== false && isset($row[$map['alamat']])) ? toNullIfEmpty($row[$map['alamat']]) : null,
                            ];

                            $stmtInsert->execute($payload);

                            if ($stmtInsert->rowCount() === 1) {
                                $stats['inserted']++;
                            } else {
                                // Duplikat NIS
                                if ($mode === 'upsert') {
                                    $stmtUpdate->execute($payload);
                                    $stats['updated'] += ($stmtUpdate->rowCount() > 0) ? 1 : 0;
                                } else {
                                    $stats['skipped']++;
                                }
                            }
                        }

                        $pdo->commit();
                        $message = "Selesai. Insert: {$stats['inserted']}, Update: {$stats['updated']}, Skip: {$stats['skipped']}.";
                    } catch (Throwable $e) {
                        $pdo->rollBack();
                        $stats['errors']++;
                        $message = "Gagal import: " . $e->getMessage();
                    }
                }
            }
            fclose($fh);
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Import Anggota (CSV)</title>
  <style>
    body{font-family:Arial, sans-serif; max-width:760px; margin:30px auto; padding:0 16px;}
    .card{border:1px solid #ddd; border-radius:10px; padding:16px;}
    .row{margin:10px 0;}
    input[type=file]{width:100%;}
    button{padding:10px 14px; border:0; border-radius:8px; cursor:pointer;}
    .msg{margin:12px 0; padding:10px; background:#f6f6f6; border-radius:8px;}
    code{background:#f1f1f1; padding:2px 6px; border-radius:6px;}
  </style>
</head>
<body>
  <h2>Import Anggota dari CSV</h2>

  <?php if ($message): ?>
    <div class="msg"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <div class="card">
    <form method="post" enctype="multipart/form-data">
      <div class="row">
        <label>File CSV</label><br>
        <input type="file" name="csv" accept=".csv" required>
      </div>

      <div class="row">
        <label>Mode</label><br>
        <label><input type="radio" name="mode" value="insert_only" checked> Insert saja (duplikat NIS di-skip)</label><br>
        <label><input type="radio" name="mode" value="upsert"> Upsert (duplikat NIS di-update kalau kolom CSV terisi)</label>
      </div>

      <button type="submit">Import</button>
    </form>

    <hr>

    <p><b>Format CSV yang didukung</b></p>
    <p>Minimal harus ada header <code>nis</code>. Kolom lain opsional.</p>
    <p>Contoh header:</p>
    <pre>nis,nama,no_hp,alamat</pre>
  </div>
</body>
</html>
