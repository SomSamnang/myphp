<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Get order IDs from URL
$order_ids_str = $_GET['order_ids'] ?? '';
$order_ids = array_filter(array_map('intval', explode(',', $order_ids_str)));

if (empty($order_ids)) {
    echo "No order IDs provided!";
    exit;
}

// Fetch orders from DB
$placeholders = implode(',', array_fill(0, count($order_ids), '?'));
$sql = "SELECT o.*, c.name AS cake_name, c.price 
        FROM orders o 
        JOIN cakes c ON o.cake_id = c.id 
        WHERE o.id IN ($placeholders)";
$stmt = $conn->prepare($sql);
$types = str_repeat('i', count($order_ids));
$stmt->bind_param($types, ...$order_ids);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
$total_amount = 0;
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
    $total_amount += $row['price'] * $row['quantity'];
}

// ✅ Create invoices table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_ids TEXT,
    total_amount DECIMAL(10,2),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// ✅ Insert invoice record
$order_ids_str = implode(',', $order_ids);
$stmt = $conn->prepare("INSERT INTO invoices (order_ids, total_amount) VALUES (?, ?)");
$stmt->bind_param("sd", $order_ids_str, $total_amount);
$stmt->execute();
$invoice_id = $conn->insert_id;
$stmt->close();

// ✅ Format invoice number like INV00001
$invoice_number = 'INV' . str_pad($invoice_id, 5, '0', STR_PAD_LEFT);

// ✅ Timezone & date
date_default_timezone_set('Asia/Phnom_Penh');
$invoice_date = date('d-F-Y');
$invoice_time = date('g:i A');
?>
<!DOCTYPE html>
<html lang="km">
<head>
  <meta charset="UTF-8" />
  <title>វិក័យបត្រ #<?= htmlspecialchars($invoice_number) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@300;400;700&display=swap" rel="stylesheet">
  <style>
    @page {
      size: A4;
      margin: 20mm;
    }
    body {
      font-family: 'Battambang', Arial, sans-serif;
      background: white;
      margin: 0;
      padding: 0;
    }
    .invoice-container {
      width: 210mm;
      min-height: 297mm;
      padding: 20mm;
      margin: auto;
      background: #fff;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      border: 1px solid #333;
      padding: 10px;
      text-align: center;
      font-size: 14pt;
    }
    th {
      background-color: #f0f0f0;
    }
    .total-row td {
      font-weight: bold;
      font-size: 16pt;
    }
    .invoice-header p {
      font-size: 15pt;
    }
    @media print {
      .btn-print, .no-print { display: none; }
      .invoice-container {
        box-shadow: none;
        width: 100%;
        padding: 0;
        margin: 0;
      }
    }
  </style>
</head>
<body>
  <div class="text-end no-print p-3">
   <!-- ប៊ូតុងបោះពុម្ព -->
<button class="btn btn-primary btn-print" onclick="printAndRedirect()">🖨️ បោះពុម្ពវិក័យបត្រ</button>

<script>
function printAndRedirect() {
  window.print();

  // បន្ទាប់ពីបោះពុម្ពចប់ នឹងត្រលប់ទៅ dashboard.php
  window.onafterprint = function() {
    window.location.href = 'dashboard.php';
  };
}
</script>

  </div>

  <div class="invoice-container">
    <h1 class="text-center">វិក័យប័ត្រ</h1>
    <h1 class="text-center fs-3">invoice</h1>

    <div class="invoice-header mb-3">
      <p><strong>លេខវិក័យប័ត្រ៖</strong> <?= $invoice_number ?></p>
      <p><strong>កាលបរិច្ឆេទ៖</strong> <?= $invoice_date ?></p>
      <p><strong>ម៉ោង៖</strong> <?= $invoice_time ?></p>
    </div>

    <table>
  <thead>
  <tr>
    <th>ល.រ</th>

    <th>ឈ្មោះនំ</th>
    <th>តម្លៃ</th>
    <th>បរិមាណ</th>
    <th>សរុប</th>
  </tr>
</thead>

<tbody>
  <?php $i = 1; foreach ($orders as $order): ?>
    <tr>
      <td><?= $i++ ?></td> <!-- ល.រ -->

      <td><?= htmlspecialchars($order['cake_name']) ?></td>
      <td><?= number_format($order['price'], 2) ?>$</td>
      <td><?= $order['quantity'] ?></td>
      <td><?= number_format($order['price'] * $order['quantity'], 2) ?>$</td>
    </tr>
  <?php endforeach; ?>
  <tr class="total-row">
    <td colspan="4" class="text-end">សរុប:</td>
    <td><?= number_format($total_amount, 2) ?>$</td>
  </tr>
</tbody>

    </table>

    <div class="mt-4">
      <h3>ABA Pay QR Code ខាងក្រោម:</h3>
      <img src="images/randy-aba.jpg" alt="ABA Pay QR Code" style="margin-left: 50px; max-width: 200px; padding: 10px; border: 1px solid #ccc; border-radius: 8px;" />
      <h3 class="mt-3">សម្រាប់បង់ប្រាក់សរុប: $<?= number_format($total_amount, 2) ?></h3>
    </div>

    <p class="mt-3">បញ្ជាក់៖ សូមពិនិត្យឲ្យបានត្រឹមត្រូវមុនពេលចេញវិក័យប័ត្រ។ សូមរក្សាវិក័យបត្រនេះសម្រាប់ការបង់ប្រាក់។</p>
    <p>អរគុណសម្រាប់ការគាំទ្រហាងនំខេករបស់យើង!</p>
  </div>
</body>
</html>
