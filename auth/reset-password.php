<?php
session_start();
require_once '../config/db.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

// Verify token
if (!empty($token)) {
    $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $error = "Invalid or expired reset token";
        $token = ''; // Clear token if invalid
    }
}

// Process password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($token)) {
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($new_password) || strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Get email from token
        $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $email = $row['email'];
        
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);
        
        if ($stmt->execute()) {
            // Delete used token
            $conn->query("DELETE FROM password_resets WHERE token = '$token'");
            
            $_SESSION['success'] = "Password updated successfully! You can now login with your new password.";
            header("Location: login.php");
            exit();
        } else {
            $error = "Error updating password. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Laundry App</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden w-full max-w-md">
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 py-6 px-8 text-white">
            <div class="flex items-center justify-center space-x-3">
                <i class="fas fa-key text-2xl"></i>
                <h1 class="text-2xl font-bold">Set New Password</h1>
            </div>
        </div>

        <div class="p-8">
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg flex items-start">
                    <i class="fas fa-exclamation-circle mt-1 mr-3"></i>
                    <div><?php echo $error; ?></div>
                </div>
            <?php endif; ?>
            
            <?php if (empty($token)): ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded-lg">
                    <p>Invalid or expired password reset link. Please request a new one.</p>
                    <a href="forgot-password.php" class="text-blue-600 hover:text-blue-800 font-medium inline-block mt-2">
                        <i class="fas fa-arrow-right mr-1"></i>Go to Forgot Password
                    </a>
                </div>
            <?php else: ?>
                <form method="POST" class="space-y-5">
                    <div class="space-y-1">
                        <label for="password" class="block text-gray-700 font-medium">New Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" id="password" name="password" required minlength="8"
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="At least 8 characters">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label for="confirm_password" class="block text-gray-700 font-medium">Confirm Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Confirm your password">
                        </div>
                    </div>

                    <button type="submit" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i>
                        Update Password
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>