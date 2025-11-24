$(document).ready(function () {
  console.log("✅ Signin service loaded");

  // Hàm hiển thị thông báo (toast)
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
    console.log("📤 Form đăng nhập được submit");

    const formData = $(this).serialize();
    console.log("📦 Dữ liệu form:", formData);
    $("#auth-error").hide();

  
    $.ajax({
      url: "/CuoiKy_LTW/api/auth_controller.php", 
      method: "POST",
      data: formData,
      dataType: "json",
      beforeSend: function () {
        console.log("🚀 Đang gửi request đến API...");
      },
      success: function (response) {
        console.log("✅ Response từ server:", response);

        if (response.success) {
          showToast(response.message, true);

          // Đồng bộ giỏ hàng
          syncCartAfterLogin();

          const redirectParam = $('input[name="redirect"]').val();
          console.log("🔄 Redirect param:", redirectParam);

          setTimeout(function () {
            if (redirectParam === "cart") {
              window.location.href = "/CuoiKy_LTW/Page/cart/cart.php";
            } else if (response.role === "Admin") {
              window.location.href = "/CuoiKy_LTW/admin/admin_dashboard.php";
            } else {
              window.location.href = "/CuoiKy_LTW/Page/home/home.php";
            }
          }, 1000);
        } else {
          console.warn("⚠️ Đăng nhập thất bại:", response.message);
          $("#auth-error").text(response.message).fadeIn();
          showToast(response.message, false);
        }
      },
      error: function (xhr, status, error) {
        console.error("❌ AJAX Error:");
        console.error("  - Status:", status);
        console.error("  - Error:", error);
        console.error("  - Response Text:", xhr.responseText);
        console.error("  - Status Code:", xhr.status);

        $("#auth-error")
          .text("Có lỗi xảy ra khi đăng nhập. Vui lòng thử lại!")
          .fadeIn();
        showToast("Lỗi kết nối đến server!", false);
      },
    });
  });

  // Hàm đồng bộ giỏ hàng
  function syncCartAfterLogin() {
    console.log("🔄 Đang đồng bộ giỏ hàng...");
    $.ajax({
      url: "/CuoiKy_LTW/api/cart.php", // ✅ Đường dẫn tuyệt đối
      method: "POST",
      data: { action: "sync" },
      dataType: "json",
      success: function (response) {
        console.log("✅ Đồng bộ giỏ hàng thành công:", response);
      },
      error: function (error) {
        console.error("❌ Lỗi đồng bộ giỏ hàng:", error);
      },
    });
  }

  // Hiển thị thông báo nếu bị đá từ trang admin
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get("error") === "AccessDenied") {
    showToast(
      "Bạn cần đăng nhập với tư cách Admin để truy cập trang đó.",
      false
    );
  }
});
