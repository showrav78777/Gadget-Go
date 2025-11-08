<?php
/*
 * This file contains the configuration for SSLCommerz payment gateway
 */

if (!defined('PROJECT_PATH')) {
    define('PROJECT_PATH', 'http://localhost/g&g'); // replace this value with your project path
}

if (!defined('IS_SANDBOX')) {
    define('IS_SANDBOX', true); // 'true' for sandbox, 'false' for live
}

if (!defined('STORE_ID')) {
    define('STORE_ID', 'niloy6616f30528c00'); // your store id without @ssl
}

if (!defined('STORE_PASSWORD')) {
    define('STORE_PASSWORD', 'niloy6616f30528c00@ssl'); // your store password.
}

function env($key, $default = null) {
    if ($key === 'SSLCZ_TESTMODE') {
        return IS_SANDBOX;
    }
    return $default;
}

return [
    'projectPath' => PROJECT_PATH,
    'apiDomain' => IS_SANDBOX ? "https://sandbox.sslcommerz.com" : "https://securepay.sslcommerz.com",
    'apiCredentials' => [
        'store_id' => STORE_ID,
        'store_password' => STORE_PASSWORD,
    ],
    'apiUrl' => [
        'make_payment' => "/gwprocess/v4/api.php",
        'transaction_status' => "/validator/api/merchantTransIDvalidationAPI.php",
        'order_validate' => "/validator/api/validationserverAPI.php",
        'refund_payment' => "/validator/api/merchantTransIDvalidationAPI.php",
        'refund_status' => "/validator/api/merchantTransIDvalidationAPI.php",
    ],
    
    'connect_from_localhost' => true,
    'success_url' => '/g&g/cart/order_success.php',
    'failed_url' => '/g&g/cart/cart.php',
    'cancel_url' => '/g&g/cart/cart.php',
    'ipn_url' => '/g&g/SSLCommerz-PHP-master/ipn.php',
];
