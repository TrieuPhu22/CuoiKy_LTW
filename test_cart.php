<?php
session_start();

// Test thêm sản phẩm
$_SESSION['cart'][1] = [
    'id' => 1,
    'name' => 'Test Product',
    'price' => 100000,
    'quantity' => 1,
    'image' => 'test.jpg'
];

echo '<pre>';
print_r($_SESSION['cart']);
echo '</pre>';

echo '<a href="Page/cart/cart.php">Xem giỏ hàng</a>';
?>