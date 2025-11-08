<?php

include '../admin/db_connect.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'get_all':
            getAllUsers($conn);
            break;
        case 'get_one':
            getOneUser($conn, $_POST['id']);
            break;
        case 'add':
            addUser($conn, $_POST);
            break;
        case 'update':
            updateUser($conn, $_POST);
            break;
        case 'delete':
            deleteUser($conn, $_POST['id']);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ.']);
    }
} else {
    getAllUsers($conn);
}

// --- CÁC HÀM XỬ LÝ ---

function getAllUsers($conn) {
    $sql = "SELECT id, username, email, role, phone, address, DATE_FORMAT(join_date, '%d/%m/%Y') AS join_date FROM users";
    $result = $conn->query($sql);
    $users = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    echo json_encode(['success' => true, 'data' => $users]);
}

function getOneUser($conn, $id) {
    $id = intval($id);
    $sql = "SELECT id, username, email, role, phone, address FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy người dùng.']);
    }
    $stmt->close();
}

function addUser($conn, $data) {
    $username = $data['username'];
    $email = $data['email'];
    $password = $data['password'];
    $role = $data['role'];
    $phone = isset($data['phone']) ? $data['phone'] : '';
    $address = isset($data['address']) ? $data['address'] : '';

    // Kiểm tra email đã tồn tại
    $check_sql = "SELECT id FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email đã được sử dụng!']);
        $check_stmt->close();
        return;
    }
    $check_stmt->close();

    // Hash mật khẩu
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Thêm user
    $sql = "INSERT INTO users (username, email, password, role, phone, address) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $username, $email, $password_hash, $role, $phone, $address);

    if ($stmt->execute()) {
        $new_id = $stmt->insert_id;
        echo json_encode([
            'success' => true, 
            'message' => 'Thêm người dùng thành công!',
            'data' => [
                'id' => $new_id,
                'username' => $username,
                'email' => $email,
                'role' => $role,
                'phone' => $phone,
                'address' => $address,
                'join_date' => date('d/m/Y')
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    $stmt->close();
}

function updateUser($conn, $data) {
    $id = intval($data['id']);
    $username = $data['username'];
    $email = $data['email'];
    $role = $data['role'];
    $phone = isset($data['phone']) ? $data['phone'] : '';
    $address = isset($data['address']) ? $data['address'] : '';

    // Kiểm tra email đã tồn tại (trừ email của chính user)
    $check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $email, $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email đã được sử dụng bởi người khác!']);
        $check_stmt->close();
        return;
    }
    $check_stmt->close();

    // Kiểm tra có đổi mật khẩu không
    if (!empty($data['password'])) {
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $sql = "UPDATE users SET username = ?, email = ?, password = ?, role = ?, phone = ?, address = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $username, $email, $password_hash, $role, $phone, $address, $id);
    } else {
        // Không đổi mật khẩu
        $sql = "UPDATE users SET username = ?, email = ?, role = ?, phone = ?, address = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $username, $email, $role, $phone, $address, $id);
    }

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Cập nhật người dùng thành công!',
            'data' => [
                'id' => $id,
                'username' => $username,
                'email' => $email,
                'role' => $role,
                'phone' => $phone,
                'address' => $address
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    $stmt->close();
}

function deleteUser($conn, $id) {
    $id = intval($id);
    
    // Không cho xóa admin chính mình (nếu muốn)
    // session_start();
    // if ($id == $_SESSION['user_id']) {
    //     echo json_encode(['success' => false, 'message' => 'Không thể xóa chính mình!']);
    //     return;
    // }

    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Xoá người dùng thành công!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    $stmt->close();
}

$conn->close();
?>