<?php
// Bắt đầu session
session_start();

// Kiểm tra đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../admin/signin.php');
    exit;
}

include __DIR__ . '/../../admin/db_connect.php';

// ✅ LẤY LỊCH SỬ ĐƠN HÀNG VÀ ĐÁNH GIÁ
$userId = intval($_SESSION['user_id']);
$sql = "SELECT o.id, o.customer_name, o.customer_phone, o.customer_address,
               o.total_price, o.status,
               DATE_FORMAT(o.order_date, '%d/%m/%Y %H:%i') as formatted_date,
               r.id as review_id, r.rating, r.comment
        FROM orders o
        LEFT JOIN reviews r ON o.id = r.order_id
        WHERE o.user_id = ?
        ORDER BY o.order_date DESC";
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
   
    <!-- Reset CSS -->
    <link rel="stylesheet" href="./Page/home/assets/css/reset.css">


    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" />


    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet" />

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
    <?php require_once __DIR__ . '/../home/includes/header.php'; ?>

    <main>
        <div class="userContainer">
            <!-- Menu -->
            <?php require_once __DIR__ . '/../home/includes/Menu.php'; ?>
           
            <div class="userMain">
                <!-- left-content -->
                <div class="container userLeft mt-4">
                    <h2 class="mb-4">Lịch sử đơn hàng</h2>
                   
                    <?php if (empty($orders)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>Bạn chưa có đơn hàng nào.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mã ĐH</th>
                                        <th>Ngày đặt</th>
                                        <th>Người nhận</th>
                                        <th>SĐT</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo $order['formatted_date']; ?></td>
                                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                            <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                                            <td class="fw-bold text-danger">
                                                <?php echo number_format($order['total_price'], 0, ',', '.'); ?>₫
                                            </td>
                                            <td>
                                                <?php
                                                $badgeClass = 'bg-secondary';
                                                switch ($order['status']) {
                                                    case 'Đang xử lý':
                                                        $badgeClass = 'bg-warning';
                                                        break;
                                                    case 'Đã giao':
                                                        $badgeClass = 'bg-success';
                                                        break;
                                                    case 'Đã hủy':
                                                        $badgeClass = 'bg-danger';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $badgeClass; ?>">
                                                    <?php echo $order['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary view-order-detail"
                                                        data-order-id="<?php echo $order['id']; ?>">
                                                    <i class="bi bi-eye"></i> Xem
                                                </button>
                                               
                                                <?php if ($order['status'] === 'Đã giao'): ?>
                                                    <?php if ($order['review_id']): ?>
                                                        <button class="btn btn-sm btn-outline-success view-review"
                                                                data-order-id="<?php echo $order['id']; ?>"
                                                                title="Xem đánh giá">
                                                            <i class="bi bi-star-fill"></i> Đã đánh giá
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-outline-warning add-review"
                                                                data-order-id="<?php echo $order['id']; ?>"
                                                                title="Đánh giá đơn hàng">
                                                            <i class="bi bi-star"></i> Đánh giá
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- Right content -->
                <div class="userRight">
                    <div class="userRightContent">
                        <a href="./Page/user/user.php">Tài khoản của tôi</a>
                        <a href="./Page/user/order_history.php">Lịch sử đơn hàng</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../home/includes/Footer.php'; ?>

    <!-- Modal Chi Tiết Đơn Hàng -->
    <div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>Chi tiết đơn hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="order-detail-content">
                    <div class="text-center"><div class="spinner-border" role="status"></div></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Đánh Giá Đơn Hàng -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-star me-2"></i>Đánh giá đơn hàng
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="review-order-id">
                   
                    <div class="mb-3">
                        <label class="form-label fw-bold">Đánh giá của bạn:</label>
                        <div class="rating-stars" id="rating-stars">
                            <i class="bi bi-star star" data-rating="1"></i>
                            <i class="bi bi-star star" data-rating="2"></i>
                            <i class="bi bi-star star" data-rating="3"></i>
                            <i class="bi bi-star star" data-rating="4"></i>
                            <i class="bi bi-star star" data-rating="5"></i>
                        </div>
                        <input type="hidden" id="rating-value" value="0">
                        <small class="text-muted">Nhấp vào sao để đánh giá</small>
                    </div>
                   
                    <div class="mb-3">
                        <label for="review-comment" class="form-label fw-bold">Nhận xét:</label>
                        <textarea class="form-control" id="review-comment" rows="4"
                                  placeholder="Chia sẻ trải nghiệm của bạn về đơn hàng này..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" id="submit-review">
                        <i class="bi bi-send me-2"></i>Gửi đánh giá
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Xem Đánh Giá -->
    <div class="modal fade" id="viewReviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-star-fill me-2"></i>Đánh giá của bạn
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="view-review-content">
                    <!-- Nội dung sẽ được load bằng JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <style>
    .rating-stars {
        font-size: 2rem;
        color: #ddd;
        cursor: pointer;
    }

    .rating-stars .star:hover,
    .rating-stars .star.active {
        color: #ffc107;
    }

    .rating-display {
        color: #ffc107;
        font-size: 1.5rem;
    }
    </style>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
   
    <script>
    $(document).ready(function() {
        let selectedRating = 0;

        // Xem chi tiết đơn hàng
        $('.view-order-detail').click(function() {
            const orderId = $(this).data('order-id');
            const modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
            modal.show();
           
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
       
        // Mở modal đánh giá
        $(document).on('click', '.add-review', function() {
            const orderId = $(this).data('order-id');
            $('#review-order-id').val(orderId);
            $('#rating-value').val(0);
            $('#review-comment').val('');
            $('.star').removeClass('active');
            selectedRating = 0;
           
            const modal = new bootstrap.Modal(document.getElementById('reviewModal'));
            modal.show();
        });

        // Chọn sao đánh giá
        $('#rating-stars .star').click(function() {
            selectedRating = $(this).data('rating');
            $('#rating-value').val(selectedRating);
           
            $('.star').removeClass('active');
            for (let i = 1; i <= selectedRating; i++) {
                $(`.star[data-rating="${i}"]`).addClass('active');
            }
        });

        // Hover effect cho sao
        $('#rating-stars .star').hover(function() {
            const hoverRating = $(this).data('rating');
            $('.star').removeClass('active');
            for (let i = 1; i <= hoverRating; i++) {
                $(`.star[data-rating="${i}"]`).addClass('active');
            }
        }, function() {
            $('.star').removeClass('active');
            for (let i = 1; i <= selectedRating; i++) {
                $(`.star[data-rating="${i}"]`).addClass('active');
            }
        });

        // Gửi đánh giá
        $('#submit-review').click(function() {
            const orderId = $('#review-order-id').val();
            const rating = $('#rating-value').val();
            const comment = $('#review-comment').val().trim();


            if (rating == 0) {
                alert('Vui lòng chọn số sao đánh giá!');
                return;
            }

            $.ajax({
                url: '/CuoiKy_LTW/api/reviews.php',
                method: 'POST',
                data: {
                    action: 'add',
                    order_id: orderId,
                    rating: rating,
                    comment: comment
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        bootstrap.Modal.getInstance(document.getElementById('reviewModal')).hide();
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    console.error('Response:', xhr.responseText);
                    alert('Lỗi khi gửi đánh giá!');
                }
            });
        });

        // Xem đánh giá
        $(document).on('click', '.view-review', function() {
            const orderId = $(this).data('order-id');
           
            $.ajax({
                url: '/CuoiKy_LTW/api/reviews.php',
                method: 'POST',
                data: {
                    action: 'get_by_order',
                    order_id: orderId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        const review = response.data;
                        let stars = '';
                        for (let i = 1; i <= 5; i++) {
                            if (i <= review.rating) {
                                stars += '<i class="bi bi-star-fill text-warning"></i>';
                            } else {
                                stars += '<i class="bi bi-star text-muted"></i>';
                            }
                        }
                       
                        const html = `
                            <div class="mb-3">
                                <label class="fw-bold">Đánh giá:</label>
                                <div class="rating-display">${stars}</div>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">Nhận xét:</label>
                                <p class="mt-2">${review.comment || 'Không có nhận xét'}</p>
                            </div>
                            <small class="text-muted">Đánh giá vào: ${review.created_at}</small>
                        `;
                       
                        $('#view-review-content').html(html);
                        const modal = new bootstrap.Modal(document.getElementById('viewReviewModal'));
                        modal.show();
                    } else {
                        alert('Không tìm thấy đánh giá!');
                    }
                },
                error: function() {
                    alert('Lỗi khi tải đánh giá!');
                }
            });
        });
    });
    </script>
    <script src="./Page/home/assets/js/home_script.js"></script>
</body>
</html>