<?php
require_once '../config/database.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int)$_POST['id'];
    $username = cleanInput($_POST['username']);
    $email = cleanInput($_POST['email']);
    $nama_lengkap = cleanInput($_POST['nama_lengkap']);
    $password = $_POST['password']; // Could be empty
    $confirm_password = $_POST['confirm_password'];
    $alamat = cleanInput($_POST['alamat']);
    $telepon = cleanInput($_POST['telepon']);
    
    // Validasi bahwa ini adalah profil user sendiri
    if ($id != $_SESSION['user_id']) {
        $_SESSION['error'] = 'Akses ditolak! Anda hanya dapat mengedit profil sendiri.';
        header('Location: ../user/profile.php');
        exit();
    }
    
    // Validasi required fields
    if (empty($username) || empty($email) || empty($nama_lengkap)) {
        $_SESSION['error'] = 'Harap lengkapi semua field yang wajib diisi!';
        header('Location: ../user/profile.php');
        exit();
    }
    
    // Validasi password jika diisi
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Password minimal 6 karakter!';
            header('Location: ../user/profile.php');
            exit();
        }
        
        if ($password !== $confirm_password) {
            $_SESSION['error'] = 'Konfirmasi password tidak sama!';
            header('Location: ../user/profile.php');
            exit();
        }
    }
    
    // Validasi email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Format email tidak valid!';
        header('Location: ../user/profile.php');
        exit();
    }
    
    try {
        $pdo = getConnection();
        
        // Ambil data user lama
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'user'");
        $stmt->execute([$id]);
        $userLama = $stmt->fetch();
        
        if (!$userLama) {
            $_SESSION['error'] = 'Data user tidak ditemukan!';
            header('Location: ../user/profile.php');
            exit();
        }
        
        // Cek apakah username sudah ada (kecuali untuk user ini sendiri)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $id]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = 'Username sudah digunakan! Gunakan username yang lain.';
            header('Location: ../user/profile.php');
            exit();
        }
        
        // Cek apakah email sudah ada (kecuali untuk user ini sendiri)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = 'Email sudah digunakan! Gunakan email yang lain.';
            header('Location: ../user/profile.php');
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
        $fotoFilename = $userLama['foto']; // Keep old photo by default
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadFile($_FILES['foto'], 'profiles');
            if ($uploadResult['success']) {
                // Hapus foto lama jika ada
                if ($userLama['foto'] && file_exists('../uploads/' . $userLama['foto'])) {
                    unlink('../uploads/' . $userLama['foto']);
                }
                $fotoFilename = $uploadResult['filename'];
            } else {
                $_SESSION['error'] = 'Error upload foto: ' . $uploadResult['message'];
                header('Location: ../user/profile.php');
                exit();
            }
        }
        
        $params[] = $fotoFilename;
        $params[] = $id;
        
        // Update data user
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
        $_SESSION['foto'] = $fotoFilename;
        
        $_SESSION['success'] = 'Profil berhasil diperbarui!';
        header('Location: ../user/profile.php');
        exit();
        
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Gagal memperbarui profil: ' . $e->getMessage();
        header('Location: ../user/profile.php');
        exit();
    }
} else {
    header('Location: ../user/profile.php');
    exit();
}
?>