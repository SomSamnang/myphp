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

$whereClause = "DATE(o.order_date) BETWEEN ? AND ?";

$stmtOrders = $conn->prepare("SELECT COUNT(*) AS total FROM orders o WHERE $whereClause");
$stmtOrders->bind_param("ss", $fromDate, $toDate);
$stmtOrders->execute();
$totalOrders = $stmtOrders->get_result()->fetch_assoc()["total"] ?? 0;

$stmtIncome = $conn->prepare("
  SELECT SUM(o.quantity * c.price) AS income
  FROM orders o
  JOIN cakes c ON o.cake_id = c.id
  WHERE $whereClause
");
$stmtIncome->bind_param("ss", $fromDate, $toDate);
$stmtIncome->execute();
$totalIncome = $stmtIncome->get_result()->fetch_assoc()["income"] ?? 0;

$stmtBestCake = $conn->prepare("
  SELECT c.name, SUM(o.quantity) AS total_quantity
  FROM orders o
  JOIN cakes c ON o.cake_id = c.id
  WHERE $whereClause
  GROUP BY o.cake_id
  ORDER BY total_quantity DESC
  LIMIT 1
");
$stmtBestCake->bind_param("ss", $fromDate, $toDate);
$stmtBestCake->execute();
$bestCakeRow = $stmtBestCake->get_result()->fetch_assoc();
$bestCakeName = $bestCakeRow["name"] ?? "គ្មានទិន្នន័យ";
$bestCakeQty = $bestCakeRow["total_quantity"] ?? 0;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {
    $stmtCakes = $conn->prepare("SELECT * FROM cakes WHERE name LIKE CONCAT('%', ?, '%')");
    $stmtCakes->bind_param("s", $search);
    $stmtCakes->execute();
    $cakes = $stmtCakes->get_result();
} else {
    $cakes = $conn->query("SELECT * FROM cakes");
}

$stmtPaid = $conn->prepare("
  SELECT COUNT(*) AS total_paid FROM orders o
  WHERE o.payment_status = 'paid' AND DATE(o.order_date) BETWEEN ? AND ?
");
$stmtPaid->bind_param("ss", $fromDate, $toDate);
$stmtPaid->execute();
$totalPaid = $stmtPaid->get_result()->fetch_assoc()["total_paid"] ?? 0;

$stmtPaidAmount = $conn->prepare("
  SELECT SUM(o.quantity * c.price) AS total_paid_amount
  FROM orders o
  JOIN cakes c ON o.cake_id = c.id
  WHERE o.payment_status = 'paid' AND DATE(o.order_date) BETWEEN ? AND ?
");
$stmtPaidAmount->bind_param("ss", $fromDate, $toDate);
$stmtPaidAmount->execute();
$totalPaidAmount = $stmtPaidAmount->get_result()->fetch_assoc()["total_paid_amount"] ?? 0;

// fetch all orders for chart
$stmtChart = $conn->prepare("
  SELECT DATE(order_date) as date, COUNT(*) as orders
  FROM orders
  WHERE DATE(order_date) BETWEEN ? AND ?
  GROUP BY DATE(order_date)
  ORDER BY date ASC
");
$stmtChart->bind_param("ss", $fromDate, $toDate);
$stmtChart->execute();
$resultChart = $stmtChart->get_result();
$chartData = [];
while ($row = $resultChart->fetch_assoc()) {
    $chartData[] = [
        "date" => $row["date"],
        "orders" => (int)$row["orders"]
    ];
}

// Pass chart data to frontend as JSON
$chartDataJson = json_encode($chartData);

date_default_timezone_set("Asia/Phnom_Penh");
$dateNow = date("d-F-Y");
$timeNow = date("g:i A");


$bgClass = "text-bg-warning"; // fallback background
$textColor = "text-white";     // fallback text color

$cakeName = strtolower($bestCakeName);

// Background color condition
if (strpos($cakeName, 'yellow') !== false) {
    $bgClass = "text-bg-primary"; // blue background only for yellow cake
    $textColor = "text-warning";
}

// Text color for the <p>
if (strpos($cakeName, 'red') !== false) {
    $textColor = "text-danger";
} elseif (strpos($cakeName, 'green') !== false) {
    $textColor = "text-success";
} elseif (strpos($cakeName, 'blue') !== false) {
    $textColor = "text-primary";
} elseif (strpos($cakeName, 'black') !== false) {
    $textColor = "text-dark";
} elseif (strpos($cakeName, 'pink') !== false) {
    $textColor = "text-pink"; // custom class, optional
} elseif (strpos($cakeName, 'chocolate') !== false || strpos($cakeName, 'sopcola') !== false) {
    $textColor = "text-brown"; // You can define this in CSS
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
  <meta charset="UTF-8" />
  <title>Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Angkor&family=Battambang:wght@100;300;400;700;900&family=Koulen&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
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
      font-style: normal;
      text-align: center;
    }
    .txt {
     font-family: "Moul", serif;
      font-weight: 400;
      font-style: normal;
      text-align: center;
    }
    h3 {
      color: #333;
      font-weight: 400;
      font-style: normal;
    }
  .text-pink { color: #e83e8c; }
  .text-brown { color: #8B4513; }
    .card {
      margin-bottom: 20px;
    }
    .btn-primary:hover, .btn-success:hover, .btn-warning:hover, .btn-danger:hover, .btn-info:hover {
      color: #fff;
    }
    .thead-dark th tr {
      background-color: blue;
      color: white;
    }

    .dashboard-cards .card {
      border-radius: 0.75rem;
      box-shadow: 0 0.5rem 1rem rgb(0 0 0 / 0.15);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .dashboard-cards .card:hover {
      transform: translateY(-6px);
      box-shadow: 0 1rem 1.5rem rgb(0 0 0 / 0.25);
    }
    .dashboard-cards .card h5 {
      font-weight: 700;
      margin-bottom: 0.8rem;
      font-size: 1.25rem;
    }
    .dashboard-cards .card p.fs-2 {
      font-weight: 800;
      font-size: 2.5rem;
      margin: 0;
    }
    .dashboard-cards .card p.fs-5 {
      font-weight: 600;
      font-size: 1.1rem;
      margin-bottom: 0.3rem;
    }
    .dashboard-cards .card small {
      color: #f8f9facc;
      font-size: 0.875rem;
    }

    /* Customize background opacity for better text contrast */
    .card.text-bg-primary {
      background-color: rgba(13, 110, 253, 0.85);
    }
    .card.text-bg-success {
      background-color: rgba(25, 135, 84, 0.85);
    }
    .card.text-bg-warning {
      background-color: rgba(255, 193, 7, 0.85);
      color: rgb(255, 255, 255);
    }
    .card.text-bg-info {
      background-color: rgba(13, 202, 240, 0.85);
      color: rgb(255, 255, 255);
    }
    .card.text-bg-secondary {
      background-color: rgba(89, 255, 0, 0.85);
    }
    .dashboard-cards .col {
      display: flex;
    }

    .dashboard-cards .card {
      flex: 1 1 auto;
      height: 70%;
      width: 500px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 2rem 1.5rem;
    }
  </style>
</head>
<body class="container py-4">
 <h1 class="mb-3 text-primary">ប្រព័ន្ធគ្រប់គ្រងការលក់នំខេក</h1>
 <h1 class="mb-2 text-danger txt ">សំណាង នំខេក</h1>

 <h5 class="mb-3 fs-1 text-center text-success">ហាងយើងខ្ញុំមានលក់នំខេកគ្រប់ប្រភេទតាមតម្រូវការដែលលោកអ្នកចង់បាន</h5>
 <h5 class="mb-2 fs-3 text-center text-danger-emphasis">អាសយដ្ឋាន៖ ភូមិទ្រង់ភូមិ ឃុំព្រៃផ្គាំ ស្រុកអង្គរបូរី ខេត្តតាកែវ</h5>



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
    <!-- Show current date and time -->
 <div class="text-left text-muted mb-3">
   ថ្ងៃទី <?= htmlspecialchars($dateNow) ?> ម៉ោង <?= htmlspecialchars($timeNow) ?>
 </div>

 </form>

 <div class="row row-cols-1 row-cols-md-5 g-4 mb-4 dashboard-cards">
  <div class="col">
    <div class="card text-bg-primary text-center">
      <h5>ចំនួនការបញ្ជាទិញសរុប</h5>
      <p class="fs-5"><?php echo $totalOrders; ?></p>
    </div>
  </div>
  <div class="col">
    <div class="card text-bg-success text-center">
      <h5>ចំណូលសរុប</h5>
      <p class="fs-5">$<?php echo number_format($totalIncome ?? 0, 2); ?></p>
    </div>
  </div>
<div class="col">
  <div class="card <?= $bgClass ?> text-center">
    <h5 class="text-white">នំដែលលក់ច្រើនបំផុត</h5>
    <p class="fs-5 <?= $textColor ?>"><?= htmlspecialchars($bestCakeName); ?></p>
    <small class="fs-5"><?= $bestCakeQty; ?> កំណត់</small>
  </div>
</div>
  <div class="col">
    <div class="card text-bg-info text-light text-center">
      <h5>ចំនួនអ្នកបង់ប្រាក់តាម <br> ABA</h5>
      <p class="fs-5"><?php echo $totalPaid; ?>​​ នាក់</p>
    </div>
  </div>
  <div class="col">
    <div class="card bg-danger-subtle text-center">
      <h5>ចំនួនប្រាក់សរុបបង់តាម<br>ABA</h5>
      <p class="fs-5 text-success">$<?php echo number_format($totalPaidAmount ?? 0, 2); ?></p>
      <small class="fs-6 text-warning"><?php echo number_format($totalPaidAmount ?? 0, 2); ?> ដុល្លារ</small>
    </div>
  </div>
</div>


 <div class="mb-2">
   <a href="add.php" class="btn btn-primary"><i class="fa-solid fa-cart-plus"></i> បន្ថែមនំថ្មី</a>
   <form action="order_add.php" method="GET" class="d-inline">
     <button type="submit" class="btn btn-success">បញ្ជាទិញ</button>
   </form>
   <a href="orders.php" class="btn btn-warning">មើលការបញ្ជាទិញ</a>
   <a href="logout.php" class="btn btn-danger">ចាកចេញ</a>
   <a href="export_statistics.php" class="btn btn-success float-end"><i class="fa-solid fa-file-export"></i> Export Statistics CSV</a>
 </div>

 <form method="GET" class="mb-3">
   <input type="hidden" name="from_date" value="<?= htmlspecialchars($fromDate) ?>">
   <input type="hidden" name="to_date" value="<?= htmlspecialchars($toDate) ?>">
   <div class="input-group">
     <input type="text" name="search" class="form-control" placeholder="ស្វែងរកនំ..." value="<?= htmlspecialchars($search) ?>">
     <button type="submit" class="btn btn-outline-primary">🔍 ស្វែងរក</button>
   </div>
 </form>

 <h2 class="text-warning">តារាងបញ្ជីនំ</h2>
 <table class="table table-bordered table-striped align-middle">
  <thead class="text-white text-center bg-success">
    <tr>
      <th class="bg-primary text-light">ល.រ</th>
      <th class="bg-primary text-light">រូបភាព</th>
      <th class="bg-primary text-light">ឈ្មោះនំ</th>
      <th class="bg-primary text-light">តម្លៃ</th>
      <th class="bg-primary text-light">បរិមាណ</th>
      <th class="bg-primary text-light">សេចក្ដីពិពណ៌នា</th>
      <th class="bg-primary text-light">កាលបរិច្ឆេទ</th>
      <th class="bg-primary text-light">ពេលវេលាម៉ោង</th>
      <th class="bg-primary text-light">សកម្មភាព</th>
    </tr>
  </thead>
<tbody class="text-center">
<?php $i = 1; while ($cake = $cakes->fetch_assoc()): ?>
  <?php
    $createdDate = date("d-F-Y", strtotime($cake['created_time']));
    $createdTime = date("g:i A", strtotime($cake['created_time']));
  ?>
  <tr>
          <td><?= $i++ ?></td>
      <td><img src="images/<?= htmlspecialchars($cake['image']) ?>" width="80" class="img-thumbnail"></td>
      <td><?= htmlspecialchars($cake['name']) ?></td>
      <td>$<?= number_format($cake['price'], 2) ?></td>
      <td><?= $cake['quantity'] ?></td>
      <td><?= nl2br(htmlspecialchars($cake['description'])) ?></td>

    <td><?= $createdDate ?></td>
    <td><?= $createdTime ?></td>

 <td>
        <a href="edit.php?id=<?= $cake['id'] ?>" class="btn btn-sm btn-primary">
          <i class="fa-solid fa-pen-to-square"></i> កែ
        </a>
        <a href="delete.php?id=<?= $cake['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('តើអ្នកចង់លុបមែន?')">
          <i class="fa-solid fa-trash"></i> លុប
        </a>
      </td>
    </tr>
  <?php endwhile; ?>

</table>

</body>
</html>
