<?php
require_once '../config/database.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = cleanInput($_POST['username']);
    $email = cleanInput($_POST['email']);
    $nama_lengkap = cleanInput($_POST['nama_lengkap']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $alamat = cleanInput($_POST['alamat']);
    $telepon = cleanInput($_POST['telepon']);
    $status = cleanInput($_POST['status']);
    
    // Validasi required fields
    if (empty($username) || empty($email) || empty($nama_lengkap) || empty($password)) {
        $_SESSION['error'] = 'Harap lengkapi semua field yang wajib diisi!';
        header('Location: ../admin/anggota/tambah.php');
        exit();
    }
    
    // Validasi password
    if (strlen($password) < 6) {
        $_SESSION['error'] = 'Password minimal 6 karakter!';
        header('Location: ../admin/anggota/tambah.php');
        exit();
    }
    
    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Konfirmasi password tidak sama!';
        header('Location: ../admin/anggota/tambah.php');
        exit();
    }
    
    // Validasi email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Format email tidak valid!';
        header('Location: ../admin/anggota/tambah.php');
        exit();
    }
    
    try {
        $pdo = getConnection();
        
        // Cek apakah username sudah ada
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = 'Username sudah digunakan! Gunakan username yang lain.';
            header('Location: ../admin/anggota/tambah.php');
            exit();
        }
        
        // Cek apakah email sudah ada
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = 'Email sudah digunakan! Gunakan email yang lain.';
            header('Location: ../admin/anggota/tambah.php');
            exit();
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Handle upload foto
        $fotoFilename = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadFile($_FILES['foto'], 'profiles');
            if ($uploadResult['success']) {
                $fotoFilename = $uploadResult['filename'];
            } else {
                $_SESSION['error'] = 'Error upload foto: ' . $uploadResult['message'];
                header('Location: ../admin/anggota/tambah.php');
                exit();
            }
        }
        
        // Insert data anggota
        $stmt = $pdo->prepare("INSERT INTO users (
            username, password, email, role, nama_lengkap, alamat, telepon, foto, status
        ) VALUES (?, ?, ?, 'user', ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $username, $hashedPassword, $email, $nama_lengkap, $alamat, $telepon, $fotoFilename, $status
        ]);
        
        $_SESSION['success'] = 'Anggota "' . $nama_lengkap . '" berhasil ditambahkan!';
        header('Location: ../admin/anggota/index.php');
        exit();
        
    } catch (PDOException $e) {
        // Hapus file yang sudah diupload jika ada error
        if ($fotoFilename && file_exists('../uploads/' . $fotoFilename)) {
            unlink('../uploads/' . $fotoFilename);
        }
        
        $_SESSION['error'] = 'Gagal menambah anggota: ' . $e->getMessage();
        header('Location: ../admin/anggota/tambah.php');
        exit();
    }
} else {
    header('Location: ../admin/anggota/tambah.php');
    exit();
}
?>
