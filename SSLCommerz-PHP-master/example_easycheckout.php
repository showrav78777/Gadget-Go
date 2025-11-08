<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 1200;
$customer_name = $_POST['customer_name'] ?? 'John Doe';
$customer_email = $_POST['customer_email'] ?? 'you@example.com';
$customer_phone = $_POST['customer_phone'] ?? '01711xxxxxx';
$shipping_address = $_POST['address'] ?? 'Dhaka';

require_once(__DIR__ . "/lib/SslCommerzNotification.php");
include("db_connection.php");
include("OrderTransaction.php");

use SslCommerz\SslCommerzNotification;

// Insert order items function
function insertOrderItems($conn, $order_id, $cart_items) {
    $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
    $item_stmt = $conn->prepare($item_sql);
    if (!$item_stmt) throw new Exception("SQL Error: " . $conn->error);

    foreach ($cart_items as $product_id => $quantity) {
        $product_query = $conn->prepare("SELECT price FROM products WHERE product_id = ?");
        $product_query->bind_param("i", $product_id);
        $product_query->execute();
        $product_result = $product_query->get_result();

        if ($row = $product_result->fetch_assoc()) {
            $price = $row['price'];
            $quantity = (int)$quantity;
            if ($quantity <= 0) throw new Exception("Invalid quantity for product ID $product_id: $quantity");

            $item_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
            if (!$item_stmt->execute()) throw new Exception("Insert order item error: " . $item_stmt->error);
        } else {
            throw new Exception("Product ID $product_id not found.");
        }
        $product_query->close();
    }

    $item_stmt->close();
}

