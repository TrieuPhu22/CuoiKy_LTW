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