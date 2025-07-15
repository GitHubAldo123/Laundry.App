<?php
session_start();
require_once '../config/db.php';

// Pastikan user login dan bukan admin (hanya user biasa yang membatalkan)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID pesanan tidak valid.";
    header("Location: list.php");
    exit();
}

$order_id = (int)$_GET['id'];

// Mulai transaksi
$conn->begin_transaction();

try {
    // Cek apakah order dimiliki user
    $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        throw new Exception("Pesanan tidak ditemukan atau bukan milik Anda.");
    }

    // Hapus item
    $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    // Hapus status
    $stmt = $conn->prepare("DELETE FROM order_status WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    // Hapus order
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    // Selesai
    $conn->commit();
    $_SESSION['success'] = "Pesanan berhasil dibatalkan.";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Gagal membatalkan pesanan: " . $e->getMessage();
}

header("Location: list.php");
exit();
