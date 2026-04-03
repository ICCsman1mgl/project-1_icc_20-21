<?php
/*
 * Konfigurasi aplikasi & database
 *
 * Catatan deployment:
 * - Jangan gunakan user DB root dan jangan kosongkan password.
 * - Gunakan environment variables agar kredensial tidak hardcoded.
 */

define('APP_ENV', getenv('APP_ENV') ?: 'production'); // production|staging|local
define('APP_DEBUG', (getenv('APP_DEBUG') ?: '0') === '1');

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USERNAME', getenv('DB_USERNAME') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'perpustakaan');

define('BASE_URL', getenv('BASE_URL') ?: '/LibraryManagement');

define('APP_LOG_PATH', getenv('APP_LOG_PATH') ?: (__DIR__ . '/../storage/logs/app.log'));

define('ENABLE_HTTPS_REDIRECT', (getenv('ENABLE_HTTPS_REDIRECT') ?: '0') === '1');
define('CSRF_TOKEN_TTL', (int)(getenv('CSRF_TOKEN_TTL') ?: 7200));

if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', __DIR__ . '/../proses/uploads');
}
if (!defined('MAX_FILE_SIZE')) {
    define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
}
if (!defined('ALLOWED_EXTENSIONS')) {
    define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);
}

function appLog(string $level, string $message, array $context = []): void {
    $level = strtoupper($level);
    $dir = dirname(APP_LOG_PATH);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    $timestamp = gmdate('Y-m-d\TH:i:s\Z');
    $contextJson = $context ? json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
    $line = '[' . $timestamp . '] ' . $level . ' ' . $message . ($contextJson !== '' ? ' ' . $contextJson : '') . PHP_EOL;
    @file_put_contents(APP_LOG_PATH, $line, FILE_APPEND | LOCK_EX);
}

function isHttpsRequest(): bool {
    if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') {
        return true;
    }
    if (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) {
        return true;
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
        return true;
    }
    return false;
}

function enforceHttpsIfConfigured(): void {
    if (php_sapi_name() === 'cli') {
        return;
    }
    if (!ENABLE_HTTPS_REDIRECT) {
        return;
    }
    if (isHttpsRequest()) {
        return;
    }

    $host = $_SERVER['HTTP_HOST'] ?? '';
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    if ($host === '') {
        return;
    }

    header('Location: https://' . $host . $requestUri, true, 301);
    exit();
}

function cspNonce(): string {
    if (!isset($GLOBALS['__csp_nonce'])) {
        $GLOBALS['__csp_nonce'] = rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '=');
    }
    return (string)$GLOBALS['__csp_nonce'];
}

function sendSecurityHeaders(): void {
    if (php_sapi_name() === 'cli') {
        return;
    }
    if (headers_sent()) {
        return;
    }

    $isHttps = isHttpsRequest();
    if ($isHttps) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

    $nonce = cspNonce();
    $csp = "default-src 'self'; "
        . "base-uri 'self'; "
        . "form-action 'self'; "
        . "frame-ancestors 'self'; "
        . "object-src 'none'; "
        . "img-src 'self' data: https:; "
        . "font-src 'self' https: data:; "
        . "style-src 'self' https: 'unsafe-inline'; "
        . "script-src 'self' https: 'nonce-{$nonce}'; "
        . "connect-src 'self' https:; ";
    if ($isHttps) {
        $csp .= "upgrade-insecure-requests";
    }
    header('Content-Security-Policy: ' . $csp);
}

function configureErrorHandling(): void {
    if (php_sapi_name() === 'cli') {
        return;
    }

    ini_set('display_errors', APP_DEBUG ? '1' : '0');
    ini_set('log_errors', '0');

    set_error_handler(function ($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        appLog('ERROR', 'PHP Error: ' . $message, ['file' => $file, 'line' => $line, 'severity' => $severity]);
        return false;
    });

    set_exception_handler(function (Throwable $e) {
        appLog('ERROR', 'Uncaught Exception: ' . $e->getMessage(), [
            'type' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        if (!headers_sent()) {
            http_response_code(500);
        }

        if (APP_DEBUG) {
            echo 'Terjadi kesalahan: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        } else {
            echo 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
        exit();
    });
}

function configureSessionSecurity(): void {
    if (php_sapi_name() === 'cli') {
        return;
    }
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    $secure = isHttpsRequest();
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', $secure ? '1' : '0');
    ini_set('session.cookie_samesite', 'Lax');
}

function csrfToken(): string {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_issued_at'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_issued_at'] = time();
    }

    if ((time() - (int)$_SESSION['csrf_token_issued_at']) > CSRF_TOKEN_TTL) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_issued_at'] = time();
    }

    return (string)$_SESSION['csrf_token'];
}

