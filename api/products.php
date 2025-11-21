<?php
// filepath: c:\xampp12\htdocs\CuoiKy_LTW\api\products.php
// Bao gồm tệp kết nối CSDL
include '../admin/db_connect.php';

// ===================================
// HÀM: XỬ LÝ UPLOAD FILE
// ===================================
function handleFileUpload($fileData, $oldImageUrl = '') {
    if (!isset($fileData) || $fileData['error'] !== UPLOAD_ERR_OK) {
        return $oldImageUrl;
    }

    $uploadDir = '../uploads/'; 
    $dbPath = '../uploads/'; 

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = uniqid() . '-' . basename($fileData['name']);
    $targetPath = $uploadDir . $fileName;
    $dbPathWithFile = $dbPath . $fileName;

    $check = getimagesize($fileData['tmp_name']);
    if ($check === false) {
        return $oldImageUrl;
    }

    if ($fileData['size'] > 5000000) {
        return $oldImageUrl;
    }

    if (move_uploaded_file($fileData['tmp_name'], $targetPath)) {
        if ($oldImageUrl && file_exists('../' . $oldImageUrl) && !filter_var($oldImageUrl, FILTER_VALIDATE_URL)) {
            @unlink('../' . $oldImageUrl);
        }
        return $dbPathWithFile;
    }

    return $oldImageUrl;
}

// =============== Lấy sản phẩm theo subcategory ===============
function getProductsBySubcategory($conn, $subcategoryId) {
    $subcategoryId = (int)$subcategoryId;
    $sql = "SELECT p.id, p.name, p.description, p.price, p.category, 
                   p.subcategory_id, s.subcategory_name,
                   p.stock, p.image_url 
            FROM products p
            LEFT JOIN subcategories s ON p.subcategory_id = s.id
            WHERE p.subcategory_id = ?
            ORDER BY p.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $subcategoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
    echo json_encode(['success' => true, 'data' => $products]);
}
// =============== Tìm kiếm sản phẩm ===============
function searchProducts($conn, $keyword) {
    $keyword = '%' . $conn->real_escape_string($keyword) . '%';
    $sql = "SELECT p.id, p.name, p.description, p.price, p.category, 
                   p.subcategory_id, s.subcategory_name,
                   p.stock, p.image_url 
            FROM products p
            LEFT JOIN subcategories s ON p.subcategory_id = s.id
            WHERE p.name LIKE ? 
               OR p.description LIKE ?
               OR s.subcategory_name LIKE ?
            ORDER BY p.name ASC
            LIMIT 20";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $keyword, $keyword, $keyword);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
    echo json_encode(['success' => true, 'data' => $products]);
}

// --- XỬ LÝ ROUTING ---
header('Content-Type: application/json');

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'get_all':
            getAllProducts($conn);
            break;

        case 'get_subcategories':
            $category = $_POST['category'] ?? '';
            $subcats = getSubcategories($conn, $category);
            echo json_encode(['success' => true, 'data' => $subcats]);  
            break;
        
        case 'get_by_subcategory':  
        getProductsBySubcategory($conn, $_POST['subcategory_id']);
        break;

        case 'search':
        $keyword = $_POST['keyword'] ?? $_GET['keyword'] ?? '';
        if (strlen($keyword) >= 2) {
            searchProducts($conn, $keyword);
        } else {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập ít nhất 2 ký tự.']);
        }
        break;
        case 'add':
            addProduct($conn, $_POST, $_FILES);
            break;

        case 'get_one':
            getOneProduct($conn, $_POST['id']);
            break;
            
        case 'update':
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
    getAllProducts($conn);
}

// --- CÁC HÀM XỬ LÝ ---

function getAllProducts($conn) {
    $sql = "SELECT p.id, p.name, p.description, p.price, p.category, 
                   p.subcategory_id, s.subcategory_name,
                   p.stock, p.image_url 
            FROM products p
            LEFT JOIN subcategories s ON p.subcategory_id = s.id
            ORDER BY p.id DESC";
    $result = $conn->query($sql);
    $products = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    echo json_encode(['success' => true, 'data' => $products]);
}

