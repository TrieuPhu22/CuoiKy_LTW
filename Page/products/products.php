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
    
    <?php
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $project_root = '/CuoiKy_LTW/';
    echo "<base href='{$protocol}://{$host}{$project_root}'>";
    ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/favicon.png">
    
    <title>Chi tiết sản phẩm</title>
    
    <!-- jQuery TRƯỚC TIÊN -->
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
    
    <!-- ✅ THÊM CHATBOT CSS -->
    <link rel="stylesheet" href="Page/home/assets/css/chatbot.css">
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
            <div class="col-md-6">
                <img id="product-image" src="" alt="Product" class="img-fluid rounded shadow">
            </div>
            <div class="col-md-6">
                <h1 id="product-name" class="mb-3"></h1>
                <p class="text-muted mb-2">
                    <span class="badge bg-secondary" id="product-category"></span>
                </p>
                <h2 class="text-danger mb-3" id="product-price"></h2>
                
                <!-- Rating Summary -->
                <div class="mb-3" id="rating-summary">
                    <div class="d-flex align-items-center">
                        <div class="rating-stars-display me-2"></div>
                        <span class="text-muted">(<span id="total-reviews">0</span> đánh giá)</span>
                    </div>
                </div>
                
                <p class="mb-3">
                    <strong>Tình trạng: </strong>
                    <span id="product-stock"></span>
                </p>
                <p class="mb-4" id="product-description"></p>
                
                <div class="mb-4">
                    <label class="form-label"><strong>Số lượng:</strong></label>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-outline-secondary" id="decrease-qty">
                            <i class="bi bi-dash"></i>
                        </button>
                        <input type="number" id="quantity" class="form-control text-center" value="1" min="1" style="width: 80px;">
                        <button class="btn btn-outline-secondary" id="increase-qty">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button class="btn btn-primary btn-lg flex-fill" id="add-to-cart">
                        <i class="bi bi-cart-plus"></i> Thêm vào giỏ hàng
                    </button>
                    <button class="btn btn-success btn-lg flex-fill" id="buy-now">
                        <i class="bi bi-lightning-fill"></i> Mua ngay
                    </button>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="reviews-section mt-5">
            <h3 class="mb-4">Đánh giá sản phẩm</h3>
            
            <!-- Rating Overview -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center border-end">
                            <h1 class="display-4 text-warning mb-0" id="average-rating">0</h1>
                            <div id="average-stars" class="mb-2"></div>
                            <p class="text-muted mb-0"><span id="total-reviews-text">0</span> đánh giá</p>
                        </div>
                        <div class="col-md-9">
                            <div id="rating-breakdown"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reviews List -->
            <div id="reviews-container">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products Section -->
        <div class="related-products mt-5">
            <h3 class="mb-4">Sản phẩm liên quan</h3>
            <div class="row" id="related-products-container"></div>
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
    
    <!-- ✅ INCLUDE CHATBOT NGAY SAU KHI LOAD BOOTSTRAP -->
    <?php include __DIR__ . '/../home/includes/chatbot.php'; ?>
    
    <!-- ✅ PRODUCT JS VỚI EVENT LISTENER -->
    <script>
        const PRODUCT_ID = <?php echo $product_id; ?>;
        console.log('🔍 Product ID loaded:', PRODUCT_ID);
        
        // ✅ GLOBAL FLAG ĐỂ BIẾT CHATBOT ĐÃ READY
        let chatbotReady = false;
        let productJSLoaded = false;
        
        // ✅ LISTEN CHO CHATBOT READY EVENT
        $(document).on('chatbotReady', function() {
            console.log('🎉 Chatbot ready event received on products page');
            chatbotReady = true;
            tryLoadProductJS();
        });
        
        // ✅ DOCUMENT READY
        $(document).ready(function() {
            console.log('📄 Products page DOM ready');
            
            // Kiểm tra xem chatbot đã ready chưa (có thể load trước DOM ready)
            if (typeof window.CHATBOT_READY !== 'undefined' && window.CHATBOT_READY) {
                console.log('✅ Chatbot was already ready');
                chatbotReady = true;
                tryLoadProductJS();
            }
            
            // Fallback: nếu sau 3 giây vẫn chưa có chatbot thì load luôn
            setTimeout(() => {
                if (!chatbotReady) {
                    console.log('⚠️ Chatbot timeout, loading product JS anyway');
                    chatbotReady = true;
                    tryLoadProductJS();
                }
            }, 3000);
        });
        
        function tryLoadProductJS() {
            if (chatbotReady && !productJSLoaded) {
                console.log('🚀 Loading product JavaScript files...');
                productJSLoaded = true;
                
                // Load product detail JS
                $.getScript('Page/products/assets/js/product-detail.js')
                    .done(function() {
                        console.log('✅ Product detail JS loaded');
                    })
                    .fail(function() {
                        console.error('❌ Failed to load product detail JS');
                    });
                    
                // Load home script JS (cho các hàm chung)
                $.getScript('Page/home/assets/js/home_script.js')
                    .done(function() {
                        console.log('✅ Home script JS loaded');
                    })
                    .fail(function() {
                        console.error('❌ Failed to load home script JS');
                    });
            }
        }
    </script>

    <!-- ✅ DEBUG SCRIPT CHO CHAT HISTORY -->
    <script>
        // Test chat history sync sau 2 giây
        setTimeout(() => {
            console.log('🧪 Testing chat history sync on products page...');
            
            const chatHistory = localStorage.getItem('ai_chat_global_history');
            if (chatHistory) {
                const data = JSON.parse(chatHistory);
                console.log('✅ Chat history found:', data.messages.length, 'messages');
                
                // Kiểm tra có products không
                let productsCount = 0;
                data.messages.forEach(msg => {
                    if (msg.products) productsCount += msg.products.length;
                });
                console.log('📦 Products in history:', productsCount);
            } else {
                console.log('ℹ️ No chat history found in localStorage');
            }
            
            // Kiểm tra chatbot elements
            const chatMessages = $('#ai-chat-messages .ai-message').length;
            const chatProducts = $('#ai-chat-products .ai-product-card').length;
            console.log('🎯 Chatbot elements:', {
                messages: chatMessages,
                products: chatProducts
            });
        }, 2000);
    </script>
</body>
</html>