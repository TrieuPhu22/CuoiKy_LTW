<?php
// Bắt đầu session (phù hợp với hệ thống đăng nhập của chúng ta)
session_start();

// Bao gồm tệp kết nối CSDL
include __DIR__ . '/../../admin/db_connect.php';

// BƯỚC 1: Lấy tất cả sản phẩm từ CSDL
$sql = "SELECT id, name, description, price, category, image_url FROM products";
$result = $conn->query($sql);

$products_from_db = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products_from_db[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <base href="http://localhost/CuoiKy_LTW/">
    
    <!-- Reset Css -->
    <link rel="stylesheet" href="./Page/home/assets/css/reset.css" />

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
    href="./node_modules/bootstrap-icons/font/bootstrap-icons.css"
    />
    <!-- Breakpoint Css -->
    <link rel="stylesheet" href="./Page/home/assets/css/breakpoint.css" />

    <!-- Custom Css -->
    <link rel="stylesheet" href="./Page/home/assets/css/style.css" />

    <title>Home</title>
</head>
<body>
    <!-- ======== Header ======== -->
    <?php include __DIR__ . '/includes/Header.php'; ?>

    <!-- ======== Main ======== -->
    <main>
    <div class="homeContainer">
        <!-- ========  Menu ======== -->
       <?php include __DIR__ . '/includes/Menu.php'; ?>

        <!-- ========  Banner Slider ======== -->
        <div
        id="carouselExampleAutoplaying"
        class="carousel slide homeBanner"
        data-bs-ride="carousel"
        >
        <div class="carousel-inner">
            <div class="carousel-item active">
            <img
                src="https://in.flowercorner.vn/uploads/P68e4db20f378c2.78650817.webp"
                class="d-block w-100"
                alt="..."
            />
            </div>
            <div class="carousel-item">
            <img
                src="https://in.flowercorner.vn/uploads/P67b80eac1dca11.10889059.webp"
                class="d-block w-100"
                alt="..."
            />
            </div>
            <div class="carousel-item">
            <img
                src="https://in.flowercorner.vn/uploads/P657fd247737038.75342862.webp"
                class="d-block w-100"
                alt="..."
            />
            </div>
            <div class="carousel-item">
            <img
                src="https://in.flowercorner.vn/uploads/P649ea8ef2ed4f0.09844576.webp"
                class="d-block w-100"
                alt="..."
            />
            </div>
        </div>
        <button
            class="carousel-control-prev banner-btn-prev"
            type="button"
            data-bs-target="#carouselExampleAutoplaying"
            data-bs-slide="prev"
        >
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button
            class="carousel-control-next banner-btn-next"
            type="button"
            data-bs-target="#carouselExampleAutoplaying"
            data-bs-slide="next"
        >
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
        </div>
        <!-- ======== Hoa Sinh Nhật ======== -->
        <div class="home-product-section">
        <h2 class="home-product-title">Hoa Sinh Nhật</h2>
        <!-- list product -->
        <div class="home-list-product" id="product-list-birthday">
            <!-- DỮ LIỆU SẼ ĐƯỢC JS ĐỔ VÀO ĐÂY -->
        </div>
        </div>

        <!-- ======== Hoa Khai Trương ======== -->
        <div class="home-product-section">
        <h2 class="home-product-title">Hoa Khai Trương</h2>
        <!-- list product -->
        <div class="home-list-product" id="product-list-opening">
            <!-- DỮ LIỆU SẼ ĐƯỢC JS ĐỔ VÀO ĐÂY -->
        </div>
        </div>
        <!-- ======== Chủ Đề ======== -->
        <div class="home-product-section">
        <h2 class="home-product-title">Chủ Đề</h2>
        <!-- list product -->
        <div class="home-list-product" id="product-list-theme">
            <!-- DỮ LIỆU SẼ ĐƯỢC JS ĐỔ VÀO ĐÂY -->
        </div>
        </div>
        <!-- ======== Thiết Kế ======== -->
        <div class="home-product-section">
        <h2 class="home-product-title">Thiết Kế</h2>
        <!-- list product -->
        <div class="home-list-product" id="product-list-design">
            <!-- DỮ LIỆU SẼ ĐƯỢC JS ĐỔ VÀO ĐÂY -->
        </div>
        </div>
        <!-- ======== Hoa Tươi ======== -->
        <div class="home-product-section">
        <h2 class="home-product-title">Hoa Tươi</h2>
        <!-- list product -->
        <div class="home-list-product" id="product-list-fresh">
            <!-- DỮ LIỆU SẼ ĐƯỢC JS ĐỔ VÀO ĐÂY -->
        </div>
        </div>
    </div>
    </main>

    <!-- ======== Footer ======== -->
    <?php include __DIR__ . '/includes/Footer.php'; ?>

    <!-- BƯỚC 2: Truyền dữ liệu từ PHP sang JavaScript -->
    <script>
        // Biến JavaScript này sẽ chứa tất cả sản phẩm từ CSDL
        const allProductsFromDB = <?php echo json_encode($products_from_db); ?>;
    </script>
    
    <!-- bootstrap js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jquery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- custom js -->
    <script src="./Page/home/assets/js/home_script.js"></script>
</body>
</html>
