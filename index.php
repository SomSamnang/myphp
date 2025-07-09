<?php
session_start();
if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="km">
<head>
  <meta charset="UTF-8">
  <title>សូមស្វាគមន៍</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Battambang&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Battambang', sans-serif;
      background: linear-gradient(to right, #ffecd2, #fcb69f);
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .welcome-box {
      background-color: white;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 0 20px rgba(0,0,0,0.15);
      text-align: center;
      max-width: 500px;
      width: 100%;
    }

    h1 {
      font-size: 30px;
      margin-bottom: 20px;
      color: #333;
    }

    .btn-custom {
      font-size: 18px;
      margin: 10px;
      padding: 10px 25px;
      border-radius: 8px;
    }

    .btn-login {
      background-color: #007bff;
      color: white;
    }

    .btn-login:hover {
      background-color: #0056b3;
    }

    .btn-register {
      background-color: #28a745;
      color: white;
    }

    .btn-register:hover {
      background-color: #1e7e34;
    }
  </style>
</head>
<body>

  <div class="welcome-box">
    <h1>🎂 សូមស្វាគមន៍មកកាន់<br>ប្រព័ន្ធគ្រប់គ្រងនិងលក់នំខេក</h1>
    <p class="mb-4">សូមជ្រើសរើសសកម្មភាពខាងក្រោម:</p>
    <a href="login.php" class="btn btn-login btn-custom">ចូលប្រើ</a>
    <a href="register.php" class="btn btn-register btn-custom">បង្កើតគណនីថ្មី</a>
  </div>

</body>
</html>
