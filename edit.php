<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "admin") {
  header("Location: login.php");
  exit;
}

if (!isset($_GET["id"])) {
  echo "Cake ID is missing!";
  exit;
}

$id = intval($_GET["id"]); // sanitize id to integer
$cake = $conn->query("SELECT * FROM cakes WHERE id=$id")->fetch_assoc();

if (!$cake) {
  echo "Cake not found!";
  exit;
}

$success = false;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $_POST["name"];
  $price = $_POST["price"];
  $quantity = $_POST["quantity"];
  $description = $_POST["description"];
  $updated_at = date("Y-m-d H:i:s");
  $time_at = date("Y-m-d H:i:s"); // usually this is for created time, so only set once on insert

  if ($_FILES["image"]["name"]) {
    $image = $_FILES["image"]["name"];
    if (!move_uploaded_file($_FILES["image"]["tmp_name"], "images/" . $image)) {
      die("Failed to upload image.");
    }
    // Update all fields including image and updated_at
    $stmt = $conn->prepare("UPDATE cakes SET name=?, price=?, quantity=?, description=?, image=?, updated_at=? WHERE id=?");
    $stmt->bind_param("sdisssi", $name, $price, $quantity, $description, $image, $updated_at, $id);
  } else {
    // Update all fields except image
    $stmt = $conn->prepare("UPDATE cakes SET name=?, price=?, quantity=?, description=?, updated_at=? WHERE id=?");
    $stmt->bind_param("sdissi", $name, $price, $quantity, $description, $updated_at, $id);
  }

  if ($stmt->execute()) {
    $stmt->close();
    header("Location: dashboard.php");
    exit;
  } else {
    die("Update failed: " . $conn->error);
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>កែប្រែនំ</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Angkor&family=Battambang:wght@100;300;400;700;900&family=Koulen&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    body {
      font-family: 'Battambang', Arial, sans-serif;
      background-color: #f8f9fa;
    }
    h2 {
      font-family: 'Angkor', serif;
    }
  </style>
</head>
<body class="container py-4">
  <h2 class="mb-4">កែប្រែនំ</h2>

  <?php if ($success): ?>
    <div class="alert alert-success">✅ ការកែប្រែបានជោគជ័យ!</div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">
    <div class="mb-3">
      <label class="form-label">ឈ្មោះនំ</label>
      <input name="name" value="<?= htmlspecialchars($cake['name']) ?>" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">តម្លៃ ($)</label>
      <input name="price" type="number" step="0.01" value="<?= htmlspecialchars($cake['price']) ?>" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">បរិមាណ</label>
      <input name="quantity" type="number" min="0" value="<?= htmlspecialchars($cake['quantity']) ?>" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">ពិពណ៌នា</label>
      <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($cake['description']) ?></textarea>
    </div>

    <?php if (!empty($cake['time_at'])): ?>
      <div class="mb-3">
        <label class="form-label">📅 ថ្ងៃបញ្ចូល</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars(date("d-F-Y H:i:s", strtotime($cake['time_at']))) ?>" readonly>
      </div>
    <?php endif; ?>

    <?php if (!empty($cake['updated_at'])): ?>
      <div class="mb-3">
        <label class="form-label">🕒 កែប្រែចុងក្រោយ</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars(date("d-F-Y H:i:s", strtotime($cake['updated_at']))) ?>" readonly>
      </div>
    <?php endif; ?>

    <div class="mb-3">
      <label class="form-label">រូបភាពបច្ចុប្បន្ន</label><br>
      <img src="images/<?= htmlspecialchars($cake['image']) ?>" width="120" class="img-thumbnail" alt="Cake Image">
    </div>

    <div class="mb-3">
      <label class="form-label">ប្ដូររូបភាពថ្មី (បើចង់)</label>
      <input name="image" type="file" class="form-control" accept="image/*">
    </div>

    <button type="submit" class="btn btn-primary mt-3">💾 កែប្រែ</button>
    <a href="dashboard.php" class="btn btn-secondary mt-2">↩ ត្រលប់</a>
  </form>
</body>
</html>
