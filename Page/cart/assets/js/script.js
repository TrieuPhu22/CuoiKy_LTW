$(document).ready(function () {
  console.log("🛒 Cart page loaded");

  // Format currency
  function formatCurrency(amount) {
    return new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(amount);
  }

  // Hàm hiển thị toast
  function showToast(message, isSuccess = true) {
    // Remove existing toasts
    $(".toast-notification").remove();

    const toast = $('<div class="toast-notification"></div>')
      .text(message)
      .addClass(isSuccess ? "toast-success" : "toast-error")
      .appendTo("body");

    setTimeout(() => toast.addClass("show"), 100);
    setTimeout(() => {
      toast.removeClass("show");
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  // Hàm cập nhật tổng tiền
  function updateCartTotal() {
    $.ajax({
      url: "/CuoiKy_LTW/api/cart.php",
      method: "POST",
      data: { action: "get" },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          const cartItems = response.data || [];
          const cartTotal = response.total || 0;
          const shippingFee = 30000;
          const discount = 0;
          const finalTotal = cartTotal + shippingFee - discount;

          $(".order-summary__row:eq(0) strong").text(formatCurrency(cartTotal));
          $(".order-summary__total strong").text(formatCurrency(finalTotal));
          $("#modal-total-price").text(formatCurrency(finalTotal));

          if (cartItems.length === 0) {
            location.reload();
          }
        }
      },
    });
  }

  // TĂNG SỐ LƯỢNG
  $(document).on("click", ".quantity-plus", function (e) {
    e.preventDefault();
    const $cartItem = $(this).closest(".cart-item");
    const productId = $cartItem.data("product-id");
    const $input = $cartItem.find(".quantity-input");
    const currentQty = parseInt($input.val());

    $.ajax({
      url: "/CuoiKy_LTW/api/cart.php",
      method: "POST",
      data: {
        action: "update",
        product_id: productId,
        quantity: currentQty + 1,
      },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          $input.val(currentQty + 1);
          const price = parseFloat(
            $cartItem.find(".cart-item__price").text().replace(/[^\d]/g, "")
          );
          const newTotal = price * (currentQty + 1);
          $cartItem
            .find(".cart-item__total")
            .text(`Thành tiền: ${formatCurrency(newTotal)}`);
          updateCartTotal();
          showToast("Đã cập nhật số lượng!", true);
        } else {
          showToast(response.message || "Lỗi khi cập nhật!", false);
        }
      },
      error: function () {
        showToast("Lỗi kết nối server!", false);
      },
    });
  });

  // GIẢM SỐ LƯỢNG
  $(document).on("click", ".quantity-minus", function (e) {
    e.preventDefault();
    const $cartItem = $(this).closest(".cart-item");
    const productId = $cartItem.data("product-id");
    const $input = $cartItem.find(".quantity-input");
    const currentQty = parseInt($input.val());

    if (currentQty <= 1) {
      showToast("Số lượng tối thiểu là 1!", false);
      return;
    }

    $.ajax({
      url: "/CuoiKy_LTW/api/cart.php",
      method: "POST",
      data: {
        action: "update",
        product_id: productId,
        quantity: currentQty - 1,
      },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          $input.val(currentQty - 1);
          const price = parseFloat(
            $cartItem.find(".cart-item__price").text().replace(/[^\d]/g, "")
          );
          const newTotal = price * (currentQty - 1);
          $cartItem
            .find(".cart-item__total")
            .text(`Thành tiền: ${formatCurrency(newTotal)}`);
          updateCartTotal();
          showToast("Đã cập nhật số lượng!", true);
        } else {
          showToast(response.message || "Lỗi khi cập nhật!", false);
        }
      },
    });
  });

  // XÓA SẢN PHẨM
  $(document).on("click", ".btn-remove", function (e) {
    e.preventDefault();
    const $cartItem = $(this).closest(".cart-item");
    const productId = $cartItem.data("product-id");

    if (confirm("Bạn có chắc chắn muốn xóa sản phẩm này?")) {
      $.ajax({
        url: "/CuoiKy_LTW/api/cart.php",
        method: "POST",
        data: { action: "remove", product_id: productId },
        dataType: "json",
        success: function (response) {
          if (response.success) {
            $cartItem.fadeOut(300, function () {
              $(this).remove();
              updateCartTotal();
            });
            showToast("Đã xóa sản phẩm khỏi giỏ hàng!", true);
          } else {
            showToast(response.message || "Lỗi khi xóa!", false);
          }
        },
      });
    }
  });

  // XÓA TOÀN BỘ GIỎ HÀNG
  $(document).on("click", "#clear-cart-btn", function (e) {
    e.preventDefault();
    if (confirm("Bạn có chắc chắn muốn xóa toàn bộ giỏ hàng?")) {
      $.ajax({
        url: "/CuoiKy_LTW/api/cart.php",
        method: "POST",
        data: { action: "clear" },
        dataType: "json",
        success: function (response) {
          if (response.success) {
            showToast("Đã xóa toàn bộ giỏ hàng!", true);
            setTimeout(() => location.reload(), 1000);
          } else {
            showToast(response.message || "Lỗi khi xóa!", false);
          }
        },
      });
    }
  });

  // ===== NÚT THANH TOÁN - CHỈ MỞ MODAL =====
  $(document).on("click", "#checkout-btn", function (e) {
    e.preventDefault();
    e.stopPropagation();

    console.log("🔘 Checkout button clicked");

    const checkoutModal = new bootstrap.Modal(
      document.getElementById("checkoutModal")
    );
    checkoutModal.show();

    console.log("✅ Modal opened");
    return false;
  });

  // ===== XÁC NHẬN THANH TOÁN =====
  $(document).on("click", "#confirm-checkout-btn", function (e) {
    e.preventDefault();

    console.log("🔘 Confirm checkout clicked");

    const customerName = $("#customer_name").val().trim();
    const customerPhone = $("#customer_phone").val().trim();
    const customerAddress = $("#customer_address").val().trim();

    if (!customerName || !customerPhone || !customerAddress) {
      showToast("Vui lòng điền đầy đủ thông tin!", false);
      return false;
    }

    const $btn = $(this);
    $btn
      .prop("disabled", true)
      .html(
        '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...'
      );

    console.log("📤 Sending order request...");

    $.ajax({
      url: "/CuoiKy_LTW/api/orders.php",
      method: "POST",
      data: {
        action: "create",
        customer_name: customerName,
        customer_phone: customerPhone,
        customer_address: customerAddress,
      },
      dataType: "json",
      success: function (response) {
        console.log("✅ Response:", response);

        if (response.success) {
          const modalInstance = bootstrap.Modal.getInstance(
            document.getElementById("checkoutModal")
          );
          if (modalInstance) modalInstance.hide();

          showToast("Đặt hàng thành công! Đang chuyển hướng...", true);

          // ✅ SỬA LẠI ĐƯỜNG DẪN - Dùng relative path
          setTimeout(() => {
            window.location.href = "Page/user/order_history.php";
          }, 1500);
        } else {
          showToast(response.message || "Có lỗi xảy ra!", false);
          $btn
            .prop("disabled", false)
            .html('<i class="bi bi-check-circle me-2"></i>Xác nhận thanh toán');
        }
      },
      error: function (xhr, status, error) {
        console.error("❌ Error:", error);
        console.error("Response:", xhr.responseText);
        showToast("Lỗi kết nối server!", false);
        $btn
          .prop("disabled", false)
          .html('<i class="bi bi-check-circle me-2"></i>Xác nhận thanh toán');
      },
    });

    return false;
  });
});
