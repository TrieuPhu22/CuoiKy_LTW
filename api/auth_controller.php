<?php
// Bắt đầu session
session_start();

// Bao gồm tệp kết nối CSDL (chú ý đường dẫn '../' vì tệp này nằm trong /api)
include '../admin/db_connect.php';

// Đặt header là JSON
header('Content-Type: application/json');

// Kiểm tra xem 'action' có được gửi không
if (!isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ.']);
    exit;
}

$action = $_POST['action'];

// =======================
// XỬ LÝ ĐĂNG KÝ (SIGNUP)
// =======================
if ($action === 'signup') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // --- Kiểm tra đầu vào ---
    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email không hợp lệ.']);
        exit;
    }

    // --- Kiểm tra xem email đã tồn tại chưa ---
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email này đã được sử dụng.']);
        $stmt->close();
        exit;
    }
    $stmt->close();

    // --- Mật khẩu an toàn: Băm (Hash) mật khẩu ---
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // --- Thêm người dùng mới (Mặc định là 'User') ---
    $default_role = 'User';
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $password_hash, $default_role);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Đăng ký thành công!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi đăng ký: ' . $stmt->error]);
    }
    $stmt->close();
}

// =======================
// XỬ LÝ ĐĂNG NHẬP (SIGNIN)
// =======================
elseif ($action === 'signin') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // --- Kiểm tra đầu vào ---
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin.']);
        exit;
    }

    // --- Tìm người dùng bằng email ---
    $stmt = $conn->prepare("SELECT id, username, email, password, role, phone, address FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Không tìm thấy người dùng
        echo json_encode(['success' => false, 'message' => 'Email hoặc mật khẩu không chính xác.']);
        $stmt->close();
        exit;
    }

    // --- Lấy dữ liệu người dùng ---
    $user = $result->fetch_assoc();
    $stmt->close();

    // --- Xác thực mật khẩu ---
    if (password_verify($password, $user['password'])) {
        // Đăng nhập thành công!
        
        // --- Lưu thông tin vào SESSION ---
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_phone'] = $user['phone'];
        $_SESSION['user_address'] = $user['address'];
        $_SESSION['user_role'] = $user['role'];

        // Trả về thông báo thành công và vai trò (role)
        echo json_encode([
            'success' => true, 
            'message' => 'Đăng nhập thành công!',
            'role' => $user['role'] // Gửi vai trò về cho JavaScript
        ]);
        
    } else {
        // Sai mật khẩu
        echo json_encode(['success' => false, 'message' => 'Email hoặc mật khẩu không chính xác.']);
    }

}

// --- HÀNH ĐỘNG KHÔNG HỢP LỆ ---
else {
    echo json_encode(['success' => false, 'message' => 'Hành động không xác định.']);
}

// Đóng kết nối CSDL
$conn->close();
?>

