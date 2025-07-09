<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"])) {
  header("Location: login.php");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_date = date('Y-m-d');
    $cake_ids = $_POST['cake_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];

    $order_ids = [];
    $errors = [];

    for ($i = 0; $i < count($cake_ids); $i++) {
        $cake_id = intval($cake_ids[$i]);
        $quantity = intval($quantities[$i]);

        if ($cake_id > 0 && $quantity > 0) {
            $stmt = $conn->prepare("INSERT INTO orders (cake_id, quantity, order_date) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $cake_id, $quantity, $order_date);
            if ($stmt->execute()) {
                $order_ids[] = $stmt->insert_id;
            } else {
                $errors[] = "បញ្ហាក្នុងការបញ្ជាទិញនំ id $cake_id";
            }
            $stmt->close();
        }
    }

    if (!empty($order_ids)) {
        // Redirect ទៅវិក័យប័ត្រជាមួយ order_ids ជា string separated by comma
        header("Location: invoice.php?order_ids=" . implode(',', $order_ids));
        exit;
    } else {
        $error = "ការបញ្ជាទិញមិនបានជោគជ័យ។ សូមព្យាយាមម្តងទៀត។";
    }
}

// ទាញនំសម្រាប់ select list
$cakes = $conn->query("SELECT * FROM cakes");
?>

<!DOCTYPE html>
<html lang="km">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>បន្ថែមការបញ្ជាទិញ</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@300;400;700&display=swap" rel="stylesheet">
  <style>
    body, html {
      font-family: 'Battambang', Arial, sans-serif;
      background: #f8f9fa;
      height: 100vh;
    }
    .form-container {
      max-width: 600px;
      margin: 50px auto;
      padding: 30px;
      background: white;
      border-radius: 10px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    h1 {
      text-align: center;
      margin-bottom: 30px;
      font-weight: 700;
      color: #333;
    }
    .btn-primary {
      width: 100%;
      font-weight: 600;
      padding: 10px;
      font-size: 18px;
    }
    .btn-secondary {
      width: 100%;
      margin-top: 10px;
      font-weight: 600;
      padding: 10px;
      font-size: 18px;
    }
    .order-row {
      display: flex;
      gap: 10px;
      margin-bottom: 15px;
    }
    .order-row select, .order-row input {
      flex: 1;
    }
  </style>
</head>
<body>

<div class="form-container shadow-sm">
  <h1>បង្កើតការបញ្ជាទិញថ្មី (3 នំ)</h1>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <?php for ($i = 0; $i < 3; $i++): ?>
    <div class="order-row">
      <select name="cake_id[]" class="form-select" required>
        <option value="">-- ជ្រើសរើស នំ --</option>
        <?php
          $cakes->data_seek(0);
          while ($cake = $cakes->fetch_assoc()):
        ?>
          <option value="<?= $cake['id'] ?>"><?= htmlspecialchars($cake['name']) ?> - $<?= number_format($cake['price'], 2) ?></option>
        <?php endwhile; ?>
      </select>
      <input type="number" name="quantity[]" min="1" value="1" class="form-control" required placeholder="បរិមាណ">
    </div>
    <?php endfor; ?>

    <button type="submit" class="btn btn-primary mt-3">បញ្ជាទិញ</button>
    <a href="dashboard.php" class="btn btn-secondary mt-2">ត្រឡប់ទៅ Dashboard</a>
  </form>
</div>

</body>
</html>
