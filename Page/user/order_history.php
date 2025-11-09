<?php
// Bắt đầu session (phù hợp với hệ thống đăng nhập của chúng ta)
session_start();

// Kiểm tra đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../admin/signin.php');
    exit;
}

include __DIR__ . '/../../admin/db_connect.php';

// Lấy lịch sử đơn hàng của người dùng từ cơ sở dữ liệu
    // $userId = $_SESSION['user_id'];
    // $stmt = $conn->prepare("SELECT order_id, order_date, total_amount, status FROM orders WHERE user_id = ? ORDER BY order_date DESC");
    // $stmt->bind_param("i", $userId);
    // $stmt->execute();
    // $result = $stmt->get_result();

    // while ($order = $result->fetch_assoc()):
?>
<?php //endwhile; ?>
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
    <title>User</title>
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
                        <!-- Nội dung lịch sử đơn hàng sẽ được hiển thị ở đây -->
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Mã Đơn Hàng</th>
                                    <th scope="col">Ngày Đặt Hàng</th>
                                    <th scope="col">Tổng Tiền</th>
                                    <th scope="col">Trạng Thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                                <tr>
                                    <td>12345</td>
                                    <td>2023-10-01</td>
                                    <td>100,00,000 VND</td>
                                    <td>Đang xử lý</td>
                                </tr>
                                
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Right content -->
                <div class="orderRight">
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

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom User JS -->
<script src="./Page/user/assets/js/user.js"></script>
</body>
</html>