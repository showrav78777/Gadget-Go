<?php
// Start the session
session_start();

// Clear all user-related cookies
if (isset($_COOKIE['user_name'])) {
    setcookie('user_name', '', time() - 3600, '/'); // Set expiration time in the past
}

if (isset($_COOKIE['cart'])) {
    setcookie('cart', '', time() - 3600, '/'); // Set expiration time in the past
}

// Destroy the session
session_unset();
session_destroy();

// Redirect to login page
header("Location: http://localhost/g&g/login_registration/login_registration.php");
exit();
?>