<?php
// Start session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set default timezone
date_default_timezone_set('Asia/Jakarta');

// Base URL helper
function base_url($path = '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    return "$protocol://$host/loundlyapp/$path";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? "$page_title - Laundry App" : "Laundry App"; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo base_url('assets/img/favicon.ico'); ?>">
    
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo base_url('assets/css/main.css'); ?>" rel="stylesheet">
    
    <!-- Scripts Head -->
    <script>
        const BASE_URL = '<?php echo base_url(); ?>';
    </script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Header Content -->
    <header class="bg-gradient-to-r from-blue-600 to-blue-800 text-white shadow-lg">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="<?php echo base_url(); ?>" class="flex items-center space-x-2">
                <i class="fas fa-tshirt text-2xl"></i>
                <span class="text-xl font-bold">Laundry App</span>
            </a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
            <nav class="hidden md:flex space-x-6">
                <a href="<?php echo base_url('dashboard/'.($_SESSION['role'] === 'admin' ? 'admin.php' : 'user.php')); ?>" 
                   class="hover:text-blue-200 transition">
                    <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                </a>
                <a href="<?php echo base_url('orders/list.php'); ?>" class="hover:text-blue-200 transition">
                    <i class="fas fa-list mr-1"></i> Orders
                </a>
                <a href="<?php echo base_url('auth/logout.php'); ?>" class="hover:text-blue-200 transition">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </a>
            </nav>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-grow container mx-auto px-4 py-8">