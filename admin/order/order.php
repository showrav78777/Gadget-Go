<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../../connection/connection.php';

// Handle status update
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    // Update the status directly
    $sql = "UPDATE orders SET status = '$status' WHERE id = $order_id";
    if ($conn->query($sql) === TRUE) {
        $success_message = "Order status updated successfully.";

        // Update total_sales in the dashboard if status is delivered or paid
        if ($status === 'delivered' || $status === 'paid') {
            // Get the amount for the specific order
            $amount_query = "SELECT amount FROM orders WHERE id = $order_id";
            $amount_result = $conn->query($amount_query);
            if ($amount_result && $amount_result->num_rows > 0) {
                $amount_row = $amount_result->fetch_assoc();
                $amount = $amount_row['amount'];

                // Update the total_sales in the dashboard
                $sales_query = "UPDATE dashboard SET total_sales = total_sales + $amount WHERE id = 1"; // Assuming there's only one dashboard record
                $conn->query($sales_query);
            }
        }

        // If the order is marked as delivered, update the stock
        if ($status === 'delivered') {
            // Fetch the order items for this order
            $items_query = "SELECT product_id, quantity FROM order_items WHERE order_id = $order_id";
            $items_result = $conn->query($items_query);

            while ($item = $items_result->fetch_assoc()) {
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];

                // Update the stock in the products table
                $update_stock_query = "UPDATE products SET stock = stock - $quantity WHERE product_id = $product_id";
                $conn->query($update_stock_query);
            }
        }
    } else {
        $error_message = "Error updating order status: " . $conn->error;
    }
}

// Set the limit of records per page
$limit = isset($_POST["limit-records"]) ? (int)$_POST["limit-records"] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1; // Ensure page is at least 1
$start = ($page - 1) * $limit;

// Fetch all orders with pagination and join with order_items
$sql = "SELECT o.*, oi.product_id, oi.quantity FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        ORDER BY o.order_date DESC LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $start, $limit);
$stmt->execute();
$result = $stmt->get_result();

// Get the total count of orders
$result1 = $conn->query("SELECT COUNT(id) AS total_count FROM orders");
$totalCount = $result1->fetch_assoc();
$total = $totalCount['total_count'];

// Calculate pagination
$pages = ceil($total / $limit);
$previous = $page - 1;
$next = $page + 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management</title>
    <link rel="stylesheet" href="../../bootstrap.min.css">
    <style>
        body {
            display: flex;
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
        .alert {
            margin-bottom: 20px;
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
    <h2>Order Management</h2>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-10">
            <!-- Pagination -->
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <!-- Previous Page -->
                    <li class="page-item <?= $previous <= 0 ? 'disabled' : '' ?>">
                        <a class="page-link" href="order.php?page=<?= $previous ?>" aria-label="Previous">
                            <span aria-hidden="true">« Previous</span>
                        </a>
                    </li>

                    <!-- Page Numbers -->
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="order.php?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <!-- Next Page -->
                    <li class="page-item <?= $next > $pages ? 'disabled' : '' ?>">
                        <a class="page-link" href="order.php?page=<?= $next ?>" aria-label="Next">
                            <span aria-hidden="true">Next »</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Records per page limit -->
        <div class="col-md-2 text-center">
            <form method="post" action="order.php?page=<?= $page ?>" id="limit-form">
                <select name="limit-records" id="limit-records" class="form-select">
                    <option disabled="disabled" selected="selected">--- Limit Records ---</option>
                    <?php foreach ([10, 20, 50] as $limitOption): ?>
                        <option value="<?= $limitOption; ?>" <?= ($limit == $limitOption) ? 'selected' : ''; ?>>
                            <?= $limitOption; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Product ID</th>
                <th>Quantity</th>
                <th>Shipping Address</th>
                <th>Action</th>
               
            </tr>
        </thead>
        <tbody>
            <?php while ($order = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $order['id']; ?></td>
                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                    <td>Tk. <?php echo number_format($order['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                    <td><?php echo htmlspecialchars($order['product_id']); ?></td>
                    <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($order['shipping_address']);?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="status">
                                <option value="not paid" <?php echo $order['status'] === 'not paid' ? 'selected' : ''; ?>>Not Paid</option>
                                <option value="paid" <?php echo $order['status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
