<?php
session_start();

include '../../includes/session_check.php';
include '../../connection/connection.php';

// Define $isLoggedIn
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

// Get username from session or default
$username = $_SESSION['user_name'] ?? 'User_' . ($_SESSION['user_id'] ?? 'Guest');

// Fetch iPhone products
$sql = "SELECT * FROM products WHERE category = 'Phone Accecories' AND brand = 'Phone Case'";
$result = $conn->query($sql);

$products = [];
$minPrice = PHP_FLOAT_MAX;
$maxPrice = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
        $minPrice = min($minPrice, $row['price']);
        $maxPrice = max($maxPrice, $row['price']);
    }
}

if (empty($products)) {
    $minPrice = 0;
    $maxPrice = 0;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phone Case Price in Bangladesh</title>
    <link rel="icon" type="image/x-icon" href="../../login_registration/MainLogo.jpg">
    <link rel="stylesheet" href="../../bootstrap.min.css">
    <script src="../../bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />

    <link rel="stylesheet" href="../../login_registration/css/navbar.css">
    <link rel="stylesheet" href="../../login_registration/css/footer.css">

    <link rel="stylesheet" href="../../style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
$(document).ready(function () {
    $("#priceRange").on("input", function () {
        const currentPrice = $(this).val();
        $("#currentPrice").text(parseInt(currentPrice).toLocaleString());
        filterProducts();
    });

    $("#sortOptions").change(function () {
        const sortType = $(this).val();
        const products = $(".product-card").toArray();
        products.sort(function (a, b) {
            const priceA = parseFloat($(a).attr("data-price"));
            const priceB = parseFloat($(b).attr("data-price"));
            return sortType === "lowToHigh" ? priceA - priceB : priceB - priceA;
        });
        $(".product-container").empty().append(products);
    });

    function filterProducts() {
        const maxPrice = parseFloat($("#priceRange").val());
        $(".product-card").each(function () {
            const productPrice = parseFloat($(this).attr("data-price"));
            $(this).toggle(productPrice <= maxPrice);
        });
    }
});

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
            // Use the response object directly (no JSON.parse needed)
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
    
    <!-- Main Content -->
    <div class="container mt-4">
        <h1 class="text-center">Phone Case Price in Bangladesh</h1>
        <p class="text-center">
            Phone Case Price & Deal starts from BDT <?php echo number_format($minPrice); ?> to BDT
            <?php echo number_format($maxPrice); ?> in Bangladesh. Buy the latest Phone Case from Gadget & Gear shop.
            Browse below and order yours now.
        </p>

        <div class="row">
            <!-- Filters Section -->
            <div class="col-md-3">
                <div class="filter-section">
                    <h4>Price Range</h4>
                    <input type="range" id="priceRange" class="form-range" min="<?php echo $minPrice; ?>"
                        max="<?php echo $maxPrice; ?>" value="<?php echo $maxPrice; ?>">
                    <label>BDT <span id="currentPrice"><?php echo number_format($maxPrice); ?></span></label>

                    <h5 class="mt-3">Availability</h5>
                    <div>
                        <input type="checkbox" id="onlineExclusive"> Online Exclusive<br>
                        <input type="checkbox" id="inStock" checked> In Stock<br>
                        <input type="checkbox" id="outOfStock"> Out of Stock
                    </div>
                </div>
            </div>

            <!-- Products Section -->
            <div class="col-md-9">
                <div class="d-flex justify-content-between mb-3">
                    <h4>Available Phone Case</h4>
                    <select class="form-select w-25" id="sortOptions">
                        <option value="default">Sort by</option>
                        <option value="lowToHigh">Price: Low to High</option>
                        <option value="highToLow">Price: High to Low</option>
                    </select>
                </div>

                <div class="product-container">
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <div class="product-card" data-price="<?php echo $product['price']; ?>">
                                <a href="http://localhost/g&g/home/product_details.php?id=<?php echo $product['product_id']; ?>">
                                    <img src="../../admin/admin_product/<?php echo $product['image_url']; ?>"
                                        alt="<?php echo $product['brand'] . ' ' . $product['model']; ?>">
                                </a>
                                <div class="product-title"><?php echo $product['brand'] . ' ' . $product['model']; ?></div>
                                <div class="product-price">Tk. <?php echo number_format($product['price']); ?></div>
                                <?php if ($product['stock'] > 0): ?>
                                    <div class="product-status available">
                                        In Stock (<?php echo $product['stock']; ?> available)
                                    </div>
                                    <div class="buttons">
                                        <button class="buy-now" onclick="buyNow(<?php echo $product['product_id']; ?>)">Buy
                                            Now</button>
                                        <button class="add-to-cart" onclick="addToCart(<?php echo $product['product_id']; ?>)">Add
                                            to Cart</button>
                                    </div>
                                <?php else: ?>
                                    <div class="product-status unavailable">
                                        Out of Stock
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No Phone Case products found.</p>
                    <?php endif; ?>


                </div>

            </div>

        </div>

    </div>
    <div>
        <p style="text-align: left; margin-left: 36rem;">Total Products: <?php echo count($products); ?></p>
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
        <p>Copyright &copy; 2025 Gadget & Go. com &nbsp; &nbsp; &nbsp; &nbsp; <img src="PayPic.jpg" alt="Logo"></p>
    </div>


</body>

</html>