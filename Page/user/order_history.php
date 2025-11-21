<?php
// Bắt đầu session (phù hợp với hệ thống đăng nhập của chúng ta)
session_start();

// Kiểm tra đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../admin/signin.php');
    exit;
}

include __DIR__ . '/../../admin/db_connect.php';

// ✅ LẤY LỊCH SỬ ĐƠN HÀNG
$userId = intval($_SESSION['user_id']);
$sql = "SELECT id, customer_name, customer_phone, customer_address, 
               total_price, status, 
               DATE_FORMAT(order_date, '%d/%m/%Y %H:%i') as formatted_date
        FROM orders 
        WHERE user_id = ? 
        ORDER BY order_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <!-- Reset Css -->
    <link rel="stylesheet" href="./Page/home/assets/css/reset.css">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/favicon.png">
    
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
    <title>Lịch sử đơn hàng</title>
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
                <div class="orderLeft">
                    <div class="userContent">
                        <h1>Lịch sử đơn hàng</h1>
                    </div>
                    
                    <div class="userOrder">
                        <?php if (empty($orders)): ?>
                            <div class="alert alert-info text-center">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-3">Bạn chưa có đơn hàng nào.</p>
                                <a href="Page/home/home.php" class="btn btn-primary mt-2">
                                    <i class="bi bi-shop me-2"></i>Mua sắm ngay
                                </a>
                            </div>
                        <?php else: ?>
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Mã Đơn Hàng</th>
                                        <th scope="col">Người Nhận</th>
                                        <th scope="col">SĐT</th>
                                        <th scope="col">Ngày Đặt</th>
                                        <th scope="col">Tổng Tiền</th>
                                        <th scope="col">Trạng Thái</th>
                                        <th scope="col">Chi tiết</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><strong>#<?php echo $order['id']; ?></strong></td>
                                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                            <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                                            <td><?php echo $order['formatted_date']; ?></td>
                                            <td class="text-danger fw-bold">
                                                <?php echo number_format($order['total_price'], 0, ',', '.'); ?>₫
                                            </td>
                                            <td>
                                                <?php 
                                                $statusClass = 'badge ';
                                                switch($order['status']) {
                                                    case 'Đang xử lý':
                                                        $statusClass .= 'bg-warning text-dark';
                                                        break;
                                                    case 'Đã giao':
                                                        $statusClass .= 'bg-success';
                                                        break;
                                                    case 'Đã hủy':
                                                        $statusClass .= 'bg-danger';
                                                        break;
                                                    default:
                                                        $statusClass .= 'bg-secondary';
                                                }
                                                ?>
                                                <span class="<?php echo $statusClass; ?>">
                                                    <?php echo $order['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary view-order-detail" 
                                                        data-order-id="<?php echo $order['id']; ?>">
                                                    <i class="bi bi-eye"></i> Xem
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Right content -->
                <div class="orderRight">
                    <div class="userRightContent">
                        <a href="./Page/user/user.php">Tài khoản của tôi</a>
                        <a href="./Page/user/order_history.php" class="active">Lịch sử đơn hàng</a>
                    </div>
                </div>
                
            </div>
            
        </div>
    
    </main>

<!-- Footer -->
<?php include __DIR__ . '/../home/includes/Footer.php'; ?>

<!-- Modal Chi Tiết Đơn Hàng -->
<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailModalLabel">
                    <i class="bi bi-receipt me-2"></i>Chi tiết đơn hàng
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="order-detail-content">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Xem chi tiết đơn hàng
    $('.view-order-detail').click(function() {
        const orderId = $(this).data('order-id');
        const modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
        modal.show();
        
        // Gọi API lấy chi tiết
        $.ajax({
            url: '/CuoiKy_LTW/api/orders.php',
            method: 'POST',
            data: { action: 'get_one', id: orderId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const order = response.data;
                    let html = `
                        <div class="mb-3">
                            <strong>Mã đơn hàng:</strong> #${order.id}<br>
                            <strong>Người nhận:</strong> ${order.customer_name}<br>
                            <strong>SĐT:</strong> ${order.customer_phone}<br>
                            <strong>Địa chỉ:</strong> ${order.customer_address}<br>
                            <strong>Trạng thái:</strong> <span class="badge bg-warning">${order.status}</span>
                        </div>
                        <h6>Sản phẩm đã đặt:</h6>
                        <table class="table table-sm">
                            <thead><tr><th>Sản phẩm</th><th>SL</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead>
                            <tbody>`;
                    
                    order.items.forEach(item => {
                        html += `<tr>
                            <td>${item.product_name}</td>
                            <td>${item.quantity}</td>
                            <td>${parseInt(item.price).toLocaleString('vi-VN')}₫</td>
                            <td>${(item.price * item.quantity).toLocaleString('vi-VN')}₫</td>
                        </tr>`;
                    });
                    
                    html += `</tbody></table>
                        <div class="text-end">
                            <h5>Tổng: <span class="text-danger">${parseFloat(order.total_price).toLocaleString('vi-VN')}₫</span></h5>
                        </div>`;
                    
                    $('#order-detail-content').html(html);
                } else {
                    $('#order-detail-content').html('<div class="alert alert-danger">Không tìm thấy đơn hàng!</div>');
                }
            },
            error: function() {
                $('#order-detail-content').html('<div class="alert alert-danger">Lỗi khi tải dữ liệu!</div>');
            }
        });
    });
});
</script>
</body>
</html>