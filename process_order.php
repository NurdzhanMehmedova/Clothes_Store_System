<?php
session_start();
$conn = new mysqli("localhost", "root", "", "kursov_proektbd");
$conn->set_charset("utf8");

// Проверка за вход
if (!isset($_SESSION['user_id'])) {
    die("Грешка: Трябва да сте влезли в профила.");
}

$user_id = $_SESSION['user_id'];
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();

if (empty($cart)) {
    die("Грешка: Количката е празна.");
}

$shipping_method = isset($_POST['shipping_method']) ? $_POST['shipping_method'] : '';
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
$shipping_address = '';

// Определяне на адрес според метода на доставка
switch ($shipping_method) {
    case 'store':
        $shipping_address = isset($_POST['store_location']) ? $_POST['store_location'] : '';
        break;
    case 'pickup':
        $shipping_address = isset($_POST['pickup_location']) ? $_POST['pickup_location'] : '';
        break;
    case 'home':
        $shipping_address = isset($_POST['home_address']) ? $_POST['home_address'] : '';
        break;
}

if (empty($shipping_address)) {
    die("Грешка: Липсва адрес за доставка.");
}

// Изчисляване на сума
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}
$shipping_fee = 0;
if ($total < 100) {
    if ($shipping_method === 'pickup') {
        $shipping_fee = 4.99;
    } elseif ($shipping_method === 'home') {
        $shipping_fee = 6.99;
    }
}
$total += $shipping_fee;

// Транзакция
$conn->begin_transaction();

function generateTrackingNumber() {
    $datePart = date("Ymd");
    $randomPart = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
    return "TRK-$datePart-$randomPart";
}

try {
    // 1. Вмъкване в orders
    $stmt = $conn->prepare("INSERT INTO orders (user_id, order_date, total_price, status) VALUES (?, NOW(), ?, 'pending')");
    $stmt->bind_param("id", $user_id, $total);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Генерираме tracking_number
$tracking_number = generateTrackingNumber();

// 2. Вмъкване в shipping
$stmt = $conn->prepare("INSERT INTO shipping (order_id, shipping_address, shipping_status, tracking_number) VALUES (?, ?, 'processing', ?)");
$stmt->bind_param("iss", $order_id, $shipping_address, $tracking_number);
$stmt->execute();
$stmt->close();

    // 3. Извличане на payment_method_id
    $stmt = $conn->prepare("SELECT payment_method_id FROM payment_method WHERE TRIM(LOWER(name)) = TRIM(LOWER(?))");
    $stmt->bind_param("s", $payment_method);
    $stmt->execute();
    $result = $stmt->get_result();
    $method_row = $result->fetch_assoc();
    $payment_method_id = isset($method_row['payment_method_id']) ? $method_row['payment_method_id'] : null;
    $stmt->close();

    if (!$payment_method_id) {
        throw new Exception("Невалиден метод на плащане.");
    }

    // 4. Вмъкване в payments
    if ($payment_method_id == 1) {
    $payment_status = 'Paid';
} elseif ($payment_method_id == 2) {
    $payment_status = 'OnDelivery';
} else {
    $payment_status = 'unpaid';
}

    $stmt = $conn->prepare("INSERT INTO payments (order_id, payment_method_id, payment_status) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $order_id, $payment_method_id, $payment_status);
    $stmt->execute();
    $stmt->close();

    // 5. Вмъкване на всеки продукт в order_details
    $stmt = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, price_per_unit) VALUES (?, ?, ?, ?)");
    foreach ($cart as $item) {
        $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
        $stmt->execute();
    }
    $stmt->close();
    
// 6. Намаляване на количеството от таблицата stock
$stmt = $conn->prepare("UPDATE stock SET quantity = quantity - ? WHERE product_id = ? AND size = ?");
foreach ($cart as $item) {
    $stmt->bind_param("iis", $item['quantity'], $item['id'], $item['size']);
    $stmt->execute();

    // Проверка дали е успешно намалено
    if ($stmt->affected_rows === 0) {
        throw new Exception("Недостатъчна наличност за продукт ID {$item['id']} с размер {$item['size']}.");
    }
}
$stmt->close();
    // Потвърждение
    $conn->commit();
    unset($_SESSION['cart']);

    echo "Поръчката беше успешно направена!";
} catch (Exception $e) {
    $conn->rollback();
    die("Грешка: " . $e->getMessage());
}
?>