function getOneProduct($conn, $id) {
    $id = intval($id);
    $sql = "SELECT p.id, p.name, p.description, p.price, p.category, 
                   p.subcategory_id, s.subcategory_name,
                   p.stock, p.image_url 
            FROM products p
            LEFT JOIN subcategories s ON p.subcategory_id = s.id
            WHERE p.id = ?";
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

function addProduct($conn, $data, $files) {
    $name = $data['name'];
    $desc = $data['description'];
    $price = $data['price'];
    $stock = $data['stock'];
    $category = $data['category'];
    $subcategory_id = !empty($data['subcategory_id']) ? (int)$data['subcategory_id'] : NULL;

    $imageFile = isset($files['image_file']) ? $files['image_file'] : null;
    $image = handleFileUpload($imageFile, '');

    $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category, subcategory_id, image_url) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdisss", $name, $desc, $price, $stock, $category, $subcategory_id, $image);

    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        
        $subcategory_name = '';
        if ($subcategory_id) {
            $sqlSub = "SELECT subcategory_name FROM subcategories WHERE id = ?";
            $stmtSub = $conn->prepare($sqlSub);
            $stmtSub->bind_param("i", $subcategory_id);
            $stmtSub->execute();
            $resSub = $stmtSub->get_result();
            if ($resSub->num_rows > 0) {
                $rowSub = $resSub->fetch_assoc();
                $subcategory_name = $rowSub['subcategory_name'];
            }
            $stmtSub->close();
        }
        
        $newProduct = [
            'id' => $newId,
            'name' => $name,
            'description' => $desc,
            'price' => $price,
            'stock' => $stock,
            'category' => $category,
            'subcategory_id' => $subcategory_id,
            'subcategory_name' => $subcategory_name,
            'image_url' => $image
        ];
        echo json_encode(['success' => true, 'message' => 'Thêm sản phẩm thành công!', 'data' => $newProduct]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    $stmt->close();
}

function updateProduct($conn, $data, $files) {
    $id = intval($data['id']);
    $name = $data['name'];
    $desc = $data['description'];
    $price = $data['price'];
    $stock = $data['stock'];
    $category = $data['category'];
    $subcategory_id = !empty($data['subcategory_id']) ? (int)$data['subcategory_id'] : NULL;
    
    $old_image_url = $data['existing_image_url'] ?? '';

    $imageFile = isset($files['image_file']) ? $files['image_file'] : null;
    $image = handleFileUpload($imageFile, $old_image_url);

    $stmt = $conn->prepare("UPDATE products 
                           SET name = ?, description = ?, price = ?, stock = ?, category = ?, subcategory_id = ?, image_url = ? 
                           WHERE id = ?");
    $stmt->bind_param("ssdisssi", $name, $desc, $price, $stock, $category, $subcategory_id, $image, $id);

    if ($stmt->execute()) {
        $subcategory_name = '';
        if ($subcategory_id) {
            $sqlSub = "SELECT subcategory_name FROM subcategories WHERE id = ?";
            $stmtSub = $conn->prepare($sqlSub);
            $stmtSub->bind_param("i", $subcategory_id);
            $stmtSub->execute();
            $resSub = $stmtSub->get_result();
            if ($resSub->num_rows > 0) {
                $rowSub = $resSub->fetch_assoc();
                $subcategory_name = $rowSub['subcategory_name'];
            }
            $stmtSub->close();
        }
        
        $updatedProduct = [
            'id' => $id,
            'name' => $name,
            'description' => $desc,
            'price' => $price,
            'stock' => $stock,
            'category' => $category,
            'subcategory_id' => $subcategory_id,
            'subcategory_name' => $subcategory_name,
            'image_url' => $image
        ];
        echo json_encode(['success' => true, 'message' => 'Cập nhật sản phẩm thành công!', 'data' => $updatedProduct]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    $stmt->close();
}

function deleteProduct($conn, $id) {
    $id = intval($id);
    
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

    $stmtDelete = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmtDelete->bind_param("i", $id);

    if ($stmtDelete->execute()) {
        if ($image_url && file_exists('../' . $image_url) && !filter_var($image_url, FILTER_VALIDATE_URL)) {
            @unlink('../' . $image_url);
        }
        echo json_encode(['success' => true, 'message' => 'Xoá sản phẩm thành công.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $stmtDelete->error]);
    }
    $stmtDelete->close();
}

$conn->close();
?>