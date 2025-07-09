<?php
session_start();
include "db.php";

// Get cakes for dropdown
$cakes = $conn->query("SELECT * FROM cakes");

// Get flash messages
$successMessage = $_SESSION['success'] ?? null;
$orderIds = $_SESSION['order_ids'] ?? null;
$errorMessages = $_SESSION['errors'] ?? null;

// Clear messages after displaying once
unset($_SESSION['success'], $_SESSION['order_ids'], $_SESSION['errors']);
?>

<!DOCTYPE html>
<html lang="km">
<head>
  <meta charset="UTF-8" />
  <title>បង្កើតការបញ្ជាទិញថ្មី</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@300;400;700&display=swap" rel="stylesheet">
  <style>
    body, html {
      font-family: 'Battambang', Arial, sans-serif;
      background: #f8f9fa;
      height: 100vh;
    }
    .form-container {
      max-width: 700px;
      margin: 50px auto;
      padding: 30px;
      background: white;
      border-radius: 10px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    h1 {
      text-align: center;
      margin-bottom: 30px;
      font-weight: 700;
      color: #333;
    }
    .btn-primary, .btn-secondary {
      font-weight: 600;
      padding: 10px;
      font-size: 18px;
    }
    #order-list .order-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #f1f1f1;
      padding: 8px 15px;
      margin-bottom: 10px;
      border-radius: 5px;
    }
  </style>
</head>
<body>

<div class="form-container shadow-sm">
  <h1>បង្កើតការបញ្ជាទិញថ្មី</h1>

  <?php if ($successMessage): ?>
    <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
    <p>
      ចុច <a href="invoice.php?order_ids=<?= implode(',', $orderIds) ?>">ទីនេះ</a> ដើម្បីមើលវិក័យប័ត្រ
    </p>
  <?php endif; ?>

  <?php if ($errorMessages && count($errorMessages) > 0): ?>
    <div class="alert alert-danger">
      <ul>
        <?php foreach ($errorMessages as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form id="order-form" method="POST" action="order_submit.php">
    <div class="mb-3 d-flex gap-2 align-items-center">
      <select id="cake-select" class="form-select" style="flex: 3;">
        <option value="">-- ជ្រើសរើស នំ --</option>
        <?php while ($cake = $cakes->fetch_assoc()): ?>
          <option value="<?= $cake['id'] ?>" data-price="<?= $cake['price'] ?>">
            <?= htmlspecialchars($cake['name']) ?> - $<?= number_format($cake['price'], 2) ?>
          </option>
        <?php endwhile; ?>
      </select>

      <input type="number" id="cake-quantity" class="form-control" style="flex: 1;" min="1" value="1" placeholder="បរិមាណ" />

      <button type="button" id="add-to-order" class="btn btn-success" style="flex: 1;">Add</button>
    </div>

    <div id="order-list" class="mb-3">
      <!-- List of added items will appear here -->
    </div>

    <!-- Hidden inputs for submitting -->
    <div id="hidden-inputs"></div>

    <button type="submit" class="btn btn-primary w-100">បញ្ជាទិញ</button>
    <a href="dashboard.php" class="btn btn-secondary w-100 mt-2">ត្រឡប់ទៅ Dashboard</a>
  </form>
</div>

<script>
  const addBtn = document.getElementById('add-to-order');
  const cakeSelect = document.getElementById('cake-select');
  const quantityInput = document.getElementById('cake-quantity');
  const orderList = document.getElementById('order-list');
  const hiddenInputs = document.getElementById('hidden-inputs');

  let addedCakes = {};

  addBtn.addEventListener('click', () => {
    const cakeId = cakeSelect.value;
    const cakeName = cakeSelect.options[cakeSelect.selectedIndex]?.text || '';
    const quantity = parseInt(quantityInput.value);

    if (!cakeId) {
      alert('សូមជ្រើសរើសនំមុនចុច Add!');
      return;
    }
    if (quantity < 1) {
      alert('បរិមាណត្រូវតែចាប់ពី 1ឡើងទៅ');
      return;
    }
    if (addedCakes[cakeId]) {
      alert('នំនេះបានបន្ថែមរួចហើយ។ សូមកែបរិមាណក្នុងបញ្ជី។');
      return;
    }

    // Create order item div
    const orderItem = document.createElement('div');
    orderItem.classList.add('order-item');
    orderItem.setAttribute('data-cake-id', cakeId);
    orderItem.innerHTML = `
      <div>${cakeName} x ${quantity}</div>
      <button type="button" class="btn btn-danger btn-sm remove-item">លុប</button>
    `;

    orderList.appendChild(orderItem);

    // Add hidden inputs for form submission
    const inputCakeId = document.createElement('input');
    inputCakeId.type = 'hidden';
    inputCakeId.name = 'cake_id[]';
    inputCakeId.value = cakeId;
    inputCakeId.id = 'input-cake-' + cakeId;

    const inputQuantity = document.createElement('input');
    inputQuantity.type = 'hidden';
    inputQuantity.name = 'quantity[]';
    inputQuantity.value = quantity;
    inputQuantity.id = 'input-quantity-' + cakeId;

    hiddenInputs.appendChild(inputCakeId);
    hiddenInputs.appendChild(inputQuantity);

    addedCakes[cakeId] = true;

    cakeSelect.value = '';
    quantityInput.value = 1;
  });

  orderList.addEventListener('click', (e) => {
    if (e.target.classList.contains('remove-item')) {
      const orderItem = e.target.closest('.order-item');
      const cakeId = orderItem.getAttribute('data-cake-id');

      orderItem.remove();

      const inputCakeId = document.getElementById('input-cake-' + cakeId);
      const inputQuantity = document.getElementById('input-quantity-' + cakeId);
      if (inputCakeId) inputCakeId.remove();
      if (inputQuantity) inputQuantity.remove();

      delete addedCakes[cakeId];
    }
  });
</script>

</body>
</html>
