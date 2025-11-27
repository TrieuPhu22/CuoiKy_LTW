<?php
// filepath: c:\xampp\htdocs\CuoiKy_LTW\api\chat_history.php

// Thêm hàm xử lý giá chuyên biệt vào đầu file:
function parseVietnamesePrice($priceInput) {
    if (empty($priceInput)) return 500000; // Giá mặc định
    
    $priceString = trim((string)$priceInput);
    
    // Loại bỏ ký tự tiền tệ
    $priceString = preg_replace('/[₫đÐ\s]/u', '', $priceString);
    
    // ✅ XỬ LÝ CÁC TRƯỜNG HỢP ĐẶC BIỆT
    
    // Case 1: "1.050.000" hoặc "2.500.000" (multiple dots)
    if (preg_match('/^\d{1,3}(\.\d{3})+$/', $priceString)) {
        $result = (int)str_replace('.', '', $priceString);
        error_log("Price Case 1 - Multiple dots: '$priceString' -> $result");
        return $result;
    }
    
    // Case 2: "105.000" (single dot with 3 digits)
    if (preg_match('/^\d{1,3}\.\d{3}$/', $priceString)) {
        $result = (int)str_replace('.', '', $priceString);
        error_log("Price Case 2 - Single dot: '$priceString' -> $result");
        return $result;
    }
    
    // Case 3: "1050000" (pure number)
    if (preg_match('/^\d+$/', $priceString)) {
        $result = (int)$priceString;
        error_log("Price Case 3 - Pure number: '$priceString' -> $result");
        return $result;
    }
    
    // Case 4: Fallback - loại bỏ tất cả không phải số
    $cleanPrice = preg_replace('/[^\d]/', '', $priceString);
    $result = !empty($cleanPrice) ? (int)$cleanPrice : 500000;
    error_log("Price Case 4 - Fallback: '$priceString' -> '$cleanPrice' -> $result");
    return $result;
}

header('Content-Type: application/json; charset=utf-8');
require_once(__DIR__ . '/../admin/db_connect.php');

session_start();

$input = file_get_contents('php://input');
$request = json_decode($input, true);

if (!$request) {
    parse_str($input, $request);
}

if (empty($request)) {
    $request = $_POST;
}

$action = $request['action'] ?? '';

switch ($action) {
    case 'save':
        saveChatHistory($conn, $request);
        break;
    case 'load':
        loadChatHistory($conn);
        break;
    case 'clear':
        clearChatHistory($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
}

function saveChatHistory($conn, $data) {
    try {
        $messages = $data['messages'] ?? [];
        
        // Xử lý từng message để đảm bảo products có dữ liệu hợp lệ
        foreach ($messages as &$msg) {
            if (isset($msg['products']) && is_array($msg['products'])) {
                foreach ($msg['products'] as &$product) {
                    // ✅ SỬ DỤNG HÀM PARSER MỚI
                    if (isset($product['price'])) {
                        $product['price'] = parseVietnamesePrice($product['price']);
                    } else {
                        $product['price'] = 500000;
                    }
                    
                    // ✅ Đảm bảo giá hợp lý (50k - 50tr)
                    if ($product['price'] < 50000) {
                        $product['price'] = 500000;
                    } else if ($product['price'] > 50000000) {
                        $product['price'] = 5000000;
                    }
                    
                    // Chuẩn hóa tên và ID
                    if (!isset($product['name']) || empty($product['name'])) {
                        $product['name'] = $product['description'] ?? 'Sản phẩm hoa';
                    }
                    
                    if (!isset($product['id'])) {
                        $product['id'] = $product['product_id'] ?? '0';
                    }
                }
            }
        }
        
        $messagesJson = json_encode($messages, JSON_UNESCAPED_UNICODE);
        
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            
            $check_sql = "SELECT id FROM chat_history WHERE user_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $user_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                $sql = "UPDATE chat_history SET messages = ?, updated_at = NOW() WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $messagesJson, $user_id);
            } else {
                $sql = "INSERT INTO chat_history (user_id, messages, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $user_id, $messagesJson);
            }
            
            if ($stmt->execute()) {
                // ✅ LOG PRODUCTS COUNT
                $productsCount = 0;
                foreach ($messages as $msg) {
                    if (isset($msg['products'])) {
                        $productsCount += count($msg['products']);
                    }
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Lưu chat thành công', 
                    'user_logged_in' => true,
                    'products_saved' => $productsCount
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi lưu chat: ' . $stmt->error]);
            }
            
            $stmt->close();
            $check_stmt->close();
        } else {
            echo json_encode(['success' => true, 'message' => 'Guest chat - không lưu', 'user_logged_in' => false]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

function loadChatHistory($conn) {
    try {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            
            $sql = "SELECT messages, updated_at FROM chat_history WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $messages = json_decode($row['messages'], true);
                
                // ✅ CHUẨN HÓA DỮ LIỆU KHI LOAD
                if (is_array($messages)) {
                    foreach ($messages as &$msg) {
                        if (isset($msg['products']) && is_array($msg['products'])) {
                            foreach ($msg['products'] as &$product) {
                                // ✅ SỬ DỤNG HÀM PARSER MỚI
                                if (isset($product['price'])) {
                                    $product['price'] = parseVietnamesePrice($product['price']);
                                } else {
                                    $product['price'] = 500000;
                                }
                                
                                // Đảm bảo giá hợp lý
                                if ($product['price'] < 50000) {
                                    $product['price'] = 500000;
                                } else if ($product['price'] > 50000000) {
                                    $product['price'] = 5000000;
                                }
                                
                                // Chuẩn hóa tên
                                if (!isset($product['name']) || empty($product['name'])) {
                                    $product['name'] = $product['description'] ?? 'Sản phẩm hoa';
                                }
                            }
                        }
                    }
                }
                
                // ✅ LOG PRODUCTS COUNT
                $productsCount = 0;
                if (is_array($messages)) {
                    foreach ($messages as $msg) {
                        if (isset($msg['products'])) {
                            $productsCount += count($msg['products']);
                        }
                    }
                }
                
                echo json_encode([
                    'success' => true, 
                    'messages' => $messages ?? [],
                    'updated_at' => $row['updated_at'],
                    'user_logged_in' => true,
                    'products_loaded' => $productsCount
                ]);
            } else {
                echo json_encode(['success' => true, 'messages' => [], 'user_logged_in' => true]);
            }
            
            $stmt->close();
        } else {
            echo json_encode(['success' => true, 'messages' => [], 'user_logged_in' => false]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

function clearChatHistory($conn) {
    try {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            
            $sql = "DELETE FROM chat_history WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Xóa lịch sử chat thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi xóa chat: ' . $stmt->error]);
            }
            
            $stmt->close();
        } else {
            echo json_encode(['success' => true, 'message' => 'Guest user - không có gì để xóa']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

$conn->close();
?>