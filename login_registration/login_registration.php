<?php

session_start();

include '../connection/connection.php';
// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: http://localhost/g&g/home/home.php");
    exit();
}

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Validate input
    if (empty($email) || empty($password)) {
        $login_error = "Please enter both email and password";
    } else {
        // Check user credentials in database
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['username'];
                $_SESSION['user_email'] = $user['email'];
                
                // Restore cart from cookie if it exists
                if (isset($_COOKIE['cart'])) {
                    $cookie_cart = json_decode($_COOKIE['cart'], true);
                    if (is_array($cookie_cart)) {
                        foreach ($cookie_cart as $product_id => $quantity) {
                            if ($quantity > 0) {
                                $_SESSION['cart'][$product_id] = $quantity; // Restore cart items
                            }
                        }
                    }
                }

                // Update the cookie to reflect the current session cart
                setcookie('cart', json_encode($_SESSION['cart']), time() + (86400 * 30), "/"); // 30 days
                
                // Additional cookie if remember me is checked
                if (isset($_POST['remember']) && $_POST['remember'] == 'on') {
                    setcookie("user_remember", $user['username'], time() + (86400 * 30), "/");
                }
                
                // Create a flag to show the cookie consent popup
                $_SESSION['show_cookie_popup'] = true;
                
                // Redirect to the page they were trying to access
                if (isset($_SESSION['redirect_after_login'])) {
                    $redirect = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                    header("Location: " . $redirect);
                } else {
                    header("Location: ../home/home.php");
                }
                exit();
            } else {
                $login_error = "Invalid email or password";
            }
        } else {
            $login_error = "Invalid email or password";
        }
    }
}

// Process registration form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate input
    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $register_error = "Please fill in all fields";
    } elseif ($password != $confirm_password) {
        $register_error = "Passwords do not match";
    } else {
        // Check if email already exists
        $check_sql = "SELECT * FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $register_error = "Email already exists";
        } else {
            // Hash password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Changed 'name' to 'username' to match the database structure
            $sql = "INSERT INTO users (username, email, phone, password) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);
            
            if ($stmt->execute()) {
                $register_success = "Registration successful! You can now log in.";
            } else {
                $register_error = "Error: " . $stmt->error;
            }
        }
    }
}

// Default username for navbar
$username = "Guest";
$isLoggedIn = false;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Registration - Gadget & Go</title>
    <link rel="icon" type="image/x-icon" href="../home/images/favicon.ico">
    <link rel="stylesheet" href="../home/bootstrap.min.css">
    <script src="../home/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="login_style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function showLogin() {
            document.getElementById('loginForm').classList.remove('hidden');
            document.getElementById('registerForm').classList.add('hidden');
            document.querySelectorAll('.tab')[0].classList.add('active');
            document.querySelectorAll('.tab')[1].classList.remove('active');
        }

        function showRegister() {
            document.getElementById('loginForm').classList.add('hidden');
            document.getElementById('registerForm').classList.remove('hidden');
            document.querySelectorAll('.tab')[0].classList.remove('active');
            document.querySelectorAll('.tab')[1].classList.add('active');
        }

        function togglePassword(element) {
            const passwordInput = element.previousElementSibling;
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                element.textContent = '🔒';
            } else {
                passwordInput.type = 'password';
                element.textContent = '👁️';
            }
        }

        function my_checkValidity() {
            let mobile = /^\+?(88)?0?1[3456789][0-9]{8}\b/i;
            let email = /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/i;
            let inputVal = $("#email_mobile").val();
            if (inputVal.match(mobile)) {
                $("#notif")
                    .text("Valid mobile: " + inputVal.match(mobile))
                    .css("backgroundColor", "lightgreen");
                return true; // Allow form submission
            } else if (inputVal.match(email)) {
                $("#notif")
                    .text("Valid email: " + inputVal.match(email))
                    .css("backgroundColor", "lightgreen");
                return true; // Allow form submission
            } else {
                $("#notif").text("Not valid!").css("backgroundColor", "red");
                return false; // Prevent form submission
            }
        }

        $(document).ready(function () {
            $('#search-input').keyup(function () {
                let query = $(this).val();
                if (query.length > 2) {
                    $.ajax({
                        url: 'http://localhost/g&g/home/search_suggestions.php',
                        method: 'POST',
                        data: { query: query },
                        success: function (data) {
                            $('#suggestions').html(data);
                            $('#suggestions').show();
                        }
                    });
                } else {
                    $('#suggestions').hide();
                }
            });

            // Hide suggestions when clicking outside
            $(document).click(function (e) {
                if (!$(e.target).closest('.search-bar').length) {
                    $('#suggestions').hide();
                }
            });
        });
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
            <input type="text" name="query" id="search-input" placeholder="Search products" required>
            <button type="submit"><i class="fa fa-search"></i></button>
            <div id="suggestions" class="suggestions"></div>
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
    <div class="login-container">
        <div class="tabs">
            <button class="tab <?php echo !isset($_POST['register']) ? 'active' : ''; ?>" onclick="showLogin()">Login</button>
            <button class="tab <?php echo isset($_POST['register']) ? 'active' : ''; ?>" onclick="showRegister()">Register</button>
        </div>

        <?php if (isset($login_error)): ?>
            <div class="alert alert-danger"><?php echo $login_error; ?></div>
        <?php endif; ?>

        <?php if (isset($register_error)): ?>
            <div class="alert alert-danger"><?php echo $register_error; ?></div>
        <?php endif; ?>

        <?php if (isset($register_success)): ?>
            <div class="alert alert-success"><?php echo $register_success; ?></div>
        <?php endif; ?>

        <form id="loginForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
         class="<?php echo isset($_POST['register']) ? 'hidden' : ''; ?>" onsubmit="return my_checkValidity();">
            <div class="form-group">
                <i class="fas fa-user"></i>
                <input type="text" name="email" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
                <span class="show-password" onclick="togglePassword(this)">👁️</span>
            </div>

            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember me</label>
            </div>

            <div class="forgot-password">
                <a href="#">Forgot Password?</a>
            </div>

            <button type="submit" name="login" class="login-btn">Log In</button>

            <div class="register-link">
                <span>Don't have an account? </span>
                <a href="#" onclick="showRegister()">Register Here</a>
            </div>
        </form>

        <form id="registerForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" 
        
        class="<?php echo !isset($_POST['register']) ? 'hidden' : ''; ?>">
        
        <div class="form-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <i class="fas fa-user"></i>
                <input type="text" name="name" placeholder="Name" required>
            </div>
            <div class="form-group">
                <i class="fas fa-phone"></i>
                <input type="tel" name="phone" placeholder="Phone" required>
            </div>
            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
                <span class="show-password" onclick="togglePassword(this)">👁️</span>
            </div>
            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <span class="show-password" onclick="togglePassword(this)">👁️</span>
            </div>

            <button type="submit" name="register" class="login-btn">Register</button>
        </form>
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
        <p>Copyright &copy; 2025 Gadget & Go.com &nbsp; &nbsp; &nbsp; &nbsp; <img src="../home/images/PayPic.jpg" alt="Payment Methods"></p>
    </div>
</body>
</html>