<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
include '../../connection/connection.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = $_POST['category'];
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    
    // Handle image upload
    $image_url = "";
    $image_id = "";
    if (!empty($_FILES["image"]["name"])) {
        
        $targetDir = "uploads/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $image_id = uniqid('img_');
        $fileName = $image_id . '_' . basename($_FILES["image"]["name"]);
        $image_url = $targetDir . $fileName;
        
      
        $imageFileType = strtolower(pathinfo($image_url, PATHINFO_EXTENSION));
        $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
        
        if (in_array($imageFileType, $allowTypes)) {
          
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $image_url)) {
              
            } else {
                echo "<script>alert('Sorry, there was an error uploading your file.');</script>";
                $image_url = "";
                $image_id = "";
            }
        } else {
            echo "<script>alert('Sorry, only JPG, JPEG, PNG & GIF files are allowed.');</script>";
            $image_url = "";
            $image_id = "";
        }
    }

    // Insert into database 
    $sql = "INSERT INTO products (category, brand, model, description, price, stock, image_url, image_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        echo "<script>alert('Error preparing statement: " . $conn->error . "');</script>";
    } else {
        $stmt->bind_param("ssssdiss", $category, $brand, $model, $description, $price, $stock, $image_url, $image_id);

        if ($stmt->execute()) {
            echo "<script>alert('Product added successfully!'); window.location.href='product.php';</script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }
}

// Fetch categories for dropdown
$categories = ["Smartphones", "PC", "Camera", "Watches", "Headphone & Speaker", "Gadgets", "Phone Accecorries"];

// Fetch brands based on category 
$brands = [
    "Smartphones" => ["iPhone", "Samsung", "Xiaomi", "Oppo", "Vivo", "Realme"],
    "PC" => ["desktop","laptop","monitor"],
    "Camera" => ["action","dslr","mirrorless"],
    "Watches" => ["fitness_tracker", "smartwatch"],
    "Headphone & Speaker" => ["Airpods", "Speaker", "Headphone", "Earphone", "Neckband"],
    "Gadgets" => ["Smart Home Devices", "Drones", "Wearable Tech"],
    "Phone Accecorries"=> ["Phone Case", "Phone Charger" , "Phone Holder"]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { display: flex; margin: 0; }
        .sidebar { width: 250px; background: #111; color: white; height: 100vh; padding: 20px; }
        .sidebar a { color: white; display: block; padding: 10px; text-decoration: none; }
        .sidebar a:hover { background: #575757; }
        .main-content { flex-grow: 1; padding: 20px; overflow-y: auto; height: 100vh; }
        .current-image { max-width: 200px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; padding: 5px; }
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
        <h2>Add New Product</h2>
        <form class="create-product-form" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Category</label>
                <select class="form-select" name="category" id="category" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Brand</label>
                <select class="form-select" name="brand" id="brand" required>
                    <option value="">Select Category First</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Model</label>
                <input type="text" class="form-control" name="model" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="3" required></textarea>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Price</label>
                    <input type="number" step="0.01" class="form-control" name="price" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Stock</label>
                    <input type="number" class="form-control" name="stock" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Product Image</label>
                <input type="file" class="form-control" name="image" accept="image/*" required>
                <small class="text-muted">Recommended size: 800x800 pixels. Max file size: 2MB.</small>
            </div>
            <button type="submit" class="btn btn-primary">Add Product</button>
            <a href="product.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script>
    
        $(document).ready(function() {
            const brands = <?php echo json_encode($brands); ?>;
            
            $('#category').change(function() {
                const category = $(this).val();
                let options = '<option value="">Select Brand</option>';
                
                if (category && brands[category]) {
                    brands[category].forEach(function(brand) {
                        options += `<option value="${brand}">${brand}</option>`;
                    });
                }
                
                $('#brand').html(options);
            });
        });
    </script>
</body>
</html>

