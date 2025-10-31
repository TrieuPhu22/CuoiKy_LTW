<?php
// Tệp "Người gác cổng"
// Bắt đầu session
session_start();

// Kiểm tra xem người dùng đã đăng nhập VÀ có phải là Admin không
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    
    // Nếu không, huỷ session (cho chắc)
    session_unset();
    session_destroy();
    
    // "Đá" họ về trang đăng nhập với thông báo lỗi
    header("Location: ../page/home/home.php?error=access_denied");
    exit;
}

// Nếu đúng là Admin, thì không làm gì cả, code trong admin_dashboard.php sẽ tiếp tục chạy.
?>
