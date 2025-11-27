$(document).ready(function () {
  // --- XỬ LÝ CHUNG ---

  // Hàm hiển thị thông báo (toast)
  function showToast(message, isSuccess = true) {
    const toast = $("#toast-message");
    toast.text(message);
    toast
      .removeClass("success error")
      .addClass(isSuccess ? "success" : "error");
    toast.addClass("show");

    // Tự động ẩn sau 2 giây
    setTimeout(() => {
      toast.removeClass("show");
    }, 2000);
  }

  // Xử lý đóng Modal
  $('.modal-close, [data-dismiss="modal"]').on("click", function () {
    $(this).closest(".modal").css("display", "none");
  });

  // Đóng modal khi nhấp ra ngoài
  $(window).on("click", function (e) {
    if ($(e.target).is(".modal")) {
      $(e.target).css("display", "none");
    }
  });
  // Hàm lấy tên danh mục từ key
  function getCategoryName(key) {
    const categories = {
      hoa_sinh_nhat: "Hoa Sinh Nhật",
      hoa_khai_truong: "Hoa Khai Trương",
      chu_de: "Chủ Đề",
      thiet_ke: "Thiết Kế",
      hoa_tuoi: "Hoa Tươi",
    };
    return categories[key] || (key ? key : "Chưa có"); // Hiển thị key nếu không khớp
  }

  // Hàm định dạng trạng thái (cho user và order)
  function formatBadge(type, value) {
    if (type === "role") {
      if (value === "Admin") {
        return `<span class="badge badge-blue">Admin</span>`;
      }
      return `<span class="badge badge-green">User</span>`;
    }
    if (type === "status") {
      if (value === "Đã giao") {
        return `<span class="badge badge-green">${value}</span>`;
      }
      if (value === "Đang xử lý") {
        return `<span class="badge badge-yellow">${value}</span>`;
      }
      if (value === "Đã huỷ") {
        return `<span class="badge badge-red">${value}</span>`;
      }
    }
    return value; // Mặc định
  }

  // --- XỬ LÝ CHUYỂN TAB ---

  // Tải dữ liệu lần đầu
  loadDashboardStats(); // ✅ Thêm dòng này
  loadProducts();

  $(".nav-link").on("click", function (e) {
    e.preventDefault();
    const targetId = $(this).data("target");

    $(".content-section").hide();
    $("#" + targetId).show();

    $(".nav-link").removeClass("active");
    $(this).addClass("active");

    // Tải dữ liệu tương ứng khi chuyển tab
    if (targetId === "dashboard-section") {
      // ✅ Thêm điều kiện này
      loadDashboardStats();
    } else if (targetId === "products-section") {
      loadProducts();
    } else if (targetId === "users-section") {
      loadUsers();
    } else if (targetId === "orders-section") {
      loadOrders();
    } else if (targetId === "reviews-section") {
      // ✅ Thêm điều kiện này
      loadReviews();
    }
  });

  // ===================================
  // --- QUẢN LÝ SẢN PHẨM (PRODUCTS) ---
  // ===================================

  // ⚡ Hàm lấy tên subcategory
  function getSubcategoryName(category, subcategory) {
    const subcategories = {
      chu_de: {
        hoa_cam_tay: "Hoa Cầm Tay",
        hoa_chuc_mung: "Hoa Chúc Mừng",
        hoa_tang_le_hoa_chia_buon: "Hoa Tăng Lễ Hoa Chia Buồn",
      },
      hoa_sinh_nhat: {
        sang_trong: "Sang Trọng",
        tang_nguoi_yeu: "Tặng Người Yêu",
      },
      hoa_khai_truong: {
        de_ban: "Để Bàn",
        hien_dai: "Hiện Đại",
      },
      thiet_ke: {
        bo_hoa: "Bó Hoa",
        gio_hoa: "Giỏ Hoa",
      },
      hoa_tuoi: {
        hoa_hong: "Hoa Hồng",
        hoa_baby: "Hoa Baby",
        hoa_huong_duong: "Hoa Hướng Dương",
      },
    };

    if (subcategories[category] && subcategories[category][subcategory]) {
      return subcategories[category][subcategory];
    }
    return "";
  }

  // Hàm tải danh sách sản phẩm
  function loadProducts() {
    $.ajax({
      url: "../api/products.php",
      method: "POST",
      data: { action: "get_all" },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          const tbody = $("#products-table tbody");
          tbody.empty();
          response.data.forEach(function (product) {
            tbody.append(renderProductRow(product));
          });
        }
      },
      error: function (xhr, status, error) {
        showToast("Lỗi tải danh sách sản phẩm: " + error, false);
      },
    });
  }

  // ⚡ Xử lý khi chọn category → load subcategories
  $("#product-category").on("change", function () {
    const category = $(this).val();

    if (category) {
      $.ajax({
        url: "../api/products.php",
        method: "POST",
        data: { action: "get_subcategories", category: category },
        dataType: "json",
        success: function (response) {
          if (response.success && Object.keys(response.data).length > 0) {
            const subcatSelect = $("#product-subcategory");
            subcatSelect.empty();
            subcatSelect.append(
              '<option value="">-- Chọn danh mục con --</option>'
            );

            // ⚡ response.data = { id: {key, name}, ... }
            $.each(response.data, function (id, obj) {
              subcatSelect.append(`<option value="${id}">${obj.name}</option>`);
            });

            $("#subcategory-group").show();
          } else {
            $("#subcategory-group").hide();
          }
        },
      });
    } else {
      $("#subcategory-group").hide();
    }
  });
  // Hàm render một hàng sản phẩm
  function renderProductRow(product) {
    const formattedPrice = new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(product.price);

    const imageUrl = product.image_url
      ? `${product.image_url}?t=${new Date().getTime()}`
      : "https://placehold.co/100x100/E2E8F0/A0AEC0?text=SP";

    let categoryText = getCategoryName(product.category);
    if (product.subcategory_name) {
      // ⚡ Dùng subcategory_name từ database
      categoryText += ` / ${product.subcategory_name}`;
    }

    return `
        <tr data-id="${product.id}">
            <td>${product.id}</td>
            <td>
                <div class="product-cell">
                    <img src="${imageUrl}" alt="Product Image">
                    <div>
                        <p class="product-name">${product.name}</p>
                        <p class="product-category">${
                          product.description
                            ? product.description.substring(0, 30)
                            : ""
                        }...</p>
                    </div>
                </div>
            </td>
            <td>${formattedPrice}</td>
            <td>${categoryText}</td> 
            <td>${product.stock}</td>
            <td>
                <button class="btn btn-edit btn-edit-product" data-id="${
                  product.id
                }">Sửa</button>
                <button class="btn btn-delete btn-delete-product" data-id="${
                  product.id
                }">Xoá</button>
            </td>
        </tr>
    `;
  }

  // Mở Modal Thêm Sản phẩm
  $("#btn-add-product").on("click", function () {
    $("#product-form")[0].reset(); // Reset form
    $("#product-modal-title").text("Thêm Sản Phẩm Mới");
    $("#product-action").val("add");
    $("#product-id").val("");
    $("#product-existing-image").val(""); // Xoá ảnh cũ
    $("#product-image-file").val(""); // Xoá file đã chọn
    $("#product-category").val("");
    $("#subcategory-group").hide();
    $("#product-modal").css("display", "flex");
  });

  // Mở Modal Sửa Sản phẩm
  $(document).on("click", ".btn-edit-product", function () {
    const id = $(this).data("id");

    // Gọi AJAX để lấy thông tin chi tiết sản phẩm
    $.ajax({
      url: "../api/products.php",
      method: "POST",
      data: { action: "get_one", id: id },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          const product = response.data;
          // Đổ dữ liệu vào form
          $("#product-id").val(product.id);
          $("#product-name").val(product.name);
          $("#product-description").val(product.description);
          $("#product-price").val(product.price);
          $("#product-stock").val(product.stock);
          $("#product-category").val(product.category);

          // Lưu link ảnh cũ và reset file input
          $("#product-existing-image").val(product.image_url);
          $("#product-image-file").val(""); // Reset ô chọn file

          if (product.category) {
            $("#product-category").trigger("change");

            // Đợi subcategories load xong rồi mới set giá trị
            setTimeout(() => {
              $("#product-subcategory").val(product.subcategory_id);
            }, 300);
          }

          $("#product-modal-title").text("Sửa Sản Phẩm");
          $("#product-action").val("update");
          $("#product-modal").css("display", "flex");
        } else {
          showToast(response.message, false);
        }
      },
      error: function () {
        showToast("Lỗi khi lấy thông tin sản phẩm.", false);
      },
    });
  });

  // Xử lý Submit Form Sản phẩm (Thêm & Sửa)
  $("#product-form").on("submit", function (e) {
    e.preventDefault();

    // Sử dụng FormData để gửi cả text và file
    const formData = new FormData(this);

    $.ajax({
      url: "../api/products.php",
      method: "POST",
      data: formData, // Gửi FormData
      dataType: "json",
      processData: false,
      contentType: false,
      success: function (response) {
        if (response.success) {
          showToast(response.message, true);
          $("#product-modal").css("display", "none");

          const action = $("#product-action").val();
          if (action === "add") {
            // Thêm hàng mới vào bảng
            $("#products-table tbody").append(renderProductRow(response.data));
          } else {
            // Cập nhật hàng
            const updatedRow = renderProductRow(response.data);
            $(`#products-table tr[data-id="${response.data.id}"]`).replaceWith(
              updatedRow
            );
          }
          loadProducts();
        } else {
          showToast(response.message, false);
        }
      },
      error: function () {
        showToast("Có lỗi xảy ra, vui lòng thử lại.", false);
      },
    });
  });

  // Xử lý Xoá Sản phẩm
  $(document).on("click", ".btn-delete-product", function () {
    const id = $(this).data("id");

    // *** Tạm thời dùng confirm() ***
    // Bạn nên thay thế bằng một modal xác nhận tự-tạo
    if (
      confirm(
        "Bạn có chắc chắn muốn xoá sản phẩm này? Mọi hình ảnh liên quan cũng sẽ bị xoá."
      )
    ) {
      $.ajax({
        url: "../api/products.php",
        method: "POST",
        data: { action: "delete", id: id },
        dataType: "json",
        success: function (response) {
          if (response.success) {
            showToast(response.message, true);
            // Xoá hàng khỏi bảng
            $(`#products-table tr[data-id="${id}"]`).fadeOut(500, function () {
              $(this).remove();
            });
          } else {
            showToast(response.message, false);
          }
        },
        error: function () {
          showToast("Lỗi khi xoá sản phẩm.", false);
        },
      });
    }
  });

  // ===================================
  // --- QUẢN LÝ NGƯỜI DÙNG (USERS) ---
  // ===================================

  // Hàm tải danh sách người dùng
  function loadUsers() {
    $.ajax({
      url: "../api/users.php",
      method: "POST",
      data: { action: "get_all" },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          const tbody = $("#users-table tbody");
          tbody.empty();
          response.data.forEach(function (user) {
            tbody.append(renderUserRow(user));
          });
        }
      },
      error: function () {
        showToast("Lỗi tải danh sách người dùng.", false);
      },
    });
  }

  // Hàm render một hàng user
  function renderUserRow(user) {
    return `
            <tr data-id="${user.id}">
                <td>${user.id}</td>
                <td>${user.username}</td>
                <td>${user.email}</td>
                <td>${user.phone || "Chưa có"}</td>
                <td>${user.address || "Chưa có"}</td>
                <td>${formatBadge("role", user.role)}</td>
                <td>${user.join_date}</td>
                <td>
                    <button class="btn btn-edit btn-edit-user" data-id="${
                      user.id
                    }">Sửa</button>
                    <button class="btn btn-delete btn-delete-user" data-id="${
                      user.id
                    }">Xoá</button>
                </td>
            </tr>
        `;
  }

  // Mở Modal Thêm User
  $("#btn-add-user").on("click", function () {
    $("#user-form")[0].reset();
    $("#user-modal-title").text("Thêm Người Dùng Mới");
    $("#user-action").val("add");
    $("#user-id").val("");
    $("#user-password").prop("required", true); // Bắt buộc khi thêm mới
    $("#password-hint").hide(); // Ẩn hint khi thêm mới
    $("#user-modal").css("display", "flex");
  });

  // Mở Modal Sửa User
  $(document).on("click", ".btn-edit-user", function () {
    const id = $(this).data("id");

    $.ajax({
      url: "../api/users.php",
      method: "POST",
      data: { action: "get_one", id: id },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          const user = response.data;
          $("#user-id").val(user.id);
          $("#user-username").val(user.username);
          $("#user-email").val(user.email);
          $("#user-phone").val(user.phone || "");
          $("#user-address").val(user.address || "");
          $("#user-role").val(user.role);
          $("#user-password").val(""); // Reset password field
          $("#user-password").prop("required", false); // Không bắt buộc khi sửa
          $("#password-hint").show(); // Hiện hint khi sửa

          $("#user-modal-title").text("Sửa Người Dùng");
          $("#user-action").val("update");
          $("#user-modal").css("display", "flex");
        } else {
          showToast(response.message, false);
        }
      },
      error: function () {
        showToast("Lỗi khi lấy thông tin người dùng.", false);
      },
    });
  });

  // Xử lý Submit Form User (Thêm & Sửa)
  $("#user-form").on("submit", function (e) {
    e.preventDefault();
    const formData = $(this).serialize();

    $.ajax({
      url: "../api/users.php",
      method: "POST",
      data: formData,
      dataType: "json",
      success: function (response) {
        if (response.success) {
          showToast(response.message, true);
          $("#user-modal").css("display", "none");

          const action = $("#user-action").val();
          if (action === "add") {
            // Thêm hàng mới
            $("#users-table tbody").append(renderUserRow(response.data));
          } else {
            // Sửa hàng - giữ nguyên join_date
            const oldJoinDate = $(
              `#users-table tr[data-id="${response.data.id}"] td:nth-child(7)`
            ).text();
            response.data.join_date = oldJoinDate;

            const updatedRow = renderUserRow(response.data);
            $(`#users-table tr[data-id="${response.data.id}"]`).replaceWith(
              updatedRow
            );
          }
        } else {
          showToast(response.message, false);
        }
      },
      error: function () {
        showToast("Có lỗi xảy ra, vui lòng thử lại.", false);
      },
    });
  });

  // Xử lý Xoá User
  $(document).on("click", ".btn-delete-user", function () {
    const id = $(this).data("id");

    if (confirm("Bạn có chắc chắn muốn xoá người dùng này?")) {
      $.ajax({
        url: "../api/users.php",
        method: "POST",
        data: { action: "delete", id: id },
        dataType: "json",
        success: function (response) {
          if (response.success) {
            showToast(response.message, true);
            $(`#users-table tr[data-id="${id}"]`).fadeOut(500, function () {
              $(this).remove();
            });
          } else {
            showToast(response.message, false);
          }
        },
        error: function () {
          showToast("Lỗi khi xoá người dùng.", false);
        },
      });
    }
  });

  // ===================================
  // --- QUẢN LÝ ĐƠN HÀNG (ORDERS) ---
  // ===================================

  // Hàm tải danh sách đơn hàng
  function loadOrders() {
    $.ajax({
      url: "../api/orders.php",
      method: "POST",
      data: { action: "get_all" },
      dataType: "json",
      success: function (response) {
        console.log("📦 Orders response:", response); // ✅ DEBUG

        if (response.success) {
          const orders = response.data;
          $("#orders-table").empty(); // ✅ Xóa dữ liệu cũ

          if (orders.length === 0) {
            $("#orders-table").html(`
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px;">
                                Chưa có đơn hàng nào.
                            </td>
                        </tr>
                    `);
          } else {
            orders.forEach((order) => {
              $("#orders-table").append(renderOrderRow(order));
            });
          }
        } else {
          showToast("Không thể tải danh sách đơn hàng.", false);
        }
      },
      error: function (xhr, status, error) {
        console.error("❌ Lỗi tải orders:", error); // ✅ DEBUG
        console.error("❌ Response:", xhr.responseText);
        showToast("Lỗi kết nối server.", false);
      },
    });
  }

  // Hàm render một hàng đơn hàng
  function renderOrderRow(order) {
    const formattedPrice = new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(order.total_price);

    return `
        <tr data-id="${order.id}">
            <td>#${order.id}</td>
            <td>${order.customer_name}</td>
            <td>${order.customer_phone || "Chưa có"}</td> <!-- ✅ THÊM SĐT -->
            <td>${
              order.customer_address || "Chưa có"
            }</td> <!-- ✅ THÊM ĐỊA CHỈ -->
            <td>${formattedPrice}</td>
            <td>${formatBadge("status", order.status)}</td>
            <td>${order.order_date}</td>
            <td>
                <button class="btn-action btn-edit-order" data-id="${
                  order.id
                }">Sửa</button>
                <button class="btn-action btn-delete btn-delete-order" data-id="${
                  order.id
                }">Xoá</button>
            </td>
        </tr>
    `;
  }

  // Mở Modal Sửa Đơn Hàng
  $(document).on("click", ".btn-edit-order", function () {
    const orderId = $(this).data("id");

    $.ajax({
      url: "../api/orders.php",
      method: "POST",
      data: { action: "get_one", id: orderId },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          const order = response.data;

          $("#order-form").html(`
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="${order.id}">
                    
                    <div class="form-group">
                        <label>Tên khách hàng:</label>
                        <input type="text" name="customer_name" value="${
                          order.customer_name
                        }" required readonly>
                    </div>
                    
                    <!-- ✅ THÊM HIỂN THỊ SĐT VÀ ĐỊA CHỈ -->
                    <div class="form-group">
                        <label>Số điện thoại:</label>
                        <input type="text" value="${
                          order.customer_phone
                        }" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Địa chỉ:</label>
                        <textarea readonly style="min-height: 60px;">${
                          order.customer_address
                        }</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Tổng tiền (VND):</label>
                        <input type="number" name="total_price" value="${
                          order.total_price
                        }" step="0.01" required readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Trạng thái:</label>
                        <select name="status" required>
                            <option value="Đang xử lý" ${
                              order.status === "Đang xử lý" ? "selected" : ""
                            }>Đang xử lý</option>
                            <option value="Đã giao" ${
                              order.status === "Đã giao" ? "selected" : ""
                            }>Đã giao</option>
                            <option value="Đã huỷ" ${
                              order.status === "Đã huỷ" ? "selected" : ""
                            }>Đã huỷ</option>
                        </select>
                    </div>
                    
                    <!-- ✅ HIỂN THỊ DANH SÁCH SẢN PHẨM -->
                    <div class="form-group">
                        <label>Sản phẩm đã đặt:</label>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>SL</th>
                                    <th>Đơn giá</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${order.items
                                  .map(
                                    (item) => `
                                    <tr>
                                        <td>${item.product_name}</td>
                                        <td>${item.quantity}</td>
                                        <td>${parseInt(
                                          item.price
                                        ).toLocaleString("vi-VN")}₫</td>
                                        <td>${(
                                          item.price * item.quantity
                                        ).toLocaleString("vi-VN")}₫</td>
                                    </tr>
                                `
                                  )
                                  .join("")}
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Cập nhật</button>
                        <button type="button" class="btn-secondary" data-dismiss="modal">Huỷ</button>
                    </div>
                `);

          $("#order-modal-title").text("Cập Nhật Đơn Hàng");
          $("#order-modal").css("display", "flex");
        }
      },
    });
  });

  // ✅ XỬ LÝ SUBMIT FORM SỬA ĐƠN HÀNG
  $(document).on("submit", "#order-form", function (e) {
    e.preventDefault();
    const formData = $(this).serialize();

    $.ajax({
      url: "../api/orders.php",
      method: "POST",
      data: formData,
      dataType: "json",
      success: function (response) {
        console.log("📦 Response từ server:", response);

        if (response.success) {
          showToast(response.message, true);
          $("#order-modal").css("display", "none");

          const orderId = response.data.id;
          const $row = $(`#orders-table tr[data-id="${orderId}"]`);

          // ✅ CẬP NHẬT ĐÚNG VỊ TRÍ CỘT (SAU KHI THÊM SĐT VÀ ĐỊA CHỈ)
          // Cột 2: Tên khách hàng
          $row.find("td:nth-child(2)").text(response.data.customer_name);

          // Cột 3: SĐT (giữ nguyên vì không sửa trong modal)
          // Cột 4: Địa chỉ (giữ nguyên vì không sửa trong modal)

          // Cột 5: Tổng tiền
          const formattedPrice = new Intl.NumberFormat("vi-VN", {
            style: "currency",
            currency: "VND",
          }).format(response.data.total_price);
          $row.find("td:nth-child(5)").text(formattedPrice);

          // Cột 6: Trạng thái
          const badgeHtml = formatBadge("status", response.data.status);
          console.log("🎨 Badge HTML:", badgeHtml);
          $row.find("td:nth-child(6)").html(badgeHtml);

          // Cột 7: Ngày đặt (không thay đổi)
        } else {
          showToast(response.message, false);
        }
      },
      error: function (xhr, status, error) {
        console.error("❌ Lỗi AJAX:", error);
        console.error("❌ Response:", xhr.responseText);
        showToast("Có lỗi xảy ra, vui lòng thử lại.", false);
      },
    });
  });

  // Xử lý Xoá Đơn Hàng
  $(document).on("click", ".btn-delete-order", function () {
    const orderId = $(this).data("id");

    if (confirm("Bạn có chắc chắn muốn xoá đơn hàng này?")) {
      $.ajax({
        url: "../api/orders.php",
        method: "POST",
        data: { action: "delete", id: orderId },
        dataType: "json",
        success: function (response) {
          if (response.success) {
            showToast(response.message, true);
            $(`#orders-table tr[data-id="${orderId}"]`).remove();
          } else {
            showToast(response.message, false);
          }
        },
        error: function () {
          showToast("Có lỗi xảy ra, vui lòng thử lại.", false);
        },
      });
    }
  });

  // ============================================================
  // ✅ QUẢN LÝ ĐÁNH GIÁ (REVIEWS)
  // ============================================================

  // Load danh sách đánh giá
  function loadReviews() {
    $.ajax({
      url: "../api/reviews.php",
      method: "POST",
      data: { action: "get_all" },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          displayReviews(response.data);
        } else {
          showToast("Lỗi khi tải danh sách đánh giá!", false);
        }
      },
      error: function () {
        showToast("Lỗi kết nối server!", false);
      },
    });
  }

  // Hiển thị danh sách đánh giá
  function displayReviews(reviews) {
    const tbody = $("#reviews-table");
    tbody.empty();

    if (reviews.length === 0) {
      tbody.append(
        '<tr><td colspan="8" style="text-align: center;">Chưa có đánh giá nào</td></tr>'
      );
      return;
    }

    reviews.forEach(function (review) {
      // Tạo sao đánh giá
      let stars = "";
      for (let i = 1; i <= 5; i++) {
        if (i <= review.rating) {
          stars += '<span style="color: #F1899F;">★</span>';
        } else {
          stars += '<span style="color: #ddd;">★</span>';
        }
      }

      // Rút gọn comment
      let shortComment = review.comment
        ? review.comment.substring(0, 50) +
          (review.comment.length > 50 ? "..." : "")
        : '<em style="color: #999;">Không có nhận xét</em>';

      // ✅ Icon trả lời
      const replyIcon = review.reply
        ? '<span style="color: #28a745;" title="Đã trả lời">✓</span>'
        : '<span style="color: #999;" title="Chưa trả lời">-</span>';

      const row = `
        <tr>
          <td>${review.id}</td>
          <td>${review.username}</td>
          <td>${review.product_name || "N/A"}</td>
          <td>#${review.order_id}</td>
          <td>${stars} (${review.rating}/5)</td>
          <td>${shortComment}</td>
          <td>${review.formatted_date} ${replyIcon}</td>
          <td>
            <button class="btn-action btn-view-review" data-id="${
              review.id
            }" title="Xem chi tiết" style="background: #3b82f6; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
              </svg>
            </button>
            <button class="btn-action btn-delete-review" data-id="${
              review.id
            }" title="Xóa" style="background: #ef4444; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; margin-left: 5px;">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
              </svg>
            </button>
          </td>
        </tr>
      `;
      tbody.append(row);
    });
  }

  // Xem chi tiết đánh giá
  $(document).on("click", ".btn-view-review", function () {
    const reviewId = $(this).data("id");

    $.ajax({
      url: "../api/reviews.php",
      method: "POST",
      data: { action: "get_one", id: reviewId },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          const review = response.data;

          // Tạo sao đánh giá
          let stars = "";
          for (let i = 1; i <= 5; i++) {
            stars +=
              i <= review.rating
                ? '<span style="color: #F1899F; font-size: 24px;">★</span>'
                : '<span style="color: #ddd; font-size: 24px;">★</span>';
          }

          // ✅ Phần hiển thị reply
          let replySection = "";
          if (review.reply_id) {
            replySection = `
              <div style="background: #f0f9ff; padding: 15px; border-radius: 5px; border-left: 4px solid #3b82f6; margin-top: 20px;">
                <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 10px;">
                  <strong style="color: #1e40af;">💬 Phản hồi từ ${review.admin_username}:</strong>
                  <div>
                    <button class="btn-edit-reply" data-reply-id="${review.reply_id}" data-review-id="${review.id}" style="background: #f59e0b; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; margin-right: 5px;">Sửa</button>
                    <button class="btn-delete-reply" data-reply-id="${review.reply_id}" style="background: #ef4444; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">Xóa</button>
                  </div>
                </div>
                <p style="margin: 0; color: #334155;" id="reply-text-${review.reply_id}">${review.reply}</p>
                <small style="color: #64748b;">Trả lời lúc: ${review.reply_date}</small>
              </div>
            `;
          } else {
            replySection = `
              <div style="margin-top: 20px; padding: 15px; background: #f9fafb; border-radius: 5px;">
                <strong>💬 Trả lời đánh giá này:</strong>
                <textarea id="reply-input" class="form-control" rows="3" placeholder="Nhập phản hồi của bạn..." style="margin-top: 10px; width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                <button class="btn-primary" id="btn-submit-reply" data-review-id="${review.id}" style="margin-top: 10px; background: #3b82f6; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer;">Gửi phản hồi</button>
              </div>
            `;
          }

          const content = `
            <div style="padding: 20px;">
              <div style="margin-bottom: 15px;">
                <strong>Người đánh giá:</strong> ${review.username} (${
            review.email
          })
              </div>
              <div style="margin-bottom: 15px;">
                <strong>Sản phẩm:</strong> ${review.product_name || "N/A"}
              </div>
              <div style="margin-bottom: 15px;">
                <strong>Đơn hàng:</strong> #${review.order_id} - ${
            review.customer_name
          }
              </div>
              <div style="margin-bottom: 15px;">
                <strong>Số sao:</strong> ${stars} (${review.rating}/5)
              </div>
              <div style="margin-bottom: 15px;">
                <strong>Nhận xét:</strong>
                <p style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin-top: 10px;">
                  ${
                    review.comment ||
                    '<em style="color: #999;">Không có nhận xét</em>'
                  }
                </p>
              </div>
              <div style="margin-bottom: 15px;">
                <strong>Ngày đánh giá:</strong> ${review.formatted_date}
              </div>
              
              ${replySection}
            </div>
          `;

          $("#review-detail-content").html(content);
          $("#review-detail-modal").fadeIn(300);
        } else {
          showToast(response.message, false);
        }
      },
      error: function () {
        showToast("Lỗi khi tải chi tiết đánh giá!", false);
      },
    });
  });

  // ✅ GỬI PHẢN HỒI MỚI
  $(document).on("click", "#btn-submit-reply", function () {
    const reviewId = $(this).data("review-id");
    const reply = $("#reply-input").val().trim();

    if (!reply) {
      showToast("Vui lòng nhập nội dung phản hồi!", false);
      return;
    }

    $.ajax({
      url: "../api/reviews.php",
      method: "POST",
      data: {
        action: "add_reply",
        review_id: reviewId,
        reply: reply,
      },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          showToast(response.message, true);
          $("#review-detail-modal").fadeOut(300);
          loadReviews();
        } else {
          showToast(response.message, false);
        }
      },
      error: function () {
        showToast("Lỗi khi gửi phản hồi!", false);
      },
    });
  });

  // ✅ SỬA PHẢN HỒI
  $(document).on("click", ".btn-edit-reply", function () {
    const replyId = $(this).data("reply-id");
    const reviewId = $(this).data("review-id");
    const currentReply = $(`#reply-text-${replyId}`).text();

    const newReply = prompt("Sửa phản hồi:", currentReply);
    if (newReply === null || newReply.trim() === "") return;

    $.ajax({
      url: "../api/reviews.php",
      method: "POST",
      data: {
        action: "update_reply",
        reply_id: replyId,
        reply: newReply.trim(),
      },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          showToast(response.message, true);
          $(`#reply-text-${replyId}`).text(newReply.trim());
        } else {
          showToast(response.message, false);
        }
      },
      error: function () {
        showToast("Lỗi khi cập nhật phản hồi!", false);
      },
    });
  });

  // ✅ XÓA PHẢN HỒI
  $(document).on("click", ".btn-delete-reply", function () {
    const replyId = $(this).data("reply-id");

    if (!confirm("Bạn có chắc muốn xóa phản hồi này?")) return;

    $.ajax({
      url: "../api/reviews.php",
      method: "POST",
      data: {
        action: "delete_reply",
        id: replyId,
      },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          showToast(response.message, true);
          $("#review-detail-modal").fadeOut(300);
          loadReviews();
        } else {
          showToast(response.message, false);
        }
      },
      error: function () {
        showToast("Lỗi khi xóa phản hồi!", false);
      },
    });
  });

  // Xóa đánh giá
  $(document).on("click", ".btn-delete-review", function () {
    const reviewId = $(this).data("id");

    if (confirm("Bạn có chắc muốn xóa đánh giá này?")) {
      $.ajax({
        url: "../api/reviews.php",
        method: "POST",
        data: { action: "delete", id: reviewId },
        dataType: "json",
        success: function (response) {
          if (response.success) {
            showToast(response.message, true);
            loadReviews();
          } else {
            showToast(response.message, false);
          }
        },
        error: function () {
          showToast("Lỗi khi xóa đánh giá!", false);
        },
      });
    }
  });

  // Load reviews khi click vào menu
  $(document).on("click", '[data-target="reviews-section"]', function () {
    loadReviews();
  });

  // ===== BIẾN TOÀN CỤC CHO BIỂU ĐỒ =====
  let weeklyChart, monthlyChart, statusChart;

  // Hàm load thống kê Dashboard (CẬP NHẬT)
  function loadDashboardStats() {
    $.ajax({
      url: "../api/dashboard.php",
      method: "GET",
      dataType: "json",
      success: function (response) {
        if (response.success) {
          const data = response.data;

          // Cập nhật các giá trị thống kê
          $("#total-revenue").text(
            new Intl.NumberFormat("vi-VN").format(data.total_revenue) + " đ"
          );
          $("#new-orders").text(data.new_orders);
          $("#new-users").text(data.new_users);
          $("#pending-orders").text(data.pending_orders);
          $("#total-products").text(data.total_products);

          // ✅ VẼ CÁC BIỂU ĐỒ
          drawWeeklyRevenueChart(data.weekly_revenue);
          drawMonthlyRevenueChart(data.monthly_revenue);
          drawOrderStatusChart(data.order_status);
        }
      },
      error: function () {
        console.error("Lỗi khi tải thống kê Dashboard");
        $(".card-value").text("Lỗi");
      },
    });
  }

  // ===== VẼ BIỂU ĐỒ DOANH THU THEO TUẦN =====
  function drawWeeklyRevenueChart(data) {
    const canvas = document.getElementById("weeklyRevenueChart");
    const ctx = canvas.getContext("2d");

    if (weeklyChart) {
      weeklyChart.destroy();
    }

    weeklyChart = new Chart(ctx, {
      type: "line",
      data: {
        labels: data.labels,
        datasets: [
          {
            label: "Doanh thu (VNĐ)",
            data: data.data,
            borderColor: "#3b82f6",
            backgroundColor: "rgba(59, 130, 246, 0.1)",
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: "#3b82f6",
            pointBorderColor: "#ffffff",
            pointBorderWidth: 2,
            pointRadius: 5,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false, // ✅ Quan trọng
        interaction: {
          intersect: false,
        },
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          x: {
            display: true,
            grid: {
              color: "rgba(0, 0, 0, 0.1)",
            },
          },
          y: {
            display: true,
            beginAtZero: true,
            grid: {
              color: "rgba(0, 0, 0, 0.1)",
            },
            ticks: {
              callback: function (value) {
                return new Intl.NumberFormat("vi-VN").format(value) + "đ";
              },
            },
          },
        },
      },
    });
  }

  // ===== VẼ BIỂU ĐỒ DOANH THU THEO THÁNG =====
  function drawMonthlyRevenueChart(data) {
    const canvas = document.getElementById("monthlyRevenueChart");
    const ctx = canvas.getContext("2d");

    if (monthlyChart) {
      monthlyChart.destroy();
    }

    monthlyChart = new Chart(ctx, {
      type: "bar",
      data: {
        labels: data.labels,
        datasets: [
          {
            label: "Doanh thu (VNĐ)",
            data: data.data,
            backgroundColor: [
              "#f59e0b",
              "#10b981",
              "#3b82f6",
              "#8b5cf6",
              "#ef4444",
              "#06b6d4",
            ],
            borderColor: [
              "#d97706",
              "#059669",
              "#2563eb",
              "#7c3aed",
              "#dc2626",
              "#0891b2",
            ],
            borderWidth: 1,
            borderRadius: 6,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false, // ✅ Quan trọng
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          x: {
            display: true,
            grid: {
              color: "rgba(0, 0, 0, 0.1)",
            },
          },
          y: {
            display: true,
            beginAtZero: true,
            grid: {
              color: "rgba(0, 0, 0, 0.1)",
            },
            ticks: {
              callback: function (value) {
                return new Intl.NumberFormat("vi-VN").format(value) + "đ";
              },
            },
          },
        },
      },
    });
  }

  // ===== VẼ BIỂU ĐỒ TRÒN TRẠNG THÁI ĐƠN HÀNG =====
  function drawOrderStatusChart(data) {
    const canvas = document.getElementById("orderStatusChart");
    const ctx = canvas.getContext("2d");

    if (statusChart) {
      statusChart.destroy();
    }

    statusChart = new Chart(ctx, {
      type: "doughnut",
      data: {
        labels: data.labels,
        datasets: [
          {
            data: data.data,
            backgroundColor: [
              "#10b981", // Đã giao - xanh lá
              "#f59e0b", // Đang xử lý - vàng
              "#ef4444", // Đã hủy - đỏ
            ],
            borderColor: "#ffffff",
            borderWidth: 3,
            hoverOffset: 10,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false, // ✅ Quan trọng
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              padding: 15, // ✅ Giảm padding
              usePointStyle: true,
              pointStyle: "circle",
              font: {
                size: 12, // ✅ Giảm font size
              },
            },
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = ((context.raw / total) * 100).toFixed(1);
                return `${context.label}: ${context.raw} đơn (${percentage}%)`;
              },
            },
          },
        },
        layout: {
          padding: {
            top: 0, // ✅ Bỏ padding trên
            bottom: 10, // ✅ Giảm padding dưới
          },
        },
      },
    });
  }

  // Tải thống kê Dashboard lần đầu
  loadDashboardStats();
});
