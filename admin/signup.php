<?php
// Bắt đầu session
session_start();

// Nếu đã đăng nhập, chuyển hướng họ đi
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'Admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: ../page/home/home.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-100">

    <div class="auth-container">
        <form id="signup-form" class="auth-form">
            <h2 class="auth-title">Tạo tài khoản</h2>
            <p class="auth-subtitle">Tham gia với chúng tôi ngay hôm nay!</p>
            
            <input type="hidden" name="action" value="signup">

            <div class="form-group">
                <label for="username">Tên người dùng:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-submit btn-full">Đăng ký</button>
            </div>
            
            <p class="auth-switch">
                Đã có tài khoản? <a href="signin.php">Đăng nhập</a>
            </p>

            <div id="auth-error" class="auth-error" style="display: none;"></div>
        </form>
    </div>

    <!-- Thông báo Toast -->
    <div id="toast-message" class="toast-message"></div>

    <script>
    $(document).ready(function() {
        // Hàm hiển thị thông báo (toast)
        function showToast(message, isSuccess = true) {
            const toast = $('#toast-message');
            toast.text(message);
            toast.removeClass('success error').addClass(isSuccess ? 'success' : 'error');
            toast.addClass('show');
            setTimeout(() => { toast.removeClass('show'); }, 3000);
        }

        // Xử lý form đăng ký
        $('#signup-form').on('submit', function(e) {
            e.preventDefault();
            $('#auth-error').hide();

            // Kiểm tra mật khẩu khớp
            const password = $('#password').val();
            const confirmPassword = $('#confirm_password').val();
            if (password !== confirmPassword) {
                $('#auth-error').text('Mật khẩu xác nhận không khớp.').show();
                return;
            }

            const formData = $(this).serialize();

            $.ajax({
                url: '../api/auth_controller.php',
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showToast('Đăng ký thành công! Đang chuyển đến trang đăng nhập...', true);
                        // Chờ 2 giây rồi chuyển hướng
                        setTimeout(() => {
                            window.location.href = 'signin.php';
                        }, 2000);
                    } else {
                        $('#auth-error').text(response.message).show();
                    }
                },
                error: function() {
                    $('#auth-error').text('Lỗi máy chủ. Vui lòng thử lại.').show();
                }
            });
        });
    });
    </script>

</body>
</html>

