<?php
require_once '../config/database.php';
requireLogin();
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
    
    // Validasi bahwa ini adalah profil admin sendiri
    if ($id != $_SESSION['user_id']) {
        $_SESSION['error'] = 'Akses ditolak! Anda hanya dapat mengedit profil sendiri.';
        header('Location: ../admin/profile.php');
        exit();
    }
    
    // Validasi required fields
    if (empty($username) || empty($email) || empty($nama_lengkap)) {
        $_SESSION['error'] = 'Harap lengkapi semua field yang wajib diisi!';
        header('Location: ../admin/profile.php');
        exit();
    }
    
    // Validasi password jika diisi
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Password minimal 6 karakter!';
            header('Location: ../admin/profile.php');
            exit();
        }
        
        if ($password !== $confirm_password) {
            $_SESSION['error'] = 'Konfirmasi password tidak sama!';
            header('Location: ../admin/profile.php');
            exit();
        }
    }
    
    // Validasi email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Format email tidak valid!';
        header('Location: ../admin/profile.php');
        exit();
    }
    
    try {
        $pdo = getConnection();
        
        // Ambil data admin lama
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $adminLama = $stmt->fetch();
        
        if (!$adminLama) {
            $_SESSION['error'] = 'Data admin tidak ditemukan!';
            header('Location: ../admin/profile.php');
            exit();
        }
        
        // Cek apakah username sudah ada (kecuali untuk admin ini sendiri)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $id]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = 'Username sudah digunakan! Gunakan username yang lain.';
            header('Location: ../admin/profile.php');
            exit();
        }
        
        // Cek apakah email sudah ada (kecuali untuk admin ini sendiri)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = 'Email sudah digunakan! Gunakan email yang lain.';
            header('Location: ../admin/profile.php');
            exit();
        }
        
        // Prepare password update
        $passwordUpdate = '';
        $params = [$username, $email, $nama_lengkap, $alamat, $telepon];
        
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $passwordUpdate = ', password = ?';
            $params[] = $hashedPassword;
        }
        
        // Handle upload foto baru
        $fotoFilename = $adminLama['foto']; // Keep old photo by default
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadFile($_FILES['foto'], 'profiles');
            if ($uploadResult['success']) {
                // Hapus foto lama jika ada
                if ($adminLama['foto'] && file_exists('../uploads/' . $adminLama['foto'])) {
                    unlink('../uploads/' . $adminLama['foto']);
                }
                $fotoFilename = $uploadResult['filename'];
            } else {
                $_SESSION['error'] = 'Error upload foto: ' . $uploadResult['message'];
                header('Location: ../admin/profile.php');
                exit();
            }
        }
        
        $params[] = $fotoFilename;
        $params[] = $id;
        
        // Update data admin
        $sql = "UPDATE users SET 
                username = ?, email = ?, nama_lengkap = ?, alamat = ?, telepon = ?" . 
                $passwordUpdate . ", foto = ?, updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Update session data
$_SESSION['username'] = $username;
$_SESSION['email'] = $email;
$_SESSION['nama_lengkap'] = $nama_lengkap;

if (!empty($fotoFilename)) {
    $_SESSION['foto'] = $fotoFilename;
}

$_SESSION['success'] = 'Profil berhasil diperbarui!';
header('Location: ../admin/profile.php');
exit();

        
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Gagal memperbarui profil: ' . $e->getMessage();
        header('Location: ../admin/profile.php');
        exit();
    }
} else {
    header('Location: ../admin/profile.php');
    exit();
}
?>