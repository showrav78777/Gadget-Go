<?php
require_once(__DIR__ . "/lib/SslCommerzNotification.php");
include("db_connection.php");

use SslCommerz\SslCommerzNotification;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tran_id = $_POST['tran_id'];
    $val_id = $_POST['val_id'];
    $status = $_POST['status'];

    // Fetch the order from the database
    $query = "SELECT * FROM orders WHERE transaction_id = ?";
    $stmt = $conn_integration->prepare($query);
    $stmt->bind_param("s", $tran_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        $customer_phone = $order['customer_phone'];
        if ($status === "VALID") {
            // Update order status in database
            $update_query = "UPDATE orders SET status = 'Paid', val_id = ? WHERE transaction_id = ?";
            $update_stmt = $conn_integration->prepare($update_query);
            $update_stmt->bind_param("ss", $val_id, $tran_id);
            $update_stmt->execute();
            
            // Redirect to the desired URL after successful payment
            header("Location: https://localhost/g&g/cart/order_success.php");
            exit; // Ensure to exit after redirection
        } else {
            echo "<h2>Payment Failed</h2>";
            echo "<p>There was an issue processing your payment. Please try again.</p>";
            echo "<a href='cart.php'>Return to Cart</a>";
        }
    } else {
        echo "<h2>Invalid Transaction</h2>";
        echo "<p>No order found for this transaction.</p>";
    }
} else {
    echo "<h2>Access Denied</h2>";
}
?>
