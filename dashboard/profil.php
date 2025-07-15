<?php
session_start();
require_once __DIR__ . '/../config/db.php'; // asumsi folder uploads & file ini di root

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = $error = null;

/* ─────────────────────────  PROCESS POST  ───────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* Hapus seluruh profil + foto */
    if (isset($_POST['delete_profile'])) {
        $conn->begin_transaction();
        try {
            $q  = $conn->prepare("SELECT photo FROM user_profiles WHERE user_id=?");
            $q->bind_param("i", $user_id);
            $q->execute();
            $row = $q->get_result()->fetch_assoc();
            if ($row && $row['photo'] && file_exists(__DIR__ . '/' . $row['photo'])) {
                unlink(__DIR__ . '/' . $row['photo']);
            }
            $del = $conn->prepare("DELETE FROM user_profiles WHERE user_id=?");
            $del->bind_param("i", $user_id);
            $del->execute();
            $conn->commit();
            $success = "Profil berhasil dihapus.";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Gagal menghapus profil!";
        }

    /* Hapus hanya foto */
    } elseif (isset($_POST['delete_photo'])) {
        $q = $conn->prepare("SELECT photo FROM user_profiles WHERE user_id=?");
        $q->bind_param("i", $user_id);
        $q->execute();
        $row = $q->get_result()->fetch_assoc();
        if ($row && $row['photo'] && file_exists(__DIR__ . '/' . $row['photo'])) {
            unlink(__DIR__ . '/' . $row['photo']);
        }
        $upd = $conn->prepare("UPDATE user_profiles SET photo = NULL WHERE user_id=?");
        $upd->bind_param("i", $user_id);
        $upd->execute();
        $success = "Foto profil dihapus.";

    /* Simpan / perbarui profil */
    } else {
        $full_name = $_POST['full_name'];
        $phone     = $_POST['phone'];
        $address   = $_POST['address'];
        $photoPath = null;

        /* Upload foto jika ada */
        if (!empty($_FILES['photo']['name'])) {
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);   // otomatis buat folder bila belum ada
            }

            $photo_name = time() . '_' . preg_replace('/\s+/', '_', $_FILES['photo']['name']);
            $filePath = $uploadDir . $photo_name;
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];

            if (in_array($ext, $allowed) && move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
                $photoPath = 'uploads/' . $photo_name; // simpan relative path
            } else {
                $error = "Gagal upload. Tipe file harus: " . implode(', ', $allowed);
            }
        }

        /* Insert atau Update */
        if (!$error) {
            $check = $conn->prepare("SELECT id FROM user_profiles WHERE user_id=?");
            $check->bind_param("i", $user_id);
            $check->execute();
            $check->store_result();

            if ($check->num_rows) { // UPDATE
                if ($photoPath) {
                    $sql = "UPDATE user_profiles SET full_name=?, phone=?, address=?, photo=? WHERE user_id=?";
                    $upd = $conn->prepare($sql);
                    $upd->bind_param("ssssi", $full_name, $phone, $address, $photoPath, $user_id);
                } else {
                    $sql = "UPDATE user_profiles SET full_name=?, phone=?, address=? WHERE user_id=?";
                    $upd = $conn->prepare($sql);
                    $upd->bind_param("sssi", $full_name, $phone, $address, $user_id);
                }
            } else { // INSERT
                $sql = "INSERT INTO user_profiles (user_id, full_name, phone, address, photo) VALUES (?,?,?,?,?)";
                $upd = $conn->prepare($sql);
                $upd->bind_param("issss", $user_id, $full_name, $phone, $address, $photoPath);
            }

            $success = $upd->execute() ? "Profil disimpan." : "Gagal menyimpan profil!";
        }
    }
}

