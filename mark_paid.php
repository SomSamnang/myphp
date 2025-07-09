<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
  header("Location: login.php");
  exit;
}

if (isset($_GET['order_id'])) {
  $orderId = intval($_GET['order_id']);

  $stmt = $conn->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ?");
  $stmt->bind_param("i", $orderId);
  $stmt->execute();
}

header("Location: orders.php");
exit;
?>
