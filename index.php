<?php
session_start();
require_once 'config/db.php';

$logged_in = isset($_SESSION['user_id']);
$username = $logged_in ? $_SESSION['username'] : 'Guest';
$role = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Laundry Service</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Inter', sans-serif;
    }
    /* Hero scroll background animation */
    @keyframes scrollBackground {
      0% { transform: translateX(0); }
      100% { transform: translateX(-50%); }
    }
    .animate-scrollBackground {
      animation: scrollBackground 40s linear infinite;
    }
  </style>
  <script>
    function scrollToSection(id) {
      const el = document.getElementById(id);
      if (el) el.scrollIntoView({ behavior: "smooth" });

      const menu = document.getElementById('mobile-menu');
      if(menu && !menu.classList.contains('hidden')) {
        menu.classList.add('hidden');
      }
    }

    function toggleDarkMode() {
      document.documentElement.classList.toggle("dark");
    }

    function toggleMobileMenu() {
      const menu = document.getElementById('mobile-menu');
      if(menu) {
        menu.classList.toggle('hidden');
      }
    }
  </script>
</head>
<body class="bg-[#F9FCFF] min-h-screen">

  <!-- Navbar -->
  <header class="flex justify-between items-center p-3 bg-blue-100 shadow-md">
    <div>
      <p class="text-sm text-gray-600">Welcome,</p>
      <h1 class="text-lg font-semibold text-blue-700"><?php echo htmlspecialchars($username); ?></h1>
    </div>
    <div class="space-x-4">
      <?php if ($logged_in): ?>
        <a href="dashboard/<?php echo $role; ?>.php" class="text-sm font-medium text-green-600 hover:underline">Dashboard</a>
        <a href="auth/logout.php" class="text-sm font-medium text-red-500 hover:underline">Logout</a>
      <?php else: ?>
        <a href="auth/login.php" class="text-sm font-medium text-blue-600 hover:underline">Login</a>
        <a href="auth/register.php" class="text-sm font-medium text-blue-600 hover:underline">Register</a>
      <?php endif; ?>
    </div>
  </header>

  <!-- Hero with scrolling background -->
  <section class="relative overflow-hidden h-[200px] sm:h-[500px] md:h-[600px] flex flex-col justify-center items-center text-white text-center" id="hero">
    <div class="absolute top-0 left-0 w-[200%] h-full flex animate-scrollBackground">
      <img alt="Hero Background" class="w-1/2 h-full object-cover" src="https://storage.googleapis.com/a1aa/image/4efa0e00-c276-4913-9f46-7825ba50a270.jpg" />
      <img alt="Hero Background" class="w-1/2 h-full object-cover" src="https://storage.googleapis.com/a1aa/image/4efa0e00-c276-4913-9f46-7825ba50a270.jpg" />
    </div>
    <div class="relative z-10 px-4">
      <h1 class="text-3xl font-extrabold mb-4 drop-shadow-lg">Solusi Laundry Modern</h1>
      <p class="text-xl max-w-2xl mx-auto mb-8 drop-shadow-lg">Cepat, Bersih, dan Hemat untuk Anda</p>
      <button class="bg-white text-indigo-600 px-6 py-2 rounded font-medium drop-shadow" onclick="scrollToSection('projects')">View Order</button>
    </div>
  </section>

  <!-- Services Section -->
  <main id="projects" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 px-6 py-12">
    <?php
    $services = [
      ['Washing', 'ee85166a-ef84-48dc-6aab-d0a90606c566.jpg'],
      ['Steam Press', '223524d0-86e7-4cf0-b6c0-43c514ae30c9.jpg'],
      ['Dry Cleaning', '2c2f1276-f98f-40a8-b6a7-9e763b9cf28e.jpg'],
      ['Formal Wash', 'e857608b-ebf6-440a-0719-252d2a133f95.jpg'],
      ['Deep Cleaning', '70bc4141-33c6-44d2-edd0-b9bda7d18933.jpg'],
      ['Powder Wash', 'a3032ebb-4c74-4645-3e97-330b0f888419.jpg'],
    ];
    foreach ($services as [$title, $img]): ?>
      <div class="bg-white rounded-xl shadow-md p-6 flex flex-col items-center text-center transform hover:scale-105 transition">
        <img src="https://storage.googleapis.com/a1aa/image/<?php echo $img; ?>" alt="<?php echo $title; ?>" class="mb-3 rounded-lg w-20 h-20 object-cover" />
        <p class="text-sm font-medium text-gray-700"><?php echo $title; ?></p>
      </div>
    <?php endforeach; ?>
  </main>

  <!-- CTA -->
  <div class="text-center mb-16">
    <?php if ($logged_in): ?>
      <a href="dashboard/<?php echo $role; ?>.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white py-3 px-8 rounded-md text-lg shadow-md transition">
        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
      </a>
    <?php else: ?>
      <a href="auth/register.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white py-3 px-8 rounded-md text-lg shadow-md transition">
        <i class="fas fa-user-plus mr-2"></i>Mulai Sekarang
      </a>
    <?php endif; ?>
  </div>

  <!-- Footer -->
  <footer class="bg-blue-100 py-4 text-center text-gray-600 text-sm">
    &copy; <?php echo date('Y'); ?> Laundry App. All rights reserved.
  </footer>

</body>
</html>
