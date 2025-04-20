<?php 
session_start();

$conn = new mysqli("localhost", "root", "", "kursov_proektbd");
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Грешка при свързване: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $size = $_POST['size'];
    $name = $_POST['name'];
    $price = floatval($_POST['price']);
    $image = $_POST['image'];
    $quantity = intval($_POST['quantity']);

    // Взимаме product_id и наличност от таблицата stock чрез JOIN с products
    $stmt = $conn->prepare("
        SELECT p.product_id, s.quantity 
        FROM products p
        JOIN stock s ON p.product_id = s.product_id
        WHERE p.name = ?
    ");
    
    if (!$stmt) {
        die("Грешка в заявката: " . $conn->error);
    }

    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($product = $result->fetch_assoc()) {
        if ($product['quantity'] < $quantity) {
            $_SESSION['error'] = "Not enough quantity of this product.";
            header("Location: cart.php");
            exit();
        }

        $product_id = $product['product_id'];

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id . '_' . $size] = [
                'id' => $product_id,
                'name' => $name,
                'price' => $price,
                'image' => $image,
                'quantity' => $quantity,
                'size' => $size
            ];
        }

        header("Location: cart.php");
        exit();
    } else {
        $_SESSION['error'] = "The product is not found.";
        header("Location: cart.php");
        exit();
    }
}
?>