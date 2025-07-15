<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Status Proses Laundry</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body class="bg-blue-50 min-h-screen flex items-center justify-center p-6">
  <div class="max-w-3xl w-full bg-white p-8 rounded-xl shadow-lg space-y-8">
    
    <!-- Header -->
    <div class="text-center">
      <h1 class="text-3xl font-bold text-blue-800">Status Proses Laundry</h1>
      <p class="text-gray-500 mt-1">Pantau laundry Anda secara real-time</p>
    </div>

    <!-- Info Order -->
    <div class="bg-blue-100 p-4 rounded-md text-blue-800 text-sm flex justify-between">
      <div>
        <p><strong>No Order:</strong> #LNDR-20250612-001</p>
        <p><strong>Nama:</strong> John Miller</p>
        <p><strong>Tanggal Order:</strong> 12 Juni 2025</p>
      </div>
      <div class="text-right">
        <p><strong>Estimasi Selesai:</strong></p>
        <p>13 Juni 2025, 17:00 WIB</p>
      </div>
    </div>

    <!-- Progres Bar -->
    <ol class="relative border-l-4 border-blue-300 pl-6 space-y-10">

      <!-- Step 1: Order Masuk -->
      <li class="flex items-start space-x-4">
        <span class="text-green-600 text-xl"><i class="fas fa-check-circle"></i></span>
        <div>
          <h3 class="text-lg font-semibold text-blue-800">Order Diterima</h3>
          <p class="text-sm text-gray-600">Pesanan telah diterima dan akan segera diproses.</p>
          <span class="text-xs text-green-700">✔️ Selesai | 12 Juni 2025, 09:00</span>
        </div>
      </li>

      <!-- Step 2: Pencucian -->
      <li class="flex items-start space-x-4">
        <span class="text-green-600 text-xl"><i class="fas fa-check-circle"></i></span>
        <div>
          <h3 class="text-lg font-semibold text-blue-800">Pencucian</h3>
          <p class="text-sm text-gray-600">Pakaian sedang dicuci dengan detergent khusus.</p>
          <span class="text-xs text-green-700">✔️ Selesai | 12 Juni 2025, 11:30</span>
        </div>
      </li>

      <!-- Step 3: Pengeringan -->
      <li class="flex items-start space-x-4">
        <span class="text-yellow-500 text-xl animate-pulse"><i class="fas fa-sync-alt"></i></span>
        <div>
          <h3 class="text-lg font-semibold text-blue-800">Pengeringan</h3>
          <p class="text-sm text-gray-600">Proses pengeringan sedang berjalan dengan suhu optimal.</p>
          <span class="text-xs text-yellow-600">🔄 Sedang diproses...</span>
        </div>
      </li>

      <!-- Step 4: Penyetrikaan -->
      <li class="flex items-start space-x-4 opacity-60">
        <span class="text-gray-400 text-xl"><i class="fas fa-clock"></i></span>
        <div>
          <h3 class="text-lg font-semibold text-gray-600">Penyetrikaan</h3>
          <p class="text-sm text-gray-500">Menunggu giliran setrika.</p>
          <span class="text-xs text-gray-400">⏳ Belum dimulai</span>
        </div>
      </li>

      <!-- Step 5: Pengepakan -->
      <li class="flex items-start space-x-4 opacity-60">
        <span class="text-gray-400 text-xl"><i class="fas fa-box-open"></i></span>
        <div>
          <h3 class="text-lg font-semibold text-gray-600">Pengepakan</h3>
          <p class="text-sm text-gray-500">Pakaian akan dikemas rapi untuk pengantaran.</p>
          <span class="text-xs text-gray-400">⏳ Belum dimulai</span>
        </div>
      </li>

      <!-- Step 6: Pengantaran -->
      <li class="flex items-start space-x-4 opacity-60">
        <span class="text-gray-400 text-xl"><i class="fas fa-truck"></i></span>
        <div>
          <h3 class="text-lg font-semibold text-gray-600">Pengantaran</h3>
          <p class="text-sm text-gray-500">Driver akan mengantar ke alamat Anda.</p>
          <span class="text-xs text-gray-400">⏳ Belum dimulai</span>
        </div>
      </li>

      <!-- Step 7: Selesai -->
      <li class="flex items-start space-x-4 opacity-60">
        <span class="text-gray-400 text-xl"><i class="fas fa-check-double"></i></span>
        <div>
          <h3 class="text-lg font-semibold text-gray-600">Selesai</h3>
          <p class="text-sm text-gray-500">Laundry Anda telah selesai dan diterima.</p>
          <span class="text-xs text-gray-400">⏳ Belum selesai</span>
        </div>
      </li>
    </ol>

    <!-- Tombol Refresh -->
    <div class="text-center pt-4">
      <button class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition">
        🔄 Perbarui Status
      </button>
    </div>
  </div>
</body>
</html>
