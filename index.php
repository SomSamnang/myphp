<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
  header("Location: login.php");
  exit;
}

$fromDate = isset($_GET["from_date"]) ? $_GET["from_date"] : date('Y-m-01');
$toDate = isset($_GET["to_date"]) ? $_GET["to_date"] : date('Y-m-d');

// --- Statistics Queries (orders count, income, best cake) ---

$whereClause = "o.order_date BETWEEN ? AND ?";

$stmtOrders = $conn->prepare("SELECT COUNT(*) AS total FROM orders o WHERE $whereClause");
if (!$stmtOrders) {
    die("Prepare failed (orders): (" . $conn->errno . ") " . $conn->error);
}
$stmtOrders->bind_param("ss", $fromDate, $toDate);
$stmtOrders->execute();
$totalOrders = $stmtOrders->get_result()->fetch_assoc()["total"];

$stmtIncome = $conn->prepare("
  SELECT SUM(o.quantity * c.price) AS income
  FROM orders o
  JOIN cakes c ON o.cake_id = c.id
  WHERE $whereClause
");
if (!$stmtIncome) {
    die("Prepare failed (income): (" . $conn->errno . ") " . $conn->error);
}
$stmtIncome->bind_param("ss", $fromDate, $toDate);
$stmtIncome->execute();
$totalIncome = $stmtIncome->get_result()->fetch_assoc()["income"];

$stmtBestCake = $conn->prepare("
  SELECT c.name, SUM(o.quantity) AS total_quantity
  FROM orders o
  JOIN cakes c ON o.cake_id = c.id
  WHERE $whereClause
  GROUP BY o.cake_id
  ORDER BY total_quantity DESC
  LIMIT 1
");
if (!$stmtBestCake) {
    die("Prepare failed (best cake): (" . $conn->errno . ") " . $conn->error);
}
$stmtBestCake->bind_param("ss", $fromDate, $toDate);
$stmtBestCake->execute();
$bestCakeRow = $stmtBestCake->get_result()->fetch_assoc();
$bestCakeName = $bestCakeRow ? $bestCakeRow["name"] : "គ្មានទិន្នន័យ";
$bestCakeQty = $bestCakeRow ? $bestCakeRow["total_quantity"] : 0;

// --- Handle Search for Cakes ---

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {
    $stmtCakes = $conn->prepare("SELECT * FROM cakes WHERE name LIKE CONCAT('%', ?, '%')");
    if (!$stmtCakes) {
        die("Prepare failed (search cakes): (" . $conn->errno . ") " . $conn->error);
    }
    $stmtCakes->bind_param("s", $search);
    $stmtCakes->execute();
    $cakes = $stmtCakes->get_result();
} else {
    $cakes = $conn->query("SELECT * FROM cakes");
    if (!$cakes) {
        die("Query cakes failed: (" . $conn->errno . ") " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Angkor&family=Battambang:wght@100;300;400;700;900&family=Koulen&display=swap" rel="stylesheet">
  <style>
    body, html {
      font-family: 'Battambang', Arial, sans-serif;
      background: #f8f9fa;
    }
    .container {
      max-width: 1200px;
    }
    h1, h2 {
      font-weight: bold;
      color: #333;
      font-family: 'Angkor', serif;
      font-weight: 400;
      font-style: normal; 
      text-align: center;
    }
    h3 {
      font-weight: bold;
      color: #333;
      font-family: 'Angkor', serif;
      font-weight: 400;
      font-style: normal; 
    }
    .card {
      margin-bottom: 20px;
    }
  </style>
</head>
<body class="container py-4">
  <h1 class="mb-4">ប្រព័ន្ធគ្រប់គ្រងនិងការលក់នំខេក</h1>
  <h3 class="mb-4">Dashboard (Admin)</h3>

  <!-- Date filter form -->
  <form method="GET" class="row g-3 mb-4">
    <div class="col-auto">
      <label for="from_date" class="form-label">ចាប់ពីថ្ងៃ:</label>
      <input type="date" id="from_date" name="from_date" class="form-control" value="<?= htmlspecialchars($fromDate) ?>" required>
    </div>
    <div class="col-auto">
      <label for="to_date" class="form-label">ដល់ថ្ងៃ:</label>
      <input type="date" id="to_date" name="to_date" class="form-control" value="<?= htmlspecialchars($toDate) ?>" required>
    </div>
    <div class="col-auto align-self-end">
      <button type="submit" class="btn btn-primary">បង្ហាញ</button>
    </div>
  </form>

  <!-- Statistics cards -->
  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card text-bg-primary p-3">
        <h5>ចំនួនការបញ្ជាទិញសរុប</h5>
        <p class="fs-2"><?= $totalOrders ?></p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-bg-success p-3">
        <h5>ចំណូលសរុប</h5>
        <p class="fs-2">$<?= number_format($totalIncome ?? 0, 2) ?></p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-bg-warning p-3">
        <h5>នំដែលលក់ច្រើនបំផុត</h5>
        <p class="fs-5"><?= htmlspecialchars($bestCakeName) ?></p>
        <small><?= $bestCakeQty ?> កំណត់</small>
      </div>
    </div>
  </div>

  <!-- Action buttons -->
  <div class="mb-3">
    <a href="add.php" class="btn btn-primary">➕ បន្ថែមនំថ្មី</a>
    <form action="order_add.php" method="GET" class="d-inline">
      <button type="submit" class="btn btn-primary">បញ្ជាទិញ</button>
    </form>
    
    <a href="orders.php" class="btn btn-info text-white">📦 មើលការបញ្ជាទិញ</a>
    <a href="logout.php" class="btn btn-danger">ចាកចេញ</a>
    <a href="export_statistics.php" class="btn btn-success float-end">📥 Export Statistics CSV</a>
  </div>

  <!-- Search form -->
  <form method="GET" class="mb-3">
    <!-- Keep date filters in the search form to persist them -->
    <input type="hidden" name="from_date" value="<?= htmlspecialchars($fromDate) ?>">
    <input type="hidden" name="to_date" value="<?= htmlspecialchars($toDate) ?>">

    <div class="input-group">
      <input type="text" name="search" class="form-control" placeholder="ស្វែងរកនំ..." value="<?= htmlspecialchars($search) ?>">
      <button type="submit" class="btn btn-outline-primary">🔍 ស្វែងរក</button>
    </div>
  </form>

  <!-- Cakes Table -->
  <h2>បញ្ជីនំ</h2>
  <table class="table table-bordered table-striped align-middle">
    <thead>
      <tr>
        <th>ID</th>
        <th>រូបភាព</th>
        <th>ឈ្មោះនំ</th>
        <th>តម្លៃ</th>
        <th>សេចក្ដីពិពណ៌នា</th>
        <th>ថ្ងៃបង្កើត</th> <!-- Created Date -->
        <th>សកម្មភាព</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($cake = $cakes->fetch_assoc()): ?>
      <tr>
        <td><?= $cake['id'] ?></td>
        <td><img src="images/<?= htmlspecialchars($cake['image']) ?>" width="80" class="img-thumbnail"></td>
        <td><?= htmlspecialchars($cake['name']) ?></td>
        <td>$<?= number_format($cake['price'], 2) ?></td>
        <td><?= nl2br(htmlspecialchars($cake['description'])) ?></td>
        <td><?= htmlspecialchars($cake['created_date']) ?></td>
        <td>
          <a href="edit.php?id=<?= $cake['id'] ?>" class="btn btn-sm btn-warning">✏️ កែ</a>
          <a href="delete.php?id=<?= $cake['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('តើអ្នកចង់លុបមែន?')">🗑️ លុប</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

</body>
</html>
