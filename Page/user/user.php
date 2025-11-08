<?php
// Bắt đầu session (phù hợp với hệ thống đăng nhập của chúng ta)
session_start();

// Kiểm tra đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../admin/signin.php');
    exit;
}

include __DIR__ . '/../../admin/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="http://localhost/CuoiKy_LTW/">

    <!-- Reset Css -->
    <link rel="stylesheet" href="./Page/home/assets/css/reset.css">

    <!-- Bootstrap Css -->
    <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
    href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap"
    rel="stylesheet"
    />
    <!-- Bootstrap Icons Css -->
    <link
    rel="stylesheet"
    href="./node_modules/bootstrap-icons/font/bootstrap-icons.css"
    />
    <!-- Custom Home Css -->
    <link rel="stylesheet" href="./Page/home/assets/css/style.css" />
    <!-- Custom User Css -->
    <link rel="stylesheet" href="./Page/user/assets/css/user.css" />
    <!-- Breakpoint Css -->
    <link rel="stylesheet" href="./Page/home/assets/css/breakpoint.css" />
    <title>User</title>
</head>
<body>
    <!-- Header -->
    <?php
        require_once __DIR__ . '/../home/includes/header.php';
    ?>
    <main>
    
        <div class="userContainer">
                <?php
        require_once __DIR__ . '/../home/includes/Menu.php';
    ?>
        
            <div class="userMain">
                <!-- Left content -->
                <div class="userLeft">
                    <div class="userContent">
                        <h1>Thông tin người dùng</h1>
                        
                    </div>
                    <div class="userForm">
                        <legend>Thông tin cá nhân của bạn</legend>
                        <form id="update-info-form">
                            <div class="form_group">
                                <label for="username">Tên đăng nhập</label>
                                <input type="text" id="username" name="username" value="<?php echo isset($_SESSION['user_username']) ? htmlspecialchars($_SESSION['user_username']) : ''; ?>" required>
                            </div>
            
                            <div class="form_group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''; ?>" required>
                            </div>
            
                            <div class="form_group">
                                <label for="phone">Số điện thoại</label>
                                <input type="text" id="phone" name="phone" value="<?php echo isset($_SESSION['user_phone']) ? htmlspecialchars($_SESSION['user_phone']) : ''; ?>">
                            </div>
            
                            <div class="form_group">
                                <label for="address">Địa chỉ</label>
                                <input type="text" id="address" name="address" value="<?php echo isset($_SESSION['user_address']) ? htmlspecialchars($_SESSION['user_address']) : ''; ?>">
                            </div>
                            
                            <div class="form-btn"><button class="submit_btn" type="submit">Cập nhật</button></div>
                        </form>
                        <!-- Form đổi mật khẩu riêng -->
                    <div class="userForm" style="margin-top: 30px;">
                        <legend>Đổi mật khẩu</legend>
                        <form id="change-password-form">
                            <div class="form_group">
                                <label for="current_password">Mật khẩu hiện tại</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="form_group">
                                <label for="new_password">Mật khẩu mới</label>
                                <input type="password" id="new_password" name="new_password" required minlength="6">
                            </div>
                            
                            <div class="form_group">
                                <label for="confirm_password">Xác nhận mật khẩu mới</label>
                                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                            </div>
                            
                            <div class="form-btn"><button class="submit_btn" type="submit">Đổi mật khẩu</button></div>
                        </form>
                    </div>
            
                    </div>
                </div>
                <!-- Right content -->
                <div class="userRight">
                    <div class="userRightContent">
                        <a href="./Page/user/user.php">Tài khoản của tôi</a>
                        <a href="./Page/user/order_history.php">Lịch sử đơn hàng</a>
                    </div>
                </div>
                
            </div>
            
        </div>
    
    </main>

<!-- Footer -->
<?php include __DIR__ . '/../home/includes/Footer.php'; ?>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom User JS -->
<script src="./Page/user/assets/js/user.js"></script>
</body>
</html>