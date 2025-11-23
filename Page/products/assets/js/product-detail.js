$(document).ready(function () {
  console.log("product-detail.js loaded");
  console.log("Product ID:", PRODUCT_ID);

  // Biến lưu thông tin sản phẩm hiện tại
  let currentProduct = null;

  // Load product detail when page loads
  loadProductDetail();

  // --- HELPER FUNCTIONS ---

  // Show toast notification
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

  // Format currency
  function formatCurrency(amount) {
    return new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(amount);
  }

  // Get category name
  function getCategoryName(key) {
    const categories = {
      hoa_sinh_nhat: "Hoa Sinh Nhật",
      hoa_khai_truong: "Hoa Khai Trương",
      chu_de: "Chủ Đề",
      thiet_ke: "Thiết Kế",
      hoa_tuoi: "Hoa Tươi",
    };
    return categories[key] || key;
  }

  // Chuẩn hóa đường dẫn ảnh
  function normalizeImagePath(imageUrl) {
    if (!imageUrl) return "";

    let cleanPath = imageUrl;
    if (cleanPath.startsWith("../")) {
      cleanPath = cleanPath.substring(3);
    } else if (cleanPath.startsWith("./")) {
      cleanPath = cleanPath.substring(2);
    }
    return cleanPath;
  }

  // --- LOAD PRODUCT DETAIL ---

  function loadProductDetail() {
    console.log("Loading product detail...");
    $("#loading-spinner").show();
    $("#product-detail-section").hide();
    $("#error-section").hide();

    $.ajax({
      url: "api/products.php",
      method: "POST",
      data: {
        action: "get_one",
        id: PRODUCT_ID,
      },
      dataType: "json",
      success: function (response) {
        console.log("API Response:", response);
        $("#loading-spinner").hide();

        if (response.success) {
          currentProduct = response.data;
          displayProductDetail(response.data);
          loadRelatedProducts(response.data.category);
        } else {
          console.error("API error:", response.message);
          $("#error-section").show();
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", status, error);
        console.error("Response Text:", xhr.responseText);
        $("#loading-spinner").hide();
        $("#error-section").show();
        showToast("Lỗi khi tải sản phẩm!", false);
      },
    });
  }

  // --- DISPLAY PRODUCT DETAIL ---

  function displayProductDetail(product) {
    console.log("Displaying product:", product);

    // Set product name
    $("#product-name").text(product.name);
    document.title = product.name + " - Chi tiết sản phẩm";

    // Set product image
    let imageUrl = "https://placehold.co/500x500/E2E8F0/A0AEC0?text=Sản+Phẩm";
    if (product.image_url) {
      imageUrl = `${normalizeImagePath(
        product.image_url
      )}?t=${new Date().getTime()}`;
    }
    $("#product-image").attr("src", imageUrl).attr("alt", product.name);

    // Set category
    $("#product-category").text(getCategoryName(product.category));

    // Set price
    $("#product-price").text(formatCurrency(product.price));

    // Set stock status
    const stockText =
      product.stock > 0 ? `Còn hàng (${product.stock} sản phẩm)` : "Hết hàng";
    const stockClass = product.stock > 0 ? "text-success" : "text-danger";
    $("#product-stock").text(stockText).addClass(stockClass);

    // Set description
    $("#product-description").text(
      product.description || "Chưa có mô tả chi tiết."
    );

    // Set max quantity
    $("#quantity").attr("max", product.stock);

    // Disable buttons if out of stock
    if (product.stock <= 0) {
      $("#add-to-cart, #buy-now").prop("disabled", true);
      $("#quantity").prop("disabled", true);
    }

    // Show product detail section
    $("#product-detail-section").fadeIn();
    console.log("✅ Product detail displayed");
  }

  // --- LOAD RELATED PRODUCTS ---

  function loadRelatedProducts(category) {
    console.log("Loading related products for category:", category);

    $.ajax({
      url: "api/products.php",
      method: "POST",
      data: { action: "get_all" },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          const relatedProducts = response.data
            .filter((p) => p.category === category && p.id != PRODUCT_ID)
            .slice(0, 4);

          displayRelatedProducts(relatedProducts);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error loading related products:", error);
      },
    });
  }

  function displayRelatedProducts(products) {
    const container = $("#related-products-container");
    container.empty();

    if (products.length === 0) {
      container.html('<p class="text-muted">Không có sản phẩm liên quan</p>');
      return;
    }

    products.forEach((product) => {
      let imageUrl = "https://placehold.co/300x300/E2E8F0/A0AEC0?text=SP";
      if (product.image_url) {
        imageUrl = normalizeImagePath(product.image_url);
      }

      const card = `
        <div class="col-md-3 col-sm-6 mb-4 product-card">
          <div class="card related-product-card" onclick="window.location.href='Page/products/products.php?id=${
            product.id
          }'">
            <img src="${imageUrl}" class="card-img-top" alt="${product.name}">
            <div class="card-body">
              <h5 class="card-title">${product.name}</h5>
              <p class="card-text">${formatCurrency(product.price)}</p>
            </div>
          </div>
        </div>
      `;
      container.append(card);
    });
  }

  // --- CART FUNCTIONS ---

  function addToCartAPI(quantity, redirectAfter = false) {
    if (!currentProduct) {
      showToast("Không tìm thấy thông tin sản phẩm!", false);
      return;
    }

    // Kiểm tra tồn kho
    if (currentProduct.stock <= 0) {
      showToast("Sản phẩm đã hết hàng!", false);
      return;
    }

    if (quantity > currentProduct.stock) {
      showToast(`Chỉ còn ${currentProduct.stock} sản phẩm!`, false);
      return;
    }

    const requestData = {
      action: "add",
      product_id: currentProduct.id,
      name: currentProduct.name,
      price: currentProduct.price,
      quantity: quantity,
      image: normalizeImagePath(currentProduct.image_url),
    };

    console.log("📦 Adding to cart:", requestData);

    $.ajax({
      url: "api/cart.php",
      method: "POST",
      data: requestData,
      dataType: "json",
      success: function (response) {
        console.log("✅ Cart API response:", response);

        if (response.success) {
          if (redirectAfter) {
            // Mua ngay - chuyển đến giỏ hàng
            window.location.href = "Page/cart/cart.php";
          } else {
            // Thêm vào giỏ - hiển thị thông báo
            showToast(
              `Đã thêm ${quantity} x "${currentProduct.name}" vào giỏ hàng!`,
              true
            );
            updateCartCount();
          }
        } else {
          // Kiểm tra xem có phải lỗi chưa đăng nhập không
          if (response.message && response.message.includes("đăng nhập")) {
            if (
              confirm(
                "Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng. Đăng nhập ngay?"
              )
            ) {
              window.location.href = "admin/signin.php?redirect=cart";
            }
          } else {
            showToast(response.message || "Có lỗi xảy ra!", false);
          }
        }
      },
      error: function (xhr, status, error) {
        console.error("❌ AJAX error:", error);
        console.error("Response:", xhr.responseText);
        showToast("Không thể thêm vào giỏ hàng!", false);
      },
    });
  }

  function updateCartCount() {
    $.ajax({
      url: "api/cart.php",
      method: "POST",
      data: { action: "get" },
      dataType: "json",
      success: function (response) {
        if (response.success && response.count > 0) {
          const badge = $(".cart-count-badge");
          if (badge.length) {
            badge.text(response.count).show();
          }
        }
      },
      error: function (error) {
        console.error("Error getting cart count:", error);
      },
    });
  }

  // --- EVENT HANDLERS ---

  // Thêm vào giỏ hàng
  $("#add-to-cart").on("click", function (e) {
    e.preventDefault();
    console.log("=== ADD TO CART CLICKED ===");

    const quantity = parseInt($("#quantity").val()) || 1;
    addToCartAPI(quantity, false);
  });

  // Mua ngay
  $("#buy-now").on("click", function (e) {
    e.preventDefault();
    console.log("=== BUY NOW CLICKED ===");

    const quantity = parseInt($("#quantity").val()) || 1;
    addToCartAPI(quantity, true);
  });

  // Tăng số lượng
  $("#increase-qty").on("click", function () {
    const qtyInput = $("#quantity");
    const currentQty = parseInt(qtyInput.val());
    const maxQty = parseInt(qtyInput.attr("max"));

    if (currentQty < maxQty) {
      qtyInput.val(currentQty + 1);
    }
  });

  // Giảm số lượng
  $("#decrease-qty").on("click", function () {
    const qtyInput = $("#quantity");
    const currentQty = parseInt(qtyInput.val());

    if (currentQty > 1) {
      qtyInput.val(currentQty - 1);
    }
  });

  // Validate input số lượng
  $("#quantity").on("input", function () {
    const value = parseInt($(this).val());
    const max = parseInt($(this).attr("max"));

    if (value < 1) $(this).val(1);
    if (value > max) $(this).val(max);
  });
});
