<?php
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../config/mail.php';

/**
 * Generate random token untuk reset password
 */
function generateResetToken() {
    return bin2hex(random_bytes(32));
}

/**
 * Validasi kekuatan password
 */
function validatePasswordStrength($password) {
    if (strlen($password) < 8) {
        return "Password harus minimal 8 karakter";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        return "Password harus mengandung minimal 1 huruf kapital";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        return "Password harus mengandung minimal 1 angka";
    }
    
    return true;
}

/**
 * Cek apakah user sudah login
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirect user berdasarkan role
 */
function redirectByRole() {
    if (isLoggedIn()) {
        $role = $_SESSION['role'];
        $location = $role === 'admin' ? 'admin.php' : 'user.php';
        header("Location: ".base_url("dashboard/$location"));
        exit();
    }
}

/**
 * Proteksi halaman untuk role tertentu
 */
function protectPage($allowed_roles = []) {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: ".base_url("auth/login.php"));
        exit();
    }
    
    if (!empty($allowed_roles) && !in_array($_SESSION['role'], $allowed_roles)) {
        header("HTTP/1.1 403 Forbidden");
        die("Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.");
    }
}

/**
 * Set flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Show flash message
 */
function showFlash() {
    if (isset($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'];
        $message = $_SESSION['flash']['message'];
        
        $color_classes = [
            'success' => 'bg-green-100 border-green-500 text-green-700',
            'error' => 'bg-red-100 border-red-500 text-red-700',
            'warning' => 'bg-yellow-100 border-yellow-500 text-yellow-700',
            'info' => 'bg-blue-100 border-blue-500 text-blue-700'
        ];
        
        $icon_classes = [
            'success' => 'fa-check-circle',
            'error' => 'fa-exclamation-circle',
            'warning' => 'fa-exclamation-triangle',
            'info' => 'fa-info-circle'
        ];
        
        $class = $color_classes[$type] ?? $color_classes['info'];
        $icon = $icon_classes[$type] ?? $icon_classes['info'];
        
        echo <<<HTML
        <div class="border-l-4 p-4 mb-6 rounded-lg $class flex items-start">
            <i class="fas $icon mt-1 mr-3"></i>
            <div>$message</div>
        </div>
HTML;
        
        unset($_SESSION['flash']);
    }
}

/**
 * Cek remember me cookie dan auto login
 */
function checkRememberMe() {
    global $conn;
    
    if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        
        $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE remember_token = ? AND token_expiry > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Perpanjang cookie
            setcookie('remember_token', $token, time() + 60 * 60 * 24 * 30, '/');
        }
    }
}

// Panggil di header.php
checkRememberMe();
?>

