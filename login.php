<?php
session_start();
$conn = new mysqli("localhost", "root", "", "kursov_proektbd");
$conn->set_charset("utf8");

$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, password, username, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password, $username, $role);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            header("Location: $redirect");
            exit();
        } else {
            $error = "Wrong password.";
        }
    } else {
        $error = "The user doesn't exist.";
    }
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
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
    <a href="index.php"><img src="images/home.png" alt="Начало" class="home-icon"></a>
    <a href="cart.php"><img src="images/shoppingcart.png" alt="Количка" class="cart-icon"></a>
    <a href="user_redirect.php"><img src="images/profile_picture.png" alt="Профил" class="login-icon"></a>
</div>

        </div>
    </header>

    <div class="form-container">
        <h2>Login</h2>
        <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="post">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign In</button>
        </form>
        <p><a href="forgot_password.php">You forgot your password?</a></p>
        <p>You don't have an account? <a href="register.php?redirect=<?= urlencode($redirect) ?>">Sign Up</a></p>
    </div>
</body>
</html>
