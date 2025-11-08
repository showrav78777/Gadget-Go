<?php
require_once(__DIR__ . "/lib/SslCommerzNotification.php");
include("db_connection.php");

use SslCommerz\SslCommerzNotification;

$sslc = new SslCommerzNotification();

$tran_id = isset($_POST['tran_id']) ? $_POST['tran_id'] : '';
$amount = isset($_POST['amount']) ? $_POST['amount'] : '';
$currency = isset($_POST['currency']) ? $_POST['currency'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : '';

// Check if the transaction is valid
if ($status == 'VALID') {
    // Update the order status in the database
    $sql = "UPDATE payment_orders SET status='Completed' WHERE tran_id='$tran_id'";
    if ($conn_integration->query($sql) === TRUE) {
        echo "Transaction is successfully completed";
    } else {
        echo "Error updating record: " . $conn_integration->error;
    }
} else {
    echo "Transaction is not valid";
} 