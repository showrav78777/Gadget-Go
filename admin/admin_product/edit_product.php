<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
include '../../connection/connection.php';

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: product.php");
    exit;
}

$product_id = $_GET['id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = $_POST['category'];
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $status = $stock > 0 ? 'Available' : 'Unavailable';
    
    // Handle image upload if a new image is provided
    $image_url = $_POST['current_image_url'];
    $image_id = $_POST['current_image_id'];
    
    if (!empty($_FILES["image"]["name"])) {
        // Create uploads directory if it doesn't exist
        $targetDir = "uploads/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $image_id = uniqid('img_');
        $fileName = $image_id . '_' . basename($_FILES["image"]["name"]);
        $image_url = $targetDir . $fileName;
        
        // Check file type
        $imageFileType = strtolower(pathinfo($image_url, PATHINFO_EXTENSION));
        $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
        
        if (in_array($imageFileType, $allowTypes)) {
            // Upload file
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $image_url)) {
                // File uploaded successfully
                // Delete old image if it exists and is different
                if (!empty($_POST['current_image_url']) && file_exists($_POST['current_image_url']) && $_POST['current_image_url'] != $image_url) {
                    unlink($_POST['current_image_url']);
                }
            } else {
                echo "<script>alert('Sorry, there was an error uploading your file.');</script>";
                $image_url = $_POST['current_image_url'];
                $image_id = $_POST['current_image_id'];
            }
        } else {
            echo "<script>alert('Sorry, only JPG, JPEG, PNG & GIF files are allowed.');</script>";
            $image_url = $_POST['current_image_url'];
            $image_id = $_POST['current_image_id'];
        }
    }

    // Update product in database
    $sql = "UPDATE products SET 
            category = ?, 
            brand = ?, 
            model = ?, 
            description = ?, 
            price = ?, 
            stock = ?, 
            status = ?, 
            image_url = ?, 
            image_id = ? 
            WHERE product_id = ?";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        echo "<script>alert('Error preparing statement: " . $conn->error . "');</script>";
    } else {
        $stmt->bind_param("ssssdssssi", $category, $brand, $model, $description, $price, $stock, $status, $image_url, $image_id, $product_id);

        if ($stmt->execute()) {
            echo "<script>alert('Product updated successfully!'); window.location.href='product.php';</script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }
}

// Fetch product data
$sql = "SELECT * FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: product.php");
    exit;
}

$product = $result->fetch_assoc();

// Fetch categories for dropdown
$categories = ["Smartphones", "PC", "Camera", "Watches", "Headphone & Speaker", "Gadgets" ,"Phone Accecories"];

// Fetch brands based on category
$brands = [
    "Smartphones" => ["iPhone", "Samsung", "Xiaomi", "Oppo", "Vivo", "Realme"],
    "PC" => ["desktop", "laptop", "monitor"],
    "Camera" => ["action", "dslr", "mirrorless"],
    "Watches" => ["fitness_tracker", "smartwatch"],
    "Headphone & Speaker" => ["Airpods", "Speaker", "Headphone", "Earphone", "Neckband"],
    "Gadgets" => ["Smart Home Devices", "Drones", "Wearable Tech"], 
    "Phone Accecories"=> ["Phone Case", "Phone Charger", "Phone Holder"]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        .edit-product-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 800px;
        }
        .current-image {
            max-width: 200px;
            max-height: 200px;
            margin-bottom: 10px;
            border-radius: 4px;
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
        <h2>Edit Product</h2>
        <form class="edit-product-form" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Category</label>
                <select class="form-select" name="category" id="category" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php echo ($product['category'] === $cat) ? 'selected' : ''; ?>>
                            <?php echo $cat; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Brand</label>
                <select class="form-select" name="brand" id="brand" required>
                    <option value="">Select Brand</option>
                    <?php 
                    if (isset($brands[$product['category']])) {
                        foreach ($brands[$product['category']] as $brand) {
                            echo '<option value="' . $brand . '" ' . 
                                 ($product['brand'] === $brand ? 'selected' : '') . 
                                 '>' . $brand . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Model</label>
                <input type="text" class="form-control" name="model" value="<?php echo htmlspecialchars($product['model']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="3" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Price</label>
                    <input type="number" step="0.01" class="form-control" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Stock</label>
                    <input type="number" class="form-control" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Product Image</label>
                <?php if (!empty($product['image_url'])): ?>
                    <div>
                        <p>Current Image:</p>
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="Current Product Image" class="current-image">
                    </div>
                <?php endif; ?>
                <input type="file" class="form-control mt-2" name="image" accept="image/*">
                <small class="text-muted">Leave empty to keep current image. Recommended size: 800x800 pixels. Max file size: 2MB.</small>
                <input type="hidden" name="current_image_url" value="<?php echo htmlspecialchars($product['image_url']); ?>">
                <input type="hidden" name="current_image_id" value="<?php echo htmlspecialchars($product['image_id']); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Update Product</button>
            <a href="product.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script>
        // Populate brands based on selected category
        $(document).ready(function() {
            const brands = <?php echo json_encode($brands); ?>;
            const currentBrand = "<?php echo $product['brand']; ?>";
            
            $('#category').change(function() {
                const category = $(this).val();
                let options = '<option value="">Select Brand</option>';
                
                if (category && brands[category]) {
                    brands[category].forEach(function(brand) {
                        const selected = brand === currentBrand ? 'selected' : '';
                        options += `<option value="${brand}" ${selected}>${brand}</option>`;
                    });
                }
                
                $('#brand').html(options);
            });
        });
    </script>
</body>
</html> 