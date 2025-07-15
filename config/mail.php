<?php
// Konfigurasi Email untuk Password Reset
define('MAIL_CONFIG', [
    'host'       => 'smtp.example.com',    // Server SMTP
    'username'   => 'noreply@laundryapp.com', // Email pengirim
    'password'   => 'your_secure_password',   // Password email
    'secure'     => 'tls',                   // Encryption: ssl atau tls
    'port'       => 587,                     // Port SMTP
    'from_email' => 'noreply@laundryapp.com',
    'from_name'  => 'Laundry App System',
    'reply_to'   => 'support@laundryapp.com',
    
    // Template Paths
    'templates' => [
        'reset_password' => __DIR__.'/../templates/emails/reset-password.html'
    ],
    
    // Debugging
    'debug' => 0 // 0 = off, 1 = client messages, 2 = client and server messages
]);

// Fungsi untuk mengirim email reset password
function sendResetPasswordEmail($to_email, $to_name, $reset_link) {
    $mail_config = MAIL_CONFIG;
    
    // Load template
    $template = file_get_contents($mail_config['templates']['reset_password']);
    $message = str_replace(
        ['{{name}}', '{{reset_link}}', '{{expiry_time}}'],
        [$to_name, $reset_link, '1 jam'],
        $template
    );
    
    // Create PHPMailer instance (pastikan sudah install PHPMailer via composer)
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $mail_config['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $mail_config['username'];
        $mail->Password   = $mail_config['password'];
        $mail->SMTPSecure = $mail_config['secure'];
        $mail->Port       = $mail_config['port'];
        $mail->SMTPDebug  = $mail_config['debug'];
        
        // Recipients
        $mail->setFrom($mail_config['from_email'], $mail_config['from_name']);
        $mail->addAddress($to_email, $to_name);
        $mail->addReplyTo($mail_config['reply_to']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Reset Password - Laundry App';
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>