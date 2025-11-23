<?php
// ⭐ THÊM DÒNG NÀY Ở ĐẦU FILE
if (!isset($_SESSION)) {
    session_start();
}
?>
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
        <a href="../CuoiKy_LTW"><img src="./img/favicon.png" alt="Logo" /></a>
    </div>
    <!-- search & login  -->
    <div class="homeSearchLogin">
        <div class="homeSearch">
            <input type="text" name="Search" autocomplete="off" placeholder="Tìm kiếm sản phẩm..." id="search-input" />
            <button id="search-btn" type="button">
                <i class="bi bi-search"></i>
            </button>
            <!-- Dropdown gợi ý -->
            <div id="search-suggestions" class="search-suggestions" style="display: none;">
                    <ul id="suggestions-list"></ul>
                </div>
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
                        <li><a class="dropdown-item" href="Page/user/user.php">Tài khoản của tôi</a></li>
                        <li><a class="dropdown-item" href="admin/signout.php">Đăng xuất</a></li>
                    </ul>
                </div>

            <?php else: ?>

                
                <a href="admin/signin.php" title="Đăng nhập"><i class="bi bi-person-fill"></i></a>

            <?php endif; ?>
        </div>
    </div>
</header>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        const searchInput = $('#search-input');
        const suggestionsList = $('#suggestions-list');
        const suggestionsBox = $('#search-suggestions');

        // ⭐ Xử lý khi nhập vào ô tìm kiếm
        searchInput.on('keyup', function() {
            const keyword = $(this).val().trim();

            if (keyword.length >= 2) {
                $.ajax({
                    url: './api/products.php',
                    method: 'POST',
                    data: { action: 'search', keyword: keyword },
                    dataType: 'json',
                    success: function(response) {
                        suggestionsList.empty();

                        if (response.success && response.data.length > 0) {
                            response.data.forEach(function(product) {
                                const html = `
                                    <li class="suggestion-item" data-id="${product.id}">
                                        <div class="suggestion-content">
                                            <div class="suggestion-text">
                                                <strong>${product.name}</strong>
                                                <p class="suggestion-category">${product.subcategory_name || product.category}</p>
                                                <span class="suggestion-price">${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(product.price)}</span>
                                            </div>
                                        </div>
                                    </li>
                                `;
                                suggestionsList.append(html);
                            });
                            suggestionsBox.show();
                        } else {
                            suggestionsList.html('<li class="suggestion-item">Không tìm thấy sản phẩm</li>');
                            suggestionsBox.show();
                        }
                    }
                });
            } else {
                suggestionsBox.hide();
            }
        });

        // ⭐ Xử lý khi click vào gợi ý
        $(document).on('click', '.suggestion-item', function() {
            const productId = $(this).data('id');
            if (productId) {
                window.location.href = `./Page/products/products.php?id=${productId}`;
            }
        });

        // ⭐ Ẩn gợi ý khi click ra ngoài
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.homeSearch').length) {
                suggestionsBox.hide();
            }
        });

        // ⭐ Xử lý khi click nút search
        $('#search-btn').on('click', function() {
            const keyword = searchInput.val().trim();
            if (keyword.length >= 2) {
                window.location.href = `./Page/search/search.php?q=${encodeURIComponent(keyword)}`;
            }
        });

        // ⭐ Xử lý khi nhấn Enter
        searchInput.on('keypress', function(e) {
            if (e.which === 13) {
                const keyword = $(this).val().trim();
                if (keyword.length >= 2) {
                    window.location.href = `./Page/search/search.php?q=${encodeURIComponent(keyword)}`;
                }
            }
        });
    });
</script>