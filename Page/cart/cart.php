<?php
// (Tuỳ chọn) Nếu bạn có dùng session cho giỏ hàng thì bật:
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Chưa đăng nhập -> Lưu URL hiện tại để redirect sau khi đăng nhập
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    
    // Chuyển hướng đến trang đăng nhập
    header('Location: ../../admin/signin.php?redirect=cart');
    exit;
}

// Kết nối database
include __DIR__ . '/../../admin/db_connect.php';

// Lấy giỏ hàng từ database
$userId = intval($_SESSION['user_id']);
$sql = "SELECT 
            c.product_id,
            c.quantity,
            c.price,
            p.name,
            p.image_url as image,
            p.stock,
            (c.quantity * c.price) as subtotal
        FROM carts c
        INNER JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$cart = [];
$cartTotal = 0;

while ($row = $result->fetch_assoc()) {
    $cart[$row['product_id']] = [
        'id' => $row['product_id'],
        'name' => $row['name'],
        'price' => floatval($row['price']),
        'quantity' => intval($row['quantity']),
        'image' => str_replace('../uploads/', 'uploads/', $row['image']), // ✅ SỬA LẠI ĐƯỜNG DẪN
        'stock' => intval($row['stock'])
    ];
    $cartTotal += floatval($row['subtotal']);
}

$stmt->close();
$conn->close();

$shippingFee = 30000;
$discount = 0;
$finalTotal = $cartTotal + $shippingFee - $discount;
?>
<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php
    // Tự động lấy giao thức (http hoặc https)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    
    // Tự động lấy tên máy chủ (localhost hoặc 192.168.1.5)
    $host = $_SERVER['HTTP_HOST'];
    
    // Tên thư mục gốc của dự án
    $project_root = '/CuoiKy_LTW/';
    
    // In ra thẻ <base> động
    echo "<base href='{$protocol}://{$host}{$project_root}'>";
