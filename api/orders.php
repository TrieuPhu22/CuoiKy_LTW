<?php
// filepath: c:\xampp\htdocs\CuoiKy_LTW\api\orders.php
session_start();
include '../admin/db_connect.php';

header('Content-Type: application/json');

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'create':
            createOrder($conn);
            break;
            
        case 'get_all':
            getAllOrders($conn);
            break;

        case 'get_one':
            getOneOrder($conn, $_POST['id']);
            break;
            
        case 'get_user_orders':
            getUserOrders($conn);
            break;
            
        case 'update':
            updateOrder($conn, $_POST);
            break;
            
        case 'delete':
            deleteOrder($conn, $_POST['id']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ.']);
            break;
    }
} else {
    getAllOrders($conn);
}

// ===== HÀM TẠO ĐƠN HÀNG MỚI =====
function createOrder($conn) {
    // Kiểm tra đăng nhập
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
        return;
    }

    $userId = intval($_SESSION['user_id']);
    $customerName = trim($_POST['customer_name']);
    $customerPhone = trim($_POST['customer_phone']);
    $customerAddress = trim($_POST['customer_address']);

    // Validate
    if (empty($customerName) || empty($customerPhone) || empty($customerAddress)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!']);
        return;
    }

    // Lấy giỏ hàng từ database
    $sql = "SELECT c.product_id, c.quantity, c.price, p.name, p.stock
            FROM carts c 
            INNER JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống!']);
        $stmt->close();
        return;
    }

    $cartItems = [];
    $totalPrice = 0;

    while ($row = $result->fetch_assoc()) {
        // Kiểm tra tồn kho
        if ($row['stock'] < $row['quantity']) {
            echo json_encode([
                'success' => false, 
                'message' => 'Sản phẩm "' . $row['name'] . '" không đủ hàng!'
            ]);
            $stmt->close();
            return;
        }
        $cartItems[] = $row;
        $totalPrice += $row['price'] * $row['quantity'];
    }
    $stmt->close();

    // Thêm phí vận chuyển
    $shippingFee = 30000;
    $totalPrice += $shippingFee;

    // Bắt đầu transaction
    $conn->begin_transaction();

    try {
        // 1. Thêm đơn hàng vào bảng orders
        $orderSql = "INSERT INTO orders (user_id, customer_name, customer_phone, customer_address, total_price, status, order_date) 
                     VALUES (?, ?, ?, ?, ?, 'Đang xử lý', NOW())";
        $orderStmt = $conn->prepare($orderSql);
        $orderStmt->bind_param("isssd", $userId, $customerName, $customerPhone, $customerAddress, $totalPrice);
        $orderStmt->execute();
        
        $orderId = $conn->insert_id;
        $orderStmt->close();

        // 2. Thêm chi tiết đơn hàng vào bảng order_items
        $orderItemSql = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)";
        $orderItemStmt = $conn->prepare($orderItemSql);

        foreach ($cartItems as $item) {
            $orderItemStmt->bind_param("iisid", 
                $orderId, 
                $item['product_id'], 
                $item['name'], 
                $item['quantity'], 
                $item['price']
            );
            $orderItemStmt->execute();
            
            // 3. Cập nhật tồn kho sản phẩm
            $updateStockSql = "UPDATE products SET stock = stock - ? WHERE id = ?";
            $updateStockStmt = $conn->prepare($updateStockSql);
            $updateStockStmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $updateStockStmt->execute();
            $updateStockStmt->close();
        }
        $orderItemStmt->close();

        // 4. Xóa giỏ hàng
        $clearCartSql = "DELETE FROM carts WHERE user_id = ?";
        $clearCartStmt = $conn->prepare($clearCartSql);
        $clearCartStmt->bind_param("i", $userId);
        $clearCartStmt->execute();
        $clearCartStmt->close();

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true, 
            'message' => 'Đặt hàng thành công!',
            'order_id' => $orderId
        ]);

    } catch (Exception $e) {
        // Rollback nếu có lỗi
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

// ===== HÀM LẤY TẤT CẢ ĐƠN HÀNG (ADMIN) =====
function getAllOrders($conn) {
    $sql = "SELECT o.id, o.customer_name, o.customer_phone, o.customer_address, 
                   o.total_price, o.status, 
                   DATE_FORMAT(o.order_date, '%d/%m/%Y %H:%i:%s') as order_date,
                   u.username as user_name
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            ORDER BY o.order_date DESC";
    
    $result = $conn->query($sql);
    $orders = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
    
    // ✅ DEBUG: In ra để kiểm tra
    error_log("📦 Total orders: " . count($orders));
    
    echo json_encode(['success' => true, 'data' => $orders]);
}

// ===== HÀM LẤY ĐƠN HÀNG CỦA USER =====
function getUserOrders($conn) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
        return;
    }

    $userId = intval($_SESSION['user_id']);
    
    $sql = "SELECT id, customer_name, customer_phone, customer_address, 
                   total_price, status, 
                   DATE_FORMAT(order_date, '%d/%m/%Y %H:%i') as order_date 
            FROM orders 
            WHERE user_id = ? 
            ORDER BY order_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $orders]);
    $stmt->close();
}

