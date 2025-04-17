<?php
$conn = new mysqli("localhost", "root", "", "kursov_proektbd");
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Грешка при свързване: " . $conn->connect_error);
}

// Зареждане на категории
$sql = "SELECT category_id, name FROM product_categories";
$result = $conn->query($sql);

$categories = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[$row["category_id"]] = $row["name"];
    }
}

$women_categories = [1, 2, 3, 4, 5, 6, 7, 9, 10];
$men_categories = [1, 2, 4, 5, 6, 8, 9, 10];
$kids_categories = [1, 2, 4, 5, 6, 8, 9];
$accessories_categories = [11, 12, 13, 14, 15, 16, 17];

// Детайли за продукта
$product_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$product_id) {
    die("Невалиден продукт.");
}

// Променено: Взимаме и името на категорията
$product_sql = "SELECT p.*, c.name AS category_name 
                FROM products p
                JOIN product_categories c ON p.category_id = c.category_id
                WHERE p.product_id = ?";
$stmt = $conn->prepare($product_sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Продуктът не е намерен.";
    exit;
}

$product = $result->fetch_assoc();
$category_id = $product['category_id'];
$category_name = strtolower($product['category_name']);

// Зареждане на наличности от таблица stock
$stock_sql = "SELECT size, quantity FROM stock WHERE product_id = ?";
$stock_stmt = $conn->prepare($stock_sql);

if (!$stock_stmt) {
    die("Грешка при prepare на stocks заявка: " . $conn->error);
}

$stock_stmt->bind_param("i", $product_id);
$stock_stmt->execute();
$stock_result = $stock_stmt->get_result();

$available_sizes = [];
while ($row = $stock_result->fetch_assoc()) {
    $available_sizes[$row['size']] = $row['quantity'];
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['name']) ?> – Детайли</title>
    <link rel="stylesheet" href="nachalna_stranica.css">
</head>
<body>

