<?php
include("db_connection.php");

// Create a new table with a different name
$sql = "CREATE TABLE payment_orders (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    tran_id VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) NOT NULL,
    status VARCHAR(50) NOT NULL,
    name VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(50),
    address VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn_integration->query($sql) === TRUE) {
    echo "Table 'payment_orders' created successfully";
} else {
    echo "Error creating table: " . $conn_integration->error;
}
?> 