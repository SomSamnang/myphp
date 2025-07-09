<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
  header("Location: login.php");
  exit;
}

$search = isset($_GET["search"]) ? $_GET["search"] : "";

$sql = "SELECT o.id, c.name AS cake_name, c.price, o.quantity, o.order_date, o.payment_status
        FROM orders o
        JOIN cakes c ON o.cake_id = c.id
        WHERE c.name LIKE ? OR o.order_date LIKE ?
        ORDER BY o.order_date DESC";

$stmt = $conn->prepare($sql);
$like = "%$search%";
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="km">
<head>
  <meta charset="UTF-8">
  <title>📦 បញ្ជីបញ្ជាទិញ</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@300;400;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Battambang', sans-serif;
      background-color: #f9f9f9;
    }
    h2 {
      font-weight: bold;
      margin-bottom: 20px;
      color: #2c3e50;
    }
    .btn-success {
      font-size: 14px;
    }
    table th {
      background-color: #f0f0f0;
      text-align: center;
    }
  </style>
</head>

<body class="container py-4">

  <h2>📦 បញ្ជីបញ្ជាទិញ</h2>

  <form method="GET" class="row g-3 mb-3">
    <div class="col-md-4">
      <input type="text" name="search" placeholder="ស្វែងរកនំ ឬ ថ្ងៃ" value="<?= htmlspecialchars($search) ?>" class="form-control" />
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-primary">🔍 ស្វែងរក</button>
    </div>
    <div class="col-auto">
      <a href="orders.php" class="btn btn-secondary">↺ លុប</a>
      <a href="dashboard.php" class="btn btn-secondary">⬅️ ត្រលប់</a>
    </div>
  </form>

  <table class="table table-striped table-bordered align-middle">
    <thead>
      <tr>
        <th>លេខកូដ</th>
        <th>ឈ្មោះនំ</th>
        <th>តម្លៃ ($)</th>
        <th>បរិមាណ</th>
        <th>សរុប ($)</th>
        <th>កាលបរិច្ឆេទ</th>
        <th>ស្ថានភាព</th>
        <th>វិក័យបត្រ</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td class="text-center"><?= $row["id"] ?></td>
        <td><?= htmlspecialchars($row["cake_name"]) ?></td>
        <td class="text-end">$<?= number_format($row["price"], 2) ?></td>
        <td class="text-center"><?= $row["quantity"] ?></td>
        <td class="text-end">$<?= number_format($row["price"] * $row["quantity"], 2) ?></td>
        <td class="text-center"><?= $row["order_date"] ?></td>
        <td class="text-center">
  <?php if ($row['payment_status'] !== 'paid'): ?>
    <a href="mark_paid.php?order_id=<?= $row['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('តើអ្នកប្រាកដថាចង់ធ្វើបង់ប្រាក់?');">💵 បង់ប្រាក់</a>
  <?php else: ?>
    ✅ បានបង់ប្រាក់
  <?php endif; ?>
</td>

        <td class="text-center">
          <a href="invoice.php?order_id=<?= $row['id'] ?>" class="btn btn-sm btn-success">🧾</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

</body>
</html>
