<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Свързване с базата
$conn = new mysqli("localhost", "root", "", "kursov_proektbd");
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Грешка при свързване: " . $conn->connect_error);
    
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

}

// Извличане на информация за потребителя
$sql = "SELECT username, email, full_name FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $full_name);
$stmt->fetch();
$stmt->close();

// Примерни поръчки
$orders = [
    ['date' => '2025-04-10', 'total' => 79.99, 'status' => 'Изпратена'],
    ['date' => '2025-03-28', 'total' => 42.50, 'status' => 'Доставена']
];

$conn->close();
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Моят профил</title>
    <link rel="stylesheet" href="nachalna_stranica.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #fff6f9;
            margin: 0;
            padding: 0;
        }
        .profile-container {
            max-width: 800px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(255, 80, 120, 0.1);
        }
        h1 {
            color: #ff4d79;
            text-align: center;
            margin-bottom: 20px;
        }
        .user-info, .order-history {
            margin-bottom: 30px;
        }
        .user-info p, .order-history p {
            font-size: 16px;
            color: #333;
            margin: 6px 0;
        }
        .order-table {
            width: 100%;
            border-collapse: collapse;
        }
        .order-table th, .order-table td {
            border: 1px solid #ffd6e0;
            padding: 10px;
            text-align: center;
        }
        .order-table th {
            background-color: #ffe6ec;
            color: #ff4d79;
        }
        .logout {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #fff;
            background-color: #ff4d79;
            padding: 10px 20px;
            border-radius: 20px;
            text-decoration: none;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }
        .logout:hover {
            background-color: #e8436f;
        }
    </style>
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
<div class="profile-container">
    <h1>Добре дошъл, <?= htmlspecialchars(isset($full_name) ? $full_name : $username) ?>!</h1>

    <div class="user-info">
        <h2>🔐 Профилна информация</h2>
        <p><strong>Потребителско име:</strong> <?= htmlspecialchars($username) ?></p>
        <p><strong>Имейл:</strong> <?= htmlspecialchars($email) ?></p>
        <p><strong>Потребителско ID:</strong> <?= $user_id ?></p>
    </div>

    <div class="order-history">
        <h2>🛍️ История на поръчките</h2>
        <table class="order-table">
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Обща сума</th>
                    <th>Статус</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= $order['date'] ?></td>
                        <td><?= number_format($order['total'], 2) ?> лв</td>
                        <td><?= $order['status'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <a href="logout.php" class="logout">Изход от профила</a>
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
</body>
</html>
