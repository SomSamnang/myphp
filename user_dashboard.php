<?php
session_start();
include "db.php";

// ត្រួតពិនិត្យ session និង role
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "user") {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$error = "";

// ប្រសិនបើ submit បញ្ជាទិញ
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cake_id = intval($_POST['cake_id']);
    $quantity = intval($_POST['quantity']);

    if ($quantity < 1) {
        $error = "បរិមាណត្រូវតែធំជាង 0";
    } else {
        $order_date = date('Y-m-d'); // តម្លៃថ្ងៃបច្ចុប្បន្ន

        $stmt = $conn->prepare("INSERT INTO orders (cake_id, quantity, order_date, user_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $cake_id, $quantity, $order_date, $user_id);
        if (!$stmt->execute()) {
            $error = "មានបញ្ហាក្នុងការបញ្ចូលការបញ្ជាទិញ: " . $stmt->error;
        }
    }
}

// ទាញនំសម្រាប់ជ្រើសរើស
$cakes = $conn->query("SELECT id, name, price FROM cakes ORDER BY name ASC");

// ទាញប្រវត្តិបញ្ជាទិញរបស់ user
$stmt2 = $conn->prepare("
    SELECT o.id, c.name AS cake_name, c.price, o.quantity, o.order_date
    FROM orders o
    JOIN cakes c ON o.cake_id = c.id
    WHERE o.user_id = ?
    ORDER BY o.order_date DESC
");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$orders = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="km">
<head>
  <meta charset="UTF-8" />
  <title>ទំព័ររបស់អ្នកប្រើ</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@300;400;700&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Battambang', sans-serif;
      background-color: #f8f9fa;
    }
    .container {
      max-width: 900px;
      margin-top: 40px;
    }
    h2 {
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
<div class="container">
  <h2>👤 ទំព័ររបស់អ្នកប្រើធម្មតា</h2>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" class="mb-4 p-3 bg-white rounded shadow-sm">
    <h4>បញ្ជាទិញនំថ្មី</h4>
    <div class="row g-3 align-items-center">
      <div class="col-md-7">
        <label for="cake_id" class="form-label">ជ្រើសរើសនំ</label>
        <select name="cake_id" id="cake_id" class="form-select" required>
          <option value="">-- ជ្រើសរើស --</option>
          <?php while ($cake = $cakes->fetch_assoc()): ?>
            <option value="<?= $cake['id'] ?>">
              <?= htmlspecialchars($cake['name']) ?> — $<?= number_format($cake['price'], 2) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="col-md-3">
        <label for="quantity" class="form-label">បរិមាណ</label>
        <input type="number" name="quantity" id="quantity" class="form-control" min="1" value="1" required>
      </div>

      <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-success w-100">🛒 បញ្ជាទិញ</button>
      </div>
    </div>
  </form>

  <h4>ប្រវត្តិបញ្ជាទិញ</h4>
  <table class="table table-bordered table-striped bg-white">
    <thead>
      <tr>
        <th>លេខកូដ</th>
        <th>ឈ្មោះនំ</th>
        <th>តម្លៃ</th>
        <th>បរិមាណ</th>
        <th>សរុប</th>
        <th>កាលបរិច្ឆេទ</th>
        <th>វិក័យបត្រ</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($orders->num_rows > 0): ?>
        <?php while ($row = $orders->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['cake_name']) ?></td>
          <td>$<?= number_format($row['price'], 2) ?></td>
          <td><?= $row['quantity'] ?></td>
          <td>$<?= number_format($row['price'] * $row['quantity'], 2) ?></td>
          <td><?= date('d-m-Y', strtotime($row['order_date'])) ?></td> <!-- បង្ហាញ dd-mm-yyyy -->
          <td>
            <a href="invoice.php?order_id=<?= $row['id'] ?>" class="btn btn-sm btn-primary" target="_blank">🧾</a>
          </td>
        </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="7" class="text-center">គ្មានប្រវត្តិបញ្ជាទិញនៅឡើយទេ</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <a href="logout.php" class="btn btn-danger mt-3">🚪 ចាកចេញ</a>
</div>
</body>
</html>
