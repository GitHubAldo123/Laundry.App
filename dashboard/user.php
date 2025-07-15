<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Get user orders (latest 3 orders with status)
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT o.id, o.order_code, o.created_at, 
           COALESCE(os.proses, 'Unknown') AS status
    FROM orders o
    LEFT JOIN order_status os ON o.id = os.order_id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
    LIMIT 3
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
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
  </style>
</head>
<body class="bg-[#F9FCFF] min-h-screen p-5 sm:p-8">
  <header class="flex justify-between items-center mb-8 relative">
    <div>
      <p class="text-sm font-normal text-black">Welcome,</p>
      <h1 class="text-lg font-semibold text-black"><?php echo htmlspecialchars($_SESSION['username']); ?></h1>
    </div>
    <button aria-label="Menu" id="menuButton" class="text-black text-2xl focus:outline-none z-20">
      <i class="fas fa-bars"></i>
    </button>

    <!-- Hamburger menu -->
    <nav id="menu" class="fixed top-0 right-0 h-full w-64 bg-white shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out z-10">
      <div class="p-6 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-black">Menu</h2>
        <button aria-label="Close Menu" id="closeMenu" class="text-black text-2xl focus:outline-none">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <ul class="mt-6 space-y-4 px-6">
        <li><a href="user.php" class="block text-black text-base font-medium hover:text-[#0077FF]">Home</a></li>
        <li><a href="profil.php" class="block text-black text-base font-medium hover:text-[#0077FF]">Profile</a></li>
        <li><a href="../orders/list.php" class="block text-black text-base font-medium hover:text-[#0077FF]">Orders</a></li>
        <li><a href="../auth/logout.php" class="block text-black text-base font-medium hover:text-[#0077FF]">Logout</a></li>
      </ul>
    </nav>

    <!-- Overlay -->
    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-30 hidden z-5"></div>
  </header>

  <main class="grid grid-cols-2 gap-6 sm:gap-8">
    <?php
    $services = [
      "Washing" => "ee85166a-ef84-48dc-6aab-d0a90606c566.jpg",
      "Steam Press" => "223524d0-86e7-4cf0-b6c0-43c514ae30c9.jpg",
      "Dry Cleaning" => "2c2f1276-f98f-40a8-b6a7-9e763b9cf28e.jpg",
      "Formal Wash" => "e857608b-ebf6-440a-0719-252d2a133f95.jpg",
      "Deep Cleaning" => "70bc4141-33c6-44d2-edd0-b9bda7d18933.jpg",
      "Powder Wash" => "a3032ebb-4c74-4645-3e97-330b0f888419.jpg",
    ];
    foreach ($services as $name => $img): ?>
    <a href="../orders/create.php?service=<?php echo urlencode($name); ?>" class="bg-white rounded-xl shadow-md p-6 flex flex-col items-center text-center cursor-pointer">
      <img src="https://storage.googleapis.com/a1aa/image/<?php echo $img; ?>" alt="<?php echo $name; ?>" class="mb-3" width="72" height="72" />
      <p class="text-sm font-normal text-black"><?php echo $name; ?></p>
    </a>
    <?php endforeach; ?>
  </main>

  <section class="mt-10 flex justify-between items-center">
    <h2 class="text-sm font-semibold text-black">Current Orders (<?php echo count($orders); ?>)</h2>
    <a href="../orders/list.php" class="text-sm text-[#0077FF] hover:underline focus:underline">View All</a>
  </section>

  <?php if (!empty($orders)): ?>
    <?php foreach ($orders as $order): ?>
      <section class="mt-4 flex items-center space-x-3 text-sm text-[#0077FF] font-normal">
        <i class="fas fa-truck"></i>
        <p>Order No: <span class="font-semibold">#<?php echo htmlspecialchars($order['order_code']); ?></span></p>
        <p class="text-black font-normal"><?php echo ucfirst($order['status']); ?></p>
      </section>
    <?php endforeach; ?>
  <?php else: ?>
    <section class="mt-4 text-sm text-gray-500">
      Tidak ada pesanan ditemukan.
    </section>
  <?php endif; ?>

  <script>
    const menuButton = document.getElementById('menuButton');
    const closeMenu = document.getElementById('closeMenu');
    const menu = document.getElementById('menu');
    const overlay = document.getElementById('overlay');

    function openMenu() {
      menu.classList.remove('translate-x-full');
      overlay.classList.remove('hidden');
      menuButton.classList.add('hidden');
    }

    function closeMenuFunc() {
      menu.classList.add('translate-x-full');
      overlay.classList.add('hidden');
      menuButton.classList.remove('hidden');
    }

    menuButton.addEventListener('click', openMenu);
    closeMenu.addEventListener('click', closeMenuFunc);
    overlay.addEventListener('click', closeMenuFunc);
  </script>
</body>
</html>
