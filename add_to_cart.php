<?php 
session_start();

$conn = new mysqli("localhost", "root", "", "kursov_proektbd");
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Грешка при свързване: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = floatval($_POST['price']);
    $image = $_POST['image'];
    $quantity = intval($_POST['quantity']);

    // Взимаме информацията за продукта от базата, включително ID
    $stmt = $conn->prepare("SELECT product_id, stock_quantity FROM products WHERE name = ?");
    if (!$stmt) {
        die("Грешка в заявката: " . $conn->error);
    }

    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($product = $result->fetch_assoc()) {
        if ($product['stock_quantity'] < $quantity) {
            $_SESSION['error'] = "Няма достатъчно наличност от този продукт.";
            header("Location: cart.php");
            exit();
        }

        $product_id = $product['product_id']; // <-- Използваме реалното ID от базата

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Използваме product_id като ключ в количката
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'id' => $product_id,         // <-- Записваме го тук!
                'name' => $name,
                'price' => $price,
                'image' => $image,
                'quantity' => $quantity
            ];
        }

        header("Location: cart.php");
        exit();
    } else {
        $_SESSION['error'] = "Продуктът не е намерен.";
        header("Location: cart.php");
        exit();
    }
}
?>
