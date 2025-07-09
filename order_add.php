<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"])) {
  header("Location: login.php");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cake_id = intval($_POST['cake_id']);
    $quantity = intval($_POST['quantity']);
    $order_date = date('Y-m-d'); // ថ្ងៃបញ្ជាទិញបច្ចុប្បន្ន

    // បញ្ចូល order ទៅ database
    $stmt = $conn->prepare("INSERT INTO orders (cake_id, quantity, order_date) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $cake_id, $quantity, $order_date);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $order_id = $stmt->insert_id;
        header("Location: invoice.php?order_id=$order_id");
        exit;
    } else {
        $error = "មានបញ្ហា ពេលបញ្ចូលការបញ្ជាទិញ";
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
  <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Angkor&family=Battambang:wght@100;300;400;700;900&family=Koulen&display=swap" rel="stylesheet">
  <style>
    body, html {
      
      font-family: 'Battambang', Arial, sans-serif;
      background: #f8f9fa;
    }
    .form-container {
      max-width: 500px;
      margin: auto;
      padding: 40px 20px;
      background: white;
      border-radius: 10px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      display: flex;
      flex-direction: column;
      justify-content: center;
      height: 100%;
    }
    h1 {
      text-align: center;
      margin-bottom: 30px;
      font-weight: 700;
      color: #333;
      font-family: "Angkor", serif;
  font-weight: 400;
  font-style: normal;
    }
    .btn-primary {
      width: 100%;
      font-weight: 600;
      padding: 10px;
      font-size: 18px;
      font-family: 'Battambang', Arial, sans-serif;
    }
    .btn-secondary {
      width: 100%;
      margin-top: 10px;
      font-weight: 600;
      padding: 10px;
      font-size: 18px;
      font-family: 'Battambang', Arial, sans-serif;
    }
  </style>
</head>
<body>

  <div class="form-container shadow-sm">
    <h1>បង្កើតការបញ្ជាទិញថ្មី</h1>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-4">
        <label for="cake_id" class="form-label fw-semibold">ជ្រើសរើសនំ</label>
        <select name="cake_id" id="cake_id" class="form-select" required>
          <option value="">-- ជ្រើសរើស --</option>
          <?php while ($cake = $cakes->fetch_assoc()): ?>
            <option value="<?= $cake['id'] ?>">
              <?= htmlspecialchars($cake['name']) ?> - $<?= number_format($cake['price'], 2) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="mb-4">
        <label for="quantity" class="form-label fw-semibold">បរិមាណ</label>
        <input type="number" name="quantity" id="quantity" min="1" value="1" class="form-control" required>
      </div>

      <button type="submit" class="btn btn-primary">បញ្ជាទិញ</button>
      <a href="dashboard.php" class="btn btn-secondary">ត្រឡប់ទៅ Dashboard</a>
    </form>
  </div>

</body>
</html>
