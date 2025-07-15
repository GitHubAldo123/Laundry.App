<?php
/* --------------------------------------------------------------------------
   receipt.php – Struk pesanan Laundry App
   -------------------------------------------------------------------------- */
session_start();
require_once '../config/db.php';

/* 1. --- Validasi parameter --- */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<h1>Order ID tidak valid.</h1>";
    exit();
}
$order_id = (int)$_GET['id'];

/* 2. --- Ambil data order + user pemilik --- */
$sql = "SELECT o.*, u.username, u.id AS owner_id
        FROM orders o
        JOIN users  u ON o.user_id = u.id
        WHERE o.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo "<h1>Order tidak ditemukan.</h1>";
    exit();
}

/* 3. --- Hak akses: admin atau pemilik order saja --- */
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
$logged_id = $_SESSION['user_id'] ?? 0;

if (!$is_admin && $logged_id !== (int)$order['owner_id']) {
    echo "<h1>Akses ditolak.</h1>";
    exit();
}

/* 4. --- Detail item pesanan --- */
$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* 5. --- Parsing layanan (jika ada) --- */
$services = [];
if (!empty($order['services'])) {
    $services = array_map('trim', explode(',', $order['services']));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Struk Pesanan #<?= htmlspecialchars($order['order_code']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    body{font-family:Inter, sans-serif}
    .shadow-deep{box-shadow:0 10px 25px rgba(0,0,0,.15)}
    @media print{.no-print{display:none}}
  </style>
</head>
<body class="bg-gray-100 py-10 px-6">
  <div class="max-w-xl mx-auto bg-white p-6 shadow-deep rounded">
    <!-- Header toko -->
    <div class="text-center mb-6">
      <h1 class="text-2xl font-extrabold text-blue-700 tracking-wide">Laundry App</h1>
      <p class="text-sm text-gray-600">Struk Pesanan</p>
      <hr class="my-4">
    </div>

    <!-- Info order -->
    <div class="mb-5 text-sm text-gray-700 space-y-1">
      <p><strong>Kode Pesanan&nbsp;:</strong> #<?= htmlspecialchars($order['order_code']) ?></p>
      <p><strong>Pelanggan&nbsp;:</strong> <?= htmlspecialchars($order['customer_name']) ?> (<?= htmlspecialchars($order['phone']) ?>)</p>
      <p><strong>Alamat&nbsp;:</strong> <?= htmlspecialchars($order['address']) ?></p>
      <p><strong>Metode Bayar&nbsp;:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
      <p><strong>Dibuat Oleh&nbsp;:</strong> <?= htmlspecialchars($order['username']) ?></p>
      <p><strong>Tanggal&nbsp;:</strong> <?= date('d M Y H:i', strtotime($order['created_at'])) ?></p>
      <?php if ($services): ?>
        <p><strong>Layanan&nbsp;:</strong>
          <?php foreach ($services as $svc): ?>
            <span class="inline-block bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-xs mr-1"><?= $svc ?></span>
          <?php endforeach; ?>
        </p>
      <?php endif; ?>
    </div>

    <!-- Tabel item -->
    <table class="w-full text-sm border border-gray-300 mb-6">
      <thead>
        <tr class="bg-gray-100 text-left">
          <th class="px-3 py-2 border">Barang</th>
          <th class="px-3 py-2 border text-center">Qty</th>
          <th class="px-3 py-2 border text-right">Harga</th>
          <th class="px-3 py-2 border text-right">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $it): ?>
        <tr>
          <td class="px-3 py-2 border"><?= ucwords($it['item_name']) ?></td>
          <td class="px-3 py-2 border text-center"><?= $it['quantity'] ?></td>
          <td class="px-3 py-2 border text-right">Rp <?= number_format($it['unit_price'],0,',','.') ?></td>
          <td class="px-3 py-2 border text-right">Rp <?= number_format($it['subtotal'],0,',','.') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr class="bg-gray-100 font-semibold">
          <td colspan="3" class="px-3 py-2 border text-right">Total</td>
          <td class="px-3 py-2 border text-right text-green-700 font-bold">Rp <?= number_format($order['total'],0,',','.') ?></td>
        </tr>
      </tfoot>
    </table>

    <!-- Tombol -->
    <div class="text-center no-print space-x-3">
      <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 shadow">Cetak Struk</button>
      <a href="list.php" class="inline-block px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-gray-800 shadow">Kembali</a>
    </div>
  </div>
</body>
</html>
