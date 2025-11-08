<?php
// Bắt đầu session
session_start();

// Nếu đã đăng nhập, chuyển hướng họ đi
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'Admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: ../index.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../img/favicon.png">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <!-- Reset Css -->
    <link rel="stylesheet" href="./assets/css/reset.css">
    

    <!-- Bootstrap Css -->
    <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link
    href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap"
    rel="stylesheet"
    />
    <!-- Bootstrap Icons Css -->
    <link
    rel="stylesheet"
    href="../node_modules/bootstrap-icons/font/bootstrap-icons.css"
    />
    <link rel="stylesheet" href="../Page/home/assets/css/style.css">

    <!-- admin -->
    <link rel="stylesheet" href="./assets/css/style.css">
    <!-- BreakPoint Css -->
        <link rel="stylesheet" href="../Page/home/assets/css/breakpoint.css">


</head>
<body>
    <header>
    <!-- icon social -->
    <div class="homeIconSocial">
        <i class="bi bi-facebook"></i>
        <i class="bi bi-tiktok"></i>
        <i class="bi bi-instagram"></i>
    </div>
    <!-- logo -->
    <div class="homeLogo">
        <!-- Use absolute paths so header works from pages in any subfolder -->
        <a href="../"><img src="../img/Logo_LTW.jpg" alt="Logo" /></a>
    </div>
    <!-- search & login  -->
    <div class="homeSearchLogin">
        <div class="homeSearch">
            <input type="text" name="Search" placeholder="Tìm kiếm" class="" />
            <button>
                <i class="bi bi-search"></i>
            </button>
        </div>
        <div class="Home_cart">
            <a href="../Page/cart/cart.php"><i class="bi bi-bag-fill"></i></a>
        </div>
        <div class="Home_user">
            <?php if (isset($_SESSION['user_username']) && !empty($_SESSION['user_username'])): ?>
                
                <!-- 1. Đã đăng nhập: Hiển thị tên user và link Đăng xuất -->
                <div class="dropdown">
                    <button class="btn btn-link dropdown-toggle text-decoration-none" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: white;">
                        <i class="bi bi-person-fill" style="color: var(--primary-color);
                        display: none;"></i>
                        <?php echo htmlspecialchars($_SESSION['user_username']); ?>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php">Tài khoản của tôi</a></li>
                        <li><a class="dropdown-item" href="signout.php">Đăng xuất</a></li>
                    </ul>
                </div>

            <?php else: ?>

                
                <a href="signin.php" title="Đăng nhập"><i class="bi bi-person-fill"></i></a>

            <?php endif; ?>
        </div>
    </div>
</header>


    <div class="auth-container">
        <form id="signin-form" class="auth-form">
            <h2 class="auth-title">Đăng nhập</h2>
            <p class="auth-subtitle">Chào mừng trở lại! Vui lòng nhập thông tin của bạn.</p>
            
            <!-- Trường ẩn cho hành động -->
            <input type="hidden" name="action" value="signin">

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-signin">Đăng nhập</button>
            </div>
            
            <p class="auth-switch">
                Chưa có tài khoản? <a class="auth-link" href="signup.php">Đăng ký ngay</a>
            </p>
            
            <div id="auth-error" class="auth-error" style="display: none;"></div>
        </form>
    </div>

    <!-- Thông báo Toast -->
    <div id="toast-message" class="toast-message"></div>

    <!-- ======== Footer absolute ======== -->
<div class="footerContact">
    <div class="homeContainer">
        <div class="footerLogo">
            <img src="../img/Logo_LTW.jpg" alt="logo" />
            <p>Hotline: 012345678</p>
            <p>Email: nhom5@gmail.com</p>
        </div>
        <div class="footerCustomerCare">
            <h4>CHĂM SÓC KHÁCH HÀNG</h4>
            <div>
                <a href="">Giới Thiệu</a>
                <a href="">Liên Hệ</a>
            </div>
        </div>
        <div class="footerFollow">
            <h4>THEO DÕI</h4>
            <div>
                <a href="">
                    <i class="bi bi-facebook"></i>
                    <p>FaceBook</p>
                </a>
                <a href="">
                    <i class="bi bi-tiktok"></i>
                    <p>TikTok</p>
                </a>
                <a href="">
                    <i class="bi bi-instagram"></i>
                    <p>Instagram</p>
                </a>
            </div>
        </div>
        <div class="footerAddress">
            <h4>ĐỊA CHỈ</h4>
            <div>
                <b>Trụ sở chính:</b>
                <a
                    href="https://www.google.com/maps/place/Tr%C6%B0%E1%BB%9Dng+%C4%90%E1%BA%A1i+H%E1%BB%8Dc+Giao+Th%C3%B4ng+V%E1%BA%ADn+T%E1%BA%A3i+Th%C3%A0nh+Ph%E1%BB%91+H%E1%BB%93+Ch%C3%AD+Minh+-+C%C6%A1+s%E1%BB%9F+1/@10.8046891,106.7164931,18.86z/data=!4m6!3m5!1s0x3175293dceb22197:0x755bb0f39a48d4a6!8m2!3d10.8045178!4d106.7167175!16s%2Fm%2F02q2__2?entry=ttu&g_ep=EgoyMDI1MTAyMi4wIKXMDSoASAFQAw%3D%3D">02 Võ Oanh, Thạnh Lộc, Mỹ Tây, Thành phố Hồ Chí Minh, Việt Nam</a>
            </div>
        </div>
    </div>
</div>

<!-- ======== Footer ======== -->
<footer>
    <div class="footer">
        <div class="phone"><i class="bi bi-telephone"></i> 0123456789</div>
        <div class="chat"><i class="bi bi-chat-fill"></i> Chat with us</div>
    </div>
</footer>
        <!-- bootstrap js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="./assets/js/signinService.js"></script>
</body>
</html>

