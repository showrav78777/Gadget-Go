<?php
include("db_connection.php");

// Drop the existing table if it's causing problems
$sql = "DROP TABLE IF EXISTS orders";
if ($conn_integration->query($sql) === TRUE) {
    echo "Existing table dropped successfully<br>";
} else {
    echo "Error dropping table: " . $conn_integration->error . "<br>";
}

// Create a new table with the correct structure
$sql = "CREATE TABLE orders (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    transaction_id VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) NOT NULL,
    status VARCHAR(50) NOT NULL,
    customer_name VARCHAR(255),
    customer_email VARCHAR(255),
    customer_phone VARCHAR(50),
    customer_address VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn_integration->query($sql) === TRUE) {
    echo "Table created successfully";
} else {
    echo "Error creating table: " . $conn_integration->error;
}
?> 