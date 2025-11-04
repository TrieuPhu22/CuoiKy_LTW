<?php
// Bắt đầu session để có thể truy cập
session_start();

// Xoá tất cả các biến session
$_SESSION = array();

// Huỷ session
session_destroy();

// Chuyển hướng người dùng về trang chủ
header("Location: ../");
exit;
?>