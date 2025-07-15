<?php
session_start();
require_once '../config/db.php';

// Validasi login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Statistik
$stats = [];
$res = $conn->query("SELECT COUNT(*) as total_orders FROM orders");
$stats['total_orders'] = $res->fetch_assoc()['total_orders'];

$res = $conn->query("SELECT COUNT(*) as total_users FROM users WHERE role = 'user'");
$stats['total_users'] = $res->fetch_assoc()['total_users'];

$res = $conn->query("SELECT SUM(total) as revenue FROM orders WHERE status = 'done'");
$stats['revenue'] = $res->fetch_assoc()['revenue'] ?? 0;

// Data user
$res = $conn->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
$users = $res->fetch_all(MYSQLI_ASSOC);

// 5 pesanan terakhir
$stmt = $conn->prepare("SELECT o.id, o.customer_name, o.status, o.created_at, u.username 
                        FROM orders o JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC LIMIT 5");
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100">
<div class="flex h-screen">
  <!-- Sidebar -->
  <aside class="bg-blue-800 text-white w-64 py-6 px-4 space-y-6">
    <div class="text-xl font-bold flex items-center gap-2">
      <i class="fas fa-tshirt"></i> Laundry Admin
    </div>
    <nav class="flex flex-col gap-2">
      <a href="admin.php" class="bg-blue-700 px-4 py-2 rounded"><i class="fas fa-chart-line mr-2"></i> Dashboard</a>
      <a href="../orders/list.php" class="hover:bg-blue-700 px-4 py-2 rounded"><i class="fas fa-list mr-2"></i> Orders</a>
      <a href="admin_profiles.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 transition">
        <i class="fas fa-users mr-2"></i>Profil User
      </a>
      <a href="../auth/register.php" class="hover:bg-blue-700 px-4 py-2 rounded"><i class="fas fa-user-plus mr-2"></i> Add User</a>
      <a href="../auth/logout.php" class="hover:bg-blue-700 px-4 py-2 rounded"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 p-8 overflow-auto">
    <h1 class="text-2xl font-bold mb-6">Dashboard Admin</h1>

    <!-- Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white p-5 rounded shadow flex items-center gap-4">
        <div class="p-3 bg-blue-100 text-blue-600 rounded-full"><i class="fas fa-receipt"></i></div>
        <div>
          <p class="text-sm text-gray-500">Total Orders</p>
          <h2 class="text-xl font-bold"><?= $stats['total_orders'] ?></h2>
        </div>
      </div>
      <div class="bg-white p-5 rounded shadow flex items-center gap-4">
        <div class="p-3 bg-green-100 text-green-600 rounded-full"><i class="fas fa-users"></i></div>
        <div>
          <p class="text-sm text-gray-500">Total Users</p>
          <h2 class="text-xl font-bold"><?= $stats['total_users'] ?></h2>
        </div>
      </div>
      <div class="bg-white p-5 rounded shadow flex items-center gap-4">
        <div class="p-3 bg-purple-100 text-purple-600 rounded-full"><i class="fas fa-wallet"></i></div>
        <div>
          <p class="text-sm text-gray-500">Total Revenue</p>
          <h2 class="text-xl font-bold">Rp <?= number_format($stats['revenue'], 0, ',', '.') ?></h2>
        </div>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="bg-white p-6 rounded shadow mb-10">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold">Pesanan Terbaru</h2>
        <a href="../orders/list.php" class="text-blue-500 text-sm hover:underline">Lihat Semua</a>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-gray-600 uppercase tracking-wider text-xs">
            <tr>
              <th class="px-4 py-2">#</th>
              <th class="px-4 py-2">Pelanggan</th>
              <th class="px-4 py-2">Status</th>
              <th class="px-4 py-2">Tanggal</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <?php foreach ($orders as $order): ?>
              <tr>
                <td class="px-4 py-2 font-semibold">#<?= $order['id'] ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($order['customer_name']) ?></td>
                <td class="px-4 py-2">
                  <span class="px-2 py-1 text-xs rounded-full 
                    <?php
                      switch ($order['status']) {
                        case 'done': echo 'bg-green-100 text-green-800'; break;
                        case 'processing': echo 'bg-blue-100 text-blue-800'; break;
                        default: echo 'bg-yellow-100 text-yellow-800';
                      }
                    ?>">
                    <?= ucfirst($order['status']) ?>
                  </span>
                </td>
                <td class="px-4 py-2"><?= date('d M Y', strtotime($order['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- User List -->
    <div class="bg-white p-6 rounded shadow">
      <h2 class="text-lg font-semibold mb-4">Daftar Pengguna</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-xs text-gray-600 uppercase">
            <tr>
              <th class="px-4 py-2">#</th>
              <th class="px-4 py-2">Username</th>
              <th class="px-4 py-2">Email</th>
              <th class="px-4 py-2">Role</th>
              <th class="px-4 py-2">Tanggal Daftar</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <?php foreach ($users as $i => $user): ?>
              <tr>
                <td class="px-4 py-2"><?= $i + 1 ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($user['username']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($user['email']) ?></td>
                <td class="px-4 py-2">
                  <span class="px-2 py-1 text-xs rounded-full <?= $user['role'] === 'admin' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>">
                    <?= ucfirst($user['role']) ?>
                  </span>
                </td>
                <td class="px-4 py-2"><?= date('d M Y', strtotime($user['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>
</body>
</html>
