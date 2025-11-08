<?php
include '../../connection/connection.php';

// Handle customer deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $customer_id = (int)$_GET['delete'];

    // Check if customer exists
    $check_sql = "SELECT id FROM users WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $customer_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Delete customer
        $delete_sql = "DELETE FROM users WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $customer_id);

        if ($delete_stmt->execute()) {
            $success_message = "Customer deleted successfully.";
        } else {
            $error_message = "Error deleting customer: " . $conn->error;
        }
        $delete_stmt->close();
    } else {
        $error_message = "Customer not found.";
    }
    $check_stmt->close();
}

// Set the limit of records per page
$limit = isset($_POST["limit-records"]) ? (int)$_POST["limit-records"] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1; // Ensure page is at least 1
$start = ($page - 1) * $limit;

// Fetch customers from the database with pagination
$stmt = $conn->prepare("SELECT id, username, email, phone, created_at FROM users ORDER BY created_at ASC LIMIT ?, ?");
$stmt->bind_param("ii", $start, $limit);
$stmt->execute();
$result = $stmt->get_result();
$customers = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get the total count of users
$result1 = $conn->query("SELECT COUNT(id) AS total_count FROM users");
if (!$result1) {
    die("Count query failed: " . $conn->error);
}
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
    <title>Admin Panel - Customers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .customer-table {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .table th {
            background-color: #f1f1f1;
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
        <h2>Customer Management</h2>

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
                            <a class="page-link" href="customer.php?page=<?= $previous ?>" aria-label="Previous">
                                <span aria-hidden="true">« Previous</span>
                            </a>
                        </li>

                        <!-- Page Numbers -->
                        <?php for ($i = 1; $i <= $pages; $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link" href="customer.php?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <!-- Next Page -->
                        <li class="page-item <?= $next > $pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="customer.php?page=<?= $next ?>" aria-label="Next">
                                <span aria-hidden="true">Next »</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- Records per page limit -->
            <div class="col-md-2 text-center">
                <form method="post" action="customer.php?page=<?= $page ?>" id="limit-form">
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

        <div class="customer-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Registration Date</th>
                        
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($customers)): ?>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($customer['id']); ?></td>
                                <td><?php echo htmlspecialchars($customer['username']); ?></td>
                                <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                <td><?php echo date('F j, Y', strtotime($customer['created_at'])); ?></td>
                                <td><button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $customer['id']; ?>)">Delete</button></td>
                            </tr>
                            
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No customers found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <p>Total Customers: <?php echo $total; ?></p>
    </div>

    <script>
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this customer? This action cannot be undone.')) {
            window.location.href = `customer.php?delete=${id}`;
        }
    }
    </script>
</body>
</html>

