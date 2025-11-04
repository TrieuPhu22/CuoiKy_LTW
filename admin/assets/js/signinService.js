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