<?php
session_start();
include '../connection/connection.php';

// Check if user is logged in and retrieve username
$username = "Guest"; // Default username
$isLoggedIn = false;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $isLoggedIn = true;

    // Fetch username from the database
    $query = "SELECT username FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $username = $row['username'];
    }
}


// Check if product ID is set in the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $product_id = (int)$_GET['id'];

    // Fetch product details from the database
    $sql = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    

    // Check if product exists
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        // Redirect to a 404 page or show an error message
        header("Location: 404.php");
        exit();
    }
} else {
    // Redirect to a 404 page or show an error message
    header("Location: 404.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['model']); ?> - Product Details</title>
    <link rel="stylesheet" href="../bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="../login_registration/css/navbar.css">
    <link rel="stylesheet" href="../login_registration/css/footer.css">
    <link rel="stylesheet" href="../style.css">

    
    <!-- Load jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script>
        function addToCart(productId) {
            $.ajax({
                url: 'http://localhost/g&g/cart/cart.php',
                method: 'POST',
                data: {
                    add_to_cart: true,
                    product_id: productId,
                    quantity: 1
                },
                success: function (response) {
                    alert('Product added! Cart items: ' + response.cart_count);
                },
                error: function (xhr) {
                    alert('Error: ' + xhr.responseText);
                }
            });
        }

        function buyNow(productId) {
            $.ajax({
                url: 'http://localhost/g&g/cart/cart.php',
                method: 'POST',
                data: {
                    add_to_cart: true,
                    product_id: productId,
                    quantity: 1
                },
                success: function () {
                    window.location.href = 'http://localhost/g&g/cart/cart.php';
                },
                error: function () {
                    alert('Error adding product to cart');
                }
            });
        }
    </script>
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

    <!-- Product Details Section -->
    <div class="container mt-4">
        <h1 class="text-center"><?php echo htmlspecialchars($product['brand'] . ' ' . $product['model']); ?></h1>
        <div class="row">
            <div class="col-md-6">
                <img src="../admin/admin_product/<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['model']); ?>" class="img-fluid">
            </div>
            <div class="col-md-6">
                <h2>Price: Tk. <?php echo number_format($product['price'], 2); ?></h2>
                <p>Brand: <?php echo htmlspecialchars($product['brand']); ?></p>
                <p>Model: <?php echo htmlspecialchars($product['model']); ?></p>
                <p>Category: <?php echo htmlspecialchars($product['category']); ?></p>

                <p>Description: <?php echo htmlspecialchars($product['description']); ?></p>
                <br>
                <div class="buttons">
                    <button class="buy-now" onclick="buyNow(<?php echo $product['product_id']; ?>)">Buy Now</button>
                    <button class="add-to-cart" onclick="addToCart(<?php echo $product['product_id']; ?>)">Add to Cart</button>
                </div>
            </div>
        </div>
<!-- 
        <h3 class="mt-4">Product Specifications</h3>
        <ul class="list-group">
            <li class="list-group-item"><strong>Display:</strong> <?php echo htmlspecialchars($product['display']); ?></li>
            <li class="list-group-item"><strong>Processor:</strong> <?php echo htmlspecialchars($product['processor']); ?></li>
            <li class="list-group-item"><strong>Camera:</strong> <?php echo htmlspecialchars($product['camera']); ?></li>
            <li class="list-group-item"><strong>Features:</strong> <?php echo htmlspecialchars($product['features']); ?></li>
            <li class="list-group-item"><strong>Warranty:</strong> <?php echo htmlspecialchars($product['warranty']); ?></li>
        </ul>
    </div> -->
<br>
<br><br><br><br><br>
   <!--footer-->
   <div class="footer">
        <div class="row">
            <div class="col-3 menu">
                <div class="nav-list">
                    <pre> <li><button type="button" class="btn btn-outline-primary">+0987654321</button></li> <li><h3><a href="#web">Company</a></h3></li> <li><a href="#program">About us</a></li> <li><a href="#course">Our Brands</a></li> <li><a href="#course">Careers</a></li> </pre>
                </div>
            </div>
            <div class="col-2" style="text-align: center;">
                <div class="nav-list">
                    <pre> <li><h4><a href="#web">Help Center</a></h4></li> <li><a href="#program">FAQ</a></li> <li><a href="#course">Support Center</a></li> <li><a href="#course">Payment Security</a></li> </pre>
                </div>
            </div>
            <div class="col-3" style="text-align: center;">
                <div class="nav-list">
                    <pre> <li><h4><a href="#web">Terms & Condition</a></h4></li></ul> <li><p><a href="#program">Terms & Conditions</a></p></li> <li><a href="#course">Privacy Policy</a></li> <li><a href="#course">Cookie Policy</a></li> </pre>
                </div>
            </div>
            <div class="col-2 right">
                <div class="aside">
                    <h5>Newsletter</h5><br>
                    <p>sign up for get latest news and update</p><br>
                    <div class="leftnav"> <input type="text" name="search" id="search" placeholder="Enter your Email">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer">
        <p>Copyright &copy; 2025 Gadget & Go. com &nbsp; &nbsp; &nbsp; &nbsp; <img src="PayPic.jpg" alt="Logo"></p>
    </div>

</body>
</html> 