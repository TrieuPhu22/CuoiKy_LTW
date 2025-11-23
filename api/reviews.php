<?php
session_start();
include '../admin/db_connect.php';

header('Content-Type: application/json');

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'add':
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
                exit;
            }
            addReview($conn);
            break;
            
        case 'get_by_order':
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
                exit;
            }
            getReviewByOrder($conn, $_POST['order_id']);
            break;
            
        case 'get_by_product':
            getReviewsByProduct($conn, $_POST['product_id']);
            break;
            
        case 'update':
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
                exit;
            }
            updateReview($conn);
            break;
            
        case 'get_all':
            getAllReviews($conn);
            break;
            
        case 'delete':
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
                echo json_encode(['success' => false, 'message' => 'Không có quyền!']);
                exit;
            }
            deleteReview($conn, $_POST['id']);
            break;
            
        case 'get_one':
            getOneReview($conn, $_POST['id']);
            break;
            
        // ✅ THÊM ACTIONS CHO REPLY
        case 'add_reply':
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
                echo json_encode(['success' => false, 'message' => 'Không có quyền!']);
                exit;
            }
            addReply($conn);
            break;
            
        case 'update_reply':
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
                echo json_encode(['success' => false, 'message' => 'Không có quyền!']);
                exit;
            }
            updateReply($conn);
            break;
            
        case 'delete_reply':
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
                echo json_encode(['success' => false, 'message' => 'Không có quyền!']);
                exit;
            }
            deleteReply($conn, $_POST['id']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ.']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Thiếu action.']);
}

$conn->close();

// ===== THÊM ĐÁNH GIÁ =====
function addReview($conn) {
    $userId = intval($_SESSION['user_id']);
    $orderId = intval($_POST['order_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    // Validate
    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Đánh giá phải từ 1-5 sao!']);
        return;
    }

    // Kiểm tra đơn hàng có tồn tại và thuộc về user này không
    $checkSql = "SELECT id, status FROM orders WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("ii", $orderId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Đơn hàng không tồn tại!']);
        $stmt->close();
        return;
    }

    $order = $result->fetch_assoc();
    $stmt->close();

    // Kiểm tra trạng thái đơn hàng
    if ($order['status'] !== 'Đã giao') {
        echo json_encode(['success' => false, 'message' => 'Chỉ có thể đánh giá đơn hàng đã giao!']);
        return;
    }

    // Kiểm tra đã đánh giá chưa
    $checkReviewSql = "SELECT id FROM reviews WHERE order_id = ?";
    $checkStmt = $conn->prepare($checkReviewSql);
    $checkStmt->bind_param("i", $orderId);
    $checkStmt->execute();
    $reviewResult = $checkStmt->get_result();

    if ($reviewResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Bạn đã đánh giá đơn hàng này rồi!']);
        $checkStmt->close();
        return;
    }
    $checkStmt->close();

    // Lấy product_id từ order_items (lấy sản phẩm đầu tiên)
    $getProductSql = "SELECT product_id FROM order_items WHERE order_id = ? LIMIT 1";
    $getProductStmt = $conn->prepare($getProductSql);
    $getProductStmt->bind_param("i", $orderId);
    $getProductStmt->execute();
    $productResult = $getProductStmt->get_result();
    
    $productId = null;
    if ($productResult->num_rows > 0) {
        $productRow = $productResult->fetch_assoc();
        $productId = $productRow['product_id'];
    }
    $getProductStmt->close();

    // Thêm đánh giá
    $insertSql = "INSERT INTO reviews (order_id, user_id, product_id, rating, comment) VALUES (?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("iiiis", $orderId, $userId, $productId, $rating, $comment);

    if ($insertStmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Cảm ơn bạn đã đánh giá!',
            'data' => [
                'id' => $conn->insert_id,
                'order_id' => $orderId,
                'product_id' => $productId,
                'rating' => $rating,
                'comment' => $comment
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $insertStmt->error]);
    }
    $insertStmt->close();
}

// ===== LẤY ĐÁNH GIÁ THEO ĐƠN HÀNG =====
function getReviewByOrder($conn, $orderId) {
    $userId = intval($_SESSION['user_id']);
    $orderId = intval($orderId);

    $sql = "SELECT r.*, u.username 
            FROM reviews r
            INNER JOIN users u ON r.user_id = u.id
            WHERE r.order_id = ? AND r.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $orderId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $review = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $review]);
    } else {
        echo json_encode(['success' => false, 'data' => null]);
    }
    $stmt->close();
}

