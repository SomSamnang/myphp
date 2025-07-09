<?php
include "db.php";

// ចាប់ព័ត៌មានពី ABA (postback/callback)
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['invoice']) || !isset($data['status'])) {
    http_response_code(400);
    echo "Invalid data";
    exit;
}

$order_id = intval($data['invoice']);
$status = strtolower($data['status']); // should be 'paid'

// បើបង់ប្រាក់បានជោគជ័យ
if ($status === 'paid') {
    $stmt = $conn->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Order #$order_id marked as paid.";
    } else {
        echo "Order #$order_id not updated or already paid.";
    }
} else {
    echo "Payment status: $status not supported.";
}
