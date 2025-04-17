<?php
session_start();
$conn = new mysqli("localhost", "root", "", "kursov_proektbd");
$conn->set_charset("utf8");

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $new_password = trim($_POST['new_password']);
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update->bind_param("ss", $hashed_password, $email);
        if ($update->execute()) {
            $success = "Your password has been successfully reset.";
        } else {
            $error = "An error occurred while updating the password.";
        }
    } else {
        $error = "No user found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
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

        .form-container .message {
            margin-bottom: 15px;
            font-weight: bold;
        }

        .error {
            color: red;
        }

        .success {
            color: green;
        }

        .form-container a {
            color: #ff4d79;
            text-decoration: none;
        }

        .form-container a:hover {
            text-decoration: underline;
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
                <a href="index.php"><img src="images/home.png" alt="Home"></a>
                <a href="cart.php"><img src="images/shoppingcart.png" alt="Cart"></a>
                <a href="user_redirect.php"><img src="images/profile_picture.png" alt="Profile" class="login-icon"></a>
            </div>
        </div>
    </header>

    <div class="form-container">
        <h2>Reset Your Password</h2>
        <?php 
            if (!empty($error)) echo "<div class='message error'>$error</div>"; 
            if (!empty($success)) echo "<div class='message success'>$success</div>"; 
        ?>
        <form method="post">
            <input type="email" name="email" placeholder="Enter your email" required>
            <input type="password" name="new_password" placeholder="Enter new password" required>
            <button type="submit">Reset Password</button>
        </form>
        <p><a href="login.php">⬅ Back to Login</a></p>
    </div>
</body>
</html>
