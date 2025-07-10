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
 image {
  display: flex;
  margin: 0 auto;
  max-width: 150px;
  margin-bottom: 2px;
}

  </style>
</head>
<body>
<div class="text-end no-print p-3">
  <div class="d-flex justify-content-end gap-2">
    <button class="btn btn-primary btn-print w-auto" onclick="printAndRedirect()">
      🖨️ បោះពុម្ពវិក័យបត្រ
    </button>
    <a href="dashboard.php" class="btn btn-secondary w-auto">
      ↩ ត្រលប់
    </a>
  </div>
</div>

<script>
  function printAndRedirect() {
    window.print();
    setTimeout(function () {
      window.location.href = "dashboard.php";
    }, 1000);
  }
</script>
  </div>
  <div class="invoice-container ">
<div class="text-center mb-0">
  <h1 class="mb-0"> វិក័យបត្រ</h1>
  <h2 class="text-primary text-center fs-4 mb-0">Invoice</h2>

</div>



    <div class="invoice-header">
      <img src="images/logo.jpg" alt="Logo" style="max-width: 100px; margin-left:60px;" />
      <p><strong>លេខវិក័យបត្រ៖</strong> <?= $invoice_number ?></p>
       
      <p><strong>កាលបរិច្ឆេទ៖</strong> <?= $invoice_date ?></p>
      <p><strong>ម៉ោង៖</strong> <?= $invoice_time ?></p>
      <p><strong>អាសយដ្ឋាន៖</strong> ផ្ទះលេខ៖123E ផ្លូវលេខ 162
ភូមិទ្រង់ភូមិ ឃុំព្រៃផ្គាំ ស្រុកអង្គរបូរី ខេត្តតាកែវ​ </p>

     
      
    </div>

    <table>
  <thead>
  <tr>
    <th class="bg-primary text-light">ល.រ</th>
    <th class="bg-primary text-light">ឈ្មោះនំ</th>
    <th class="bg-primary text-light">តម្លៃ</th>
    <th class="bg-primary text-light">បរិមាណ</th>
    <th class="bg-primary text-light">សរុប</th>
  </tr>
</thead>
<tbody>
  <?php $i = 1; foreach ($orders as $order): ?>
    <tr>
      <td><?= $i++ ?></td> 
      <td><?= htmlspecialchars($order['cake_name']) ?></td>
      <td><?= number_format($order['price'], 2) ?>$</td>
      <td><?= $order['quantity'] ?></td>
      <td><?= number_format($order['price'] * $order['quantity'], 2) ?>$</td>
    </tr>
  <?php endforeach; ?>
  <tr class="total-row">
    <td colspan="4" class="text-end text-primary">សរុប:</td>
    <td class="text-danger "><?= number_format($total_amount, 2) ?>$</td>
  </tr>
</tbody>

    </table>

    <div class="mt-1">
      <h3 class="fs-6" style="margin-left: 40px; ">ABA Pay QR Code ខាងក្រោម:</h3>
      <img src="images/randy-aba.jpg" alt="ABA Pay QR Code" style="margin-left: 50px; max-width: 170px; padding: 10px; border: 1px solid #ccc; border-radius: 8px;" />
      <h3 class="mt-2 fs-5 text-primary" style="margin-left">សម្រាប់បង់ប្រាក់សរុប: $<?= number_format($total_amount, 2) ?></h3>
    </div>

    <p class="mt-1 text-danger ">បញ្ជាក់៖ សូមពិនិត្យមើលឥវ៉ានឲ្យបានត្រឹមត្រូវមុនពេលចេញ សូមរក្សាវិក័យបត្រនេះសម្រាប់ការបង់ប្រាក់។</p>
    <p class="mt-1  ">អរគុណសម្រាប់ការគាំទ្រហាងនំខេករបស់យើង!</p>
  </div>
</body>
</html>
