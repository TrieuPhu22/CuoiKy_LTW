<?php
// filepath: c:\xampp12\htdocs\CuoiKy_LTW\Page\home\chu_de\hoa_cam_tay.php
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
        <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/favicon.png">
    <title>Hoa Chúc Mừng</title>
    <?php
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $project_root = '/CuoiKy_LTW/';
    echo "<base href='{$protocol}://{$host}{$project_root}'>";
    ?>
    <link rel="stylesheet" href="./Page/home/assets/css/reset.css" />
    <link rel="stylesheet" href="./node_modules/bootstrap/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="./node_modules/bootstrap-icons/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="./Page/home/assets/css/breakpoint.css">
    <link rel="stylesheet" href="./Page/home/assets/css/style.css" />
    <link rel="stylesheet" href="./Page/home/assets/css/hoa_sinh_nhat_sang_trong.css" />
</head>

<body>
    <?php include __DIR__ . '/../includes/Header.php'; ?>

    <main>
        <div class="homeContainer my-5">
            <?php include __DIR__ . '/../includes/Menu.php'; ?>
            <h1 class="text-center category-title my-3">Hoa Chúc Mừng</h1>
            <hr class="mb-4">

            <div class="row align-items-center mb-4">
                <div class="col-auto">
                    <button id="button-grid" class="btn btn-outline-secondary"><i class="bi bi-grid-3x3-gap"></i></button>
                </div>
                <div class="col"></div>
                <div class="col-auto">
                    <label for="input-sort" class="form-label visually-hidden">Sắp xếp</label>
                    <select id="input-sort" class="form-select sort-select" style="width:220px;">
                        <option value="default">Sắp xếp (Mặc định)</option>
                        <option value="price_asc">Giá: Thấp → Cao</option>
                        <option value="price_desc">Giá: Cao → Thấp</option>
                        <option value="name_asc">Tên: A → Z</option>
                        <option value="name_desc">Tên: Z → A</option>
                        <option value="newest">Mới nhất</option>
                    </select>
                </div>
            </div>

            <div class="home-list-product" id="product-grid">
                <div class="col-12">
                    <p>Đang tải sản phẩm...</p>
                </div>
            </div>


        </div>
    </main>

    <?php include __DIR__ . '/../includes/Footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="./Page/home/assets/js/home_script.js"></script>
    <script>
        $(document).ready(function() {
            let currentSortBy = 'default';  

            // Hàm load sản phẩm
            function loadProducts() {
                $.ajax({
                    url: './api/products.php',
                    method: 'POST',
                    data: { 
                        action: 'get_by_subcategory', 
                        subcategory_id: 2,
                        sort_by: currentSortBy  
                    },  
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data.length > 0) {
                            renderProductList(response.data, '#product-grid');
                        } else {
                            $('#product-grid').html('<div class="col-12"><p>Không có sản phẩm nào.</p></div>');
                        }
                    }
                });
            }

            // Load sản phẩm ban đầu
            loadProducts();

            // Xử lý khi thay đổi sắp xếp
            $('#input-sort').on('change', function() {
                currentSortBy = $(this).val();
                loadProducts();  // Tải lại sản phẩm
            });
        });
    </script>
</body>
</html>