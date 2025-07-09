<?php
session_start();
include "db.php";

if ($_SESSION["role"] != "admin") {
  header("Location: login.php");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $_POST["name"];
  $price = $_POST["price"];
  $description = $_POST["description"];
  $quantity = isset($_POST["quantity"]) ? (int)$_POST["quantity"] : 0;
  $image = $_FILES["image"]["name"];

  // Upload image
  $targetDir = "images/";
  $targetFile = $targetDir . basename($image);
  move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);

  // Insert with quantity
  $stmt = $conn->prepare("INSERT INTO cakes (name, price, description, image, quantity, created_date) VALUES (?, ?, ?, ?, ?, CURDATE())");
  $stmt->bind_param("sdssi", $name, $price, $description, $image, $quantity);
  $stmt->execute();

  header("Location: index.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
  <meta charset="UTF-8">
  <title>បន្ថែមនំថ្មី</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    body {
      font-family: 'Battambang', Arial, sans-serif;
      padding: 30px;
      background-color: #f8f9fa;
      width:500px ;
    }
    h2 {
      font-family: 'Angkor', serif;
    }
    .container{

      max-width: 600px;
      margin-top: 50px;
      padding: 20px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);


    }
  </style>
</head>
<body class="container">
  <h2 class="mb-4">បន្ថែមនំថ្មី</h2>

  <form action="" method="post" enctype="multipart/form-data" class="row g-4">
    <div class="col-md-6">
      <label class="form-label">ឈ្មោះនំ:</label>
      <input type="text" name="name" class="form-control" required>
    </div>
    <div class="col-md-6">        
      <label class="form-label">តម្លៃ:</label>
      <input type="number" step="0.01" name="price" class="form-control" required>
    </div>
    <div class="col-md-12">
      <label class="form-label">បរិមាណ:</label>
      <input type="number" name="quantity" class="form-control" value="0" min="0" required>
    </div>
    <div class="col-md-12">
      <label class="form-label">សេចក្ដីពិពណ៌នា:</label>
      <textarea name="description" class="form-control" rows="3" required></textarea>
    </div>
    <div class="col-md-12">
      <label class="form-label">រូបភាព:</label>
      <input type="file" name="image" class="form-control" required>
    </div>
    <div class="col-md-12">
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> រក្សាទុក</button>
      <a href="index.php" class="btn btn-secondary">ត្រឡប់</a>
    </div>
  </form>
</body>
</html>