function csrfField(): string {
    $token = csrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function csrfValidate(?string $token): bool {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    if (!is_string($token) || $token === '') {
        return false;
    }
    return hash_equals((string)$_SESSION['csrf_token'], $token);
}

function actionToken(string $action): string {
    if (!isset($_SESSION['action_tokens'])) {
        $_SESSION['action_tokens'] = [];
    }
    if (!isset($_SESSION['action_tokens'][$action])) {
        $_SESSION['action_tokens'][$action] = bin2hex(random_bytes(16));
    }
    return (string)$_SESSION['action_tokens'][$action];
}

function validateActionToken(string $action, ?string $token): bool {
    if (!isset($_SESSION['action_tokens'][$action])) {
        return false;
    }
    if (!is_string($token) || $token === '') {
        return false;
    }
    return hash_equals((string)$_SESSION['action_tokens'][$action], $token);
}

function requireCsrf(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $token = $_POST['csrf_token'] ?? null;
    if (!csrfValidate(is_string($token) ? $token : null)) {
        appLog('WARNING', 'Permintaan ditolak karena CSRF token tidak valid', [
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ]);
        $_SESSION['error'] = 'Permintaan tidak valid. Silakan muat ulang halaman dan coba lagi.';
        $referer = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/index.php');
        header('Location: ' . $referer);
        exit();
    }
}

function currentUrl(): string {
    $scheme = isHttpsRequest() ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    return $scheme . '://' . $host . $requestUri;
}

function rateLimitKey(string $name): string {
    return 'rate_limit_' . preg_replace('/[^a-z0-9_]+/i', '_', strtolower($name));
}

function registerRateLimitFailure(string $name, int $windowSeconds = 900, int $maxAttempts = 5): void {
    $key = rateLimitKey($name);
    $now = time();
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [];
    }

    $_SESSION[$key] = array_values(array_filter((array)$_SESSION[$key], static fn($attempt) => ($now - (int)$attempt) <= $windowSeconds));
    $_SESSION[$key][] = $now;
    $_SESSION[$key . '_max'] = $maxAttempts;
    $_SESSION[$key . '_window'] = $windowSeconds;
}

function clearRateLimit(string $name): void {
    $key = rateLimitKey($name);
    unset($_SESSION[$key], $_SESSION[$key . '_max'], $_SESSION[$key . '_window']);
}

function isRateLimited(string $name): bool {
    $key = rateLimitKey($name);
    $now = time();
    $windowSeconds = (int)($_SESSION[$key . '_window'] ?? 900);
    $maxAttempts = (int)($_SESSION[$key . '_max'] ?? 5);
    $attempts = array_values(array_filter((array)($_SESSION[$key] ?? []), static fn($attempt) => ($now - (int)$attempt) <= $windowSeconds));
    $_SESSION[$key] = $attempts;
    return count($attempts) >= $maxAttempts;
}

function remainingRateLimitCooldown(string $name): int {
    $key = rateLimitKey($name);
    $attempts = (array)($_SESSION[$key] ?? []);
    if (!$attempts) {
        return 0;
    }
    $windowSeconds = (int)($_SESSION[$key . '_window'] ?? 900);
    $oldest = (int)min($attempts);
    return max(0, ($oldest + $windowSeconds) - time());
}

enforceHttpsIfConfigured();
sendSecurityHeaders();
configureErrorHandling();


// Koneksi ke database
function getConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USERNAME,
            DB_PASSWORD,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        appLog('ERROR', 'Koneksi database gagal', ['error' => $e->getMessage()]);
        if (APP_DEBUG) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
        die("Koneksi database gagal. Silakan hubungi admin.");
    }
}

// Fungsi untuk mengecek koneksi database
function checkConnection() {
    try {
        $pdo = getConnection();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Inisialisasi session jika belum ada
configureSessionSecurity();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fungsi untuk mengecek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Fungsi untuk mengecek apakah user adalah admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Fungsi untuk redirect jika tidak login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }
}

// Fungsi untuk redirect jika bukan admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . '/user/dashboard.php');
        exit();
    }
}

// Fungsi untuk membersihkan input
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Fungsi untuk generate kode unik
function generateCode($prefix, $length = 8) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = $prefix;
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $code;
}

// Konstanta untuk upload
// Fungsi untuk upload file
function uploadFile($file, $subfolder = '') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Tidak ada file yang diupload atau terjadi error'];
    }
    
    $subfolder = trim($subfolder, "/\\");
    $uploadDir = rtrim(UPLOAD_DIR, "/\\") . ($subfolder !== '' ? DIRECTORY_SEPARATOR . $subfolder : '');
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'message' => 'Format file tidak diizinkan'];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'Ukuran file terlalu besar (maksimal 2MB)'];
    }
    
    $fileName = uniqid() . '.' . $fileExtension;
    $targetPath = rtrim($uploadDir, "/\\") . DIRECTORY_SEPARATOR . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $relativePath = ($subfolder !== '' ? $subfolder . '/' : '') . $fileName;
        return ['success' => true, 'filename' => $relativePath];
    } else {
        return ['success' => false, 'message' => 'Gagal mengupload file'];
    }
}
?>
