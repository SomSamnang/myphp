<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
  header("Location: login.php");
  exit;
}

$fromDate = isset($_GET["from_date"]) ? $_GET["from_date"] : date('Y-m-01');
$toDate = isset($_GET["to_date"]) ? $_GET["to_date"] : date('Y-m-d');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=statistics_' . $fromDate . '_to_' . $toDate . '.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['From Date', 'To Date', $fromDate, $toDate]);

// Get total orders
$stmtOrders = $conn->prepare("SELECT COUNT(*) AS total FROM orders WHERE order_date BETWEEN ? AND ?");
$stmtOrders->bind_param("ss", $fromDate, $toDate);
$stmtOrders->execute();
$totalOrders = $stmtOrders->get_result()->fetch_assoc()["total"];

// Get total income
$stmtIncome = $conn->prepare("
  SELECT SUM(o.quantity * c.price) AS income
  FROM orders o
  JOIN cakes c ON o.cake_id = c.id
  WHERE o.order_date BETWEEN ? AND ?
");
$stmtIncome->bind_param("ss", $fromDate, $toDate);
$stmtIncome->execute();
$totalIncome = $stmtIncome->get_result()->fetch_assoc()["income"] ?? 0;

// Get best selling cake
$stmtBestCake = $conn->prepare("
  SELECT c.name, SUM(o.quantity) AS total_quantity
  FROM orders o
  JOIN cakes c ON o.cake_id = c.id
  WHERE o.order_date BETWEEN ? AND ?
  GROUP BY o.cake_id
  ORDER BY total_quantity DESC
  LIMIT 1
");
$stmtBestCake->bind_param("ss", $fromDate, $toDate);
$stmtBestCake->execute();
$bestCakeRow = $stmtBestCake->get_result()->fetch_assoc();
$bestCakeName = $bestCakeRow ? $bestCakeRow["name"] : "None";
$bestCakeQty = $bestCakeRow ? $bestCakeRow["total_quantity"] : 0;

fputcsv($output, ['Total Orders', $totalOrders]);
fputcsv($output, ['Total Income', number_format($totalIncome, 2)]);
fputcsv($output, ['Best Selling Cake', $bestCakeName]);
fputcsv($output, ['Quantity Sold', $bestCakeQty]);

fclose($output);
exit;
?>
