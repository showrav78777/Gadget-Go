<?php
session_start();
include '../connection/connection.php';

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Guest';

// If not logged in via session, check cookies
if (!$isLoggedIn && isset($_COOKIE['user_id']) && !empty($_COOKIE['user_id'])) {
    // Restore session from cookies
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['user_name'] = $_COOKIE['user_name'] ?? 'User';
    $username = $_SESSION['user_name'];
    $isLoggedIn = true;
}

// Clear ONLY the cart data, preserve login information
if (isset($_SESSION['cart'])) {
    // Empty the cart
    $_SESSION['cart'] = [];
    
    // Also clear the cart cookie if it exists
    if (isset($_COOKIE['cart'])) {
        setcookie('cart', '', time() - 3600, '/'); // Set expiration time in the past to delete the cookie
    }
}

// For debugging - you can remove this in production
$debug_info = [];
$debug_info['session_id'] = session_id();
$debug_info['user_id'] = $_SESSION['user_id'] ?? 'Not set';
$debug_info['user_name'] = $_SESSION['user_name'] ?? 'Not set';
$debug_info['is_logged_in'] = $isLoggedIn ? 'Yes' : 'No';

// Display success message
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success | Gadget & Go</title>
    <link rel="stylesheet" href="../bootstrap.min.css">

    <link rel="stylesheet" href="../../style.css">
    <style>
        .success-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            background-color: #fff;
            text-align: center;
        }
        .success-icon {
            color: #28a745;
            font-size: 80px;
            margin-bottom: 20px;
        }
        .btn-home {
            margin-top: 20px;
        }
        .debug-info {
            margin-top: 30px;
            font-size: 12px;
            color: #999;
            text-align: left;
            display: none; /* Hide in production */
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="success-container">
            <div class="success-icon">✓</div>
            <h2>Order Successful!</h2>
            <p class="lead">Thank you for your purchase, <?php echo htmlspecialchars($username); ?>!</p>
            <p>Your order has been successfully processed.</p>
            <p>You will receive a confirmation email shortly.</p>
            <a href="https://localhost/g&g/home/home.php" class="btn btn-primary btn-home">Return to Homepage</a>
            
            <!-- Debug information - remove in production -->
            <div class="debug-info">
                <h4>Debug Information</h4>
                <pre><?php print_r($debug_info); ?></pre>
            </div>
        </div>
    </div>

    <footer class="my-5 pt-5 text-muted text-center text-small">
        <p class="mb-1">&copy; 2025 Gadget & Go</p>
    </footer>
    
    <script>
    // This script ensures that the session is maintained
    document.addEventListener('DOMContentLoaded', function() {
        // Send a ping to keep the session alive
        fetch('keep_session.php', {
            method: 'POST',
            credentials: 'same-origin'
        });
    });
    </script>
</body>
</html> 