<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

// Set username variable
if ($isLoggedIn && isset($_SESSION['user_logged_in'])) {
    $username = $_SESSION['user_logged_in'];
} else {
    $username = "Guest";
}



// Function to check login status and redirect if needed
function requireLogin() {
    global $isLoggedIn;
    if (!$isLoggedIn) {
        // Store the current URL in session to redirect back after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header("Location: http://localhost/g&g/login_registration/login_registration.php");
        exit();
    }
}
?> 