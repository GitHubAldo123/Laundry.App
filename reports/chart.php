<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

function getMonthlyData($conn, $table) {
    $query = $conn->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total FROM $table GROUP BY month ORDER BY month");
    $data = [];
    while ($row = $query->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

$order_data = getMonthlyData($conn, 'orders');
$user_data = getMonthlyData($conn, 'users');

// Gabungkan dan sortir label bulan
$labels = array_unique(array_merge(array_column($order_data, 'month'), array_column($user_data, 'month')));
sort($labels);

function mapData($labels, $data) {
    return array_map(fn($label) => (int) ($data[array_search($label, array_column($data, 'month'))]['total'] ?? 0), $labels);
}

$order_chart = mapData($labels, $order_data);
$user_chart = mapData($labels, $user_data);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Grafik</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        canvas {
            max-height: 240px;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-green-50 min-h-screen flex items-center justify-center p-6">

<div class="w-full max-w-5xl bg-white rounded-3xl shadow-xl px-8 py-10 space-y-10">
    <div class="text-center">
        <h1 class="text-3xl font-extrabold text-blue-700 mb-2">📊 Grafik Laporan Bulanan</h1>
        <p class="text-gray-500 text-sm">Menampilkan data jumlah pesanan dan pengguna setiap bulan</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Pesanan Chart -->
        <div class="bg-white rounded-xl shadow border p-5 hover:shadow-md transition duration-300">
            <h2 class="text-lg font-semibold text-blue-700 mb-2">🧺 Total Pesanan / Bulan</h2>
            <canvas id="ordersChart"></canvas>
        </div>

        <!-- User Chart -->
        <div class="bg-white rounded-xl shadow border p-5 hover:shadow-md transition duration-300">
            <h2 class="text-lg font-semibold text-green-700 mb-2">👥 Total User / Bulan</h2>
            <canvas id="usersChart"></canvas>
        </div>
    </div>

    <div class="text-center pt-4 space-x-4">
    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 transition text-white font-medium px-6 py-2 rounded-full shadow">
        🖨️ Cetak Grafik
    </button>

    <a href="../orders/list.php" class="inline-block bg-gray-500 hover:bg-gray-700 text-white font-medium px-6 py-2 rounded-full shadow transition">
        ⬅️ Kembali ke Orders
    </a>
</div>

</div>

<script>
    const labels = <?= json_encode($labels) ?>;

    new Chart(document.getElementById('ordersChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Pesanan',
                data: <?= json_encode($order_chart) ?>,
                backgroundColor: 'rgba(59,130,246,0.7)',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 },
                    grid: { color: '#e5e7eb' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    new Chart(document.getElementById('usersChart'), {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'User',
                data: <?= json_encode($user_chart) ?>,
                fill: true,
                backgroundColor: 'rgba(34,197,94,0.2)',
                borderColor: '#22c55e',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 },
                    grid: { color: '#e5e7eb' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
</script>
</body>
</html>
