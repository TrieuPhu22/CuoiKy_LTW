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
    <title>Đăng nhập</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-100">

    <div class="auth-container">
        <form id="signin-form" class="auth-form">
            <h2 class="auth-title">Đăng nhập</h2>
            <p class="auth-subtitle">Chào mừng trở lại! Vui lòng nhập thông tin của bạn.</p>
            
            <!-- Trường ẩn cho hành động -->
            <input type="hidden" name="action" value="signin">

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-submit btn-full">Đăng nhập</button>
            </div>
            
            <p class="auth-switch">
                Chưa có tài khoản? <a href="signup.php">Đăng ký ngay</a>
            </p>
            
            <div id="auth-error" class="auth-error" style="display: none;"></div>
        </form>
    </div>

    <!-- Thông báo Toast -->
    <div id="toast-message" class="toast-message"></div>

    <script>
    $(document).ready(function() {
        // Hàm hiển thị thông báo (toast) - copy từ app.js
        function showToast(message, isSuccess = true) {
            const toast = $('#toast-message');
            toast.text(message);
            toast.removeClass('success error').addClass(isSuccess ? 'success' : 'error');
            toast.addClass('show');
            setTimeout(() => { toast.removeClass('show'); }, 3000);
        }

        // Xử lý form đăng nhập
        $('#signin-form').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            $('#auth-error').hide(); // Ẩn lỗi cũ

            $.ajax({
                url: '../api/auth_controller.php',
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showToast('Đăng nhập thành công! Đang chuyển hướng...', true);
                        // Chờ 2 giây để xem toast rồi chuyển hướng
                        setTimeout(() => {
                            if (response.role === 'Admin') {
                                window.location.href = 'admin_dashboard.php';
                            } else {
                                window.location.href = '../page/home/home.php';
                            }
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

        // Hiển thị thông báo (nếu bị đá từ trang admin)
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('error') === 'AccessDenied') {
            showToast('Bạn cần đăng nhập với tư cách Admin để truy cập trang đó.', false);
        }
    });
    </script>

</body>
</html>

