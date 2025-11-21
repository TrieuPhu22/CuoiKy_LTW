<?php
session_start();
include '../admin/db_connect.php';

header('Content-Type: application/json');

// Kiểm tra đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Kiểm tra action để phân biệt cập nhật thông tin hay đổi mật khẩu
    $action = isset($_POST['action']) ? $_POST['action'] : 'update_info';
    
    // ==========================================
    // XỬ LÝ ĐỔI MẬT KHẨU
    // ==========================================
    if ($action === 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate dữ liệu
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!']);
            exit;
        }

        // Kiểm tra mật khẩu mới và xác nhận khớp nhau
        if ($new_password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'Mật khẩu mới và xác nhận không khớp!']);
            exit;
        }

        // Kiểm tra độ dài mật khẩu
        if (strlen($new_password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự!']);
            exit;
        }

        // Lấy mật khẩu hiện tại từ database
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy người dùng!']);
            exit;
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        // Xác thực mật khẩu hiện tại
        if (!password_verify($current_password, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Mật khẩu hiện tại không đúng!']);
            exit;
        }

        // Hash mật khẩu mới
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        // Cập nhật mật khẩu
        $update_sql = "UPDATE users SET password = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $new_password_hash, $user_id);

        if ($update_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Đổi mật khẩu thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $update_stmt->error]);
        }

        $update_stmt->close();
    }
    
    // ==========================================
    // XỬ LÝ CẬP NHẬT THÔNG TIN
    // ==========================================
    else if ($action === 'update_info') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);

        // Validate dữ liệu
        if (empty($username) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Tên đăng nhập và email không được để trống!']);
            exit;
        }

        // Kiểm tra email đã tồn tại (trừ email của chính user)
        $check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $email, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email đã được sử dụng bởi người khác!']);
            exit;
        }

        // Cập nhật thông tin
        $update_sql = "UPDATE users SET username = ?, email = ?, phone = ?, address = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssssi", $username, $email, $phone, $address, $user_id);

        if ($update_stmt->execute()) {
            // Cập nhật session
            $_SESSION['user_username'] = $username;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_phone'] = $phone;
            $_SESSION['user_address'] = $address;

            echo json_encode(['success' => true, 'message' => 'Cập nhật thông tin thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $update_stmt->error]);
        }

        $update_stmt->close();
        $check_stmt->close();
    }
    
    // ==========================================
    // ACTION KHÔNG HỢP LỆ
    // ==========================================
    else {
        echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ!']);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ!']);
}

$conn->close();
?>