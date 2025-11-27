<?php
// filepath: c:\xampp\htdocs\CuoiKy_LTW\api\dashboard.php
session_start();
include '../admin/db_connect.php';

header('Content-Type: application/json');

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

// Lấy thống kê cơ bản
$stats = [];

// 1. Tổng doanh thu (từ đơn hàng đã giao)
$sql = "SELECT SUM(total_price) as total_revenue FROM orders WHERE status = 'Đã giao'";
$result = $conn->query($sql);
$stats['total_revenue'] = $result->fetch_assoc()['total_revenue'] ?? 0;

// 2. Số đơn hàng mới (24h qua)
$sql = "SELECT COUNT(*) as new_orders FROM orders WHERE order_date >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
$result = $conn->query($sql);
$stats['new_orders'] = $result->fetch_assoc()['new_orders'] ?? 0;

// 3. Người dùng đăng ký (hôm nay)
$sql = "SELECT COUNT(*) as new_users FROM users WHERE DATE(join_date) = CURDATE()";
$result = $conn->query($sql);
$stats['new_users'] = $result->fetch_assoc()['new_users'] ?? 0;

// 4. Tổng số sản phẩm
$sql = "SELECT COUNT(*) as total_products FROM products";
$result = $conn->query($sql);
$stats['total_products'] = $result->fetch_assoc()['total_products'] ?? 0;

// 5. Đơn hàng đang xử lý
$sql = "SELECT COUNT(*) as pending_orders FROM orders WHERE status = 'Đang xử lý'";
$result = $conn->query($sql);
$stats['pending_orders'] = $result->fetch_assoc()['pending_orders'] ?? 0;

// ===== THÊM DỮ LIỆU BIỂU ĐỒ =====

// 6. Doanh thu theo tuần (7 tuần gần nhất)
$weeklyRevenue = [];
$sql = "SELECT 
    YEARWEEK(order_date, 1) as week_year,
    WEEK(order_date, 1) as week_num,
    YEAR(order_date) as year,
    SUM(total_price) as revenue
FROM orders 
WHERE status = 'Đã giao' 
    AND order_date >= DATE_SUB(NOW(), INTERVAL 7 WEEK)
GROUP BY YEARWEEK(order_date, 1)
ORDER BY week_year ASC";
$result = $conn->query($sql);

$weekLabels = [];
$weekData = [];
while ($row = $result->fetch_assoc()) {
    $weekLabels[] = "Tuần " . $row['week_num'] . "/" . $row['year'];
    $weekData[] = (float)$row['revenue'];
}

// Đảm bảo có đủ 7 tuần (điền 0 cho tuần không có dữ liệu)
for ($i = count($weekLabels); $i < 7; $i++) {
    $weekLabels[] = "Tuần " . (date('W') - (7 - $i - 1)) . "/" . date('Y');
    $weekData[] = 0;
}

$stats['weekly_revenue'] = [
    'labels' => $weekLabels,
    'data' => $weekData
];

// 7. Doanh thu theo tháng (6 tháng gần nhất)
$monthlyRevenue = [];
$sql = "SELECT 
    YEAR(order_date) as year,
    MONTH(order_date) as month,
    SUM(total_price) as revenue
FROM orders 
WHERE status = 'Đã giao' 
    AND order_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
GROUP BY YEAR(order_date), MONTH(order_date)
ORDER BY year ASC, month ASC";
$result = $conn->query($sql);

$monthLabels = [];
$monthData = [];
while ($row = $result->fetch_assoc()) {
    $monthLabels[] = "Tháng " . $row['month'] . "/" . $row['year'];
    $monthData[] = (float)$row['revenue'];
}

// Đảm bảo có đủ 6 tháng
for ($i = count($monthLabels); $i < 6; $i++) {
    $currentMonth = date('n') - (6 - $i - 1);
    $currentYear = date('Y');
    if ($currentMonth <= 0) {
        $currentMonth += 12;
        $currentYear--;
    }
    $monthLabels[] = "Tháng " . $currentMonth . "/" . $currentYear;
    $monthData[] = 0;
}

$stats['monthly_revenue'] = [
    'labels' => $monthLabels,
    'data' => $monthData
];

// 8. Đơn hàng theo trạng thái
$sql = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
$result = $conn->query($sql);

$orderStatusLabels = [];
$orderStatusData = [];
while ($row = $result->fetch_assoc()) {
    $orderStatusLabels[] = $row['status'];
    $orderStatusData[] = (int)$row['count'];
}

$stats['order_status'] = [
    'labels' => $orderStatusLabels,
    'data' => $orderStatusData
];

echo json_encode(['success' => true, 'data' => $stats]);
$conn->close();
?>