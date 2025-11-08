<?php


include '../connection/connection.php';


if (isset($_SESSION['user_id'])) {
    header("Location: http://localhost/g&g/home/home.php");
    exit();
}

session_start(); // Default username in case session doesn't exist 

$username = "Guest"; // Only try to get username if user is logged in
$isLoggedIn = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id']; // Check if connection is established 
    $isLoggedIn = true;
    if (isset($conn) && $conn) {
        try { // Get username from database 
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

 $show_cookie_popup = isset($_SESSION['show_cookie_popup']) && $_SESSION['show_cookie_popup'] === true;

// Handle cookie consent
if (isset($_POST['accept_cookies']) ) {
    // Set cookie consent cookie
    setcookie("cookie_consent", "accepted", time() + (86400 * 30), "/"); // 30 days

    if ($isLoggedIn) {
        setcookie("user_name", $username, time() + (86400 * 30), "/"); // 30 days
        

        setcookie("cart", $username, time() + (86400 * 30), "/"); // 30 days
    } else {

        setcookie("cart", "null", time() + (86400 * 30), "/"); // 30 days
    }
    
    // Hide the cookie popup
    $_SESSION['show_cookie_popup'] = false;
    
    // Refresh the page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
} 
// If user declines cookies
else if (isset($_POST['decline_cookies'])) {
 
    if (isset($_COOKIE['cookie_consent'])) {
        setcookie('cookie_consent', '', time() - 3600, '/');
    }
    if (isset($_COOKIE['user_name'])) {
        setcookie('user_name', '', time() - 3600, '/');
    }
    if (isset($_COOKIE['cart'])) {
        setcookie('cart', '', time() - 3600, '/');
    }
    
    // Hide the cookie popup
    $_SESSION['show_cookie_popup'] = false;
    
    // Refresh the page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();

    
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gadget and Go</title>
    <link rel="icon" type="image/x-icon" href="../login_registration/MainLogo.jpg">
    <link rel="stylesheet" href="../bootstrap.min.css">
    <script src="../bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <script src="https://unpkg.com/react@18/umd/react.development.js"></script>
<script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <link rel="stylesheet" href="../login_registration/css/navbar.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../login_registration/css/footer.css">
    
<script>
document.addEventListener('DOMContentLoaded', function() {
        var myIndex = 0; carousel(); 
    function carousel() { 
        var i; 
        var x = document.getElementsByClassName("mySlides"); 
        for (i = 0; i < x.length; i++) { 
            x[i].style.display = "none";

         } myIndex++; 
         if (myIndex > x.length)
          {
            myIndex = 1 

          }
           x[myIndex - 1].style.display = "block";
            setTimeout(carousel, 2500);
         } 
         
    });
   
</script>

</head>

<body>
    <!-- Top Navigation Bar -->
    <div class="top-navbar">
        <div class="logo">
            <a href="http://localhost/g&g/home/home.php">
                <img src="img/MainLogo.jpg" alt="Logo" class="logo-image">
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
                        <a href="http://localhost/g&g/profile/my_orders.php"><i class="fa fa-shopping-bag"></i> My
                            Orders</a>
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


    <!--content-->
    <div class="content section" style="max-width:2000px"> <img class="mySlides animate-right" src="img/eid.png"
            style="width:100%"> <img class="mySlides animate-right" src="img/header pic.png" style="width:100%"> <img
            class="mySlides animate-right" src="img/slide_1.png" style="width:100%"> <img class="mySlides animate-right"
            src="img/slide_2.png" style="width:100%"> <img class="mySlides animate-right" src="img/location.png"
            style="width:100%"> </div>





            <section> <br><br>
    <h1 class="text-center mb-4">Featured Categories</h1>
    <ul class="category-list">
        <li> 
            <a href="http://localhost/g&g/pc/pc.php">
                <div class="sprite pc"></div>
            </a>
            <a href="#">PC</a>
        </li>
        <li> 
            <a href="http://localhost/g&g/watches/watches.php">
                <div class="sprite watches"></div>
            </a>
            <a href="#">Watches</a>
        </li>
        <li> 
            <a href="http://localhost/g&g/camera/camera.php">
                <div class="sprite camera"></div>
            </a>
            <a href="#">Camera</a>
        </li>
        <li> 
            <a href="http://localhost/g&g/phone/phone.php">
                <div class="sprite phones"></div>
            </a>
            <a href="#">Phones</a>
        </li>
        <li> 
            <a href="http://localhost/g&g/gadgets/gadgets.php">
                <div class="sprite gadgets"></div>
            </a>
            <a href="#">Gadgets</a>
        </li>
    </ul>
</section>



    <!-- Featured Products Section -->
    <div class="container mt-4" id="featured-products">
        
        <h2 class="text-center mb-4">Featured Products</h2>
        <div class="row" style="white-space: nowrap; overflow-x: auto;">
            <?php
            // Fetch featured products from the database
            $query = "SELECT * FROM products ORDER BY RAND() LIMIT 30";
            $result = mysqli_query($conn, $query);

            if (!$result) {
                die("Query failed: " . mysqli_error($conn));
            }

            if (mysqli_num_rows($result) > 0): // Check if there are results
                while ($row = mysqli_fetch_assoc($result)):
            ?>
            <div class="col-md-4 d-inline-block mb-4"> <!-- Use d-inline-block for horizontal alignment -->
                <div class="card" style="width: 18rem; display: inline-block; margin-right: 10px; media">
                    <img src="../admin/admin_product/<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['model']); ?>" class="card-img-top" loading="lazy">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($row['brand'] . ' ' . $row['model']); ?></h5>
                        <p class="card-text">Price: Tk. <?php echo number_format($row['price'], 2); ?></p>
                        <a href="http://localhost/g&g/home/product_details.php?id=<?php echo $row['product_id']; ?>" class="btn btn-primary">View Details</a>
                    </div>
                </div>
            </div>
            <?php 
                endwhile; 
            else: // Handle case where no products are found
                echo '<div class="col-12"><p class="text-center">No featured products available at this time.</p></div>';
            endif; 
            ?>
        </div>
    </div>

   <div class="banner">
    <img src="img/home-banner_1320x330_final985.jpeg" alt="Banner" class="banner-image" style="width: 100%; height:100% ;"> 
   </div>

 <!-- <picture class="responsive-img">

<source media="(max-width:464px)" srcset="img\PayPic.jpg">
<img src="img/home-banner_1320x330_final985.jpeg" alt="Banner" class="banner-image" style="width: 100%; height:100% ;"> 

</picture> -->

    <div class="container mt-5">
        <h2 class="mb-4">Welcome to Gadget & Go</h2>
        <p class="mb-4">Apple Authorized Reseller in Bangladesh</p>
        <p>Technology has seamlessly blended into our daily lives, and many tech products have become a part of our home. From smartphones to laptops and wearables, these devices power our day-to-day interactions and tasks. This is where Gadget & Gear comes in, starting its journey in 2011. We're here to bring the best tech gadgets to people in Bangladesh, with customer service that truly cares. We're proud to be Bangladesh's most trusted Apple Authorized Reseller, offering a complete range of Apple products, from the latest iPhone 16 Pro Max to MacBooks and Apple Watches. With Gadget & Gear, you're assured of authentic Apple products, exclusive rewards, and deals that add extra value to every purchase.</p>

        <h3 class="mt-4">Best iPhone Shop</h3>
        <p>Undoubtedly, iPhones stand out for their sleek design, powerful performance, and unmatched user experience when it comes to premium smartphones. Gadget & Gear is the proud Apple Authorized Reseller in Bangladesh. Under the roof of Gadget & Gear we have the widest selection of the latest and official iPhone mobiles such as iPhone 16 series, iPhone 15 series, and iPhone 14 series. So, whether you're looking to upgrade or grab your very first iPhone, we're here to provide official devices, exciting deals, and the best customer support that is going to take your shopping experience to a whole new level!</p>

        <h3 class="mt-4">Best Apple MacBook Store</h3>
        <p>Gadget & Gear has the most comprehensive collection of Apple's MacBook, including the latest MacBook Air and MacBook Pro laptops with the latest chipsets including M4 Pro and M4 Max as of today's date. Being an Apple Authorized Reseller, you can count Gadget & Gear as the best Apple MacBook Store in BD. Not to mention, Apple MacBooks are the top choice for professionals who prioritize performance, durability and a seamless ecosystem. MacBook laptops, including the Air and Pro versions, are powerful enough to run any power-hungry software such as Blender, Cinema 4D, Sketchup and many more. If you want to personalize your latest MacBook with the best configurations suited to your needs, Gadget & Gear ensures you can do that without any hiccups.</p>

        <h3 class="mt-4">Official Apple Store in BD</h3>
        <p>By offering authentic Apple devices and accessories, Gadget & Gear stands apart as Bangladesh's go-to Apple Store for Apple users in Bangladesh. Besides iPhones, MacBooks, and Apple Watches, our wide range of accessories also includes Apple Watch Straps, AirPods, iPhone Cases, MagSafe Chargers, & Charging cables. When you shop from our stores including online stores, you get the added perks of gifts, reward points, and exclusive deals. At Gadget & Gear, we put the customer first, making us the most trusted Apple Authorized Reseller Store in Bangladesh.</p>

        <h3 class="mt-4">Official Mobile Phone Shop</h3>
        <p>While we're widely known as an Apple Authorized Reseller in Bangladesh, Gadget & Gear is also home to an impressive selection of official mobile phones and mobile accessories from brands you love, such as Samsung, Xiaomi, OnePlus, Oppo, Realme, Vivo, HONOR, and Tecno. Gadget & Gear also offers 0% EMI facility and a nationwide delivery system, making us the top mobile phone shop in BD.</p>

        <h3 class="mt-4">Best Computer & Mobile Accessories Shop</h3>
        <p>If you're looking for the best computer & mobile accessories shop in Bangladesh, you are in the right spot because Gadget & Go is here for all your computer & mobile accessories needs. We have a Mac Display, iPad, Tablets, Keyboard, Mouse, Router, Power Surge Protector, Screen protector, phone case, Mac case, earbuds, & charger. So, if you simply want to upgrade your minimalist workstation, or give your handset a new-look Gadget & Go has got you covered.</p>

        <h3 class="mt-4">Best Online Gadget Shop in Bangladesh</h3>
        <p>Gadget & Go is the most premium and multi-branded retailer in Bangladesh. Our services are not only limited to the physical outlets. With our fully operational e-commerce platform, our valued customers can now enjoy the same dependable and time-tested services we have consistently provided, conveniently and seamlessly online. They can also enjoy nationwide delivery once they purchase anything from our websites, and earn reward points. This reward point is an example of the loyalty and how much Gadget & Go care customers.</p>

        <h3 class="mt-4">Why You Should Consider Gadget & Go</h3>
        <p>Choosing Gadget & Go means more than just buying tech gadgets, it's about getting quality, trust and a great shopping experience every time. For our authenticity, transparency, and great customer service we're proudly holding the official title of Apple Authorized Reseller in Bangladesh.</p>

        <p>With 14 years of experience and over 20 locations across Dhaka, we're committed to making shopping easy, reliable and rewarding. At Gadget & Gear, you'll find exclusive benefits like great discounts, flexible EMI options, and loyalty rewards that help you save on future purchases.</p>

        <p>Gadget & Go has a vast selection of high-end gadgets and accessories from some of the most reputable global brands. This impressive lineup includes Apple, Samsung, OnePlus, Xiaomi, Vivo, Oppo, Huawei, HONOR, Realme, Tecno, Meta, Amazon, SanDisk, Baseus, Anker, Tucano, JBL, Bose, Edifier, Marshall, Beats, Sony, MEKO, Harman Kardon, DJI, GoPro, Amazfit, Belkin, UAG, Spigen, TORRAS, and many more.</p>
    </div>

    <!--footer-->
    <div class="footer">
        <div class="row">
            <div class="col-3 menu">
                <div class="nav-list">
                 <li><button type="button" class="btn btn-outline-primary">+0987654321</button>
                </li> <li><h3><a href="#web">Company</a></h3></li>
                 <li><a href="#program">About us</a></li> 
                 <li><a href="#course">Our Brands</a></li> 
                 <li><a href="#course">Careers</a></li> </pre>
                </div>
            </div>
            <div class="col-2" style="text-align: center;">
               <br> <br> <br> 

                <div class="nav-list">
                    <pre> <li><h3><a href="#web">Help Center</a></h3></li> 
                    <li><a href="#program">FAQ</a></li> 
                    <li><a href="#course">Support Center</a></li> 
                    <li><a href="#course">Payment Security</a></li> </pre>
                </div>
            </div>
            <div class="col-3" style="text-align: center;">
            <br> <br> <br> 
                <div class="nav-list">
                    <pre> <li><h3><a href="#web">Terms & Condition</a></h3>
                <li><a href="#course">Privacy Policy</a></li> 
                <li><a href="#course">Cookie Policy</a></li> </pre>
                </div>
            </div>
            <div class="col-2 right">

                <div class="aside">
                    <h5 class="btn btn-outline-primary">Newsletter</h5><br>
                    <p>sign up for get latest news and update</p><br>
                    <div class="leftnav"> <input type="text" name="search" id="search" placeholder="Enter your Email">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer">
        <p>Copyright &copy; 2025 Gadget & Go. com &nbsp; &nbsp; &nbsp; &nbsp; <img src="img/PayPic.jpg" alt="Logo"></p>
    </div>

    <!-- Cookie Consent Popup -->
    <?php if ($show_cookie_popup): ?>
        <div class="cookie-popup">
            <p>This website uses cookies to ensure you get the best experience on our website.
                By continuing to browse, you agree to our use of cookies.</p>
            <div class="cookie-buttons">
                <form method="post">
                    <button type="submit" name="accept_cookies" class="accept-btn">Accept</button>
                    <button type="submit" name="decline_cookies" class="decline-btn">Decline</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</body>

</html>