?>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/favicon.png">
    
    <!-- Reset CSS -->
    <link rel="stylesheet" href="Page/cart/assets/css/reset.css" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap"
      rel="stylesheet"
    />

    <!-- Bootstrap CSS -->
    <link
      rel="stylesheet"
      href="node_modules/bootstrap/dist/css/bootstrap.min.css"
    />
    <link
      rel="stylesheet"
      href="node_modules/bootstrap-icons/font/bootstrap-icons.css"
    />

    <!-- Your CSS -->
    
    <link rel="stylesheet" href="Page/cart/assets/css/style.css" />
    <link rel="stylesheet" href="Page/home/assets/css/style.css" />
    <link rel="stylesheet" href="Page/home/assets/css/breakpoint.css"/>

    <title>Giỏ hàng - <?php echo count($cart); ?> sản phẩm</title>
  </head>
  <body>
    <!-- ======== Header (include) ======== -->
      <!-- // includes nằm ở: Page/home/includes/header.php & footer.php
      // Từ /Page/cart/cart.php -> -->
    <?php
      require_once __DIR__ . '/../home/includes/header.php';
    ?>

    <!-- ======== Main ======== -->
    <main class="container my-5 cart-page">
        <?php if (empty($cart)): ?>
            <!-- Giỏ hàng trống -->
            <div class="cart-empty-state">
                <div class="item cart"><i class="bi bi-cart-x"></i></div>
                <div class="item"><h3>Giỏ hàng của bạn đang trống</h3></div>
                <div class="item"><p>Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm</p></div>
                <div class="item">
                  <a href="Page/home/home.php" class="btn btn-primary">
                  <i class="bi bi-arrow-left me-2"></i>
                  <div>Tiếp tục mua sắm</div>
                </a>
              </div>
            </div>
        <?php else: ?>
            <!-- Giỏ hàng có sản phẩm -->
            <div class="row g-4">
                <!-- Cột trái - Danh sách sản phẩm -->
                <div class="col-lg-8">
                    <h2 class="cart-title">Giỏ hàng của bạn</h2>
                    
                    <div class="cart-items-list">
                        <?php foreach ($cart as $productId => $item): 
                            $itemTotal = $item['price'] * $item['quantity'];
                            // ✅ SỬA LẠI ĐƯỜNG DẪN ẢNH
                            $imageUrl = !empty($item['image']) ? $item['image'] : 'img/placeholder.jpg';
                        ?>
                            <div class="cart-item" data-product-id="<?php echo $productId; ?>">
                                <div class="cart-item__image">
                                    <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                                         onerror="this.src='img/placeholder.jpg'">
                                </div>
                                
                                <div class="cart-item__info">
                                    <h5 class="cart-item__name"><?php echo htmlspecialchars($item['name']); ?></h5>
                                    <p class="cart-item__price">Đơn giá: <?php echo number_format($item['price'], 0, ',', '.'); ?>₫</p>
                                    <p class="cart-item__total">Thành tiền: <?php echo number_format($itemTotal, 0, ',', '.'); ?>₫</p>
                                </div>
                                
                                <div class="cart-item__actions">
                                    <div class="quantity-control">
                                        <button class="quantity-btn quantity-minus" title="Giảm số lượng">
                                            <i class="bi bi-dash"></i>
                                        </button>
                                        <input type="number" 
                                               class="quantity-input" 
                                               value="<?php echo $item['quantity']; ?>" 
                                               min="1" 
                                               readonly>
                                        <button class="quantity-btn quantity-plus" title="Tăng số lượng">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                    <button class="btn-remove" title="Xóa sản phẩm">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="cart-actions">
                        <button class="btn btn-outline-secondary" id="clear-cart-btn">
                            <i class="bi bi-trash me-2"></i>Xóa toàn bộ giỏ hàng
                        </button>
                        <a href="Page/home/home.php" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left me-2"></i>Tiếp tục mua sắm
                        </a>
                    </div>
                </div>

                <!-- Cột phải - Tóm tắt đơn hàng -->
                <div class="col-lg-4">
                    <div class="order-summary">
                        <h5 class="order-summary__title">Tóm tắt đơn hàng</h5>
                        
                        <div class="order-summary__row">
                            <span>Tạm tính:</span>
                            <strong><?php echo number_format($cartTotal, 0, ',', '.'); ?>₫</strong>
                        </div>
                        
                        <div class="order-summary__row">
                            <span>Phí vận chuyển:</span>
                            <strong><?php echo number_format($shippingFee, 0, ',', '.'); ?>₫</strong>
                        </div>
                        
                        <div class="order-summary__row">
                            <span>Giảm giá:</span>
                            <strong><?php echo number_format($discount, 0, ',', '.'); ?>₫</strong>
                        </div>
                        
                        <hr>
                        
                        <div class="order-summary__total">
                            <span>Tổng cộng:</span>
                            <strong><?php echo number_format($finalTotal, 0, ',', '.'); ?>₫</strong>
                        </div>
                        
                        <!-- Đảm bảo nút checkout ĐÚNG cấu trúc này -->
                    <button class="btn btn-success w-100" id="checkout-btn" type="button">
                        <i class="bi bi-credit-card me-2"></i>Thanh toán
                    </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- ======== Footer (include) ======== -->
    <?php include __DIR__ . '/../home/includes/Footer.php'; ?>
    
    <!-- jQuery (load trước) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap JS & deps -->  
    <script src="node_modules/bootstrap/dist/js/bootstrap.bundle.js"></script>

    <!-- Custom JS -->
    <script src="Page/cart/assets/js/script.js"></script>
    <script src="./Page/home/assets/js/home_script.js"></script>

    <!-- Thêm Modal xác nhận thanh toán TRƯỚC thẻ đóng </body> -->
<!-- Modal Xác Nhận Thanh Toán -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="checkoutModalLabel">
          <i class="bi bi-credit-card me-2"></i>Xác nhận thanh toán
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3">Bạn có chắc chắn muốn thanh toán đơn hàng này?</p>
        <div class="alert alert-info">
          <strong>Tổng tiền:</strong> <span id="modal-total-price"><?php echo number_format($finalTotal, 0, ',', '.'); ?>₫</span>
        </div>
        <div class="mb-3">
          <label for="customer_name" class="form-label">Tên người nhận <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="customer_name" value="<?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : ''; ?>" required>
        </div>
        <div class="mb-3">
          <label for="customer_phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
          <input type="tel" class="form-control" id="customer_phone" required>
        </div>
        <div class="mb-3">
          <label for="customer_address" class="form-label">Địa chỉ giao hàng <span class="text-danger">*</span></label>
          <textarea class="form-control" id="customer_address" rows="2" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <button type="button" class="btn btn-success" id="confirm-checkout-btn">
          <i class="bi bi-check-circle me-2"></i>Xác nhận thanh toán
        </button>
      </div>
    </div>
  </div>
</div>
  </body>
</html>