/* ─────────────────────────  FETCH PROFILE  ───────────────────────── */
$stmt = $conn->prepare("SELECT * FROM user_profiles WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Saya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .fade-slide{animation:fadeSlide .5s ease-out}
        @keyframes fadeSlide{0%{opacity:0;transform:translateY(12px)}100%{opacity:1;transform:translateY(0)}}
    </style>
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-200 min-h-screen flex items-center justify-center p-4">

<div class="max-w-2xl w-full bg-white p-8 rounded-2xl shadow-xl space-y-6 fade-slide">

    <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-2">
        <i class="fas fa-user-circle text-blue-500"></i> Profil Saya
    </h1>

    <?php if ($success): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-4 rounded shadow fade-slide">
            <?= $success ?>
        </div>
    <?php elseif ($error): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-4 rounded shadow fade-slide">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <!--  Tampilan profil  -->
    <div class="flex items-center gap-6">
        <?php
            $photoURL = (!empty($profile['photo']) && file_exists(__DIR__ . '/' . $profile['photo']))
                        ? $profile['photo']
                        : null;
        ?>
        <?php if ($photoURL): ?>
            <img src="<?= $photoURL ?>" alt="Foto Profil"
                 class="w-24 h-24 rounded-full object-cover shadow-md transition hover:scale-105">
        <?php else: ?>
            <div class="w-24 h-24 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 shadow-md">
                <i class="fas fa-user text-2xl"></i>
            </div>
        <?php endif; ?>

        <div>
            <p class="text-lg font-semibold"><?= htmlspecialchars($profile['full_name'] ?? '-') ?></p>
            <p class="text-gray-500"><?= htmlspecialchars($profile['phone'] ?? '-') ?></p>
            <p class="text-gray-500"><?= htmlspecialchars($profile['address'] ?? '-') ?></p>
        </div>
    </div>

    <!--  Tombol aksi  -->
    <div class="flex flex-wrap gap-2">
        <button onclick="toggleForm()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow transition">
            <i class="fas fa-edit mr-1"></i>Edit Profil
        </button>

        <?php if ($profile): ?>
            <form method="POST" onsubmit="return confirm('Hapus profil ini?')" class="inline">
                <input type="hidden" name="delete_profile" value="1">
                <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded shadow transition">
                    <i class="fas fa-trash mr-1"></i>Hapus
                </button>
            </form>
        <?php endif; ?>

        <a href="user.php"
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow transition">
            <i class="fas fa-arrow-left mr-1"></i>Kembali
        </a>
    </div>

    <!--  Form edit  -->
    <form id="editForm" method="POST" enctype="multipart/form-data"
          class="hidden border-t pt-6 space-y-4 fade-slide">

        <div>
            <label class="block font-medium">Nama Lengkap</label>
            <input type="text" name="full_name"
                   value="<?= htmlspecialchars($profile['full_name'] ?? '') ?>" required
                   class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-400">
        </div>

        <div>
            <label class="block font-medium">Nomor HP</label>
            <input type="text" name="phone"
                   value="<?= htmlspecialchars($profile['phone'] ?? '') ?>"
                   class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-400">
        </div>

        <div>
            <label class="block font-medium">Alamat</label>
            <textarea name="address" rows="2"
                      class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-400"><?= htmlspecialchars($profile['address'] ?? '') ?></textarea>
        </div>

        <div>
            <label class="block font-medium">Foto Profil</label>
            <input type="file" name="photo" accept="image/*"
                   class="w-full border px-3 py-2 rounded">
            <?php if ($profile && $profile['photo']): ?>
                <button type="submit" name="delete_photo" value="1"
                        onclick="return confirm('Hapus foto profil sekarang?')"
                        class="mt-2 bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded shadow">
                    <i class="fas fa-image-slash mr-1"></i>Hapus Foto
                </button>
            <?php endif; ?>
        </div>

        <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow transition">
            <i class="fas fa-save mr-1"></i>Simpan
        </button>
    </form>
</div>

<script>
    function toggleForm() {
        document.getElementById('editForm').classList.toggle('hidden');
        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
    }
</script>
</body>
</html>
