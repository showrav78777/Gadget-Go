<?php
include '../../connection/connection.php';

// Fetch Total Sales from delivered orders only
$total_sales_query = "SELECT SUM(amount) AS total_sales FROM orders WHERE status IN ('delivered', 'paid')";
$total_sales_result = $conn->query($total_sales_query);
$total_sales = ($total_sales_result && $total_sales_result->num_rows > 0) ? $total_sales_result->fetch_assoc()['total_sales'] : 0;
$total_sales = $total_sales ?: 0; // Handle NULL value

// Fetch Total Orders
$total_orders_query = "SELECT COUNT(*) AS total_orders FROM orders";
$total_orders_result = $conn->query($total_orders_query);
$total_orders = ($total_orders_result && $total_orders_result->num_rows > 0)
 ? $total_orders_result->fetch_assoc()['total_orders'] : 0;

// Fetch Total Products
$total_products_query = "SELECT COUNT(*) AS total_products FROM products";
$total_products_result = $conn->query($total_products_query);
$total_products = ($total_products_result && $total_products_result->num_rows > 0)
 ? $total_products_result->fetch_assoc()['total_products'] : 0;

// Save the dashboard statistics to the database
try {
    // Check if dashboard table has any records
    $check_query = "SELECT COUNT(*) as count FROM dashboard";
    $check_result = $conn->query($check_query);
    $has_records = ($check_result && $check_result->num_rows > 0) ? $check_result->fetch_assoc()['count'] > 0 : false;
    
    if ($has_records) {
        // Update existing record
        $update_query = "UPDATE dashboard SET total_sales = ?, total_orders = ?, total_products = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("dii", $total_sales, $total_orders, $total_products);
        $stmt->execute();
    } else {
        // Insert new record
        $insert_query = "INSERT INTO dashboard (total_sales, total_orders, total_products) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("dii", $total_sales, $total_orders, $total_products);
        $stmt->execute();
    }
} catch (Exception $e) {
    $db_error = "Database error: " . $e->getMessage();
}

// Fetch Latest Orders
$latest_orders_query = "SELECT * FROM orders ORDER BY order_date DESC LIMIT 8";
$latest_orders_result = $conn->query($latest_orders_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            font-family: Arial, sans-serif;
            margin: 0;
        }
        .sidebar {
            width: 250px;
            background: #111;
            color: white;
            height: 100vh;
            padding: 20px;
        }
        .sidebar a {
            color: white;
            display: block;
            padding: 10px;
            text-decoration: none;
        }
        .sidebar a:hover {
            background: #575757;
        }
        .main-content {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
            height: 100vh;
        }
        .dashboard-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .dashboard-stats .stat {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            width: 30%;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat h3 {
            margin-bottom: 10px;
            color: #333;
        }
        .stat p {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin: 0;
        }
        .latest-orders {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .latest-orders h3 {
            margin-bottom: 15px;
        }
        .latest-orders table {
            width: 100%;
            border-collapse: collapse;
        }
        .latest-orders table th, .latest-orders table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .latest-orders table th {
            background-color: #f1f1f1;
        }
        .delivered {
            background-color: #d4f8d4;
            color: #2d8a2d;
            text-align: center;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .shipped {
            background-color: #d1ecf1;
            color: #0c5460;
            text-align: center;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .processing {
            background-color: #fff3cd;
            color: #856404;
            text-align: center;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .cancelled {
            background-color: #f8d7da;
            color: #721c24;
            text-align: center;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .pending {
            background-color: #f8e4b4;
            color: #8a6f2d;
            text-align: center;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .refresh-btn {
            margin-top: 10px;
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .refresh-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h4>Admin Panel</h4>
        <a href="/g&g/admin/admin_dashboard/dashboard.php">Dashboard</a>
        <a href="/g&g/admin/order/order.php">Orders</a>
        <a href="/g&g/admin/admin_product/product.php">Products</a>
        <a href="/g&g/admin/customer/customer.php">Customers</a>
    </div>
    <div class="main-content">
        <h1 class="text-center">Dashboard</h1>
        <br>
        <br>
        <?php if (isset($db_error)): ?>
            <div class="alert alert-danger"><?php echo $db_error; ?></div>
        <?php endif; ?>
        
    
        
        <div class="dashboard-stats">
            <div class="stat">
                <h3>Total Sales</h3>
                <p>Tk. <?php echo number_format($total_sales, 2); ?></p>
                <small>(From delivered orders only)</small>
            </div>
            <div class="stat">
                <h3>Total Orders</h3>
                <p><?php echo $total_orders; ?></p>
            </div>
            <div class="stat">
                <h3>Total Products</h3>
                <p><?php echo $total_products; ?></p>
            </div>
        </div>
        
        <div class="latest-orders">
            <h3>Latest Orders</h3>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($latest_orders_result && $latest_orders_result->num_rows > 0): ?>
                        <?php while ($order = $latest_orders_result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td>Tk. <?php echo number_format($order['amount'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                <td>
                                    <span class="<?php echo strtolower($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No orders found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
