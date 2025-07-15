<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $customer_name = $_POST['customer_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $payment_method = $_POST['payment_method'];
    $items = $_POST['items'];
    $services = isset($_POST['services']) ? implode(', ', $_POST['services']) : '';

    if (empty($customer_name) || empty($phone) || empty($address) || empty($payment_method) || empty($items)) {
        $error = "Semua kolom harus diisi.";
    } else {
        $order_code = 'ORD' . date('Ymd') . strtoupper(substr(uniqid(), -6));
        $total = 0;

        $conn->begin_transaction();

        try {
            // Tambahkan services ke dalam query
            $stmt = $conn->prepare("INSERT INTO orders (user_id, order_code, customer_name, phone, address, services, payment_method, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssssi", $user_id, $order_code, $customer_name, $phone, $address, $services, $payment_method, $total);
            $stmt->execute();
            $order_id = $conn->insert_id;

            $stmt_items = $conn->prepare("INSERT INTO order_items (order_id, item_name, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");

            foreach ($items as $item) {
                $name = strtolower($item['name']);
                $quantity = (int)$item['quantity'];

                $perKg = ['baju', 'celana', 'jaket', 'handuk'];
                $perItem = ['selimut', 'bedcover', 'karpet', 'boneka', 'gorden', 'sprei', 'helm', 'tas', 'sepatu'];

                $unit_price = in_array($name, $perKg) ? 5000 : (in_array($name, $perItem) ? 10000 : 0);
                $subtotal = $quantity * $unit_price;
                $total += $subtotal;

                $stmt_items->bind_param("isiid", $order_id, $name, $quantity, $unit_price, $subtotal);
                $stmt_items->execute();
            }

            $stmt_status = $conn->prepare("INSERT INTO order_status (order_id, proses) VALUES (?, 'Order')");
            $stmt_status->bind_param("i", $order_id);
            $stmt_status->execute();

            $stmt_update = $conn->prepare("UPDATE orders SET total = ? WHERE id = ?");
            $stmt_update->bind_param("ii", $total, $order_id);
            $stmt_update->execute();

            $conn->commit();

            header("Location: receipt.php?id=" . $order_id);
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $error = "Gagal membuat pesanan: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat Pesanan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 py-8 px-4">
<div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Buat Pesanan</h1>

    <?php if (!empty($error)): ?>
        <div class="bg-red-100 text-red-700 p-4 rounded mb-4"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
        <div>
            <label class="block font-medium">Nama Pelanggan</label>
            <input type="text" name="customer_name" required class="w-full border px-3 py-2 rounded">
        </div>
        <div>
            <label class="block font-medium">Nomor HP</label>
            <input type="tel" name="phone" required class="w-full border px-3 py-2 rounded">
        </div>
        <div>
            <label class="block font-medium">Alamat</label>
            <textarea name="address" required class="w-full border px-3 py-2 rounded"></textarea>
        </div>
        <!-- Tambahan Checkbox Layanan -->
        <div>
            <label class="block font-medium mb-1">Layanan</label>
            <div class="space-y-2 text-sm">
                <label><input type="checkbox" name="services[]" value="Pencucian"> Pencucian</label><br>
                <label><input type="checkbox" name="services[]" value="Penyetrikaan"> Penyetrikaan</label><br>
                <label><input type="checkbox" name="services[]" value="Dry Cleaning"> Dry Cleaning</label>
            </div>
        </div>
        <!-- Metode Pembayaran -->
        <div>
            <label class="block font-medium">Metode Pembayaran</label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-2">
                <button type="button" onclick="showPayment('ewallet')" class="bg-blue-100 px-3 py-1 rounded">E-Wallet</button>
                <button type="button" onclick="showPayment('bank')" class="bg-green-100 px-3 py-1 rounded">Bank</button>
                <button type="button" onclick="showPayment('qris')" class="bg-yellow-100 px-3 py-1 rounded">QRIS</button>
                <button type="button" onclick="showPayment('cod')" class="bg-red-100 px-3 py-1 rounded">COD</button>
                <button type="button" onclick="showPayment('card')" class="bg-purple-100 px-3 py-1 rounded">Kartu</button>
                <button type="button" onclick="showPayment('cash')" class="bg-gray-200 px-3 py-1 rounded">Cash</button>
            </div>

            <!-- dropdown sesuai metode -->
            <div id="ewallet" class="hidden">
                <select name="payment_method" class="w-full border px-3 py-2 rounded">
                    <option value="DANA">DANA</option>
                    <option value="GoPay">GoPay</option>
                    <option value="OVO">OVO</option>
                    <option value="ShopeePay">ShopeePay</option>
                    <option value="LinkAja">LinkAja</option>
                </select>
            </div>
            <div id="bank" class="hidden">
                <select name="payment_method" class="w-full border px-3 py-2 rounded">
                    <option value="Bank BCA">Bank BCA</option>
                    <option value="Bank BNI">Bank BNI</option>
                    <option value="Bank BRI">Bank BRI</option>
                    <option value="Bank Mandiri">Bank Mandiri</option>
                </select>
            </div>
            <div id="qris" class="hidden">
                <select name="payment_method" class="w-full border px-3 py-2 rounded">
                    <option value="QRIS">QRIS</option>
                </select>
            </div>
            <div id="cod" class="hidden">
                <select name="payment_method" class="w-full border px-3 py-2 rounded">
                    <option value="COD">COD</option>
                </select>
            </div>
            <div id="card" class="hidden">
                <select name="payment_method" class="w-full border px-3 py-2 rounded">
                    <option value="Credit Card">Credit Card</option>
                    <option value="Debit Card">Debit Card</option>
                </select>
            </div>
            <div id="cash" class="hidden">
                <select name="payment_method" class="w-full border px-3 py-2 rounded">
                    <option value="Cash">Cash</option>
                </select>
            </div>
        </div>

        <!-- Items -->
        <div id="items-container" class="space-y-4">
            <div class="flex justify-between items-center">
                <h2 class="font-semibold">Barang</h2>
                <button type="button" id="add-item" class="bg-blue-500 text-white px-3 py-1 rounded">+ Item</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 item-row">
                <div>
                    <label class="block">Jenis Barang</label>
                    <select name="items[0][name]" class="item-name border px-3 py-2 rounded w-full" required>
                        <option value="">-- Pilih --</option>
                        <option value="baju">Baju</option>
                        <option value="celana">Celana</option>
                        <option value="jaket">Jaket</option>
                        <option value="handuk">Handuk</option>
                        <option value="selimut">Selimut</option>
                        <option value="bedcover">Bedcover</option>
                        <option value="karpet">Karpet</option>
                        <option value="boneka">Boneka</option>
                        <option value="gorden">Gorden</option>
                        <option value="sprei">Sprei</option>
                        <option value="helm">Helm</option>
                        <option value="tas">Tas</option>
                        <option value="sepatu">Sepatu</option>
                    </select>
                </div>
                <div>
                    <label class="block">Jumlah</label>
                    <input type="number" name="items[0][quantity]" class="item-qty border px-3 py-2 rounded w-full" min="1" required>
                </div>
                <div>
                    <label class="block">Harga</label>
                    <input type="text" name="items[0][unit_price]" class="item-price border px-3 py-2 rounded w-full" readonly>
                </div>
            </div>
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Simpan Pesanan</button>
        <a href="../dashboard/user.php" class="block text-center mt-4 text-sm text-blue-600 hover:underline">← Kembali ke Dashboard</a>
    </form>
</div>

<script>
function showPayment(id) {
    ['ewallet', 'bank', 'qris', 'cod', 'card', 'cash'].forEach(el => {
        document.getElementById(el).classList.add('hidden');
    });
    document.getElementById(id).classList.remove('hidden');
}

let itemCount = 1;
const container = document.getElementById('items-container');
const addBtn = document.getElementById('add-item');

const itemTemplate = (i) => `
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 item-row mb-2">
        <div>
            <select name="items[${i}][name]" class="item-name border px-3 py-2 rounded w-full" required>
                <option value="">-- Pilih --</option>
                <option value="baju">Baju</option>
                <option value="celana">Celana</option>
                <option value="jaket">Jaket</option>
                <option value="handuk">Handuk</option>
                <option value="selimut">Selimut</option>
                <option value="bedcover">Bedcover</option>
                <option value="karpet">Karpet</option>
                <option value="boneka">Boneka</option>
                <option value="gorden">Gorden</option>
                <option value="sprei">Sprei</option>
                <option value="helm">Helm</option>
                <option value="tas">Tas</option>
                <option value="sepatu">Sepatu</option>
            </select>
        </div>
        <div>
            <input type="number" name="items[${i}][quantity]" class="item-qty border px-3 py-2 rounded w-full" min="1" required>
        </div>
        <div>
            <input type="text" name="items[${i}][unit_price]" class="item-price border px-3 py-2 rounded w-full" readonly>
        </div>
    </div>
`;

function attachEvents() {
    document.querySelectorAll('.item-name').forEach((el, index) => {
        el.onchange = function () {
            const priceField = document.getElementsByClassName('item-price')[index];
            const selected = this.value.toLowerCase();
            const perKg = ['baju', 'celana', 'jaket', 'handuk'];
            const perItem = ['selimut', 'bedcover', 'karpet', 'boneka', 'gorden', 'sprei', 'helm', 'tas', 'sepatu'];

            let unitPrice = 0;
            let satuan = '';

            if (perKg.includes(selected)) {
                unitPrice = 5000;
                satuan = ' /kg';
            } else if (perItem.includes(selected)) {
                unitPrice = 10000;
                satuan = ' /item';
            }

            priceField.value = unitPrice > 0 ? `${unitPrice}${satuan}` : '';
        };
    });
}

addBtn.addEventListener('click', () => {
    const row = document.createElement('div');
    row.innerHTML = itemTemplate(itemCount);
    container.appendChild(row);
    attachEvents();
    itemCount++;
});

window.addEventListener('DOMContentLoaded', attachEvents);
</script>
</body>
</html>
