<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$isAdmin = ($_SESSION['role'] === 'admin');

// Ambil data pesanan
if ($isAdmin) {
  $stmt = $conn->prepare("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
} else {
  $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
  $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Badge warna status
$statusBadge = [
  'pending' => 'bg-yellow-100 text-yellow-800',
  'processing' => 'bg-blue-100 text-blue-800',
  'washing' => 'bg-indigo-100 text-indigo-800',
  'drying' => 'bg-purple-100 text-purple-800',
  'ironing' => 'bg-pink-100 text-pink-800',
  'packing' => 'bg-gray-200 text-gray-800',
  'delivered' => 'bg-green-100 text-green-800',
  'done' => 'bg-green-200 text-green-800',
  'cancelled' => 'bg-red-100 text-red-800'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Daftar Pesanan | Laundry App</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body { font-family: 'Inter', sans-serif; }
    .action-btn { transition: 0.2s; }
    .action-btn:hover { transform: scale(1.1); }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">
<div class="container mx-auto px-4 py-10">

  <!-- Flash Message -->
  <?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
      <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
  <?php elseif (isset($_SESSION['error'])): ?>
    <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
      <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <div class="bg-white shadow rounded-lg p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Order List</h1>
      <div class="flex flex-wrap gap-3">
        <a href="../dashboard/<?= $isAdmin ? 'admin' : 'user'; ?>.php" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded">
          <i class="fas fa-home mr-2"></i>Dashboard
        </a>
        <?php if ($isAdmin): ?>
          <a href="../reports/chart.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
            <i class="fas fa-chart-bar mr-2"></i>Reports
          </a>
        <?php endif; ?>
        <a href="create.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
          <i class="fas fa-plus mr-2"></i>New Order
        </a>
      </div>
    </div>

    <!-- Order Table -->
    <?php if (!$orders): ?>
      <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 rounded">
        Belum ada pesanan. Klik <strong>New Order</strong> untuk membuat pesanan pertama.
      </div>
    <?php else: ?>
      <div class="overflow-x-auto border rounded-lg">
        <table class="min-w-full text-sm divide-y divide-gray-200">
          <thead class="bg-gray-50 text-xs font-semibold text-gray-600 uppercase">
            <tr class="bg-gray-100">
              <th class="px-6 py-3 text-left">Code</th>
              <th class="px-6 py-3 text-left">Customer</th>
              <?php if ($isAdmin): ?>
                <th class="px-6 py-3 text-left">User</th>
              <?php endif; ?>
              <th class="px-6 py-3 text-left">Status</th>
              <th class="px-6 py-3 text-left">Total</th>
              <th class="px-6 py-3 text-left">Date</th>
              <th class="px-6 py-3 text-left">Layanan</th>
              <th class="px-6 py-3 text-left">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            <?php foreach ($orders as $o): ?>
              <?php
                $badge = $statusBadge[strtolower($o['status'])] ?? 'bg-gray-100 text-gray-800';
                $layanan = !empty($o['services']) ? explode(',', $o['services']) : [];
              ?>
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 font-semibold text-gray-800">#<?= htmlspecialchars($o['order_code']) ?></td>
                <td class="px-6 py-4">
                  <div><?= htmlspecialchars($o['customer_name']) ?></div>
                  <div class="text-gray-500"><?= htmlspecialchars($o['phone']) ?></div>
                </td>
                <?php if ($isAdmin): ?>
                  <td class="px-6 py-4"><?= htmlspecialchars($o['username']) ?></td>
                <?php endif; ?>
                <td class="px-6 py-4">
                  <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold <?= $badge ?>">
                    <?= ucwords(str_replace('_', ' ', $o['status'])) ?>
                  </span>
                </td>
                <td class="px-6 py-4 font-semibold">
                  Rp <?= number_format($o['total'], 0, ',', '.') ?>
                </td>
                <td class="px-6 py-4">
                  <?= date('d M Y', strtotime($o['created_at'])) ?><br>
                  <span class="text-xs text-gray-500"><?= date('H:i', strtotime($o['created_at'])) ?></span>
                </td>
                <td class="px-6 py-4">
                  <?php if (!empty($layanan)): ?>
                    <div class="flex flex-wrap gap-1">
                      <?php foreach ($layanan as $l): ?>
                        <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs"><?= htmlspecialchars(trim($l)) ?></span>
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    <span class="text-gray-400 italic text-sm">-</span>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <a href="tracking.php?id=<?= $o['id'] ?>" class="text-blue-600 hover:text-blue-800 action-btn" title="Tracking">
                      <i class="fas fa-truck"></i>
                    </a>
                    <a href="update.php?id=<?= $o['id'] ?>" class="text-green-600 hover:text-green-800 action-btn" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <a href="receipt.php?id=<?= $o['id'] ?>" class="text-indigo-600 hover:text-indigo-800 action-btn" title="Struk">
                      <i class="fas fa-receipt"></i>
                    </a>
                    <?php if ($isAdmin): ?>
                      <a href="delete.php?id=<?= $o['id'] ?>" class="text-red-600 hover:text-red-800 action-btn" onclick="return confirm('Hapus pesanan ini?')" title="Hapus">
                        <i class="fas fa-trash"></i>
                      </a>
                    <?php else: ?>
                      <a href="cancel.php?id=<?= $o['id'] ?>" class="text-red-500 hover:text-red-700 action-btn" onclick="return confirm('Batalkan pesanan ini?')" title="Batalkan">
                        <i class="fas fa-times-circle"></i>
                      </a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
