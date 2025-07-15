<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Ambil semua data user beserta profilnya
$query = "
    SELECT u.id, u.username, u.email, u.role, p.full_name, p.phone, p.address, p.photo
    FROM users u
    LEFT JOIN user_profiles p ON u.id = p.user_id
    WHERE u.role = 'user'
    ORDER BY u.created_at DESC
";
$result = $conn->query($query);
$profiles = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Profil Pengguna</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Data Profil Semua User</h1>

        <a href="admin.php" class="mb-4 inline-block bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800">
            ← Kembali ke Dashboard
        </a>

        <div class="overflow-x-auto bg-white shadow rounded-lg p-4">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 text-left text-sm font-semibold text-gray-600">
                    <tr>
                        <th class="py-2 px-4">Username</th>
                        <th class="py-2 px-4">Email</th>
                        <th class="py-2 px-4">Nama Lengkap</th>
                        <th class="py-2 px-4">Telepon</th>
                        <th class="py-2 px-4">Alamat</th>
                        <th class="py-2 px-4">Foto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($profiles as $profile): ?>
                        <tr>
                            <td class="py-2 px-4"><?= htmlspecialchars($profile['username']) ?></td>
                            <td class="py-2 px-4"><?= htmlspecialchars($profile['email']) ?></td>
                            <td class="py-2 px-4"><?= htmlspecialchars($profile['full_name'] ?? '-') ?></td>
                            <td class="py-2 px-4"><?= htmlspecialchars($profile['phone'] ?? '-') ?></td>
                            <td class="py-2 px-4"><?= htmlspecialchars($profile['address'] ?? '-') ?></td>
                            <td class="py-2 px-4">
                                <?php if ($profile['photo']): ?>
                                    <img src="../uploads/<?= $profile['photo'] ?>" class="h-12 w-12 rounded-full object-cover" alt="Foto">
                                <?php else: ?>
                                    <span class="text-gray-400">Belum diunggah</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
