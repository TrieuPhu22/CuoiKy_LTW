$(document).ready(function () {
  console.log("Cart page loaded");
  // Load giỏ hàng khi trang load
  loadCart();

  // Format currency
  function formatCurrency(amount) {
    return new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(amount);
  }

  // Chuẩn hóa đường dẫn ảnh
  function normalizeImagePath(imageUrl) {
    if (!imageUrl) {
      return "https://placehold.co/100x100/E2E8F0/A0AEC0?text=SP";
    }

    let cleanPath = imageUrl;
    if (cleanPath.startsWith("../")) {
      cleanPath = cleanPath.substring(3);
    } else if (cleanPath.startsWith("./")) {
      cleanPath = cleanPath.substring(2);
    }

    // Nếu đường dẫn đã đầy đủ thì return luôn
    if (cleanPath.startsWith("http://") || cleanPath.startsWith("https://")) {
      return cleanPath;
    }

    return cleanPath;
  }

  // Load giỏ hàng từ server
  function loadCart() {
    console.log("📦 Loading cart...");

    $.ajax({
      url: "api/cart.php",
      method: "POST",
      data: { action: "get" },
      dataType: "json",
      success: function (response) {
        console.log("✅ Cart response:", response);

        if (response.success) {
          if (response.data && response.data.length > 0) {
            displayCart(response.data, response.total);
          } else {
            showEmptyCart();
          }
        } else {
          console.error("❌ Cart error:", response.message);
          showEmptyCart();
        }
      },
      error: function (xhr, status, error) {
        console.error("❌ Error loading cart:", error);
        console.error("Response:", xhr.responseText);
        showEmptyCart();
      },
    });
  }

  // Hiển thị giỏ hàng
  function displayCart(cartItems, total) {
    console.log("📦 Displaying cart items:", cartItems);

    const container = $(".cart-page");
    container.empty();

    let cartHTML = `
      <div class="row g-4">
        <!-- LEFT COLUMN - Cart Items -->
        <div class="col-lg-8">
          <h2 class="cart-title">Giỏ hàng của bạn</h2>
          <div class="cart-items-list">
    `;

    cartItems.forEach(function (item) {
      const imageUrl = normalizeImagePath(item.image);
      const itemTotal = item.price * item.quantity;

      console.log("Item:", item.name, "Image:", imageUrl);

      cartHTML += `
        <div class="cart-item" data-product-id="${item.id}">
          <div class="cart-item__image">
            <img src="${imageUrl}" 
                 alt="${item.name}" 
                 onerror="this.src='https://placehold.co/100x100/E2E8F0/A0AEC0?text=SP'">
          </div>
          <div class="cart-item__info">
            <h5 class="cart-item__name">${item.name}</h5>
            <p class="cart-item__price">Đơn giá: ${formatCurrency(
              item.price
            )}</p>
            <p class="cart-item__total">Thành tiền: ${formatCurrency(
              itemTotal
            )}</p>
          </div>
          <div class="cart-item__actions">
            <div class="quantity-control">
              <button class="quantity-btn quantity-minus" title="Giảm số lượng">
                <i class="bi bi-dash"></i>
              </button>
              <input type="number" 
                     class="quantity-input" 
                     value="${item.quantity}" 
                     min="1" 
                     readonly>
              <button class="quantity-btn quantity-plus" title="Tăng số lượng">
                <i class="bi bi-plus"></i>
              </button>
            </div>
            <button class="btn-remove" title="Xóa sản phẩm">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      `;
    });

    const shippingFee = 30000;
    const discount = 0;
    const finalTotal = total + shippingFee - discount;

    cartHTML += `
          </div>
          <div class="cart-actions">
            <button class="btn btn-outline-secondary" id="clear-cart-btn">
              <i class="bi bi-trash me-2"></i>Xóa toàn bộ giỏ hàng
            </button>
            <a href="Page/home/home.php" class="btn btn-outline-primary">
              <i class="bi bi-arrow-left me-2"></i>Tiếp tục mua sắm
            </a>
          </div>
        </div>

        <!-- RIGHT COLUMN - Order Summary -->
        <div class="col-lg-4">
          <div class="order-summary">
            <h5 class="order-summary__title">Tóm tắt đơn hàng</h5>
            <div class="order-summary__row">
              <span>Tạm tính:</span>
              <strong>${formatCurrency(total)}</strong>
            </div>
            <div class="order-summary__row">
              <span>Phí vận chuyển:</span>
              <strong>${formatCurrency(shippingFee)}</strong>
            </div>
            <div class="order-summary__row">
              <span>Giảm giá:</span>
              <strong>${formatCurrency(discount)}</strong>
            </div>
            <hr>
            <div class="order-summary__total">
              <span>Tổng cộng:</span>
              <strong>${formatCurrency(finalTotal)}</strong>
            </div>
            <button class="btn btn-success w-100" id="checkout-btn">
              <i class="bi bi-credit-card me-2"></i>Thanh toán
            </button>
          </div>
        </div>
      </div>
    `;

    container.html(cartHTML);
    console.log("✅ Cart displayed successfully");
  }

  // Hiển thị giỏ hàng trống
  function showEmptyCart() {
    console.log("📭 Showing empty cart");

    const container = $(".cart-page");
    container.html(`
      <div class="cart-empty-state text-center py-5">
        <i class="bi bi-cart-x" style="font-size: 5rem; color: #ccc;"></i>
        <h3 class="mt-4 mb-3">Giỏ hàng của bạn đang trống</h3>
        <p class="text-muted mb-4">Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm</p>
        <a href="Page/home/home.php" class="btn btn-primary btn-lg">
          <i class="bi bi-arrow-left me-2"></i>Tiếp tục mua sắm
        </a>
      </div>
    `);
  }

  // Xử lý tăng số lượng
  $(document).on("click", ".quantity-plus", function () {
    const item = $(this).closest(".cart-item");
    const productId = item.data("product-id");
    const input = item.find(".quantity-input");
    const newQty = parseInt(input.val()) + 1;

    updateQuantity(productId, newQty);
  });

  // Xử lý giảm số lượng
  $(document).on("click", ".quantity-minus", function () {
    const item = $(this).closest(".cart-item");
    const productId = item.data("product-id");
    const input = item.find(".quantity-input");
    const currentQty = parseInt(input.val());

    if (currentQty > 1) {
      updateQuantity(productId, currentQty - 1);
    }
  });

  // Cập nhật số lượng
  function updateQuantity(productId, quantity) {
    $.ajax({
      url: "api/cart.php",
      method: "POST",
      data: {
        action: "update",
        product_id: productId,
        quantity: quantity,
      },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          location.reload(); // Reload để cập nhật từ database
        } else {
          alert(response.message || "Cập nhật thất bại!");
        }
      },
      error: function () {
        alert("Không thể cập nhật số lượng!");
      },
    });
  }

  // Xóa sản phẩm
  $(document).on("click", ".btn-remove", function () {
    const item = $(this).closest(".cart-item");
    const productId = item.data("product-id");

    if (confirm("Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?")) {
      $.ajax({
        url: "api/cart.php",
        method: "POST",
        data: {
          action: "remove",
          product_id: productId,
        },
        dataType: "json",
        success: function (response) {
          if (response.success) {
            location.reload();
          } else {
            alert(response.message || "Xóa thất bại!");
          }
        },
        error: function () {
          alert("Không thể xóa sản phẩm!");
        },
      });
    }
  });

  // Xóa toàn bộ giỏ hàng
  $(document).on("click", "#clear-cart-btn", function () {
    if (confirm("Bạn có chắc muốn xóa toàn bộ giỏ hàng?")) {
      $.ajax({
        url: "api/cart.php",
        method: "POST",
        data: { action: "clear" },
        dataType: "json",
        success: function (response) {
          if (response.success) {
            location.reload();
          }
        },
        error: function () {
          alert("Không thể xóa giỏ hàng!");
        },
      });
    }
  });

  // Thanh toán
  $(document).on("click", "#checkout-btn", function () {
    window.location.href = "Page/payment/payment.php";
  });
});
