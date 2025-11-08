<?php
session_start();

// Suppress any unintended output until we decide what to send
ob_start();

include '../connection/connection.php';
include '../includes/session_check.php';

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Restore cart from cookie if available and session cart is empty
// if (empty($_SESSION['cart']) && isset($_COOKIE['cart'])) {
//     $cookie_cart = json_decode($_COOKIE['cart'], true);
//     if (is_array($cookie_cart)) {
//         foreach ($cookie_cart as $product_id => $quantity) {
//             if ($quantity > 0) { // Only add positive quantities
//                 $_SESSION['cart'][$product_id] = $quantity; // Add new item
//             }
//         }
//     }
// }

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$username = $_SESSION['user_name'] ?? 'Guest'; // Default to 'Guest' if not set

if (!$isLoggedIn) {
    header('Location: http://localhost/g&g/login_registration/login_registration.php');
    exit();
}

// Debugging: Check the username
if (isset($_SESSION['user_name'])) {
    $username = $_SESSION['user_name'];
} else {
    // Log an error or handle the case where the username is not set
    error_log("Username session variable is not set.");
}


// Handle adding product to cart only if logged in
if ($isLoggedIn && isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];



    if ($product_id > 0 && $quantity > 0) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity; 
        } else {
            $_SESSION['cart'][$product_id] = $quantity; 
        }
    }

    // Update the cookie
    setcookie('cart', json_encode($_SESSION['cart']), time() + (86400 * 30), "/"); // 30 days

    // AJAX response
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        ob_end_clean(); // Clear any buffered output
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'cart_count' => count($_SESSION['cart'])]);
        exit;
    } else {
        ob_end_clean();
        header("Location: cart.php");
        exit;
    }
}

// Handle removing product from cart
if (isset($_GET['remove'])) {
    $product_id = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
    // Update the cookie
    setcookie('cart', json_encode($_SESSION['cart']), time() + (86400 * 30), "/"); // 30 days
    ob_end_clean();
    header("Location: cart.php");
    exit;
}

// Handle updating product quantity in cart
if (isset($_POST['update_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    if ($product_id > 0 && $quantity >= 0) { // Allow quantity to be 0 for removal
        if ($quantity == 0) {
            unset($_SESSION['cart'][$product_id]); // Remove item if quantity is 0
        } else {
            $_SESSION['cart'][$product_id] = $quantity; // Update quantity
        }
    }
    // Update the cookie
    setcookie('cart', json_encode($_SESSION['cart']), time() + (86400 * 30), "/"); // 30 days
    ob_end_clean();
    header("Location: cart.php"); // Redirect to cart to show updated quantities
    exit;
}

// Fetch cart items from database
$cart_items = [];
$total = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($product = $result->fetch_assoc()) {
            $product['quantity'] = $quantity;
            $product['subtotal'] = $product['price'] * $quantity;
            $cart_items[] = $product;
            $total += $product['subtotal'];
        }
        $stmt->close();
    }
}

// Output buffering ends here for HTML rendering
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="icon" type="image/x-icon" href="../home/MainLogo.jpg">
    <link rel="stylesheet" href="../bootstrap.min.css">
    <script src="../bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"/>
    <link rel="stylesheet" href="../login_registration/css/navbar.css">
    <link rel="stylesheet" href="../login_registration/css/footer.css">
    <link rel="stylesheet" href="cart.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</head>
<body>

     <!-- Top Navigation Bar -->
     <div class="top-navbar">
        <div class="logo">
            <a href="http://localhost/g&g/home/home.php">
                <img src="MainLogo.jpg" alt="Logo">
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
<!-- <div class="breadcrumb">
        <a href="http://localhost/g&g/home/home.php">Home</a>
    </div> -->
    <br>
    <br>
    <div class="cart-container">
        <div class="cart-items">
            <?php if (empty($cart_items)): ?>
                <div class="empty-cart">
                    <h2>Your cart is empty</h2>
                    <a href="http://localhost/g&g/home/home.php">Continue Shopping</a>
                </div>
            <?php else: ?>
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <img src="../admin/admin_product/<?php echo htmlspecialchars($item['image_url']); ?>" 
                        alt="<?php echo htmlspecialchars($item['model']); ?>" class="item-image">
                        <div class="item-details">
                            <h3 class="item-title"><?php echo htmlspecialchars($item['model'] ); ?></h3>
                            <p class="item-brand"><?php echo htmlspecialchars($item['brand']); ?></p>
                            <p class="item-price">BDT <?php echo number_format($item['price'], 2); ?></p>
                            <div class="quantity-controls">
                                <form method="POST" action="cart.php">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" class="form-control">
                                    <button type="submit" name="update_cart" class="update-btn" >Update</button>
                                </form>
                            </div>
                        </div>
                        <div class="item-subtotal">
                            BDT <?php echo number_format($item['subtotal'], 2); ?>
                        </div>
                        <a href="?remove=<?php echo $item['product_id']; ?>" class="btn btn-danger">Remove</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="cart-summary">
            <h2 class="summary-title">Order Summary</h2>
            <div class="summary-row">
                <span>Subtotal</span>
                <span>BDT <?php echo number_format($total, 2); ?></span>
            </div>
            <div class="summary-row">
                <span>Shipping</span>
                <span>BDT 100.00</span>
            </div>
            <div class="summary-total">
                <span>TOTAL</span>
                <span>BDT <?php echo number_format($total + 100, 2); ?></span>
            </div>
            <?php if (!empty($cart_items)): ?>
                <form method="POST" action="http://localhost/g&g/SSLCommerz-PHP-master/example_easycheckout.php">
                    <input type="hidden" name="amount" value="<?php echo $total + 100; ?>">
                    <input type="hidden" name="customer_name" value="<?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Guest'; ?>">
                    <input type="hidden" name="customer_email" value="<?php echo isset($_SESSION['user_email']) ? $_SESSION['user_email'] : ''; ?>">
                    <input type="hidden" name="customer_mobile" value="<?php echo isset($_SESSION['user_phone']) ? $_SESSION['user_phone'] : ''; ?>">
                    <button type="submit" class="checkout-btn">Proceed to Checkout</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

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
        <p>Copyright &copy; 2025 Gadget & Go. com &nbsp; &nbsp; &nbsp; &nbsp; <img src="../home/PayPic.jpg" alt="Logo"></p>
    </div>

    
</body>
</html> 