<?php
// Báo cáo lỗi (để debug)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Thông tin kết nối CSDL
$servername = "localhost";
$username = "root";       // Tên người dùng XAMPP mặc định
$password = "";           // Mật khẩu XAMPP mặc định là rỗng
$dbname = "flower_shop";     // Tên CSDL bạn đã tạo
$port = "3307";            // Cổng mặc định của MySQL trên XAMPP
// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Đặt bảng mã thành UTF-8 (Rất quan trọng)
$conn->set_charset("utf8mb4");
?>
