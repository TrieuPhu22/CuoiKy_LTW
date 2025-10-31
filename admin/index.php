<?php
// Bắt đầu session
session_start();

// Bao gồm tệp kết nối CSDL (ĐÃ SẠCH)
include 'db_connect.php';

// Lấy tất cả sản phẩm từ CSDL
$sql = "SELECT id, name, description, price, stock, image_url FROM products";
$result = $conn->query($sql);

$products = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ - Cửa hàng Hoa</title>
    <!-- Tải jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Tùy chỉnh font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Liên kết đến tệp CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-100">

    <!-- Bao gồm phần Header (Menu) (ĐÃ SẠCH) -->
    <?php include 'header.php'; ?>

    <!-- Phần chính của trang chủ -->
    <main class="homepage-main">
        <div class="homepage-container">
            <!-- Tiêu đề -->
            <h1 class="homepage-title">Chào mừng đến với Cửa hàng Hoa</h1>
            <p class="homepage-subtitle">Những sản phẩm mới nhất của chúng tôi</p>

            <!-- Lưới hiển thị sản phẩm -->
            <div class="product-grid">
                
                <?php if (empty($products)): ?>
                    <p>Hiện chưa có sản phẩm nào để hiển thị.</p>
                <?php else: ?>
                    <!-- Vòng lặp PHP để hiển thị từng sản phẩm -->
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <?php
                                // Xử lý đường dẫn ảnh
                                $imageUrl = htmlspecialchars($product['image_url']);
                                $isExternal = filter_var($imageUrl, FILTER_VALIDATE_URL);
                                $isLocalFile = !$isExternal && !empty($imageUrl) && file_exists($imageUrl);

                                if (!$isExternal && !$isLocalFile) {
                                    $imageUrl = 'https://placehold.co/300x300/E2E8F0/A0AEC0?text=Hoa';
                                }
                                
                                $formattedPrice = number_format($product['price'], 0, ',', '.') . ' VNĐ';
                            ?>
                            <img src="<?php echo $imageUrl; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-card-image">
                            <div class="product-card-content">
                                <h3 class="product-card-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="product-card-description">
                                    <?php echo htmlspecialchars(substr($product['description'], 0, 50)); ?>...
                                </p>
                                <div class="product-card-footer">
                                    <span class="product-card-price"><?php echo $formattedPrice; ?></span>
                                    <button class="btn btn-primary btn-sm">Thêm vào giỏ</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </div>
    </main>

    <!-- Thông báo Toast (Nếu có) -->
    <div id="toast-message" class="toast-message"></div>

    <script>
    $(document).ready(function() {
        // Hàm lấy tham số từ URL
        function getQueryParam(param) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        }

        // Hàm hiển thị thông báo (toast)
        function showToast(message, isSuccess = true) {
            const toast = $('#toast-message');
            toast.text(message);
            toast.removeClass('success error').addClass(isSuccess ? 'success' : 'error');
            toast.addClass('show');
            setTimeout(() => { toast.removeClass('show'); }, 3000);
        }

        const status = getQueryParam('status');
        const message = getQueryParam('message');

        if (status && message) {
            showToast(decodeURIComponent(message), status === 'success');
        }
    });
    </script>

</body>
</html>

