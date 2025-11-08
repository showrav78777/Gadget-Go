<?php
// Include database connection
include '../../connection/connection.php';




// Handle product deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product_id = $_GET['delete'];

    // Check if product exists
    $check_sql = "SELECT product_id FROM products WHERE product_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $product_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Delete product
        $delete_sql = "DELETE FROM products WHERE product_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $product_id);

        if ($delete_stmt->execute()) {
            $success_message = "Product deleted successfully.";
        } else {
            $error_message = "Error deleting product: " . $conn->error;
        }
    } else {
        $error_message = "Product not found.";
    }
}


// Set the limit of records per page
$limit = isset($_POST["limit-records"]) ? (int)$_POST["limit-records"] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1; // Ensure page is at least 1
$start = ($page - 1) * $limit;

// Fetch products from the database with pagination
$stmt = $conn->prepare("SELECT * FROM products ORDER BY product_id ASC LIMIT ?, ?");
$stmt->bind_param("ii", $start, $limit);
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get the total count of products
$result1 = $conn->query("SELECT COUNT(product_id) AS total_count FROM products");
if (!$result1) {
    die("Count query failed: " . $conn->error);
}
$productCount = $result1->fetch_all(MYSQLI_ASSOC);
$total = $productCount[0]['total_count'];

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
    <title>Admin Panel - Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- jQuery Script to handle the record limit change -->
    <script type="text/javascript">
        $(document).ready(function() {
            $("#limit-records").change(function() {
                $('form').submit();
            });
        });
    </script>
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

        .product-table {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table th {
            background-color: #f1f1f1;
        }

        .alert {
            margin-bottom: 20px;
        }

        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }

        .actions-column {
            width: 150px;
        }

        .status-available {
            color: #28a745;
            font-weight: bold;
        }

        .status-unavailable {
            color: #dc3545;
            font-weight: bold;
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Product Management</h2>
            <a href="add_product.php" class="btn btn-primary">Add New Product</a>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-10">
                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <!-- Previous Page -->
                        <li class="page-item <?= $previous <= 0 ? 'disabled' : '' ?>">
                            <a class="page-link" href="product.php?page=<?= $previous ?>" aria-label="Previous">
                                <span aria-hidden="true">« Previous</span>
                            </a>
                        </li>

                        <!-- Page Numbers -->
                        <?php for ($i = 1; $i <= $pages; $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link" href="product.php?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <!-- Next Page -->
                        <li class="page-item <?= $next > $pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="product.php?page=<?= $next ?>" aria-label="Next">
                                <span aria-hidden="true">Next »</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- Records per page limit -->
            <div class="col-md-2 text-center">
                <form method="post" action="product.php?page=<?= $page ?>">
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

        <div class="product-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Model</th>
                        <th>Brand</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th class="actions-column">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                                <td>
                                    <?php if (!empty($row['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="Product Image" class="product-image">
                                    <?php else: ?>
                                        <span class="text-muted">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['model']); ?></td>
                                <td><?php echo htmlspecialchars($row['brand']); ?></td>
                                <td><?php echo htmlspecialchars($row['category']); ?></td>
                                <td>BDT <?php echo number_format($row['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($row['stock']); ?></td>
                                <td class="<?php echo $row['status'] === 'Available' ? 'status-available' : 'status-unavailable'; ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </td>
                                <td>
                                    <a href="edit_product.php?id=<?php echo $row['product_id']; ?>" class="btn btn-sm btn-info">Edit</a>
                                    <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $row['product_id']; ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No products found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                window.location.href = `product.php?delete=${id}&page=<?php echo $page; ?>`;
            }
        }
    </script>
</body>
</html>