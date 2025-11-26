<?php
// filepath: c:\xampp\htdocs\CuoiKy_LTW\api\chat_history.php
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
        // ✅ LƯU CẢ MESSAGES VÀ PRODUCTS DATA
        $messages = json_encode($data['messages'] ?? [], JSON_UNESCAPED_UNICODE);
        
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
                $stmt->bind_param("si", $messages, $user_id);
            } else {
                $sql = "INSERT INTO chat_history (user_id, messages, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $user_id, $messages);
            }
            
            if ($stmt->execute()) {
                // ✅ LOG PRODUCTS COUNT
                $messagesArray = json_decode($messages, true);
                $productsCount = 0;
                foreach ($messagesArray as $msg) {
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