// ===== LẤY ĐÁNH GIÁ THEO SẢN PHẨM =====
function getReviewsByProduct($conn, $productId) {
    $productId = intval($productId);

    if ($productId <= 0) {
        echo json_encode([
            'success' => true,
            'data' => [
                'reviews' => [],
                'total' => 0,
                'average_rating' => 0,
                'rating_count' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0]
            ]
        ]);
        return;
    }

    // ✅ Lấy cả reply khi load reviews cho sản phẩm
    $sql = "SELECT r.*, u.username,
            DATE_FORMAT(r.created_at, '%d/%m/%Y') as formatted_date,
            rr.id as reply_id, rr.reply, rr.created_at as reply_date,
            admin.username as admin_username
            FROM reviews r
            INNER JOIN users u ON r.user_id = u.id
            LEFT JOIN review_replies rr ON r.id = rr.review_id
            LEFT JOIN users admin ON rr.admin_id = admin.id
            WHERE r.product_id = ?
            ORDER BY r.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    $reviews = [];
    $totalRating = 0;
    $ratingCount = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
        $totalRating += intval($row['rating']);
        $ratingCount[intval($row['rating'])]++;
    }
    
    $stmt->close();

    $averageRating = count($reviews) > 0 ? round($totalRating / count($reviews), 1) : 0;

    echo json_encode([
        'success' => true,
        'data' => [
            'reviews' => $reviews,
            'total' => count($reviews),
            'average_rating' => $averageRating,
            'rating_count' => $ratingCount
        ]
    ]);
}

// ✅ LẤY TẤT CẢ ĐÁNH GIÁ (ADMIN) - CÓ REPLY
function getAllReviews($conn) {
    $sql = "SELECT r.*, 
            u.username,
            p.name as product_name,
            DATE_FORMAT(r.created_at, '%d/%m/%Y %H:%i') as formatted_date,
            rr.id as reply_id, rr.reply
            FROM reviews r
            INNER JOIN users u ON r.user_id = u.id
            LEFT JOIN products p ON r.product_id = p.id
            LEFT JOIN review_replies rr ON r.id = rr.review_id
            ORDER BY r.created_at DESC";
    
    $result = $conn->query($sql);
    $reviews = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }
    }

    echo json_encode(['success' => true, 'data' => $reviews]);
}

// ✅ LẤY CHI TIẾT 1 ĐÁNH GIÁ - CÓ REPLY
function getOneReview($conn, $id) {
    $id = intval($id);
    
    $sql = "SELECT r.*, 
            u.username, u.email,
            p.name as product_name, p.image_url,
            o.customer_name, o.customer_phone,
            DATE_FORMAT(r.created_at, '%d/%m/%Y %H:%i') as formatted_date,
            rr.id as reply_id, rr.reply, 
            DATE_FORMAT(rr.created_at, '%d/%m/%Y %H:%i') as reply_date,
            admin.username as admin_username
            FROM reviews r
            INNER JOIN users u ON r.user_id = u.id
            LEFT JOIN products p ON r.product_id = p.id
            LEFT JOIN orders o ON r.order_id = o.id
            LEFT JOIN review_replies rr ON r.id = rr.review_id
            LEFT JOIN users admin ON rr.admin_id = admin.id
            WHERE r.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $review = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $review]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy đánh giá!']);
    }
    $stmt->close();
}

// ✅ THÊM TRẢ LỜI ĐÁNH GIÁ
function addReply($conn) {
    $adminId = intval($_SESSION['user_id']);
    $reviewId = intval($_POST['review_id']);
    $reply = trim($_POST['reply']);

    if (empty($reply)) {
        echo json_encode(['success' => false, 'message' => 'Nội dung trả lời không được để trống!']);
        return;
    }

    // Kiểm tra đã trả lời chưa
    $checkSql = "SELECT id FROM review_replies WHERE review_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $reviewId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Đánh giá này đã được trả lời!']);
        $checkStmt->close();
        return;
    }
    $checkStmt->close();

    // Thêm reply
    $sql = "INSERT INTO review_replies (review_id, admin_id, reply) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $reviewId, $adminId, $reply);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Đã trả lời đánh giá!',
            'data' => [
                'id' => $conn->insert_id,
                'review_id' => $reviewId,
                'reply' => $reply
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    $stmt->close();
}

// ✅ CẬP NHẬT TRẢ LỜI
function updateReply($conn) {
    $replyId = intval($_POST['reply_id']);
    $reply = trim($_POST['reply']);

    if (empty($reply)) {
        echo json_encode(['success' => false, 'message' => 'Nội dung trả lời không được để trống!']);
        return;
    }

    $sql = "UPDATE review_replies SET reply = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $reply, $replyId);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Đã cập nhật trả lời!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy trả lời để cập nhật!']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    $stmt->close();
}

// ✅ XÓA TRẢ LỜI
function deleteReply($conn, $id) {
    $id = intval($id);
    
    $sql = "DELETE FROM review_replies WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Đã xóa trả lời!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy trả lời để xóa!']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    $stmt->close();
}

function deleteReview($conn, $id) {
    $id = intval($id);
    
    $sql = "DELETE FROM reviews WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Xóa đánh giá thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy đánh giá để xóa!']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    $stmt->close();
}
?>