<?php
session_start();

// Kiểm tra xem có ID sản phẩm không
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: /CuoiKy_LTW/Page/home/home.php');
    exit;
}

$product_id = intval($_GET['id']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- QUAN TRỌNG: Thêm base tag -->
    <base href="http://localhost/CuoiKy_LTW/">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/favicon.png">
    
    <title>Chi tiết sản phẩm</title>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" />
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" />
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="Page/home/assets/css/reset.css">
    <link rel="stylesheet" href="Page/home/assets/css/style.css">
    <link rel="stylesheet" href="Page/home/assets/css/breakpoint.css">
    <link rel="stylesheet" href="Page/products/assets/css/product-detail.css">
</head>
<body>
    <?php require_once __DIR__ . '/../home/includes/header.php'; ?>

    <!-- Loading Spinner -->
    <div id="loading-spinner" class="loading-spinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Đang tải...</span>
        </div>
    </div>

    <!-- Product Detail Section -->
    <div class="container product-detail-container" id="product-detail-section" style="display: none;">
        <div class="row mt-5">
            <!-- Product Image -->
            <div class="col-md-6">
                <div class="product-image-wrapper">
                    <img id="product-image" src="" alt="Product Image" class="img-fluid product-main-image">
                </div>
            </div>

            <!-- Product Info -->
            <div class="col-md-6">
                <div class="product-info">
                    <h1 id="product-name" class="product-title"></h1>
                    
                    <div class="product-category mb-3">
                        <span class="badge bg-secondary" id="product-category"></span>
                    </div>
                    
                    <div class="product-price mb-4">
                        <h2 id="product-price" class="text-danger"></h2>
                    </div>
                    
                    <div class="product-stock mb-3">
                        <p><strong>Tình trạng:</strong> 
                            <span id="product-stock" class="stock-status"></span>
                        </p>
                    </div>
                    
                    <div class="product-description mb-4">
                        <h5>Mô tả sản phẩm:</h5>
                        <p id="product-description"></p>
                    </div>
                    
                    <!-- Quantity Selector -->
                    <div class="quantity-selector mb-4">
                        <label class="form-label"><strong>Số lượng:</strong></label>
                        <div class="input-group" style="width: 150px;">
                            <button class="btn btn-outline-secondary" type="button" id="decrease-qty">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" class="form-control text-center" id="quantity" value="1" min="1">
                            <button class="btn btn-outline-secondary" type="button" id="increase-qty">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="product-actions">
                        <button class="btn btn-primary btn-lg me-2" id="add-to-cart">
                            <i class="bi bi-cart-plus"></i> Thêm vào giỏ hàng
                        </button>
                        <button class="btn btn-success btn-lg" id="buy-now">
                            <i class="bi bi-bag-check"></i> Mua ngay
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products Section -->
        <div class="related-products mt-5">
            <h3 class="mb-4">Sản phẩm liên quan</h3>
            <div class="row" id="related-products-container">
                <!-- Related products will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Error Section -->
    <div class="container text-center py-5" id="error-section" style="display: none;">
        <h3>Không tìm thấy sản phẩm</h3>
        <p>Sản phẩm bạn tìm kiếm không tồn tại hoặc đã bị xóa.</p>
        <a href="Page/home/home.php" class="btn btn-primary">Quay về trang chủ</a>
    </div>

    <!-- Toast Notification -->
    <div id="toast-message" class="toast-message"></div>

    <?php include __DIR__ . '/../home/includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Pass product ID to JavaScript
        const PRODUCT_ID = <?php echo $product_id; ?>;
        console.log('Product ID loaded:', PRODUCT_ID);
    </script>
    <script src="Page/products/assets/js/product-detail.js"></script>
</body>
</html>