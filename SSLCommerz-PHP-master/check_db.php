<?php
include("db_connection.php");

// Get all tables in the database
$result = $conn_integration->query("SHOW TABLES");
echo "<h2>Tables in Database</h2>";
echo "<ul>";
while ($row = $result->fetch_row()) {
    echo "<li>" . $row[0] . "</li>";
}
echo "</ul>";

// Check if orders table exists
$result = $conn_integration->query("SHOW TABLES LIKE 'orders'");
if ($result->num_rows > 0) {
    // Get the structure of the orders table
    $result = $conn_integration->query("DESCRIBE orders");
    echo "<h2>Structure of 'orders' Table</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>The 'orders' table does not exist.</p>";
}
?> 