if (isset($_POST['direct_pay']) || isset($_POST['confirm_cod'])) {
    $conn_integration->begin_transaction();

    try {
        $is_cod = isset($_POST['confirm_cod']) || ($_POST['payment_method'] ?? '') === 'COD';
        $payment_method = $is_cod ? 'COD' : 'Online';
        $status = $is_cod ? 'Payment Due' : 'Paid';

        $sql_insert = "INSERT INTO orders (customer_name, customer_email, customer_phone, shipping_address, payment_method, amount, order_date, status) 
                       VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
        $stmt = $conn_integration->prepare($sql_insert);
        if (!$stmt) throw new Exception("Prepare error: " . $conn_integration->error);

        $stmt->bind_param("sssssds", $customer_name, $customer_email, $customer_phone, $shipping_address, $payment_method, $amount, $status);
        if (!$stmt->execute()) throw new Exception("Execution error: " . $stmt->error);

        $order_id = $conn_integration->insert_id;

        if (!empty($_SESSION['cart'])) {
            insertOrderItems($conn_integration, $order_id, $_SESSION['cart']);
        } else {
            throw new Exception("Cart is empty.");
        }

        $update_orders = $conn_integration->prepare("UPDATE dashboard SET total_orders = total_orders + 1");
        if (!$update_orders) throw new Exception("Prepare update error: " . $conn_integration->error);
        if (!$update_orders->execute()) throw new Exception("Execute update error: " . $update_orders->error);
        $update_orders->close();

        $conn_integration->commit();
        unset($_SESSION['cart']);

        if ($is_cod) {
            header("Location: http://localhost/g&g/cart/order_success.php");
            exit();
        } else {
            $post_data = [
                'total_amount' => $amount,
                'currency' => "BDT",
                'tran_id' => "SSLCZ_TEST_" . uniqid(),
                'cus_name' => $customer_name,
                'cus_email' => $customer_email,
                'cus_add1' => $shipping_address,
                'cus_city' => "Dhaka",
                'cus_postcode' => "1000",
                'cus_country' => "Bangladesh",
                'cus_phone' => $customer_phone,
                'shipping_method' => "YES",
                'ship_name' => $customer_name,
                'ship_add1' => $shipping_address,
                'ship_city' => "Dhaka",
                'ship_postcode' => "1000",
                'ship_country' => "Bangladesh",
                'product_category' => "Electronic",
                'product_profile' => "general",
                'product_name' => "Gadgets",
                'num_of_item' => "1"
            ];

            $sslcz = new SslCommerzNotification();
            $sslcz->makePayment($post_data, 'hosted');
            
        }
    } catch (Exception $e) {
        $conn_integration->rollback();
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="SSLCommerz">
    <title>Checkout | Gadget & Gear</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }
    </style>
</head>
<body class="bg-light">
<div class="container">
    <div class="py-5 text-center">
        <h2>Complete Your Order</h2>
        <p class="lead">Please review your information before proceeding to payment.</p>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-md-4 order-md-2 mb-4">
            <h4 class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted">Your Order</span>
            </h4>
            <ul class="list-group mb-3">
                <li class="list-group-item d-flex justify-content-between">
                    <span>Total (BDT)</span>
                    <strong><?php echo number_format($amount, 2); ?> TK</strong>
                </li>
            </ul>
        </div>
        <div class="col-md-8 order-md-1">
            <h4 class="mb-3">Billing address</h&nbsp;4>
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="firstName">Full name</label>
                        <input type="text" name="customer_name" class="form-control" id="customer_name" 
                               value="<?php echo htmlspecialchars($customer_name); ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="mobile">Mobile</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">+88</span>
                        </div>
                        <input type="text" name="customer_phone" class="form-control" id="mobile" placeholder="Mobile"
                               value="<?php echo htmlspecialchars($customer_phone); ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email">Email</label>
                    <input type="email" name="customer_email" class="form-control" id="email"
                           value="<?php echo htmlspecialchars($customer_email); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="address">Address</label>
                    <input type="text" name="address" class="form-control" id="address" placeholder="Your shipping address"
                           value="" required>
                </div>

                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label for="country">Country</label>
                        <select class="custom-select d-block w-100" id="country" name="country" required>
                            <option value="Bangladesh" selected>Bangladesh</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="state">City</label>
                        <select class="custom-select d-block w-100" id="state" name="city" required>
                            <option value="Dhaka" selected>Dhaka</option>
                            <option value="Chittagong">Chittagong</option>
                            <option value="Khulna">Khulna</option>
                            <option value="Rajshahi">Rajshahi</option>
                            <option value="Sylhet">Sylhet</option>
                            <option value="Barishal">Barishal</option>
                            <option value="Rangpur">Rangpur</option>
                            <option value="Mymensingh">Mymensingh</option>
                            <option value="Comilla">Comilla</option>
                            <option value="Narayanganj">Narayanganj</option>
                            <option value="Tangail">Tangail</option>
                            <option value="Bogura">Bogura</option>
                            <option value="Joypurhat">Joypurhat</option>
                            <option value="Pabna">Pabna</option>
                            <option value="Jessore">Jessore</option>
                            <option value="Nawabganj">Nawabganj</option>
                            <option value="Natore">Natore</option>
                            <option value="Rupganj">Rupganj</option>
                            <option value="Sirajganj">Sirajganj</option>
                            <option value="Dinajpur">Dinajpur</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="zip">Zip</label>
                        <input type="text" class="form-control" id="zip" name="zip" placeholder="" value="1000" required>
                    </div>
                </div>
                <hr class="mb-4">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="same-address">
                    <input type="hidden" value="<?php echo $amount; ?>" name="amount" id="total_amount" required/>
                    <label class="custom-control-label" for="same-address">Shipping address is the same as my billing address</label>
                </div>
                <hr class="mb-4">
                <div class="mb-3">
                    <label for="payment_method">Payment Method</label>
                    <select class="custom-select d-block w-100" id="payment_method" name="payment_method" required>
                        <option value="Online" selected>Online Payment</option>
                        <option value="COD">Cash on Delivery (COD)</option>
                    </select>
                </div>
                <hr class="mb-4">
                <button type="submit" name="direct_pay" class="btn btn-primary btn-lg btn-block">Proceed to Payment</button>
                <button type="submit" name="confirm_cod" class="btn btn-secondary btn-lg btn-block" value="COD">Confirm Order (COD)</button>

                <a href="http://localhost/g&g/cart/cart.php" class="btn btn-secondary btn-lg btn-block mt-2">Back to Cart</a>
            </form>
        </div>
    </div>

    <footer class="my-5 pt-5 text-muted text-center text-small">
        <p class="mb-1">© 2025 Gadget & Go</p>
    </footer>
</div>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
        crossorigin="anonymous"></script>
</html>