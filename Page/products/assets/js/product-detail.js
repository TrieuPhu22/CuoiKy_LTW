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
          loadProductReviews(response.data.id); // Load reviews for the product
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

  // --- LOAD PRODUCT REVIEWS ---

  function loadProductReviews(productId) {
    console.log("🔄 Loading reviews for product:", productId);

    // ✅ Kiểm tra productId hợp lệ
    if (!productId || productId <= 0) {
      console.error("❌ Invalid product ID:", productId);
      displayEmptyReviews();
      return;
    }

    $.ajax({
      url: "/CuoiKy_LTW/api/reviews.php",
      method: "POST",
      data: {
        action: "get_by_product",
        product_id: productId,
      },
      dataType: "json",
      timeout: 10000, // ✅ Thêm timeout 10 giây
      success: function (response) {
        console.log("✅ Reviews API response:", response);
        if (response.success) {
          displayReviews(response.data);
        } else {
          console.warn("⚠️ No reviews:", response.message);
          displayEmptyReviews();
        }
      },
      error: function (xhr, status, error) {
        console.error("❌ Error loading reviews:", {
          status: status,
          error: error,
          response: xhr.responseText,
        });
        displayErrorReviews();
      },
    });
  }

  // ✅ Hiển thị khi chưa có đánh giá
  function displayEmptyReviews() {
    $("#average-rating").text("0");
    $("#total-reviews").text("0");
    $("#total-reviews-text").text("0");
    $("#average-stars").html('<i class="bi bi-star text-muted"></i>'.repeat(5));
    $(".rating-stars-display").html(
      '<i class="bi bi-star text-muted"></i>'.repeat(5) + " <strong>0</strong>"
    );

    let breakdownHtml = "";
    for (let i = 5; i >= 1; i--) {
      breakdownHtml += `
        <div class="d-flex align-items-center mb-2">
          <span class="me-2" style="width: 60px;">${i} <i class="bi bi-star-fill text-warning"></i></span>
          <div class="progress flex-grow-1" style="height: 8px;">
            <div class="progress-bar bg-warning" role="progressbar" style="width: 0%"></div>
          </div>
          <span class="ms-2 text-muted" style="width: 50px;">0</span>
        </div>
      `;
    }
    $("#rating-breakdown").html(breakdownHtml);

    $("#reviews-container").html(
      '<p class="text-muted">Chưa có đánh giá nào cho sản phẩm này.</p>'
    );
  }

  // ✅ Hiển thị khi có lỗi
  function displayErrorReviews() {
    $("#reviews-container").html(
      '<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>Không thể tải đánh giá. Vui lòng thử lại sau.</div>'
    );
  }

  // DISPLAY REVIEWS
  function displayReviews(data) {
    const { reviews, total, average_rating, rating_count } = data;

    console.log("📊 Displaying reviews:", { total, average_rating });

    // Update average rating
    $("#average-rating").text(average_rating);
    $("#total-reviews").text(total);
    $("#total-reviews-text").text(total);

    // Display average stars
    let avgStarsHtml = "";
    for (let i = 1; i <= 5; i++) {
      if (i <= Math.floor(average_rating)) {
        avgStarsHtml += '<i class="bi bi-star-fill text-warning"></i>';
      } else if (i === Math.ceil(average_rating) && average_rating % 1 !== 0) {
        avgStarsHtml += '<i class="bi bi-star-half text-warning"></i>';
      } else {
        avgStarsHtml += '<i class="bi bi-star text-muted"></i>';
      }
    }
    $("#average-stars").html(avgStarsHtml);

    // Display rating summary stars
    let summaryStarsHtml = "";
    for (let i = 1; i <= 5; i++) {
      summaryStarsHtml +=
        i <= Math.floor(average_rating)
          ? '<i class="bi bi-star-fill text-warning"></i>'
          : '<i class="bi bi-star text-muted"></i>';
    }
    $(".rating-stars-display").html(
      summaryStarsHtml + ` <strong>${average_rating}</strong>`
    );

    // Display rating breakdown
    let breakdownHtml = "";
    for (let i = 5; i >= 1; i--) {
      const count = rating_count[i] || 0;
      const percentage = total > 0 ? ((count / total) * 100).toFixed(0) : 0;
      breakdownHtml += `
        <div class="d-flex align-items-center mb-2">
          <span class="me-2" style="width: 60px;">${i} <i class="bi bi-star-fill text-warning"></i></span>
          <div class="progress flex-grow-1" style="height: 8px;">
            <div class="progress-bar bg-warning" role="progressbar" 
                 style="width: ${percentage}%" aria-valuenow="${percentage}" 
                 aria-valuemin="0" aria-valuemax="100"></div>
          </div>
          <span class="ms-2 text-muted" style="width: 50px;">${count}</span>
        </div>
      `;
    }
    $("#rating-breakdown").html(breakdownHtml);

    // Display reviews list
    if (reviews.length === 0) {
      $("#reviews-container").html(
        '<p class="text-muted">Chưa có đánh giá nào cho sản phẩm này.</p>'
      );
      return;
    }

    let reviewsHtml = "";
    reviews.forEach((review) => {
      let starsHtml = "";
      for (let i = 1; i <= 5; i++) {
        starsHtml +=
          i <= review.rating
            ? '<i class="bi bi-star-fill text-warning"></i>'
            : '<i class="bi bi-star text-muted"></i>';
      }

      // ✅ Phần reply từ Admin
      let replyHtml = "";
      if (review.reply_id && review.reply) {
        replyHtml = `
          <div class="admin-reply mt-3 p-3" style="background: #f0f9ff; border-left: 4px solid #3b82f6; border-radius: 4px;">
            <div class="d-flex align-items-center mb-2">
              <i class="bi bi-person-badge text-primary me-2"></i>
              <strong class="text-primary">Phản hồi từ Flower Shop</strong>
            </div>
            <p class="mb-1">${review.reply}</p>
            <small class="text-muted">${review.reply_date || ""}</small>
          </div>
        `;
      }

      reviewsHtml += `
        <div class="card mb-3">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <strong>${review.username}</strong>
                <div class="mt-1">${starsHtml}</div>
              </div>
              <small class="text-muted">${review.formatted_date}</small>
            </div>
            <p class="mb-0">${
              review.comment ||
              '<em class="text-muted">Người dùng không để lại nhận xét</em>'
            }</p>
            ${replyHtml}
          </div>
        </div>
      `;
    });

    $("#reviews-container").html(reviewsHtml);
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