<header>
    <div class="header-container">
        <div class="logo">
            <a href="index.php"><h1>Dressify</h1></a>
        </div>
        <div class="category-menu">
            <ul>
                <li class="dropdown">
                    <a href="#">Women</a>
                    <ul class="dropdown-content">
                        <?php foreach ($women_categories as $id): ?>
                            <?php if (isset($categories[$id])): ?>
                                <li><a href="products.php?gender=2&category=<?= $id ?>"><?= htmlspecialchars($categories[$id]) ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#">Men</a>
                    <ul class="dropdown-content">
                        <?php foreach ($men_categories as $id): ?>
                            <?php if (isset($categories[$id])): ?>
                                <li><a href="products.php?gender=1&category=<?= $id ?>"><?= htmlspecialchars($categories[$id]) ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#">Kids</a>
                    <ul class="dropdown-content">
                        <?php foreach ($kids_categories as $id): ?>
                            <?php if (isset($categories[$id])): ?>
                                <li><a href="products.php?gender=3&category=<?= $id ?>"><?= htmlspecialchars($categories[$id]) ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#">Accessory</a>
                    <ul class="dropdown-content">
                        <?php foreach ($accessories_categories as $id): ?>
                            <?php if (isset($categories[$id])): ?>
                                <li><a href="products.php?category=<?= $id ?>"><?= htmlspecialchars($categories[$id]) ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li><a href="#">Sale</a></li>
                <li class="search-item">
                    <form action="search.php" method="get">
                        <input type="text" name="search" placeholder="Search category..." />
                        <button type="submit">Search</button>
                    </form>
                </li>
            </ul>
        </div>
        <div class="nav-icons">
            <a href="index.php"><img src="images/home.png" alt="Начало" class="home-icon"></a>
            <a href="cart.php"><img src="images/shoppingcart.png" alt="Количка" class="cart-icon"></a>
            <a href="user_redirect.php"><img src="images/profile_picture.png" alt="Профил" class="login-icon"></a>
        </div>
    </div>
    <style>
        .product-detail-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }

        .product-content {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            align-items: flex-start;
            justify-content: center;
        }

        .product-left img {
            max-width: 400px;
            width: 100%;
            border-radius: 10px;
        }

        .product-right {
            flex: 1;
            min-width: 300px;
        }

        .product-right .description {
            font-size: 16px;
            line-height: 1.6;
            color: #444;
            margin-bottom: 15px;
        }

        .product-right .price {
            font-size: 22px;
            color: #e6005c;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .quantity-wrapper {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }

        .quantity-btn {
            background-color: #ffb6c1;
            border: none;
            padding: 6px 12px;
            font-size: 16px;
            cursor: pointer;
        }

        .quantity-input {
            width: 50px;
            text-align: center;
            border: 1px solid #ccc;
            margin: 0 5px;
        }

        .add-to-cart-btn {
            background-color: #ff4d79;
            color: white;
            border: none;
            padding: 10px 20px;
            margin-top: 10px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }

        .add-to-cart-btn:hover {
            background-color: #ff1a57;
        }
        
        .sizes-button-group {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.size-btn {
    background-color: #ffb6c1;
    border: none;
    padding: 10px 16px;
    cursor: pointer;
    border-radius: 5px;
    font-weight: bold;
    font-size: 16px;
}

.size-btn.active {
    background-color: #ff4d79;
    color: white;
}

.size-btn.disabled-size {
    background-color: #ddd !important;
    color: #999;
    text-decoration: line-through;
    cursor: not-allowed;
}
.info-box {
    background-color: #fff0f5;
    padding: 25px;
    border: 2px solid #ffb6c1;
    border-radius: 15px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}



        @media (max-width: 768px) {
            .product-content {
                flex-direction: column;
                align-items: center;
            }

            .product-left img {
                max-width: 100%;
            }

            .product-right {
                text-align: center;
            }
        }
    </style>
</header>


<div class="product-detail-container">
    <h1><?= htmlspecialchars($product['name']) ?></h1>

    <div class="product-content">
        <div class="product-left">
            <img src="<?= $product['image_url'] ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>
        <div class="info-box">
        <div class="product-right">
            <p class="description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            <p class="price"><?= number_format($product['price'], 2) ?> BGN</p>

            <form method="post" action="add_to_cart.php">
                <input type="hidden" name="name" value="<?= htmlspecialchars($product['name']) ?>">
                <input type="hidden" name="price" value="<?= $product['price'] ?>">
                <input type="hidden" name="image" value="<?= htmlspecialchars($product['image_url']) ?>">

                <div class="size-selector">
    <p>Размер:</p>
    <div class="sizes-button-group">
        <?php
        if (in_array($category_id, $accessories_categories)) {
            echo '<button type="button" class="size-btn active" disabled>One Size</button>';
            echo '<input type="hidden" name="size" value="One Size">';
        } else {
            $sizes = ['S', 'M', 'L', 'XL'];
            foreach ($sizes as $size) {
                $available = isset($available_sizes[$size]) && $available_sizes[$size] > 0;
                echo '<button type="button" class="size-btn ' . (!$available ? 'disabled-size' : '') . '" data-size="' . $size . '" ' . (!$available ? 'disabled' : '') . '>' . $size . '</button>';
            }
            echo '<input type="hidden" name="size" id="selected-size" required>';
        }
        ?>
    </div>
</div>


                <div class="product-actions">
                    <div class="quantity-wrapper">
                        <button type="button" class="quantity-btn" onclick="changeQuantity(this, -1)">-</button>
                        <input type="number" name="quantity" value="1" min="1" class="quantity-input" readonly>
                        <button type="button" class="quantity-btn" onclick="changeQuantity(this, 1)">+</button>
                    </div>
                    <button type="submit" class="add-to-cart-btn large">🛒 Добави в количката</button>
                </div>

            </form>
        </div>
        </div>
    </div>
</div>

<footer style="background-color: #ffedf3; padding: 40px 20px; margin-top: 50px; border-top: 1px solid #ffd6e0;">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 40px;">
        <!-- About Us -->
        <div style="flex: 1; min-width: 250px;">
            <h3 style="color: #ff4d79; margin-bottom: 15px;">Dressify</h3>
            <p style="color: #555; line-height: 1.6;">
                <a href="aboutUs.php" style="color: #ff4d79; text-decoration: none;">Learn more about us</a>
            </p>
        </div>

        <!-- Контакти -->
        <div style="flex: 1; min-width: 250px;">
            <h3 style="color: #ff4d79; margin-bottom: 15px;">Contacts</h3>
            <p style="color: #555;">📞 Mobile Phone: +359 895 093 700</p>
            <p style="color: #555;">📧 Email: nurdzhann31@gmail.com</p>
            <p style="color: #555;">🕒 Bussiness hours: Mon-Fri: 9:00 - 18:00</p>
        </div>

        <!-- Социални мрежи -->
        <div style="flex: 1; min-width: 250px; text-align: center;">
            <h3 style="color: #ff4d79; margin-bottom: 15px;">Follow Us</h3>
            <div style="display: flex; justify-content: center; gap: 20px;">
                <a href="https://www.instagram.com/nurdzhann/" target="_blank" style="display: flex; align-items: center; justify-content: center;">
                    <img src="images/instagram.png" alt="Instagram" style="height: 36px;">
                </a>
                <a href="https://www.facebook.com/nurdzhann" target="_blank" style="display: flex; align-items: center; justify-content: center;">
                    <img src="images/facebook.jpg" alt="Facebook" style="height: 36px;">
                </a>
            </div>
        </div>
    </div>

    <!-- COPYRIGHT - поставено под целия flex контейнер -->
    <div style="text-align: center; margin-top: 30px; color: #888;">
        © <?= date("Y") ?> Dressify. All rights reserved.
    </div>
</footer>

<script>
function changeQuantity(button, delta) {
    const input = button.parentElement.querySelector('.quantity-input');
    let value = parseInt(input.value);
    if (isNaN(value)) value = 1;
    value += delta;
    if (value < 1) value = 1;
    input.value = value;
}

document.querySelectorAll('.size-btn').forEach(button => {
    button.addEventListener('click', function () {
        if (this.disabled) return;
        document.querySelectorAll('.size-btn').forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');
        const selected = document.getElementById('selected-size');
        if (selected) selected.value = this.dataset.size;
    });
});
</script>
</body>
</html>