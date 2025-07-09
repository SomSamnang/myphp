<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
  header("Location: login.php");
  exit;
}

if (isset($_GET["id"])) {
    $id = intval($_GET["id"]);

    // Delete order by ID
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: orders.php?deleted=1");
        exit;
    } else {
        echo "កំហុសពេលលុប: " . $conn->error;
    }
} else {
    echo "មិនមានលេខកូដ ID ត្រូវបានផ្តល់!";
}
?>
