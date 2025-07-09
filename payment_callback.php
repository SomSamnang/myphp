<?php
include "db.php";

// ABA sends POST payload with payment data
$data = json_decode(file_get_contents("php://input"), true);

$order_id = $data["order_id"];
$status   = $data["status"];

// ABA returns status = "success" or other
if ($status == "success") {
  $stmt = $conn->prepare("UPDATE orders SET payment_status='paid' WHERE id=?");
  $stmt->bind_param("s", $order_id);
  $stmt->execute();
}

// Always return 200 OK
http_response_code(200);
echo "OK";
