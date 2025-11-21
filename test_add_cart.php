<?php
session_start();

echo '<h3>Test Add to Cart API</h3>';

// Giả lập thêm sản phẩm
$_POST['action'] = 'add';
$_POST['product_id'] = 1;
$_POST['name'] = 'Test Hoa Hồng';
$_POST['price'] = 150000;
$_POST['quantity'] = 2;
$_POST['image'] = 'uploads/test.jpg';

echo '<h4>Request Data:</h4>';
echo '<pre>';
print_r($_POST);
echo '</pre>';

// Include cart API
ob_start();
include 'api/cart.php';
$response = ob_get_clean();

echo '<h4>API Response:</h4>';
echo '<pre>' . $response . '</pre>';

echo '<h4>Session Cart:</h4>';
echo '<pre>';
print_r($_SESSION['cart']);
echo '</pre>';

echo '<br><a href="Page/cart/cart.php">Xem giỏ hàng</a>';
?>