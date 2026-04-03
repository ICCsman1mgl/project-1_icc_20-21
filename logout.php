<?php
session_start();

// Hapus semua data session
session_unset();
session_destroy();

// Set pesan sukses
session_start();
$_SESSION['success'] = 'Anda berhasil logout. Terima kasih!';

// Redirect ke halaman login
header('Location: login.php');
exit();
?>
