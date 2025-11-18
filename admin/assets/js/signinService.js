$(document).ready(function () {
  console.log("Signin service loaded");

  // Hàm hiển thị thông báo (toast) - copy từ app.js
  function showToast(message, isSuccess = true) {
    const toast = $("#toast-message");
    toast.text(message);
    toast
      .removeClass("success error")
      .addClass(isSuccess ? "success" : "error");
    toast.addClass("show");
    setTimeout(() => {
      toast.removeClass("show");
    }, 3000);
  }

  // Xử lý form đăng nhập
  $("#signin-form").on("submit", function (e) {
    e.preventDefault();
    console.log("Form submitted");

    // Lấy dữ liệu từ form
    const formData = $(this).serialize();
    console.log("Form data:", formData);
    $("#auth-error").hide(); // Ẩn lỗi cũ

    // Gửi AJAX request
    $.ajax({
      url: "../api/auth_controller.php",
      method: "POST",
      data: formData,
      dataType: "json",
      success: function (response) {
        console.log("Response:", response);

        if (response.success) {
          // Đăng nhập thành công
          showToast(response.message, true);

          // Đồng bộ giỏ hàng từ session vào database
          syncCartAfterLogin();

          // Kiểm tra xem có redirect parameter không
          const redirectParam = $('input[name="redirect"]').val();

          setTimeout(function () {
            if (redirectParam === "cart") {
              // Redirect về giỏ hàng
              window.location.href = "../Page/cart/cart.php";
            } else if (response.role === "Admin") {
              // Admin -> Dashboard
              window.location.href = "admin_dashboard.php";
            } else {
              // User thường -> Trang chủ
              window.location.href = "../index.php";
            }
          }, 1000);
        } else {
          // Đăng nhập thất bại
          showError(response.message);
          showToast(response.message, false);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", error);
        console.error("Response:", xhr.responseText);
        showError("Có lỗi xảy ra khi đăng nhập. Vui lòng thử lại!");
        showToast("Có lỗi xảy ra!", false);
      },
    });
  });

  // Hiển thị lỗi
  function showError(message) {
    const errorDiv = $("#auth-error");
    errorDiv.text(message).fadeIn();

    setTimeout(function () {
      errorDiv.fadeOut();
    }, 5000);
  }

  // Hiển thị thông báo (nếu bị đá từ trang admin)
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get("error") === "AccessDenied") {
    showToast(
      "Bạn cần đăng nhập với tư cách Admin để truy cập trang đó.",
      false
    );
  }

  // Hàm đồng bộ giỏ hàng sau khi đăng nhập
  function syncCartAfterLogin() {
    $.ajax({
      url: "../api/cart.php",
      method: "POST",
      data: { action: "sync" },
      dataType: "json",
      success: function (response) {
        console.log("Cart sync response:", response);
      },
      error: function (error) {
        console.error("Error syncing cart:", error);
      },
    });
  }
});
