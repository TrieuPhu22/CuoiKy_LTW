<?php
// filepath: c:\xampp\htdocs\CuoiKy_LTW\api\cart.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Kết nối database
include '../admin/db_connect.php';

error_log('Cart API called - Action: ' . (isset($_POST['action']) ? $_POST['action'] : 'none'));
error_log('Session ID: ' . session_id());
error_log('User ID: ' . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set'));

header('Content-Type: application/json');

// KIỂM TRA ĐĂNG NHẬP
$publicActions = ['get']; // Các action có thể xem mà không cần đăng nhập
$action = isset($_POST['action']) ? $_POST['action'] : 'get';

if (!in_array($action, $publicActions)) {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Vui lòng đăng nhập để sử dụng tính năng này',
            'require_login' => true
        ]);
        exit;
    }
}

// Xử lý các action
if (isset($_POST['action'])) {
    switch ($action) {
        case 'add':
            addToCart($_POST, $conn);
            break;
        case 'update':
            updateCart($_POST, $conn);
            break;
        case 'remove':
            removeFromCart($_POST, $conn);
            break;
        case 'clear':
            clearCart($conn);
            break;
        case 'get':
            getCart($conn);
            break;
        case 'sync':
            syncSessionToDatabase($conn);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
            break;
    }
} else {
    getCart($conn);
}

$conn->close();

// ========== FUNCTIONS ==========

/**
 * Thêm sản phẩm vào giỏ hàng
 */
function addToCart($data, $conn) {
    $userId = intval($_SESSION['user_id']);
    $productId = intval($data['product_id']);
    $quantity = intval($data['quantity']);
    $price = floatval($data['price']);

    error_log("Adding to cart - User: $userId, Product: $productId, Qty: $quantity");

    if ($quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Số lượng không hợp lệ']);
        return;
    }

    // Kiểm tra xem sản phẩm đã có trong giỏ chưa
    $checkSql = "SELECT id, quantity FROM carts WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Sản phẩm đã có -> Cập nhật số lượng
        $row = $result->fetch_assoc();
        $newQuantity = $row['quantity'] + $quantity;
        
        $updateSql = "UPDATE carts SET quantity = ?, price = ?, updated_at = NOW() WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("idi", $newQuantity, $price, $row['id']);
        
        if ($updateStmt->execute()) {
            $cartCount = getCartCount($userId, $conn);
            echo json_encode([
                'success' => true,
                'message' => 'Đã cập nhật số lượng sản phẩm trong giỏ hàng',
                'cart_count' => $cartCount
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật giỏ hàng']);
        }
        $updateStmt->close();
    } else {
        // Sản phẩm chưa có -> Thêm mới
        $insertSql = "INSERT INTO carts (user_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("iiid", $userId, $productId, $quantity, $price);
        
        if ($insertStmt->execute()) {
            $cartCount = getCartCount($userId, $conn);
            echo json_encode([
                'success' => true,
                'message' => 'Đã thêm sản phẩm vào giỏ hàng',
                'cart_count' => $cartCount
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi thêm vào giỏ hàng']);
        }
        $insertStmt->close();
    }
    $stmt->close();
}

/**
 * Lấy thông tin giỏ hàng
 */
function getCart($conn) {
    // Nếu chưa đăng nhập, trả về giỏ hàng trống
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => true,
            'data' => [],
            'total' => 0,
            'count' => 0
        ]);
        return;
    }

    $userId = intval($_SESSION['user_id']);
    
    $sql = "SELECT 
                c.id,
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

    $cartItems = [];
    $total = 0;

    while ($row = $result->fetch_assoc()) {
        $cartItems[] = [
            'id' => $row['product_id'],
            'name' => $row['name'],
            'price' => floatval($row['price']),
            'quantity' => intval($row['quantity']),
            'image' => $row['image'],
            'stock' => intval($row['stock']),
            'subtotal' => floatval($row['subtotal'])
        ];
        $total += floatval($row['subtotal']);
    }

    $stmt->close();

    echo json_encode([
        'success' => true,
        'data' => $cartItems,
        'total' => $total,
        'count' => count($cartItems)
    ]);
}

/**
 * Cập nhật số lượng sản phẩm
 */
function updateCart($data, $conn) {
    $userId = intval($_SESSION['user_id']);
    $productId = intval($data['product_id']);
    $quantity = intval($data['quantity']);

    error_log("Updating cart - User: $userId, Product: $productId, New Qty: $quantity");

    if ($quantity <= 0) {
        // Nếu số lượng <= 0, xóa sản phẩm
        removeFromCart($data, $conn);
        return;
    }

    $sql = "UPDATE carts SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $quantity, $userId, $productId);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Đã cập nhật số lượng']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật']);
    }

    $stmt->close();
}

/**
 * Xóa sản phẩm khỏi giỏ hàng
 */
function removeFromCart($data, $conn) {
    $userId = intval($_SESSION['user_id']);
    $productId = intval($data['product_id']);

    error_log("Removing from cart - User: $userId, Product: $productId");

    $sql = "DELETE FROM carts WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $productId);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Đã xóa sản phẩm']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa']);
    }

    $stmt->close();
}

/**
 * Xóa toàn bộ giỏ hàng
 */
function clearCart($conn) {
    $userId = intval($_SESSION['user_id']);

    error_log("Clearing cart for user: $userId");

    $sql = "DELETE FROM carts WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Đã xóa toàn bộ giỏ hàng']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa giỏ hàng']);
    }

    $stmt->close();
}

/**
 * Đồng bộ giỏ hàng từ session vào database (khi user đăng nhập)
 */
function syncSessionToDatabase($conn) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        echo json_encode(['success' => false, 'message' => 'Không có dữ liệu để đồng bộ']);
        return;
    }

    $userId = intval($_SESSION['user_id']);
    $sessionCart = $_SESSION['cart'];
    $syncCount = 0;

    foreach ($sessionCart as $productId => $item) {
        $productId = intval($productId);
        $quantity = intval($item['quantity']);
        $price = floatval($item['price']);

        // Kiểm tra xem sản phẩm đã có trong DB chưa
        $checkSql = "SELECT quantity FROM carts WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Cập nhật số lượng (cộng thêm)
            $row = $result->fetch_assoc();
            $newQuantity = $row['quantity'] + $quantity;
            
            $updateSql = "UPDATE carts SET quantity = ?, price = ? WHERE user_id = ? AND product_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("idii", $newQuantity, $price, $userId, $productId);
            $updateStmt->execute();
            $updateStmt->close();
        } else {
            // Thêm mới
            $insertSql = "INSERT INTO carts (user_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("iiid", $userId, $productId, $quantity, $price);
            $insertStmt->execute();
            $insertStmt->close();
        }
        
        $stmt->close();
        $syncCount++;
    }

    // Xóa session cart sau khi đồng bộ
    unset($_SESSION['cart']);

    echo json_encode([
        'success' => true,
        'message' => "Đã đồng bộ $syncCount sản phẩm vào giỏ hàng",
        'synced_count' => $syncCount
    ]);
}

/**
 * Đếm số lượng sản phẩm trong giỏ hàng
 */
function getCartCount($userId, $conn) {
    $sql = "SELECT COUNT(*) as count FROM carts WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return intval($row['count']);
}
?>