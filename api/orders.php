<?php
include '../admin/db_connect.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'get_all':
            getAllOrders($conn);
            break;

        case 'get_one':
            getOneOrder($conn, $_POST['id']);
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

// --- CÁC HÀM XỬ LÝ ---

function getAllOrders($conn) {
    // Định dạng lại ngày tháng cho dễ đọc
    $sql = "SELECT id, customer_name, total_price, status, DATE_FORMAT(order_date, '%d/%m/%Y') AS order_date FROM orders";
    $result = $conn->query($sql);
    $orders = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
    echo json_encode(['success' => true, 'data' => $orders]);
}

function getOneOrder($conn, $id) {
    $id = intval($id);
    // Lấy thông tin cơ bản của đơn hàng để sửa
    $sql = "SELECT id, customer_name, total_price, status FROM orders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => true, 'data' => $result->fetch_assoc()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng.']);
    }
    $stmt->close();
}

function updateOrder($conn, $data) {
    $id = intval($data['id']);
    $customer_name = $data['customer_name'];
    $total_price = $data['total_price'];
    $status = $data['status'];

    $stmt = $conn->prepare("UPDATE orders SET customer_name = ?, total_price = ?, status = ? WHERE id = ?");
    $stmt->bind_param("sdsi", $customer_name, $total_price, $status, $id);

    if ($stmt->execute()) {
        $updatedOrder = [
            'id' => $id,
            'customer_name' => $customer_name,
            'total_price' => $total_price,
            'status' => $status
        ];
        echo json_encode(['success' => true, 'message' => 'Cập nhật đơn hàng thành công!', 'data' => $updatedOrder]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    $stmt->close();
}

function deleteOrder($conn, $id) {
    $id = intval($id);
    
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Xoá đơn hàng thành công.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    $stmt->close();
}

$conn->close();
?>
