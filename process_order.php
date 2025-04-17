<?php
session_start();
$conn = new mysqli("localhost", "root", "", "kursov_proektbd");
$conn->set_charset("utf8");

if (!isset($_SESSION['user_id'])) {
    die("Грешка: Трябва да сте влезли в профила.");
}

$user_id = $_SESSION['user_id'];
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

if (empty($cart)) {
    die("Грешка: Количката е празна.");
}

// Данни от формата
$shipping_method = isset($_POST['shipping_method']) ? $_POST['shipping_method'] : '';
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
$shipping_address = '';

if ($shipping_method === 'store') {
    $shipping_method = isset($_POST['shipping_method']) ? $_POST['shipping_method'] : '';
} elseif ($shipping_method === 'pickup') {
$shipping_address = isset($_POST['pickup_location']) ? $_POST['pickup_location'] : '';
} elseif ($shipping_method === 'home') {
$shipping_address = isset($_POST['home_address']) ? $_POST['home_address'] : '';
}

// Изчисляване на сума
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}
if ($shipping_method === 'pickup' && $total < 100) {
    $total += 4.99;
} elseif ($shipping_method === 'home' && $total < 100) {
    $total += 6.99;
}

// 1. Вмъкване в orders
$stmt = $conn->prepare("INSERT INTO orders (user_id, order_date, total_price, status) VALUES (?, NOW(), ?, 'pending')");
$stmt->bind_param("id", $user_id, $total);
if (!$stmt->execute()) {
    die("Грешка при запис на поръчката.");
}
$order_id = $stmt->insert_id;
$stmt->close();

// 2. Вмъкване в shipping
$stmt = $conn->prepare("INSERT INTO shipping (order_id, shipping_address, shipping_status, tracking_number) VALUES (?, ?, 'processing', '')");
$stmt->bind_param("is", $order_id, $shipping_address);
$stmt->execute();
$stmt->close();

// 3. Получаване на payment_method_id
$stmt = $conn->prepare("SELECT payment_method_id FROM payment_method WHERE TRIM(LOWER(name)) = TRIM(LOWER(?))");
$stmt->bind_param("s", $payment_method);
$stmt->execute();
$result = $stmt->get_result();
$method_row = $result->fetch_assoc();
echo "Получен метод на плащане: [" . $payment_method . "]";
$payment_method_id = isset($method_row['payment_method_id']) ? $method_row['payment_method_id'] : null;
$stmt->close();

if (!$payment_method_id) {
    die("Невалиден метод на плащане.");
}

// 4. Вмъкване в payments
$stmt = $conn->prepare("INSERT INTO payments (order_id, payment_method_id, payment_status) VALUES (?, ?, 'unpaid')");
$stmt->bind_param("ii", $order_id, $payment_method_id);
$stmt->execute();
$stmt->close();

// 5. Вмъкване на всеки продукт в order_details
$stmt = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
foreach ($cart as $item) {
    $product_id = $item['id'];
    $quantity = $item['quantity'];
    $price = $item['price'];

    $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
    $stmt->execute();
}
$stmt->close();

// Изчистване на количката
unset($_SESSION['cart']);

echo "Поръчката беше успешно направена!";
?>
