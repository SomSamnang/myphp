<?php
include "db.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST["username"];
  $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
  $role = $_POST["role"];

  $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
  $stmt->bind_param("sss", $username, $password, $role);
  $stmt->execute();
  header("Location: login.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="km">
<head>
  <meta charset="UTF-8" />
  <title>បង្កើតគណនី</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@300;400;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Battambang', sans-serif;
      background-color: #f8f9fa;
    }

    .register-box {
      max-width: 400px;
      margin: 60px auto;
      padding: 30px;
      background-color: white;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }

    h2 {
  
      color: blue;
      font-family: 'Angkor', serif;
      font-style: normal;
      text-align: center;
    }

    .form-control, .form-select {
      font-size: 16px;
    }
  </style>
</head>
<body>

  <div class="register-box">
    <h2>បង្កើតគណនី</h2>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">ឈ្មោះអ្នកប្រើ:</label>
        <input name="username" type="text" class="form-control" placeholder="បញ្ចូលឈ្មោះអ្នកប្រើ" required>
      </div>

      <div class="mb-3">
        <label class="form-label">ពាក្យសម្ងាត់:</label>
        <input name="password" type="password" class="form-control" placeholder="បញ្ចូលពាក្យសម្ងាត់" required>
      </div>

      <div class="mb-3">
        <label class="form-label">តួនាទី</label>
        <select name="role" class="form-select">
          <option value="user">អ្នកប្រើ</option>
          <option value="admin">អ្នកគ្រប់គ្រង</option>
        </select>
      </div>

      <button type="submit" class="btn btn-success w-100">ចុះឈ្មោះ</button>
      <a href="login.php" class="btn btn-secondary w-100 mt-2">ចូលវិញ</a>
    </form>
  </div>

</body>
</html>
