<?php
// API để lấy danh sách sản phẩm theo category
header('Content-Type: application/json; charset=utf-8');

// Kết nối database
require_once '../admin/db_connect.php';

// Lấy category từ query parameter
$category = isset($_GET['category']) ? $_GET['category'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'price_asc';

if (empty($category)) {
    echo json_encode(['success' => false, 'message' => 'Category không được để trống']);
    exit;
}

// Xây dựng câu truy vấn với ORDER BY
$sql = "SELECT * FROM products WHERE category = ?";

// Thêm điều kiện sắp xếp
switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY price DESC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY name ASC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY name DESC";
        break;
    default:
        $sql .= " ORDER BY price ASC";
}

// Chuẩn bị và thực thi truy vấn
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();

// Lấy danh sách sản phẩm
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Trả về JSON
echo json_encode([
    'success' => true,
    'data' => $products,
    'count' => count($products)
]);

$stmt->close();
$conn->close();
