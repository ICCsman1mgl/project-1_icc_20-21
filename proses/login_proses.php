<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    requireCsrf();

    if (isRateLimited('login')) {
        $cooldown = remainingRateLimitCooldown('login');
        $_SESSION['error'] = 'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . $cooldown . ' detik.';
        header('Location: ../login.php');
        exit();
    }

    $username = cleanInput($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Username dan password harus diisi!';
        header('Location: ../login.php');
        exit();
    }
    
    try {
        $pdo = getConnection();
        
        // Cari user berdasarkan username atau email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'aktif'");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Login berhasil
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['foto'] = $user['foto'];

            clearRateLimit('login');
            
            // Update last login
            $updateStmt = $pdo->prepare("UPDATE users SET updated_at = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            // Redirect berdasarkan role
            if ($user['role'] === 'admin') {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: ../user/dashboard.php');
            }
            exit();
        } else {
            registerRateLimitFailure('login', 900, 5);
            appLog('WARNING', 'Login gagal', ['username' => $username, 'ip' => $_SERVER['REMOTE_ADDR'] ?? '']);
            $_SESSION['error'] = 'Username/email atau password salah!';
            header('Location: ../login.php');
            exit();
        }
        
    } catch (PDOException $e) {
        appLog('ERROR', 'Error pada proses login', ['error' => $e->getMessage()]);
        $_SESSION['error'] = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        header('Location: ../login.php');
        exit();
    }
} else {
    header('Location: ../login.php');
    exit();
}
?>
