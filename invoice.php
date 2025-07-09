<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Redirect តាម Role
$redirect = ($_SESSION['role'] === 'admin') ? 'dashboard.php' : 'user_dashboard.php';

// បញ្ជាក់ order_id
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    echo "No order ID provided!";
    exit;
}

$order_id = intval($_GET['order_id']);

// Query order & cake info
$stmt = $conn->prepare("
    SELECT o.*, c.name AS cake_name, c.price
    FROM orders o
    JOIN cakes c ON o.cake_id = c.id
    WHERE o.id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "Order not found!";
    exit;
}

$total_amount = $order['price'] * $order['quantity'];
?>

<!DOCTYPE html>
<html lang="km">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>វិក័យប័ត្រ #<?= $order_id ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@300;400;700&display=swap" rel="stylesheet">
  <style>
    @page {
      size: A4;
      margin: 20mm;
    }
    body {
      font-family: 'Battambang', Arial, sans-serif;
      background: #fff;
      margin: 0;
      padding: 0;
      -webkit-print-color-adjust: exact;
    }
    .invoice-container {
      width: 210mm;
      min-height: 297mm;
      padding: 20mm;
      margin: auto;
      background: white;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h1 {
      margin-bottom: 20px;
      font-weight: bold;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      border: 1px solid #333;
      padding: 10px;
      text-align: left;
      font-size: 14pt;
    }
    th {
      background-color: #f0f0f0;
    }
    .text-right {
      text-align: right;
    }
    .total-row td {
      font-weight: bold;
      font-size: 16pt;
    }
    .aba-qr-container {
      text-align: left;
      margin-top: 20px;
    }
    .aba-qr {
      max-width: 200px;
      height: auto;
    }
    @media print {
      .no-print {
        display: none;
      }
      .invoice-container {
        box-shadow: none;
        width: 100%;
        padding: 0;
        margin: 0;
      }
      img {
        max-width: 100%;
        height: 50%;
      }
    }
  </style>
</head>
<body>

<div class="invoice-container">
  <h1>វិក័យប័ត្រ #<?= $order_id ?></h1>
  <p><strong>ថ្ងៃបញ្ជាទិញ:</strong> <?= htmlspecialchars($order['order_date']) ?></p>

  <table>
    <thead>
      <tr>
        <th>ឈ្មោះនំ</th>
        <th>តម្លៃ ($)</th>
        <th>បរិមាណ</th>
        <th>សរុប</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><?= htmlspecialchars($order['cake_name']) ?></td>
        <td>$<?= number_format($order['price'], 2) ?></td>
        <td><?= $order['quantity'] ?></td>
        <td>$<?= number_format($total_amount, 2) ?></td>
      </tr>
      <tr class="total-row">
        <td colspan="3" class="text-right">ចំណូលសរុប:</td>
        <td>$<?= number_format($total_amount, 2) ?></td>
      </tr>
    </tbody>
  </table>
  <div class="no-print mt-4 text-end">
    <button
      class="btn btn-primary"
      onclick="
        if (confirm('តើអ្នកចង់បោះពុម្ពវិក័យបត្រនេះមែនទេ?')) {
          window.print();
          setTimeout(function() {
            alert('វិក័យបត្របានបោះពុម្ពរួចរាល់។ កំពុងត្រឡប់...');
            window.location.href = '<?= $redirect ?>';
          }, 1000);
        }
      "
    >
      🖨️ បោះពុម្ពវិក័យបត្រ
    </button>
  </div>

  <div class="aba-qr-container">
    <h3>ABA Pay QR Code</h3>
    <p>សម្រាប់បង់ប្រាក់តាម ABA Pay សូមស្កែន QR Code ខាងក្រោម:</p>
    <img src="images/randy-aba.jpg" alt="ABA Pay QR Code" class="aba-qr">
    <p class="mt-2">លេខកូដ ABA: 001 23 52 <strong><?= htmlspecialchars($order['aba_code'] ?? '') ?></strong></p>
  </div>

  <p class="text-left mt-3">សូមរក្សាវិក័យបត្រនេះសម្រាប់ការបង់ប្រាក់។</p>
  <p class="text-left">អរគុណសម្រាប់ការបញ្ជាទិញរបស់អ្នក!</p>


</div>

</body>
</html>
