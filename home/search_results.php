<?php 
// Include database connection
include '../connection/connection.php';
session_start();

// Default username in case session doesn't exist 
$username = "Guest";
$isLoggedIn = false;

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $isLoggedIn = true;
    if (isset($conn) && $conn) {
        try {
            $query = "SELECT username FROM users WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $username = $row['username'];
            }
        } catch (Exception $e) {
            $username = "User";
        }
    }
}

// Process search query
$search_results = [];
$no_results = false;

if (isset($_GET['query']) && !empty($_GET['query'])) {
    $search_term = $_GET['query'];
    
    // Sanitize the search term
    $search_term = htmlspecialchars($search_term);
    
    // Check if connection exists
    if (isset($conn) && $conn) {  //%iphone%
        try { 
            // Updated search query to match your database structure
            $search_pattern = '%' . $conn->real_escape_string($search_term) . '%';
            $sql = "SELECT * FROM products WHERE 
                    model LIKE '$search_pattern' OR 
                    brand LIKE '$search_pattern' OR 
                    category LIKE '$search_pattern' OR 
                    description LIKE '$search_pattern'";
            
            // Log the query for debugging
            error_log("Search query: " . $sql);
            
            $result = $conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $search_results[] = $row;
                }
            } else {
                $no_results = true;
            }
        } catch (Exception $e) {
            // Log the error for debugging
            error_log("Search error: " . $e->getMessage());
            $error_message = "Sorry, we encountered an error while searching: " . $e->getMessage();
        }
    } else {
        $error_message = "Database connection error. Please check your connection settings.";
        error_log("Database connection failed in search_results.php");
    }
} else {
    // No search term provided
    $no_results = true;
}

