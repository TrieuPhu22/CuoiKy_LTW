<?php
// Tệp header này được 'include' bởi các tệp đã gọi session_start(),
// nên chúng ta không cần gọi session_start() lại ở đây.
?>
<header class="homepage-header">
    <div class="homepage-container header-flex">
        <!-- Logo/Tên trang web -->
        <a href="index.php" class="header-logo">
            Cửa Hàng Hoa
        </a>
        
        <!-- Menu điều hướng -->
        <nav class="header-nav">
            <a href="index.php" class="nav-item">Trang chủ</a>
            <a href="#" class="nav-item">Sản phẩm</a>
            <a href="#" class="nav-item">Giới thiệu</a>
            <a href="#" class="nav-item">Liên hệ</a>
        </nav>
        
        <!-- Nút Đăng nhập/Đăng ký hoặc Thông tin người dùng -->
        <div class="header-auth">
            
            <!-- Kiểm tra xem có session hợp lệ không -->
            <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_username']) && isset($_SESSION['user_role'])): ?>
                
                <!-- Đã đăng nhập -->
                <span class="nav-item welcome-user">
                    Chào, <?php echo htmlspecialchars($_SESSION['user_username']); ?>!
                </span>
                
                <!-- Nếu là Admin, hiển thị nút vào trang Admin -->
                <?php if ($_SESSION['user_role'] === 'Admin'): ?>
                    <a href="admin_dashboard.php" class="btn btn-secondary btn-sm">Vào Admin</a>
                <?php endif; ?>
                
                <!-- Nút Đăng xuất -->
                <a href="signout.php" class="btn btn-primary btn-sm">Đăng xuất</a>
                
            <?php else: ?>
            
                <!-- Chưa đăng nhập -->
                <a href="signin.php" class="btn btn-secondary btn-sm">Đăng nhập</a>
                <a href="signup.php" class="btn btn-primary btn-sm">Đăng ký</a>
                
            <?php endif; ?>
        </div>
    </div>
</header>

