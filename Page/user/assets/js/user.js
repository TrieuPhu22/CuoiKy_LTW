$(document).ready(function () {
    // Hàm hiển thị thông báo toast
    function showToast(message, isSuccess = true) {
        let toast = $("#toast-message");
        if (toast.length === 0) {
            $("body").append(
                '<div id="toast-message" style="position: fixed; top: 20px; right: 20px; padding: 15px 20px; border-radius: 5px; z-index: 9999; display: none; min-width: 250px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></div>'
            );
            toast = $("#toast-message");
        }

        toast.text(message);
        toast.css({
            backgroundColor: isSuccess ? "#28a745" : "#dc3545",
            color: "white",
            display: "block",
        });

        setTimeout(() => {
            toast.fadeOut();
        }, 3000);
    }

    // Xử lý form cập nhật thông tin
    $("#update-info-form").on("submit", function (e) {
        e.preventDefault();

        const formData = $(this).serialize() + "&action=update_info";

        $.ajax({
            url: "api/userUpdate.php",
            method: "POST",
            data: formData,
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    showToast(response.message, true);
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast(response.message, false);
                }
            },
            error: function (xhr, status, error) {
                console.error("Error:", error);
                console.error("Response:", xhr.responseText);
                showToast("Lỗi kết nối server! Vui lòng thử lại.", false);
            },
        });
    });

    // Xử lý form đổi mật khẩu
    $("#change-password-form").on("submit", function (e) {
        e.preventDefault();

        const currentPassword = $("#current_password").val();
        const newPassword = $("#new_password").val();
        const confirmPassword = $("#confirm_password").val();

        // Validate phía client
        if (newPassword !== confirmPassword) {
            showToast("Mật khẩu mới và xác nhận không khớp!", false);
            return;
        }

        if (newPassword.length < 6) {
            showToast("Mật khẩu mới phải có ít nhất 6 ký tự!", false);
            return;
        }

        const formData = $(this).serialize() + "&action=change_password";

        $.ajax({
            url: "api/userUpdate.php",
            method: "POST",
            data: formData,
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    showToast(response.message, true);
                    // Reset form sau khi thành công
                    $("#change-password-form")[0].reset();
                } else {
                    showToast(response.message, false);
                }
            },
            error: function (xhr, status, error) {
                console.error("Error:", error);
                console.error("Response:", xhr.responseText);
                showToast("Lỗi kết nối server! Vui lòng thử lại.", false);
            },
        });
    });
});