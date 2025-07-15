<?php
$host = "localhost";        // Ganti jika hosting berbeda
$user = "root";             // Ganti sesuai user MySQL kamu
$password = "";             // Default kosong di XAMPP
$dbname = "laundry_db";     // Ganti sesuai nama database kamu

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Optional: set charset
$conn->set_charset("utf8");
?>
