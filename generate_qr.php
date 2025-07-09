<?php
// Set this to production values when you get real access
$merchant_id = "YOUR_MERCHANT_ID";
$terminal_id = "YOUR_TERMINAL_ID";
$api_key     = "YOUR_API_KEY";

// Get amount from URL or database
$order_id = $_GET["order_id"];
$amount   = $_GET["amount"]; // e.g. 5.00

$data = [
  "merchant_id" => $merchant_id,
  "terminal_id" => $terminal_id,
  "order_id"    => $order_id,
  "amount"      => $amount,
  "currency"    => "USD",
  "description" => "Cake Order #$order_id",
  "timeout"     => 300,
  "locale"      => "en",
  "callback_url"=> "https://yourdomain.com/payment_callback.php"
];

$ch = curl_init("https://payway.ababank.com/api/payment");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Content-Type: application/json",
  "Authorization: Bearer $api_key"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if (isset($result["qr_code"])) {
    echo "<h4>សូមស្កេន ABA Pay</h4>";
    echo "<img src='" . $result["qr_code"] . "' width='260'>";
    echo "<p><a href='" . $result["payment_url"] . "' target='_blank'>បើកក្នុង ABA App</a></p>";
} else {
    echo "❌ Error: " . htmlspecialchars($result["message"] ?? "Something went wrong.");
}
