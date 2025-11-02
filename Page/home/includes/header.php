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
        <a href="../CuoiKy_LTW"><img src="./img/Logo_LTW.jpg" alt="Logo" /></a>
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
            <a href="Page/cart/cart.php"><i class="bi bi-bag-fill"></i></a>
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
                        <li><a class="dropdown-item" href="admin/profile.php">Tài khoản của tôi</a></li>
                        <li><a class="dropdown-item" href="admin/signout.php">Đăng xuất</a></li>
                    </ul>
                </div>

            <?php else: ?>

                
                <a href="admin/signin.php" title="Đăng nhập"><i class="bi bi-person-fill"></i></a>

            <?php endif; ?>
        </div>
    </div>
</header>