// ===== HÀM LẤY CHI TIẾT 1 ĐƠN HÀNG =====
function getOneOrder($conn, $id) {
    $id = intval($id);
    
    // Lấy thông tin đơn hàng
    $sql = "SELECT * FROM orders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        
        // Lấy chi tiết sản phẩm
        $itemSql = "SELECT * FROM order_items WHERE order_id = ?";
        $itemStmt = $conn->prepare($itemSql);
        $itemStmt->bind_param("i", $id);
        $itemStmt->execute();
        $itemResult = $itemStmt->get_result();
        
        $items = [];
        while ($item = $itemResult->fetch_assoc()) {
            $items[] = $item;
        }
        
        $order['items'] = $items;
        $itemStmt->close();
        
        echo json_encode(['success' => true, 'data' => $order]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng.']);
    }
    $stmt->close();
}

// ✅ HÀM CẬP NHẬT ĐƠN HÀNG - TRẢ VỀ ĐẦY ĐỦ DỮ LIỆU
function updateOrder($conn, $data) {
    $id = intval($data['id']);
    $status = $data['status'];

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        $stmt->close();
        
        // ✅ LẤY LẠI THÔNG TIN ĐƠN HÀNG SAU KHI UPDATE
        $getStmt = $conn->prepare("SELECT id, customer_name, total_price, status, 
                                          DATE_FORMAT(order_date, '%Y-%m-%d %H:%i:%s') as order_date 
                                   FROM orders WHERE id = ?");
        $getStmt->bind_param("i", $id);
        $getStmt->execute();
        $result = $getStmt->get_result();
        $updatedOrder = $result->fetch_assoc();
        $getStmt->close();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Cập nhật đơn hàng thành công!',
            'data' => $updatedOrder // ✅ TRẢ VỀ ĐẦY ĐỦ
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $stmt->error]);
        $stmt->close();
    }
}

// ===== HÀM XÓA ĐƠN HÀNG =====
function deleteOrder($conn, $id) {
    $id = intval($id);
    
    $conn->begin_transaction();
    
    try {
        // Xóa chi tiết đơn hàng trước
        $deleteItemsSql = "DELETE FROM order_items WHERE order_id = ?";
        $stmtItems = $conn->prepare($deleteItemsSql);
        $stmtItems->bind_param("i", $id);
        $stmtItems->execute();
        $stmtItems->close();
        
        // Xóa đơn hàng
        $deleteOrderSql = "DELETE FROM orders WHERE id = ?";
        $stmt = $conn->prepare($deleteOrderSql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Xoá đơn hàng thành công.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

$conn->close();
?>
