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
  loadProducts();

  $(".nav-link").on("click", function (e) {
    e.preventDefault();
    const targetId = $(this).data("target");

    $(".content-section").hide();
    $("#" + targetId).show();

    $(".nav-link").removeClass("active");
    $(this).addClass("active");

    // Tải dữ liệu tương ứng khi chuyển tab
    if (targetId === "products-section") {
      loadProducts();
    } else if (targetId === "users-section") {
      loadUsers();
    } else if (targetId === "orders-section") {
      loadOrders();
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
});
