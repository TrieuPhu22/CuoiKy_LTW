<?php
include '../admin/db_connect.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'get_all':
            getAllUsers($conn);
            break;
        
        case 'add':
            addUser($conn, $_POST);
            break;

        case 'get_one':
            getOneUser($conn, $_POST['id']);
            break;
            
        case 'update':
            updateUser($conn, $_POST);
            break;
            
        case 'delete':
            deleteUser($conn, $_POST['id']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ.']);
            break;
    }
} else {
    getAllUsers($conn);
}

// --- CÁC HÀM XỬ LÝ ---

function getAllUsers($conn) {
    $sql = "SELECT id, username, email, role, DATE_FORMAT(join_date, '%d/%m/%Y') AS join_date FROM users";
    $result = $conn->query($sql);
    $users = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    echo json_encode(['success' => true, 'data' => $users]);
}

function getOneUser($conn, $id) {
    $id = intval($id);
    $sql = "SELECT id, username, email, role FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => true, 'data' => $result->fetch_assoc()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy người dùng.']);
    }
    $stmt->close();
}

function addUser($conn, $data) {
    $username = $data['username'];
    $email = $data['email'];
    $role = $data['role'];
    // LƯU Ý: Trong ứng dụng thực tế, bạn PHẢI băm (hash) mật khẩu.
    // $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
    // Ở đây chúng ta bỏ qua mật khẩu để làm đơn giản form.

    $stmt = $conn->prepare("INSERT INTO users (username, email, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $role);

    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        $newUser = [
            'id' => $newId,
            'username' => $username,
            'email' => $email,
            'role' => $role,
            'join_date' => date('d/m/Y') // Lấy ngày hiện tại
        ];
        echo json_encode(['success' => true, 'message' => 'Thêm người dùng thành công!', 'data' => $newUser]);
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

    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
    $stmt->bind_param("sssi", $username, $email, $role, $id);

    if ($stmt->execute()) {
        $updatedUser = [
            'id' => $id,
            'username' => $username,
            'email' => $email,
            'role' => $role
        ];
        echo json_encode(['success' => true, 'message' => 'Cập nhật người dùng thành công!', 'data' => $updatedUser]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    $stmt->close();
}

function deleteUser($conn, $id) {
    $id = intval($id);
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Xoá người dùng thành công.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    $stmt->close();
}

$conn->close();
?>
