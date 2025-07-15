<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['role'] === 'admin');

// Get order details
if ($is_admin) {
    $stmt = $conn->prepare("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->bind_param("i", $order_id);
} else {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
}

$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header("Location: index.php");
    exit();
}

// Get order items
$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get order status history
$stmt = $conn->prepare("SELECT * FROM order_status WHERE order_id = ? ORDER BY waktu DESC");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$status_history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Status icon & label mapping
$status_timeline = [
    'pending' => ['icon' => 'fas fa-clock', 'color' => 'bg-yellow-500', 'label' => 'Pending'],
    'Order' => ['icon' => 'fas fa-clipboard-list', 'color' => 'bg-blue-500', 'label' => 'Order Received'],
    'processing' => ['icon' => 'fas fa-spinner', 'color' => 'bg-blue-400', 'label' => 'Processing'],
    'washing' => ['icon' => 'fas fa-soap', 'color' => 'bg-blue-300', 'label' => 'Washing'],
    'drying' => ['icon' => 'fas fa-wind', 'color' => 'bg-indigo-300', 'label' => 'Drying'],
    'ironing' => ['icon' => 'fas fa-iron', 'color' => 'bg-purple-400', 'label' => 'Ironing'],
    'packaging' => ['icon' => 'fas fa-box-open', 'color' => 'bg-indigo-400', 'label' => 'Packaging'],
    'ready_for_pickup' => ['icon' => 'fas fa-people-carry', 'color' => 'bg-green-400', 'label' => 'Ready for Pickup'],
    'done' => ['icon' => 'fas fa-check-circle', 'color' => 'bg-green-500', 'label' => 'Completed']
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tracking Pesanan - Laundry App</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="container mx-auto py-8">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Tracking Pesanan</h1>
            <a href="list.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar
            </a>
        </div>

        <!-- Order Detail -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-gray-500">Kode Pesanan</h3>
                <p class="text-lg font-semibold">#<?= htmlspecialchars($order['order_code']) ?></p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-gray-500">Pelanggan</h3>
                <p class="text-lg font-semibold"><?= htmlspecialchars($order['customer_name']) ?></p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-gray-500">Total</h3>
                <p class="text-lg font-semibold">Rp <?= number_format($order['total'], 0, ',', '.') ?></p>
            </div>
        </div>

        <?php if (!empty($order['services'])): ?>
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <h3 class="text-sm font-medium text-gray-500">Layanan</h3>
            <div class="flex flex-wrap gap-2 mt-2">
                <?php foreach (explode(',', $order['services']) as $service): ?>
                    <span class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-full">
                        <?= htmlspecialchars(trim($service)) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($is_admin): ?>
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <h3 class="text-sm font-medium text-gray-500">Dipesan oleh</h3>
            <p class="text-lg font-semibold"><?= htmlspecialchars($order['username'] ?? '-') ?></p>
        </div>
        <?php endif; ?>

        <!-- Items -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Item Pesanan</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border">Item</th>
                            <th class="px-4 py-2 border">Jumlah</th>
                            <th class="px-4 py-2 border">Harga Satuan</th>
                            <th class="px-4 py-2 border">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td class="px-4 py-2 border"><?= ucwords($item['item_name']) ?></td>
                            <td class="px-4 py-2 border"><?= $item['quantity'] ?></td>
                            <td class="px-4 py-2 border">Rp <?= number_format($item['unit_price'], 0, ',', '.') ?></td>
                            <td class="px-4 py-2 border">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Status -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Status Saat Ini</h2>
            <div class="inline-flex items-center px-4 py-2 rounded-lg <?= $status_timeline[strtolower($order['status'])]['color'] ?? 'bg-blue-100' ?> text-white">
                <i class="<?= $status_timeline[strtolower($order['status'])]['icon'] ?? 'fas fa-info-circle' ?> mr-2"></i>
                <span><?= $status_timeline[strtolower($order['status'])]['label'] ?? ucfirst($order['status']) ?></span>
            </div>
        </div>

        <!-- Riwayat -->
        <div>
            <h2 class="text-xl font-semibold mb-4">Riwayat Status</h2>
            <?php if (empty($status_history)): ?>
                <p class="text-gray-500">Belum ada riwayat status.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($status_history as $status): ?>
                    <div class="border-l-4 border-blue-500 pl-4 py-2">
                        <div class="flex justify-between items-center">
                            <div class="font-medium"><?= $status_timeline[$status['proses']]['label'] ?? ucfirst($status['proses']) ?></div>
                            <div class="text-sm text-gray-500"><?= date('d M Y H:i', strtotime($status['waktu'])) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
