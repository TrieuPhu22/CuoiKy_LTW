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

    // ✅ SỬ DỤNG BOOTSTRAP 5 API
    const checkoutModalEl = document.getElementById("checkoutModal");
    if (checkoutModalEl) {
      const checkoutModal = new bootstrap.Modal(checkoutModalEl);
      checkoutModal.show();
      console.log("✅ Modal opened successfully");
    } else {
      console.error("❌ Modal element not found!");

      // ✅ FALLBACK: Tạo modal động nếu không tìm thấy
      createDynamicModal();
    }

    return false;
  });

  // ✅ THÊM HÀM TẠO MODAL ĐỘNG
  function createDynamicModal() {
    console.log("🔧 Creating dynamic modal...");

    const modalHtml = `
      <div class="modal fade" id="dynamicCheckoutModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="bi bi-credit-card me-2"></i>Thông tin thanh toán
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <form id="dynamic-checkout-form">
                <div class="row">
                  <div class="col-md-6">
                    <h6 class="mb-3">Thông tin giao hàng</h6>
                    <div class="mb-3">
                      <label for="dynamic_customer_name" class="form-label">Họ và tên *</label>
                      <input type="text" class="form-control" id="dynamic_customer_name" required>
                    </div>
                    <div class="mb-3">
                      <label for="dynamic_customer_phone" class="form-label">Số điện thoại *</label>
                      <input type="tel" class="form-control" id="dynamic_customer_phone" required>
                    </div>
                    <div class="mb-3">
                      <label for="dynamic_customer_address" class="form-label">Địa chỉ giao hàng *</label>
                      <textarea class="form-control" id="dynamic_customer_address" rows="3" required></textarea>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <h6 class="mb-3">Chi tiết đơn hàng</h6>
                    <div class="alert alert-info">
                      <p class="mb-2"><strong>Tổng cộng: </strong><span class="text-danger h5">${$(
                        ".order-summary__total strong"
                      ).text()}</span></p>
                      <small><i class="bi bi-info-circle me-1"></i>Đơn hàng sẽ được giao trong 1-2 ngày.</small>
                    </div>
                  </div>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
              <button type="button" class="btn btn-success" id="dynamic-confirm-checkout-btn">
                <i class="bi bi-check-circle me-2"></i>Xác nhận thanh toán
              </button>
            </div>
          </div>
        </div>
      </div>
    `;

    // Thêm modal vào body
    $("body").append(modalHtml);

    // Mở modal
    const dynamicModal = new bootstrap.Modal(
      document.getElementById("dynamicCheckoutModal")
    );
    dynamicModal.show();

    console.log("✅ Dynamic modal created and opened");
  }

  // ✅ XÁC NHẬN THANH TOÁN - HỖ TRỢ CẢ 2 MODAL
  $(document).on(
    "click",
    "#confirm-checkout-btn, #dynamic-confirm-checkout-btn",
    function (e) {
      e.preventDefault();

      console.log("🔘 Confirm checkout clicked");

      // ✅ XÁC ĐỊNH MODAL ĐANG SỬ DỤNG
      const isDynamicModal =
        $(this).attr("id") === "dynamic-confirm-checkout-btn";
      const nameSelector = isDynamicModal
        ? "#dynamic_customer_name"
        : "#customer_name";
      const phoneSelector = isDynamicModal
        ? "#dynamic_customer_phone"
        : "#customer_phone";
      const addressSelector = isDynamicModal
        ? "#dynamic_customer_address"
        : "#customer_address";

      const customerName = $(nameSelector).val().trim();
      const customerPhone = $(phoneSelector).val().trim();
      const customerAddress = $(addressSelector).val().trim();

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
            // ✅ ĐÓNG MODAL
            const modalSelector = isDynamicModal
              ? "#dynamicCheckoutModal"
              : "#checkoutModal";
            const modalEl = document.querySelector(modalSelector);
            if (modalEl) {
              const modalInstance = bootstrap.Modal.getInstance(modalEl);
              if (modalInstance) modalInstance.hide();
            }

            showToast("Đặt hàng thành công! Đang chuyển hướng...", true);

            // ✅ CHUYỂN HƯỚNG
            setTimeout(() => {
              window.location.href = "Page/user/order_history.php";
            }, 1500);
          } else {
            showToast(response.message || "Có lỗi xảy ra!", false);
            $btn
              .prop("disabled", false)
              .html(
                '<i class="bi bi-check-circle me-2"></i>Xác nhận thanh toán'
              );
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
    }
  );

  // ✅ THÊM CSS CHO TOAST NOTIFICATIONS
  const toastCSS = `
    <style>
      .toast-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        border-radius: 6px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
      }
      .toast-notification.show {
        opacity: 1;
        transform: translateX(0);
      }
      .toast-notification.toast-success {
        background-color: #28a745;
      }
      .toast-notification.toast-error {
        background-color: #dc3545;
      }
    </style>
  `;
  $("head").append(toastCSS);

  console.log("✅ Cart script loaded successfully");
});
