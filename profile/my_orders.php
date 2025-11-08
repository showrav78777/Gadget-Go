<?php
session_start();
include '../connection/connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login_registration/login_registration.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['user_name'] ?? 'User';
$isLoggedIn = true; // Since we already check above that user is logged in




// Fetch user's orders 
$orders = [];
try {
    $orders_query = "SELECT id, order_date, amount, status FROM orders WHERE customer_name = ?";
    $orders_stmt = $conn->prepare($orders_query);
    if (!$orders_stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    $orders_stmt->bind_param("s", $username);
    $orders_stmt->execute();
    $orders_result = $orders_stmt->get_result();
    
    while ($row = $orders_result->fetch_assoc()) {
        $orders[] = $row;
    }
} catch (Exception $e) {
    die("Error fetching orders: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Gadget & Go</title>
    <link rel="stylesheet" href="../bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../login_registration/css/navbar.css">
    <link rel="stylesheet" href="../login_registration/css/footer.css">
    <link rel="stylesheet" href="../style.css">
    <style>
        body { padding: 20px; }
        .order-card {
            background: #fff;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .order-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            text-transform: capitalize;
        }
        .status-not-paid { background-color: #ffecb3; color: #856404; }
        .status-paid { background-color: #b3e6ff; color: #0c5460; }
        .status-processing { background-color: #d1ecf1; color: #0c5460; }
        .status-shipped { background-color: #d4edda; color: #155724; }
        .status-delivered { background-color: #c3e6cb; color: #155724; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
   
    <!-- Top Navigation Bar -->
    <div class="top-navbar">
        <div class="logo">
            <a href="http://localhost/g&g/home/home.php">
                <img src="../home/img/MainLogo.jpg" alt="Logo">
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
                <div class="user-profile">
                    <a href="http://localhost/g&g/profile/profile.php" class="orange-text"><i class="fa fa-user"></i>
                        <?php echo htmlspecialchars($username); ?></a>
                    <div class="dropdown-menu">
                        <a href="http://localhost/g&g/profile/profile.php"><i class="fas fa-user"></i> My Profile</a>
                        <a href="http://localhost/g&g/profile/my_orders.php"><i class="fas fa-shopping-bag"></i> My Orders</a>
                        <a href="http://localhost/g&g/profile/wishlist.php"><i class="fas fa-heart"></i> Wishlist</a>
                        <a href="http://localhost/g&g/login_registration/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="http://localhost/g&g/login_registration/login_registration.php">Login / Register</a>
            <?php endif; ?>
       
        </div>
    </div>

    <!-- Category Navigation -->
    <div class="category-navbar">
        <ul class="nav-links">
            <li class="has-dropdown"><a href="http://localhost/g&g/phone/phone.php">Phones</a>
                <div class="category-dropdown">
                    <a href="http://localhost/g&g/phone/iphone/iphone.php">iPhone</a>
                    <a href="http://localhost/g&g/phone/samsung/samsung.php">Samsung</a>
                    <a href="http://localhost/g&g/phone/xiaomi/xiaomi.php">Xiaomi</a>
                </div>
            </li>
            <li class="has-dropdown"><a href="http://localhost/g&g/phone_accessories/phone_accessories.php">Phone
                    Accessories</a>
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
    <div class="container mt-5">
        <h1>My Orders</h1>
        <p>View and track all your orders</p>
        
        <?php if (empty($orders)): ?>
            <div class="alert alert-info">You don't have any orders yet.</div>
            <a href="../home/home.php" class="btn btn-primary">Continue Shopping</a>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <h3>Order #<?php echo $order['id']; ?></h3>
                    <div class="d-flex justify-content-between">
                        <div>
                            <p>Name: <?php echo $username; ?></p>
                            <p>Date: <?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
                            <p>Amount: Tk. <?php echo number_format($order['amount'], 2); ?></p>
                        </div>
                        <div>
                            <p>Status: 
                                <span class="order-status status-<?php echo str_replace(' ', '-', strtolower($order['status'])); ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Footer -->
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
                    <pre> <li><h4><a href="#web">Terms & Condition</a></h4></li> <li><a href="#course">Privacy Policy</a></li> <li><a href="#course">Cookie Policy</a></li> </pre>
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
        <p>Copyright &copy; 2025 Gadget & Go. com &nbsp; &nbsp; &nbsp; &nbsp; <img src="../home/img/PayPic.jpg" alt="Logo"></p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownToggle = document.querySelector('.dropdown-toggle');
            const dropdownMenu = document.querySelector('.dropdown-menu');
            
            if (dropdownToggle) {
                dropdownToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    dropdownMenu.classList.toggle('show');
                });
            }
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown')) {
                    const menus = document.querySelectorAll('.dropdown-menu');
                    menus.forEach(menu => menu.classList.remove('show'));
                }
            });
        });
    </script>
</body>
</html> 