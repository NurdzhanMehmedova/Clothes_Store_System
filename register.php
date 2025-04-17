<?php
session_start();
$conn = new mysqli("localhost", "root", "", "kursov_proektbd");
$conn->set_charset("utf8");

$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $address = trim($_POST['address']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'user';

    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "Имейлът вече е регистриран.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, full_name, address, email, password, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $username, $full_name, $address, $email, $password, $role);
        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id;
            header("Location: $redirect");
            exit();
        } else {
            $error = "Error with registration.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="nachalna_stranica.css">
    <style>
        .form-container {
            max-width: 400px;
            margin: 60px auto;
            background-color: #fff0f5;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 4px 8px rgba(0,0,0,0.1);
        }

        .form-container h2 {
            color: #ff4d79;
            margin-bottom: 20px;
        }

        .form-container input {
            width: 100%;
            padding: 12px;
            margin: 8px 0 20px;
            border: 1px solid #ff80a1;
            border-radius: 8px;
        }

        .form-container button {
            background-color: #ff80a1;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        .form-container button:hover {
            background-color: #ff4d79;
        }

        .form-container p {
            margin-top: 15px;
        }

        .form-container a {
            color: #ff4d79;
            text-decoration: none;
        }

        .form-container a:hover {
            text-decoration: underline;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <a href="index.php"><h1>Dressify</h1></a>
            </div>
            <div class="nav-icons">
                <a href="index.php"><img src="images/home.png" alt="Начало"></a>
                <a href="cart.php"><img src="images/shoppingcart.png" alt="Количка"></a>
                <a href="user_redirect.php"><img src="images/profile_picture.png" alt="Профил" class="login-icon"></a>
            </div>
        </div>
    </header>

    <div class="form-container">
        <h2>Sign Up</h2>
        <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="text" name="address" placeholder="Address" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign Up</button>
        </form>
        <p>Already have an account? <a href="login.php?redirect=<?= urlencode($redirect) ?>">Click here</a></p>
    </div>
</body>
</html>
