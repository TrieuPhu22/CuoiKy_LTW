<?php
// filepath: c:\xampp\htdocs\CuoiKy_LTW\api\check_session.php
header('Content-Type: application/json; charset=utf-8');

session_start();

$response = [
    'logged_in' => isset($_SESSION['user_id']) && !empty($_SESSION['user_id']),
    'user_id' => $_SESSION['user_id'] ?? null,
    'user_name' => $_SESSION['user_name'] ?? null
];

echo json_encode($response);
?>