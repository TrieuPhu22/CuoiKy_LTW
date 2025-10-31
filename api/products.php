<?php
// Bao gồm tệp kết nối CSDL
include '../admin/db_connect.php';

// ===================================
// HÀM: XỬ LÝ UPLOAD FILE
// ===================================
/**
 * Xử lý việc upload file ảnh.
 * @param array $fileData Dữ liệu từ $_FILES['image_file']
 * @param string $oldImageUrl Đường dẫn ảnh cũ (để xoá khi update)
 * @return string Đường dẫn ảnh mới, hoặc ảnh cũ nếu lỗi, hoặc rỗng nếu không có ảnh
 */
function handleFileUpload($fileData, $oldImageUrl = '') {
    // 1. Kiểm tra xem có file mới được upload không
    if (!isset($fileData) || $fileData['error'] !== UPLOAD_ERR_OK) {
        // Không có file mới, giữ nguyên ảnh cũ
        return $oldImageUrl;
    }

    // Đường dẫn thư mục upload (tính từ file api/products.php)
    $uploadDir = '../uploads/'; 
    // Đường dẫn lưu vào CSDL (tính từ file admin_dashboard.php)
    $dbPath = '../uploads/'; 

    // Tạo thư mục nếu chưa tồn tại
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Tạo tên file duy nhất để tránh trùng lặp
    $fileName = uniqid() . '-' . basename($fileData['name']);
    $targetPath = $uploadDir . $fileName;
    $dbPathWithFile = $dbPath . $fileName;

    // 2. Kiểm tra bảo mật cơ bản
    // Kiểm tra xem có phải ảnh thật không
    $check = getimagesize($fileData['tmp_name']);
    if ($check === false) {
        // Không phải file ảnh
        return $oldImageUrl; // Giữ ảnh cũ
    }

    // Kiểm tra dung lượng (ví dụ: 5MB)
    if ($fileData['size'] > 5000000) {
        // File quá lớn
        return $oldImageUrl; // Giữ ảnh cũ
    }

    // 3. Di chuyển file
    if (move_uploaded_file($fileData['tmp_name'], $targetPath)) {
        // 4. Xoá file ảnh cũ (nếu có và không phải là link placeholder)
        if ($oldImageUrl && file_exists('../' . $oldImageUrl) && !filter_var($oldImageUrl, FILTER_VALIDATE_URL)) {
            unlink('../' . $oldImageUrl);
        }
        
        // Trả về đường dẫn MỚI (để lưu vào CSDL)
        return $dbPathWithFile;
    }

    // Nếu di chuyển thất bại, giữ nguyên ảnh cũ
    return $oldImageUrl;
}


// --- XỬ LÝ ROUTING ---
// Đặt header là JSON
header('Content-Type: application/json');

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'get_all':
            getAllProducts($conn);
            break;
        
        case 'add':
            // Gửi $_FILES vào hàm add
            addProduct($conn, $_POST, $_FILES);
            break;

        case 'get_one':
            getOneProduct($conn, $_POST['id']);
            break;
            
        case 'update':
            // Gửi $_FILES vào hàm update
            updateProduct($conn, $_POST, $_FILES);
            break;
            
        case 'delete':
            deleteProduct($conn, $_POST['id']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ.']);
            break;
    }
} else {
    // Nếu không có action POST, mặc định lấy tất cả
    getAllProducts($conn);
}

// --- CÁC HÀM XỬ LÝ ---

// Hàm lấy TẤT CẢ sản phẩm
function getAllProducts($conn) {
    $sql = "SELECT id, name, description, price, category, stock, image_url FROM products";
    $result = $conn->query($sql);
    $products = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    echo json_encode(['success' => true, 'data' => $products]);
}

// Hàm lấy MỘT sản phẩm
function getOneProduct($conn, $id) {
    $id = intval($id);
    $sql = "SELECT id, name, description, price, category, stock, image_url FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => true, 'data' => $result->fetch_assoc()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm.']);
    }
    $stmt->close();
}


// Hàm THÊM sản phẩm mới
function addProduct($conn, $data, $files) {
    // Lấy dữ liệu từ $data (là $_POST)
    $name = $data['name']; //lấy tên sản phẩm
    $desc = $data['description']; //lấy mô tả sản phẩm
    $price = $data['price']; //lấy giá sản phẩm
    $stock = $data['stock']; //lấy số lượng tồn kho
    $category = $data['category']; //lấy danh mục sản phẩm

    // Xử lý file upload
    $imageFile = isset($files['image_file']) ? $files['image_file'] : null;
    $image = handleFileUpload($imageFile, ''); // '' vì đây là 'add', chưa có ảnh cũ

    $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category, image_url) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdiss", $name, $desc, $price, $stock, $category, $image);

    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        $newProduct = [
            'id' => $newId,
            'name' => $name,
            'description' => $desc,
            'price' => $price,
            'stock' => $stock,
            'category' => $category,
            'image_url' => $image // Trả về đường dẫn ảnh mới
        ];
        echo json_encode(['success' => true, 'message' => 'Thêm sản phẩm thành công!', 'data' => $newProduct]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    $stmt->close();
}

// Hàm CẬP NHẬT sản phẩm
function updateProduct($conn, $data, $files) {
    $id = intval($data['id']);
    $name = $data['name'];
    $desc = $data['description'];
    $price = $data['price'];
    $stock = $data['stock'];
    $category = $data['category'];
    
    // Lấy đường dẫn ảnh cũ từ trường hidden
    $old_image_url = $data['existing_image_url'];

    // Xử lý file upload
    $imageFile = isset($files['image_file']) ? $files['image_file'] : null;
    $image = handleFileUpload($imageFile, $old_image_url); // Gửi ảnh cũ để xử lý

    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category = ?, image_url = ? WHERE id = ?");
    $stmt->bind_param("ssdissi", $name, $desc, $price, $stock, $category, $image, $id);

    if ($stmt->execute()) {
         $updatedProduct = [
            'id' => $id,
            'name' => $name,
            'description' => $desc,
            'price' => $price,
            'stock' => $stock,
            'image_url' => $image // Trả về đường dẫn ảnh (mới hoặc cũ)
        ];
        echo json_encode(['success' => true, 'message' => 'Cập nhật sản phẩm thành công!', 'data' => $updatedProduct]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    $stmt->close();
}

// Hàm XOÁ sản phẩm
function deleteProduct($conn, $id) {
    $id = intval($id);
    
    // 1. Lấy đường dẫn ảnh trước khi xoá
    $sqlSelect = "SELECT image_url FROM products WHERE id = ?";
    $stmtSelect = $conn->prepare($sqlSelect);
    $stmtSelect->bind_param("i", $id);
    $stmtSelect->execute();
    $result = $stmtSelect->get_result();
    $image_url = '';
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $image_url = $row['image_url'];
    }
    $stmtSelect->close();

    // 2. Xoá sản phẩm khỏi CSDL
    $stmtDelete = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmtDelete->bind_param("i", $id);

    if ($stmtDelete->execute()) {
        // 3. Nếu xoá CSDL thành công, xoá file ảnh
        if ($image_url && file_exists('../' . $image_url) && !filter_var($image_url, FILTER_VALIDATE_URL)) {
            unlink('../' . $image_url);
        }
        echo json_encode(['success' => true, 'message' => 'Xoá sản phẩm thành công.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $stmtDelete->error]);
    }
    $stmtDelete->close();
}

// Đóng kết nối CSDL
$conn->close();
?>

