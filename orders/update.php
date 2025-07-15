<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$is_admin = $_SESSION['role'] === 'admin';

if (!isset($_GET['id'])) {
    header("Location: list.php");
    exit();
}
$order_id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: list.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = $_POST['customer_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $payment_method = $_POST['payment_method'];
    $status = $_POST['status'] ?? $order['status'];
    $items_data = $_POST['items'];
    $services = isset($_POST['laundry_services']) ? implode(',', $_POST['laundry_services']) : null;

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("UPDATE orders SET customer_name=?, phone=?, address=?, payment_method=?, status=?, services=? WHERE id=?");
        $stmt->bind_param("ssssssi", $customer_name, $phone, $address, $payment_method, $status, $services, $order_id);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();

        $total = 0;
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, item_name, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
        foreach ($items_data as $item) {
            $name = $item['name'];
            $qty = (int)$item['quantity'];

            $per_kg = ['baju', 'celana', 'jaket', 'handuk'];
            $per_item = ['selimut', 'bedcover', 'karpet', 'boneka', 'gorden', 'sprei', 'helm', 'tas', 'sepatu'];
            $unit_price = in_array(strtolower($name), $per_kg) ? 5000 : (in_array(strtolower($name), $per_item) ? 10000 : 0);

            $subtotal = $qty * $unit_price;
            $total += $subtotal;
            $stmt->bind_param("isiid", $order_id, $name, $qty, $unit_price, $subtotal);
            $stmt->execute();
        }

        $stmt = $conn->prepare("UPDATE orders SET total = ? WHERE id = ?");
        $stmt->bind_param("ii", $total, $order_id);
        $stmt->execute();

        $conn->commit();
        $_SESSION['success'] = "Order berhasil diperbarui.";
        header("Location: tracking.php?id=$order_id");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Gagal memperbarui order: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Order</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 py-8 px-4">
<div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
  <h2 class="text-2xl font-bold mb-4">Edit Order #<?= htmlspecialchars($order['order_code']) ?></h2>

  <?php if (isset($error)): ?>
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST" class="space-y-4">
    <div>
      <label class="block font-medium">Nama Pelanggan</label>
      <input type="text" name="customer_name" required class="w-full border px-3 py-2 rounded" value="<?= htmlspecialchars($order['customer_name']) ?>">
    </div>
    <div>
      <label class="block font-medium">No. Telepon</label>
      <input type="text" name="phone" required class="w-full border px-3 py-2 rounded" value="<?= htmlspecialchars($order['phone']) ?>">
    </div>
    <div>
      <label class="block font-medium">Alamat</label>
      <textarea name="address" required class="w-full border px-3 py-2 rounded"><?= htmlspecialchars($order['address']) ?></textarea>
    </div>

    <!-- ✅ Checkbox Layanan -->
    <div>
      <label class="block font-medium mb-1">Layanan Laundry</label>
      <?php
        $availableServices = ['Pencucian', 'Penyetrikaan', 'Dry Cleaning'];
        $selected = isset($order['services']) ? explode(',', $order['services']) : [];
      ?>
      <div class="flex flex-wrap gap-4">
        <?php foreach ($availableServices as $svc): ?>
          <label class="inline-flex items-center">
            <input type="checkbox" name="laundry_services[]" value="<?= $svc ?>"
                   <?= in_array($svc, $selected) ? 'checked' : '' ?>
                   class="form-checkbox h-4 w-4 text-blue-600">
            <span class="ml-2 text-sm text-gray-700"><?= $svc ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- METODE PEMBAYARAN -->
    <div>
      <label class="block font-medium mb-2">Metode Pembayaran</label>
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-2">
        <?php foreach (['ewallet', 'bank', 'card', 'qris', 'cod', 'cash'] as $method): ?>
        <button type="button" onclick="showPayment('<?= $method ?>')" class="bg-gray-200 px-2 py-1 rounded"><?= strtoupper($method) ?></button>
        <?php endforeach; ?>
      </div>

      <?php
        $payments = [
          'ewallet' => ['DANA','GoPay','OVO','ShopeePay','LinkAja'],
          'bank' => ['Bank BCA','Bank BNI','Bank BRI','Bank Mandiri'],
          'card' => ['Credit Card','Debit Card'],
          'qris' => ['QRIS'],
          'cod' => ['COD'],
          'cash' => ['Cash']
        ];
        foreach ($payments as $key => $options):
      ?>
      <div id="<?= $key ?>" class="hidden">
        <select name="payment_method" class="w-full border px-3 py-2 rounded" required>
          <?php foreach ($options as $opt): ?>
            <option value="<?= $opt ?>" <?= $order['payment_method'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- STATUS (admin only) -->
    <?php if ($is_admin): ?>
      <div>
        <label class="block font-medium">Status</label>
        <select name="status" class="w-full border px-3 py-2 rounded">
          <?php foreach (['pending','processing','washing','drying','ironing','packaging','ready_for_pickup','done'] as $s): ?>
            <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ', $s)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php endif; ?>

    <!-- ITEM -->
    <div class="flex justify-between items-center mt-6 mb-2">
      <h3 class="text-lg font-semibold">Item Pesanan</h3>
      <button type="button" id="add-item" class="bg-blue-600 text-white px-3 py-1 rounded">+ Tambah Item</button>
    </div>

    <div id="items-container" class="space-y-4">
      <?php foreach ($items as $i => $item): ?>
        <div class="grid grid-cols-<?= $is_admin ? '5' : '4' ?> gap-4 item-row bg-gray-50 p-3 rounded items-center">
          <select name="items[<?= $i ?>][name]" class="item-name border px-3 py-2 rounded" required>
            <?php
              $list = ['baju','celana','jaket','handuk','selimut','bedcover','karpet','boneka','gorden','sprei','helm','tas','sepatu'];
              foreach ($list as $opt):
                $sel = strtolower($item['item_name']) === $opt ? 'selected' : '';
                echo "<option value='$opt' $sel>".ucfirst($opt)."</option>";
              endforeach;
            ?>
          </select>
          <input type="number" name="items[<?= $i ?>][quantity]" class="item-qty border px-3 py-2 rounded" value="<?= $item['quantity'] ?>" required>
          <?php if ($is_admin): ?>
            <input type="number" name="items[<?= $i ?>][unit_price]" class="item-price border px-3 py-2 rounded" value="<?= $item['unit_price'] ?>" required>
          <?php else: ?>
            <input type="hidden" name="items[<?= $i ?>][unit_price]" value="<?= $item['unit_price'] ?>">
            <div class="pt-2 text-sm">
              Rp <?= number_format($item['unit_price'],0,',','.') ?>/<?= $item['unit_price'] == 5000 ? 'Kg' : 'Unit' ?>
            </div>
          <?php endif; ?>
          <button type="button" onclick="removeItem(this)" class="text-red-600 hover:text-red-800 font-bold text-sm">✕</button>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="flex justify-end mt-4">
      <a href="list.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded mr-2">Batal</a>
      <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded">Simpan</button>
    </div>
  </form>
</div>

<script>
let itemCount = <?= count($items) ?>;
const container = document.getElementById('items-container');
const isAdmin = <?= json_encode($is_admin) ?>;

function showPayment(method) {
  ['ewallet','bank','card','qris','cod','cash'].forEach(id => {
    document.getElementById(id).style.display = (id === method) ? 'block' : 'none';
  });
}

function getItemRow(i) {
  const options = ['baju','celana','jaket','handuk','selimut','bedcover','karpet','boneka','gorden','sprei','helm','tas','sepatu']
    .map(n => `<option value="${n}">${n.charAt(0).toUpperCase() + n.slice(1)}</option>`).join('');

  return `
    <div class="grid grid-cols-${isAdmin ? '5' : '4'} gap-4 item-row bg-gray-50 p-3 rounded items-center">
      <select name="items[${i}][name]" class="item-name border px-3 py-2 rounded" required>${options}</select>
      <input type="number" name="items[${i}][quantity]" class="item-qty border px-3 py-2 rounded" required>
      ${isAdmin ? `<input type="number" name="items[${i}][unit_price]" class="item-price border px-3 py-2 rounded" required>` :
        `<input type="hidden" name="items[${i}][unit_price]" value="0"><div class="pt-2 text-sm text-gray-700">Auto</div>`}
      <button type="button" onclick="removeItem(this)" class="text-red-600 hover:text-red-800 font-bold text-sm">✕</button>
    </div>
  `;
}

document.getElementById('add-item').addEventListener('click', () => {
  const div = document.createElement('div');
  div.innerHTML = getItemRow(itemCount);
  container.appendChild(div);
  attachItemLogic();
  itemCount++;
});

function attachItemLogic() {
  document.querySelectorAll('.item-name').forEach(el => {
    el.addEventListener('change', () => {
      const val = el.value.toLowerCase();
      const perKg = ['baju','celana','jaket','handuk'];
      const perItem = ['selimut','bedcover','karpet','boneka','gorden','sprei','helm','tas','sepatu'];
      const price = perKg.includes(val) ? 5000 : (perItem.includes(val) ? 10000 : 0);
      const priceInput = el.closest('.item-row').querySelector('.item-price');
      if (priceInput) priceInput.value = price;
    });
  });
}

function removeItem(button) {
  const row = button.closest('.item-row');
  if (row) row.remove();
}

attachItemLogic();
</script>
</body>
</html>
