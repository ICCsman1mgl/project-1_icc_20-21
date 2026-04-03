<?php
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$pdo = getConnection();


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
                    $emailIndex = array_search('email', $headers, true);
                    $statusIndex = array_search('status', $headers, true);

                    // Mode:
                    // - insert_only: kalau NIS sudah ada -> skip
                    // - upsert: kalau NIS sudah ada -> update data non-null
                    $mode = ($_POST['mode'] ?? 'insert_only') === 'upsert' ? 'upsert' : 'insert_only';

                    $stmtInsert = $pdo->prepare("
                        INSERT IGNORE INTO users (
                            username, password, email, role, nama_lengkap, alamat, telepon, status
                        ) VALUES (
                            :username, :password, :email, 'user', :nama_lengkap, :alamat, :telepon, :status
                        )
                    ");

                    $stmtUpdate = $pdo->prepare("
                        UPDATE users
                        SET
                            nama_lengkap = COALESCE(:nama_lengkap, nama_lengkap),
                            telepon = COALESCE(:telepon, telepon),
                            alamat = COALESCE(:alamat, alamat),
                            email = COALESCE(:email, email),
                            status = COALESCE(:status, status),
                            updated_at = NOW()
                        WHERE username = :username AND role = 'user'
                    ");

                    $pdo->beginTransaction();
                    try {
                        while (($row = fgetcsv($fh)) !== false) {
                            $nis = isset($row[$map['nis']]) ? trim((string)$row[$map['nis']]) : '';
                            if ($nis === '') {
                                $stats['skipped']++;
                                continue;
                            }

                            $emailFromCsv = null;
                            if ($emailIndex !== false && isset($row[$emailIndex])) {
                                $emailFromCsv = toNullIfEmpty($row[$emailIndex]);
                            }

                            $statusFromCsv = null;
                            if ($statusIndex !== false && isset($row[$statusIndex])) {
                                $statusFromCsv = toNullIfEmpty($row[$statusIndex]);
                            }

                            $emailForInsert = $emailFromCsv ?: ($nis . '@import.local');
                            $namaFinal = ($map['nama'] !== false && isset($row[$map['nama']])) ? toNullIfEmpty($row[$map['nama']]) : null;
                            $namaFinal = $namaFinal ?: $nis;

                            $commonPayload = [
                                ':username' => $nis,
                                ':nama_lengkap' => $namaFinal,
                                ':telepon' => ($map['no_hp'] !== false && isset($row[$map['no_hp']])) ? toNullIfEmpty($row[$map['no_hp']]) : null,
                                ':alamat' => ($map['alamat'] !== false && isset($row[$map['alamat']])) ? toNullIfEmpty($row[$map['alamat']]) : null,
                                ':status' => $statusFromCsv,
                            ];

                            $payloadInsert = $commonPayload + [
                                ':password' => password_hash($nis, PASSWORD_DEFAULT),
                                ':email' => $emailForInsert,
                                ':status' => $statusFromCsv ?: 'aktif',
                            ];
                            $stmtInsert->execute($payloadInsert);

                            if ($stmtInsert->rowCount() === 1) {
                                $stats['inserted']++;
                            } else {
                                if ($mode === 'upsert') {
                                    $payloadUpdate = $commonPayload + [':email' => $emailFromCsv];
                                    $stmtUpdate->execute($payloadUpdate);
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
    <p>Minimal harus ada header <code>nis</code>. Kolom lain opsional: <code>nama</code>, <code>email</code>, <code>no_hp</code>, <code>alamat</code>, <code>status</code>. Password default mengikuti nilai <code>nis</code>.</p>
    <p>Contoh header:</p>
    <pre>nis,nama,email,no_hp,alamat,status</pre>
  </div>
</body>
</html>
