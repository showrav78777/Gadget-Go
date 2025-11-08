<?php
session_start();

include '../../includes/session_check.php';
include '../../connection/connection.php';

// Define $isLoggedIn
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

// Get username from session or default
$username = $_SESSION['user_name'] ?? 'User_' . ($_SESSION['user_id'] ?? 'Guest');

// Set the limit of records per page
$limit = isset($_POST["limit-records"]) ? (int)$_POST["limit-records"] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1; // Ensure page is at least 1
$start = ($page - 1) * $limit;

// Fetch iPhone products with pagination
$sql = "SELECT * FROM products WHERE category = 'Smartphones' AND brand = 'iPhone' LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $start, $limit);
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get the total count of iPhone products
$totalResult = $conn->query("SELECT COUNT(product_id) AS total_count FROM products WHERE category = 'Smartphones' AND brand = 'iPhone'");
if (!$totalResult) {
    die("Count query failed: " . $conn->error);
}
$totalCount = $totalResult->fetch_assoc();
$total = $totalCount['total_count'];

// Calculate pagination
$pages = ceil($total / $limit);
$previous = $page - 1;
$next = $page + 1;

// Get min and max price for the price range filter (for all iPhone products, not just the paginated subset)
$priceRangeResult = $conn->query("SELECT MIN(price) AS min_price, MAX(price) AS max_price FROM products WHERE category = 'Smartphones' AND brand = 'iPhone'");
$priceRange = $priceRangeResult->fetch_assoc();
$minPrice = $priceRange['min_price'] ?? 0;
$maxPrice = $priceRange['max_price'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iPhone Price in Bangladesh</title>
    <link rel="preload" href="../../bootstrap.min.css" as="style">
    <link rel="preload" href="../../login_registration/css/navbar.css" as="style">
    <link rel="preload" href="../../login_registration/css/footer.css" as="style">
    <link rel="preload" href="../../style.css" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" as="style">
    
 
    <link rel="preload" href="../../bootstrap.bundle.min.js" as="script">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" as="script">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js" as="script">
    
  
    <link rel="preload" href="MainLogo.jpg" as="image" type="image/jpeg">
    

    <link rel="prefetch" href="../../home/home.php">
    <link rel="prefetch" href="../../login_registration/login_registration.php">
    <link rel="prefetch" href="../../offer/offers.html">
    <link rel="prefetch" href="../../home/store_locator.php">
    <link rel="prefetch" href="../../cart/cart.php">
    <link rel="prefetch" href="../../profile/profile.php">
    <link rel="prefetch" href="../../profile/my_orders.php">
    <link rel="prefetch" href="../../profile/wishlist.php">
    

    <link rel="icon" type="image/x-icon" href="MainLogo.jpg">
    <link rel="stylesheet" href="../../bootstrap.min.css">
    <script src="../../bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="../../login_registration/css/navbar.css">
    <link rel="stylesheet" href="../../login_registration/css/footer.css">
    <link rel="stylesheet" href="../../style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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
        <h1 class="text-center">iPhone Price in Bangladesh</h1>
        <p class="text-center">
            iPhone Price & Deal starts from BDT <?php echo number_format($minPrice); ?> to BDT
            <?php echo number_format($maxPrice); ?> in Bangladesh. Buy the latest iPhone from Gadget & Gear shop.
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
                <div class="d-flex justify-content-between mb-3 align-items-center">
                    <h4>Available iPhone</h4>
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
                                        alt="<?php echo $product['brand'] . ' ' . $product['model']; ?>" loading="lazy">
                                </a>
                                
                                <div class="product-title"><?php echo $product['brand'] . ' ' . $product['model']; ?></div>
                                <div class="product-price">Tk. <?php echo number_format($product['price']); ?></div>
                                <?php if ($product['stock'] > 0): ?>
                                    <div class="product-status available">
                                        In Stock (<?php echo $product['stock']; ?> available)
                                    </div>
                                    <div class="buttons">
                                        <button class="buy-now" onclick="buyNow(<?php echo $product['product_id']; ?>)">Buy Now</button>
                                        <button class="add-to-cart" onclick="addToCart(<?php echo $product['product_id']; ?>)">Add to Cart</button>
                                    </div>
                                <?php else: ?>
                                    <div class="product-status unavailable">
                                        Out of Stock
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No iPhone products found.</p>
                    <?php endif; ?>
                </div>

                <!-- Pagination and Records per Page -->
                <div class="row mt-4">
                    <div class="col-md-10">
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <!-- Previous Page -->
                                <li class="page-item <?= $previous <= 0 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="iphone.php?page=<?= $previous ?>" aria-label="Previous">
                                        <span aria-hidden="true">« Previous</span>
                                    </a>
                                </li>

                                <!-- Page Numbers -->
                                <?php for ($i = 1; $i <= $pages; $i++): ?>
                                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                        <a class="page-link" href="iphone.php?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Next Page -->
                                <li class="page-item <?= $next > $pages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="iphone.php?page=<?= $next ?>" aria-label="Next">
                                        <span aria-hidden="true">Next »</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>

                    <!-- Records per page limit -->
                    <div class="col-md-2 text-center">
                        <form method="post" action="iphone.php?page=<?= $page ?>">
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

                <div>
                    <p style="text-align: left; margin-left: 1rem;">Total Products: <?php echo $total; ?></p>
                </div>
            </div>
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
        <p>Copyright &copy; 2025 Gadget & Go. com &nbsp; &nbsp; &nbsp; &nbsp; <img src="PayPic.jpg" alt="Logo"></p>
    </div>


</body>

</html>