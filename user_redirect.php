<?php
session_start();

// Ако е логнат – пращаме към профила
if (isset($_SESSION['user_id'])) {
    header("Location: profile.php");
    exit;
}

// Ако не е логнат – към логин
header("Location: login.php");
exit;
?>
