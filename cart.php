<?php 
session_start();
$conn = new mysqli("localhost", "root", "", "kursov_proektbd");
$conn->set_charset("utf8");
if ($conn->connect_error) {
    die("Connection error: " . $conn->connect_error);
}

if (isset($_POST['remove'])) {
    $product_id = $_POST['remove'];
    unset($_SESSION['cart'][$product_id]);
    header("Location: cart.php");
    exit();
}

if (isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $change = (int) $_POST['update_quantity'];
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += $change;
        if ($_SESSION['cart'][$product_id]['quantity'] < 1) {
            unset($_SESSION['cart'][$product_id]);
        }
    }
    header("Location: cart.php");
    exit();
}

$sql = "SELECT category_id, name FROM product_categories";
$result = $conn->query($sql);

$categories = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[$row["category_id"]] = $row["name"];
    }
}

$conn->close();

$women_categories = [1, 2, 3, 4, 5, 6, 7, 9, 10];
$men_categories = [1, 2, 4, 5, 6, 8, 9, 10];
$kids_categories = [1, 2, 4, 5, 6, 8, 9];
$accessories_categories = [11, 12, 13, 14, 15, 16, 17];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="nachalna_stranica.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            padding: 15px;
            text-align: center;
        }
        th {
            background: #ff80a1;
            color: white;
        }
        td img {
            width: 150px;
            height: auto;
        }
        button {
            background: #ff80a1;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
        }
        button:hover {
            background: #ff4d79;
        }
    </style>
</head>
<body>
<header>
    <div class="header-container">
        <div class="logo">
            <a href="index.php"><h1>Dressify</h1></a>
        </div>
        <div class="cart-title">
            <a href="index.php" class="cart-link">Your Shopping Cart</a>
        </div>
        <div class="nav-icons">
            <a href="index.php"><img src="images/home.png" alt="Home" class="home-icon"></a>
            <a href="cart.php"><img src="images/shoppingcart.png" alt="Cart" class="cart-icon"></a>
            <a href="user_redirect.php"><img src="images/profile_picture.png" alt="Профил" class="login-icon"></a>
        </div>
    </div>
</header>

<style>
    .cart-title {
        text-align: center;
        flex-grow: 1;
    }

    .cart-title .cart-link {
        font-size: 24px;
        font-weight: bold;
        text-decoration: none;
        color: black;
        transition: color 0.3s ease;
    }

    .cart-title .cart-link:hover {
        color: #ff80a1;
    }
</style>


<main>
    <?php if (empty($_SESSION['cart'])): ?>
        <p style="text-align:center;">Your cart is empty.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Product</th>
                <th>Image</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Remove</th>
            </tr>
            <?php $total = 0; ?>
            <?php foreach ($_SESSION['cart'] as $id => $item): 
                $item_total = $item['price'] * $item['quantity'];
                $total += $item_total;
            ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><img src="<?= htmlspecialchars($item['image']) ?>"></td>
                    <td><?= number_format($item['price'], 2) ?> BGN</td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="product_id" value="<?= $id ?>">
                            <button type="submit" name="update_quantity" value="-1">-</button>
                        </form>
                        <?= $item['quantity'] ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="product_id" value="<?= $id ?>">
                            <button type="submit" name="update_quantity" value="1">+</button>
                        </form>
                    </td>
                    <td><?= number_format($item_total, 2) ?> BGN</td>
                    <td>
                        <form method="post">
                            <button type="submit" name="remove" value="<?= $id ?>">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="4"><strong>Grand Total:</strong></td>
                <td><strong><?= number_format($total, 2) ?> BGN</strong></td>
                <td></td>
            </tr>
        </table>
        <div style="text-align:center; margin-top: 20px;">
            <button onclick="window.location.href='checkout.php'">Proceed to Checkout</button>
        </div>
    <?php endif; ?>
</main>
</body>
</html>