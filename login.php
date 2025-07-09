<?php
session_start();
include "db.php";

$error = '';
$username = '';
$password = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST["username"] ?? '';
  $password = $_POST["password"] ?? '';

  $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username=?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($user_id, $hashed_password, $role);

  if ($stmt->num_rows == 1) {
    $stmt->fetch();
    if (password_verify($password, $hashed_password)) {
      $_SESSION["user_id"] = $user_id;
      $_SESSION["role"] = $role;

      // 🔀 បញ្ជូនទៅតាម role
      if ($role === "admin") {
        header("Location: dashboard.php");
      } else {
        header("Location: user_dashboard.php");
      }
      exit;
    } else {
      $error = "❌ ពាក្យសម្ងាត់មិនត្រឹមត្រូវ!";
    }
  } else {
    $error = "❌ គណនីនេះមិនមានទេ!";
  }
}
?>
<!DOCTYPE html>
<html lang="km">
<head>
  <meta charset="UTF-8" />
  <title>ចូលគណនី</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@300;400;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Battambang', sans-serif;
      background-color: #f2f2f2;
    }
    .login-box {
      max-width: 400px;
      margin: 80px auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #2c3e50;
    }
  </style>
</head>
<body>

<div class="login-box">
  <h2>🔐 ចូលគណនី</h2>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label class="form-label">ឈ្មោះអ្នកប្រើ</label>
      <input name="username" type="text" class="form-control" value="<?= htmlspecialchars($username) ?>" placeholder="បញ្ចូលឈ្មោះអ្នកប្រើ" required>
    </div>

    <div class="mb-3">
      <label class="form-label">ពាក្យសម្ងាត់</label>
      <input name="password" type="password" class="form-control" placeholder="បញ្ចូលពាក្យសម្ងាត់" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">➡️ ចូល</button>
    <a href="register.php" class="btn btn-secondary w-100 mt-2">📋 ចុះឈ្មោះថ្មី</a>
  </form>
</div>

</body>
</html>
