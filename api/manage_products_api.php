<?php
// API để quản lý sản phẩm (Thêm, Sửa, Xóa)
session_start();
header('Content-Type: application/json; charset=utf-8');

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../admin/db_connect.php';

$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'add':
        addProduct($conn);
        break;
    case 'edit':
        editProduct($conn);
        break;
    case 'delete':
        deleteProduct($conn);
        break;
    case 'get':
        getProduct($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

// Thêm sản phẩm
function addProduct($conn)
{
    // Validate input
    if (empty($_POST['name']) || empty($_POST['category']) || empty($_POST['price'])) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
        return;
    }

    // Upload ảnh
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng chọn hình ảnh']);
        return;
    }

    $imagePath = uploadImage($_FILES['image']);
    if (!$imagePath) {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi upload ảnh']);
        return;
    }

    // Insert vào database
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $old_price = !empty($_POST['old_price']) ? $_POST['old_price'] : NULL;
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $stock = isset($_POST['stock']) ? $_POST['stock'] : 0;

    $sql = "INSERT INTO products (name, category, price, old_price, description, image_path, stock) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssddssi", $name, $category, $price, $old_price, $description, $imagePath, $stock);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Thêm sản phẩm thành công']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }

    $stmt->close();
}

// Sửa sản phẩm
function editProduct($conn)
{
    if (empty($_POST['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
        return;
    }

    $id = $_POST['id'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $old_price = !empty($_POST['old_price']) ? $_POST['old_price'] : NULL;
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $stock = isset($_POST['stock']) ? $_POST['stock'] : 0;

    // Kiểm tra có upload ảnh mới không
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Xóa ảnh cũ
        $sql = "SELECT image_path FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $oldImagePath = '../' . $row['image_path'];
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
        $stmt->close();

        // Upload ảnh mới
        $imagePath = uploadImage($_FILES['image']);
        if (!$imagePath) {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi upload ảnh']);
            return;
        }

        $sql = "UPDATE products SET name=?, category=?, price=?, old_price=?, description=?, image_path=?, stock=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssddssii", $name, $category, $price, $old_price, $description, $imagePath, $stock, $id);
    } else {
        // Không thay đổi ảnh
        $sql = "UPDATE products SET name=?, category=?, price=?, old_price=?, description=?, stock=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssddsii", $name, $category, $price, $old_price, $description, $stock, $id);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Cập nhật sản phẩm thành công']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }

    $stmt->close();
}

// Xóa sản phẩm
function deleteProduct($conn)
{
    if (empty($_POST['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
        return;
    }

    $id = $_POST['id'];

    // Lấy đường dẫn ảnh để xóa
    $sql = "SELECT image_path FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $imagePath = '../' . $row['image_path'];

        // Xóa sản phẩm khỏi database
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            // Xóa file ảnh
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            echo json_encode(['success' => true, 'message' => 'Xóa sản phẩm thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $stmt->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm']);
    }

    $stmt->close();
}

// Lấy thông tin sản phẩm
function getProduct($conn)
{
    if (empty($_POST['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
        return;
    }

    $id = $_POST['id'];
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm']);
    }

    $stmt->close();
}

// Upload ảnh
function uploadImage($file)
{
    $targetDir = "../uploads/";

    // Tạo thư mục nếu chưa có
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Tạo tên file unique
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $newFileName = uniqid() . '_' . time() . '.' . $imageFileType;
    $targetFile = $targetDir . $newFileName;

    // Kiểm tra file có phải ảnh không
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return false;
    }

    // Kiểm tra định dạng file
    $allowedFormats = array("jpg", "jpeg", "png", "gif", "webp");
    if (!in_array($imageFileType, $allowedFormats)) {
        return false;
    }

    // Kiểm tra kích thước file (max 5MB)
    if ($file["size"] > 5000000) {
        return false;
    }

    // Upload file
    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return "uploads/" . $newFileName;
    }

    return false;
}

$conn->close();
