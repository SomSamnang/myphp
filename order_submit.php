<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_date = date('Y-m-d H:i:s'); // current datetime

    $cake_ids = $_POST['cake_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];

    $order_ids = [];
    $errors = [];

    for ($i = 0; $i < count($cake_ids); $i++) {
        $cake_id = intval($cake_ids[$i]);
        $quantity = intval($quantities[$i]);

        if ($cake_id > 0 && $quantity > 0) {
            $stmt = $conn->prepare("INSERT INTO orders (cake_id, quantity, order_date) VALUES (?, ?, ?)");
            if ($stmt === false) {
                $errors[] = "Failed to prepare statement: " . $conn->error;
                continue;
            }
            $stmt->bind_param("iis", $cake_id, $quantity, $order_date);

            if ($stmt->execute()) {
                $order_ids[] = $stmt->insert_id;
            } else {
                $errors[] = "Failed to insert order for cake ID $cake_id: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $errors[] = "Invalid cake ID or quantity.";
        }
    }

    if (!empty($order_ids)) {
        $_SESSION['success'] = "ការបញ្ជាទិញបានរក្សាទុកជោគជ័យ!";
        $_SESSION['order_ids'] = $order_ids;

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
        }

        header("Location: order_form.php");
        exit;
    } else {
        $_SESSION['errors'] = $errors;
        header("Location: order_form.php");
        exit;
    }
} else {
    header("Location: order_form.php");
    exit;
}
