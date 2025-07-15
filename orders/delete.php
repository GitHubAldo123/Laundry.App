<?php
session_start();
require_once '../config/db.php';

// --- 1. Validasi Login dan Role ---
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu.";
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Akses ditolak. Hanya admin yang dapat menghapus order.";
    header("Location: list.php");
    exit();
}

// --- 2. Validasi Parameter ID ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID Order tidak valid.";
    header("Location: list.php");
    exit();
}

$order_id = (int)$_GET['id'];

// --- 3. Proses Penghapusan dengan Transaksi ---
$conn->begin_transaction();

try {
    // --- 3a. Verifikasi Order Ada ---
    $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        throw new Exception("Order tidak ditemukan.");
    }

    // --- 3b. Hapus data dari tabel order_items ---
    $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    if (!$stmt->execute()) {
        throw new Exception("Gagal menghapus item dari order_items.");
    }

    // --- 3c. Hapus data dari tabel order_status ---
    $stmt = $conn->prepare("DELETE FROM order_status WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    if (!$stmt->execute()) {
        throw new Exception("Gagal menghapus riwayat dari order_status.");
    }

    // --- 3d. Hapus data dari tabel orders ---
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    if (!$stmt->execute()) {
        throw new Exception("Gagal menghapus data dari orders.");
    }

    // --- 4. Commit jika sukses ---
    $conn->commit();
    $_SESSION['success'] = "Pesanan dengan ID #{$order_id} berhasil dihapus.";

} catch (Exception $e) {
    // --- 5. Rollback jika gagal ---
    $conn->rollback();
    $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();

    // Log internal error (tidak ditampilkan ke user)
    error_log("Gagal menghapus order ID {$order_id}: " . $e->getMessage());
}

// --- 6. Redirect kembali ke list ---
header("Location: list.php");
exit();
?>