// Debug information
error_log("Search term: " . (isset($search_term) ? $search_term : "None"));
error_log("Results count: " . count($search_results));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Gadget & Go</title>
    <link rel="stylesheet" href="../../bootstrap.min.css">
    <script src="../../bootstrap.bundle.min.js"></script>
    <link rel="stylesheet"href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"/>
    <link rel="stylesheet" href="../login_registration/css/navbar.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../login_registration/css/footer.css">
    <style>
        .search-results-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 15px;
        }
        
        .search-header {
            margin-bottom: 30px;
        }
        
        .search-header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .search-count {
            color: #666;
            font-size: 16px;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }
        
        .product-card {
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .product-image {
            height: 200px;
            overflow: hidden;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.05);
        }
        
        .product-info {
            padding: 15px;
        }
        
        .product-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        
        .product-price {
            color: #ff7722;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .product-description {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .view-button {
            display: block;
            background-color: #ff7722;
            color: white;
            text-align: center;
            padding: 10px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .view-button:hover {
            background-color: #e65c00;
        }
        
        .no-results {
            text-align: center;
            padding: 50px 0;
        }
        
        .no-results h2 {
            color: #333;
            margin-bottom: 15px;
        }
        
        .no-results p {
            color: #666;
            margin-bottom: 20px;
        }
        
        .category-suggestions {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }
        
        .category-link {
            background-color: #f5f5f5;
            color: #333;
            padding: 8px 15px;
            border-radius: 20px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .category-link:hover {
            background-color: #e0e0e0;
        }
        
        .debug-info {
            margin-top: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
</head>

<body>
  <!-- Top Navigation Bar -->
  <div class="top-navbar">
        <div class="logo">
            <a href="http://localhost/g&g/home/home.php">
                <img src="img/MainLogo.jpg" alt="Logo">
            </a>
        </div>


        <form class="search-bar" action="http://localhost/g&g/home/search_results.php" method="GET">
            <input type="text" name="query" placeholder="Search products" required>
            <button type="submit"><i class="fa fa-search"></i></button>
        </form>

        <div class="user-menu">
            <a href="http://localhost/g&g/offer/offers.html"><i class="fa fa-tag"></i> Eid Offer</a>
            <a href="http://localhost/g&g/home/store_locator.php"><i class="fa fa-map-marker-alt"></i> Store Locator</a>
            <a href="http://localhost/g&g/cart/cart.php"><i class="fa fa-shopping-cart"></i></a>

            <?php if ($isLoggedIn): ?>
                <!-- Show user profile dropdown if logged in -->
                <div class="user-profile">
                    <a href="http://localhost/g&g/profile/profile.php" class="orange-text"><i class="fa fa-user"></i>
                        <?php echo htmlspecialchars($username); ?></a>
                    <div class="dropdown-menu">
                        <a href="http://localhost/g&g/profile/profile.php"><i class="fa fa-user"></i> My Profile</a>
                        <a href="http://localhost/g&g/profile/my_orders.php"><i class="fa fa-shopping-bag"></i> My Orders</a>
                        <a href="http://localhost/g&g/profile/wishlist.php"><i class="fa fa-heart"></i> Wishlist</a>
                        <a href="http://localhost/g&g/login_registration/logout.php"><i class="fa fa-sign-out-alt"></i>
                            Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Show login link if not logged in -->
                <a href="http://localhost/g&g/login_registration/login_registration.php"><i class="fa fa-user"></i>
                    Login</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Category Navigation -->
    <div class="category-navbar">
        <ul class="nav-links">
            <li class="has-dropdown"><a href="http://localhost/g&g/phone/phone.php">Phones</a>
                <div class="category-dropdown">
                    <a href="http://localhost/g&g/phone/iphone/iphone.php">iphone</a>
                    <a href="http://localhost/g&g/phone/samsung/samsung.php">Samsung</a>
                    <a href="http://localhost/g&g/phone/xiaomi/xiaomi.php">Xiaomi</a>
                </div>
            </li>

            <li class="has-dropdown"><a href="http://localhost/g&g/phone_accessories/phone_accessories.php">Phone Accessories</a>
                <div class="category-dropdown">
                    <a href="http://localhost/g&g/phone_accessories/phone_case/phone_case.php">Phone Case</a>
                    <a href="http://localhost/g&g/phone_accessories/phone_charger/phone_charger.php">Phone
                        Charger</a>
                    <a href="http://localhost/g&g/phone_accessories/phone_holder/phone_holder.php">Phone
                        Holder</a>
                </div>
            </li>
            <li class="has-dropdown"><a href="http://localhost/g&g/pc/pc.php">PC</a>
                <div class="category-dropdown">
                    <a href="http://localhost/g&g/pc/laptops/laptops.php">Laptop</a>
                    <a href="http://localhost/g&g/pc/desktops/desktops.php">Desktop</a>
                    <a href="http://localhost/g&g/pc/monitors/monitors.php">Monitor</a>
                </div>
            </li>
            <li class="has-dropdown"><a href="http://localhost/g&g/watches/watches.php">Watches</a>
                <div class="category-dropdown">
                    <a href="http://localhost/g&g/watches/smartwatches/smartwatches.php">Smartwatch</a>
                    <a href="http://localhost/g&g/watches/fitness_trackers/fitness_trackers.php">Fitness Tracker</a>
      
                </div>
            </li>
            <li class="has-dropdown">
                <a href="http://localhost/g&g/headphone&speakers/headphone&speaker.php">Headphone & Speaker</a>
                <div class="category-dropdown">
                    <a href="http://localhost/g&g/headphone&speakers/airpods/airpods.php">AirPods</a>
                    <a href="http://localhost/g&g/headphone&speakers/speaker/speaker.php">Speaker</a>
                    <a href="http://localhost/g&g/headphone&speakers/headphone/headphone.php">Headphone</a>
                    <a href="http://localhost/g&g/headphone&speakers/earphone/earphone.php">Earphone</a>
                    <a href="http://localhost/g&g/headphone&speakers/neckband/neckband.php">Neckband</a>
                </div>
            </li>

            <li class="has-dropdown"><a href="http://localhost/g&g/camera/camera.php">Camera</a>
                <div class="category-dropdown">
                    <a href="http://localhost/g&g/camera/dslr/dslr.php">DSLR</a>
                    <a href="http://localhost/g&g/camera/mirrorless/mirrorless.php">Mirrorless</a>
                    <a href="http://localhost/g&g/camera/action/actioncamera.php">Action Camera</a>
                </div>
            </li>
            <li class="has-dropdown"><a href="http://localhost/g&g/gadgets/gadgets.php">Gadget</a>
                <div class="category-dropdown">
                    <a href="http://localhost/g&g/gadgets/smart_home_devices/smart_home_devices.php">Smart Home
                        Devices</a>
                    <a href="http://localhost/g&g/gadgets/drones/drones.php">Drones</a>
                    <a href="http://localhost/g&g/gadgets/wearable_tech/wearable_tech.php">Wearable Tech</a>
                </div>
            </li>

        </ul>
    </div>
    <!-- Search Results Content -->
    <div class="search-results-container">
        <div class="search-header">
            <h1>Search Results for "<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>"</h1>
            <div class="search-count">
                <?php echo count($search_results); ?> products found
            </div>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
                
                <!-- Connection debugging info -->
                <div style="margin-top: 20px; padding: 10px; background: #f8f9fa; border: 1px solid #ddd;">
                    <h4>Connection Debugging</h4>
                    <p>Connection variable exists: <?php echo isset($conn) ? 'Yes' : 'No'; ?></p>
                    <p>Connection is valid: <?php echo (isset($conn) && $conn) ? 'Yes' : 'No'; ?></p>
                    <p>Include path: <?php echo '../connection/connection.php'; ?></p>
                    <p>Current directory: <?php echo getcwd(); ?></p>
                </div>
            </div>
        <?php elseif ($no_results): ?>
            <div class="no-results">
                <h2>No products found</h2>
                <p>We couldn't find any products matching your search. Try different keywords or browse our categories.</p>
                <div class="category-suggestions">
                    <a href="http://localhost/g&g/phone/phone.php" class="category-link">Phones</a>
                    <a href="http://localhost/g&g/pc/laptops/laptops.php" class="category-link">Laptops</a>
                    <a href="http://localhost/g&g/watches/smartwatches/smartwatches.php" class="category-link">Smartwatches</a>
                    <a href="http://localhost/g&g/headphone&speakers/headphone/headphone.php" class="category-link">Headphones</a>
                    <a href="http://localhost/g&g/camera/camera.php" class="category-link">Cameras</a>
                </div>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($search_results as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="../admin/admin_product/<?php echo htmlspecialchars($product['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['model']); ?>">
                        </div>
                        <div class="product-info">
                            <div class="product-name">
                                <?php echo htmlspecialchars($product['brand'] . ' ' . $product['model']); ?>
                            </div>
                            <div class="product-price">৳<?php echo number_format($product['price'], 2); ?></div>
                        
                            <a href="product_details.php?id=<?php echo $product['product_id']; ?>" class="view-button">View Details</a>
                            
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
       
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="row">
            <div class="col-3 menu">
                <div class="nav-list">
                    <ul>
                        <li><button type="button" class="btn btn-outline-primary">+0987654321</button></li>
                        <li><h3><a href="#">Company</a></h3></li>
                        <li><a href="#">About us</a></li>
                        <li><a href="#">Our Brands</a></li>
                        <li><a href="#">Careers</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-2">
                <div class="nav-list">
                    <ul>
                        <li><h4><a href="#">Help Center</a></h4></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Support Center</a></li>
                        <li><a href="#">Payment Security</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-3">
                <div class="nav-list">
                    <ul>
                        <li><h4><a href="#">Terms & Condition</a></h4></li>
                        <li><a href="#">Terms & Conditions</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Cookie Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-2 right">
                <div class="aside">
                    <h5>Newsletter</h5>
                    <p>Sign up for latest news and updates</p>
                    <div class="leftnav">
                        <input type="text" name="search" id="search" placeholder="Enter your Email">
                    </div>
                </div>
            </div>
        </div>
        <p>Copyright &copy; 2025 Gadget & Go.com &nbsp; &nbsp; &nbsp; &nbsp; <img src="images/PayPic.jpg" alt="Payment Methods"></p>
    </div>
</body>
</html> 