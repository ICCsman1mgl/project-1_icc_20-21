<?php
require_once '../config/database.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int)$_POST['id'];
    $username = cleanInput($_POST['username']);
    $email = cleanInput($_POST['email']);
    $nama_lengkap = cleanInput($_POST['nama_lengkap']);
    $password = $_POST['password']; // Could be empty
    $confirm_password = $_POST['confirm_password'];
    $alamat = cleanInput($_POST['alamat']);
    $telepon = cleanInput($_POST['telepon']);
    $status = cleanInput($_POST['status']);
    
    // Validasi required fields
    if (empty($username) || empty($email) || empty($nama_lengkap)) {
        $_SESSION['error'] = 'Harap lengkapi semua field yang wajib diisi!';
        header('Location: ../admin/anggota/edit.php?id=' . $id);
        exit();
    }
    
    // Validasi password jika diisi
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Password minimal 6 karakter!';
            header('Location: ../admin/anggota/edit.php?id=' . $id);
            exit();
        }
        
        if ($password !== $confirm_password) {
            $_SESSION['error'] = 'Konfirmasi password tidak sama!';
            header('Location: ../admin/anggota/edit.php?id=' . $id);
            exit();
        }
    }
    
    // Validasi email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Format email tidak valid!';
        header('Location: ../admin/anggota/edit.php?id=' . $id);
        exit();
    }
    
    try {
        $pdo = getConnection();
        
        // Ambil data anggota lama
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'user'");
        $stmt->execute([$id]);
        $anggotaLama = $stmt->fetch();
        
        if (!$anggotaLama) {
            $_SESSION['error'] = 'Anggota tidak ditemukan!';
            header('Location: ../admin/anggota/index.php');
            exit();
        }
        
        // Cek apakah username sudah ada (kecuali untuk anggota ini sendiri)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $id]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = 'Username sudah digunakan! Gunakan username yang lain.';
            header('Location: ../admin/anggota/edit.php?id=' . $id);
            exit();
        }
        
        // Cek apakah email sudah ada (kecuali untuk anggota ini sendiri)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = 'Email sudah digunakan! Gunakan email yang lain.';
            header('Location: ../admin/anggota/edit.php?id=' . $id);
            exit();
        }
        
        // Prepare password update
        $passwordUpdate = '';
        $params = [$username, $email, $nama_lengkap, $alamat, $telepon, $status];
        
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $passwordUpdate = ', password = ?';
            $params[] = $hashedPassword;
        }
        
        // Handle upload foto baru
        $fotoFilename = $anggotaLama['foto']; // Keep old photo by default
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadFile($_FILES['foto'], 'profiles');
            if ($uploadResult['success']) {
                // Hapus foto lama jika ada
                if ($anggotaLama['foto'] && file_exists('../uploads/' . $anggotaLama['foto'])) {
                    unlink('../uploads/' . $anggotaLama['foto']);
                }
                $fotoFilename = $uploadResult['filename'];
            } else {
                $_SESSION['error'] = 'Error upload foto: ' . $uploadResult['message'];
                header('Location: ../admin/anggota/edit.php?id=' . $id);
                exit();
            }
        }
        
        $params[] = $fotoFilename;
        $params[] = $id;
        
        // Update data anggota
        $sql = "UPDATE users SET 
                username = ?, email = ?, nama_lengkap = ?, alamat = ?, telepon = ?, 
                status = ?" . $passwordUpdate . ", foto = ?, updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $_SESSION['success'] = 'Anggota "' . $nama_lengkap . '" berhasil diperbarui!';
        header('Location: ../admin/anggota/index.php');
        exit();
        
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Gagal memperbarui anggota: ' . $e->getMessage();
        header('Location: ../admin/anggota/edit.php?id=' . $id);
        exit();
    }
} else {
    header('Location: ../admin/anggota/index.php');
    exit();
}
?>
