<?php
session_start();
include "db.php";
include "datetime.php"; // ✅ ដកថ្ងៃ/ម៉ោងចេញពី datetime.php

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
  header("Location: login.php");
  exit;
}

$search = $_GET["search"] ?? "";

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
  <title>បញ្ជីបញ្ជាទិញ</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@300;400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
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
    table th {
      background-color: #f0f0f0;
      text-align: center;
    }
  </style>
</head>

<body class="container py-4">

  <h2>បញ្ជីបញ្ជាទិញ</h2>

  <p><strong>ថ្ងៃបច្ចុប្បន្ន:</strong> <?= $currentDate ?> | <strong>ម៉ោង:</strong> <?= $currentTime ?></p>

  <?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">🗑️ លុបការបញ្ជាទិញបានជោគជ័យ!</div>
  <?php endif; ?>

  <form method="GET" class="row g-3 mb-3">
    <div class="col-md-4">
      <input type="text" name="search" placeholder="ស្វែងរកនំ ឬ ថ្ងៃ" value="<?= htmlspecialchars($search) ?>" class="form-control" />
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-primary">🔍 ស្វែងរក</button>
    </div>
    <div class="col-auto">
      <a href="orders.php" class="btn btn-danger">↺ សម្អាត់</a>
      <a href="dashboard.php" class="btn btn-secondary">ត្រឡប់</a>
    </div>
  </form>

  <table class="table table-striped table-bordered align-middle">
    <thead>
      <tr>
        <th class="bg-primary text-light">លេខកូដ</th>
        <th class="bg-primary text-light">ឈ្មោះនំ</th>
        <th class="bg-primary text-light">តម្លៃ</th>
        <th class="bg-primary text-light">បរិមាណ</th>
        <th class="bg-primary text-light">សរុប</th>
        <th class="bg-primary text-light">កាលបរិច្ឆេទ</th>

        <th class="bg-primary text-light">ស្ថានភាព</th>
        <th class="bg-primary text-light">សកម្មភាព</th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
        <?php
          $createdDate = date("d-F-Y", strtotime($row['order_date']));
          $createdTime = date("g:i A", strtotime($row['order_date']));
          
        ?>
        <tr>
          <td class="text-center"><?= $i++ ?></td>
          <td class="text-center"><?= htmlspecialchars($row["cake_name"]) ?></td>
          <td class="text-center">$<?= number_format($row["price"], 2) ?></td>
          <td class="text-center"><?= $row["quantity"] ?></td>
          <td class="text-center">$<?= number_format($row["price"] * $row["quantity"], 2) ?></td>

  <td class="text-center"><?= $currentDate ?></td>
  
  


          <td class="text-center">
            <?php if ($row['payment_status'] !== 'paid'): ?>
              <a href="mark_paid.php?order_id=<?= $row['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('តើអ្នកប្រាកដថាចង់ធ្វើបង់ប្រាក់?');">💵 បង់ប្រាក់</a>
            <?php else: ?>
              ✅ បានបង់ប្រាក់
            <?php endif; ?>
          </td>
          <td class="text-center">
            <a href="delete_order.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('តើអ្នកប្រាកដថាចង់លុបការបញ្ជាទិញនេះមែនទេ?');"><i class="fa-solid fa-trash"> លុប</i></a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

</body>
</html>
