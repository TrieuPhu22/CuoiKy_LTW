$(document).ready(function() {
            
    // --- XỬ LÝ CHUNG ---

    // Hàm hiển thị thông báo (toast)
    function showToast(message, isSuccess = true) {
        const toast = $('#toast-message');
        toast.text(message);
        toast.removeClass('success error').addClass(isSuccess ? 'success' : 'error');
        toast.addClass('show');
        
        // Tự động ẩn sau 2 giây
        setTimeout(() => {
            toast.removeClass('show');
        }, 2000);
    }

    // Xử lý đóng Modal
    $('.modal-close, [data-dismiss="modal"]').on('click', function() {
        $(this).closest('.modal').css('display', 'none');
    });
    
    // Đóng modal khi nhấp ra ngoài
    $(window).on('click', function(e) {
        if ($(e.target).is('.modal')) {
            $(e.target).css('display', 'none');
        }
    });
    // Hàm lấy tên danh mục từ key
    function getCategoryName(key) {
        const categories = {
            'hoa_sinh_nhat': 'Hoa Sinh Nhật',
            'hoa_khai_truong': 'Hoa Khai Trương',
            'chu_de': 'Chủ Đề',
            'thiet_ke': 'Thiết Kế',
            'hoa_tuoi': 'Hoa Tươi'
        };
        return categories[key] || (key ? key : 'Chưa có'); // Hiển thị key nếu không khớp
    }

    // Hàm định dạng trạng thái (cho user và order)
    function formatBadge(type, value) {
        if (type === 'role') {
            if (value === 'Admin') {
                return `<span class="badge badge-blue">Admin</span>`;
            }
            return `<span class="badge badge-green">User</span>`;
        }
        if (type === 'status') {
            if (value === 'Đã giao') {
                return `<span class="badge badge-green">${value}</span>`;
            }
            if (value === 'Đang xử lý') {
                return `<span class="badge badge-yellow">${value}</span>`;
            }
            if (value === 'Đã huỷ') {
                return `<span class="badge badge-red">${value}</span>`;
            }
        }
        return value; // Mặc định
    }


    // --- XỬ LÝ CHUYỂN TAB ---
    
    // Tải dữ liệu lần đầu
    loadProducts();

    $('.nav-link').on('click', function(e) {
        e.preventDefault(); 
        const targetId = $(this).data('target');

        $('.content-section').hide();
        $('#' + targetId).show();

        $('.nav-link').removeClass('active');
        $(this).addClass('active');
        
        // Tải dữ liệu tương ứng khi chuyển tab
        if (targetId === 'products-section') {
            loadProducts();
        } else if (targetId === 'users-section') {
            loadUsers();
        } else if (targetId === 'orders-section') {
            loadOrders();
        }
    });
    
    
    // ===================================
    // --- QUẢN LÝ SẢN PHẨM (PRODUCTS) ---
    // ===================================

    // Hàm tải danh sách sản phẩm
    function loadProducts() {
        $.ajax({
            url: '../api/products.php',
            method: 'POST', // Đổi sang POST để thống nhất
            data: { action: 'get_all' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const tbody = $('#products-table tbody');
                    tbody.empty(); // Xoá dữ liệu cũ
                    response.data.forEach(function(product) {
                        tbody.append(renderProductRow(product));
                    });
                }
            },
            error: function(xhr, status, error) {
                showToast('Lỗi tải danh sách sản phẩm: ' + error, false);
            }
        });
    }
    
    // Hàm render một hàng sản phẩm
    function renderProductRow(product) {
         // Định dạng giá tiền
        const formattedPrice = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(product.price);
        
        // Thêm timestamp (new Date().getTime()) để tránh lỗi cache của trình duyệt khi ảnh được cập nhật
        const imageUrl = product.image_url 
                        ? `${product.image_url}?t=${new Date().getTime()}` 
                        : 'https://placehold.co/100x100/E2E8F0/A0AEC0?text=SP';
        
        return `
            <tr data-id="${product.id}">
                <td>${product.id}</td>
                <td>
                    <div class="product-cell">
                        <img src="${imageUrl}" alt="Product Image">
                        <div>
                            <p class="product-name">${product.name}</p>
                            <!-- SỬA: Hiển thị mô tả ngắn -->
                            <p class="product-category">${product.description ? product.description.substring(0, 30) : ''}...</p>
                        </div>
                    </div>
                </td>
                <td>${formattedPrice}</td>
                <td>${getCategoryName(product.category)}</td> 
                <td>${product.stock}</td>
                <td>
                    <button class="btn btn-edit btn-edit-product" data-id="${product.id}">Sửa</button>
                    <button class="btn btn-delete btn-delete-product" data-id="${product.id}">Xoá</button>
                </td>
            </tr>
        `;
    }

    // Mở Modal Thêm Sản phẩm
    $('#btn-add-product').on('click', function() {
        $('#product-form')[0].reset(); // Reset form
        $('#product-modal-title').text('Thêm Sản Phẩm Mới');
        $('#product-action').val('add');
        $('#product-id').val('');
        $('#product-existing-image').val(''); // Xoá ảnh cũ
        $('#product-image-file').val(''); // Xoá file đã chọn
        $('#product-category').val('');
        $('#product-modal').css('display', 'flex');
    });
    
    // Mở Modal Sửa Sản phẩm
    $(document).on('click', '.btn-edit-product', function() {
        const id = $(this).data('id');
        
        // Gọi AJAX để lấy thông tin chi tiết sản phẩm
        $.ajax({
            url: '../api/products.php',
            method: 'POST',
            data: { action: 'get_one', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const product = response.data;
                    // Đổ dữ liệu vào form
                    $('#product-id').val(product.id);
                    $('#product-name').val(product.name);
                    $('#product-description').val(product.description);
                    $('#product-price').val(product.price);
                    $('#product-stock').val(product.stock);
                    $('#product-category').val(product.category);
                    
                    // Lưu link ảnh cũ và reset file input
                    $('#product-existing-image').val(product.image_url);
                    $('#product-image-file').val(''); // Reset ô chọn file
                    
                    // Cập nhật modal
                    $('#product-modal-title').text('Sửa Sản Phẩm');
                    $('#product-action').val('update');
                    $('#product-modal').css('display', 'flex');
                } else {
                    showToast(response.message, false);
                }
            },
            error: function() {
                showToast('Lỗi khi lấy thông tin sản phẩm.', false);
            }
        });
    });

    // Xử lý Submit Form Sản phẩm (Thêm & Sửa)
    $('#product-form').on('submit', function(e) {
        e.preventDefault();
        
        // Sử dụng FormData để gửi cả text và file
        const formData = new FormData(this);
        
        $.ajax({
            url: '../api/products.php',
            method: 'POST',
            data: formData, // Gửi FormData
            dataType: 'json',
            // Thêm 2 dòng này để AJAX gửi file
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showToast(response.message, true);
                    $('#product-modal').css('display', 'none');
                    
                    const action = $('#product-action').val();
                    if (action === 'add') {
                        // Thêm hàng mới vào bảng
                        $('#products-table tbody').append(renderProductRow(response.data));
                    } else {
                        // Cập nhật hàng
                        const updatedRow = renderProductRow(response.data);
                        $(`#products-table tr[data-id="${response.data.id}"]`).replaceWith(updatedRow);
                    }
                } else {
                    showToast(response.message, false);
                }
            },
            error: function() {
                showToast('Có lỗi xảy ra, vui lòng thử lại.', false);
            }
        });
    });

    // Xử lý Xoá Sản phẩm
    $(document).on('click', '.btn-delete-product', function() {
        const id = $(this).data('id');
        
        // *** Tạm thời dùng confirm() ***
        // Bạn nên thay thế bằng một modal xác nhận tự-tạo
        if (confirm("Bạn có chắc chắn muốn xoá sản phẩm này? Mọi hình ảnh liên quan cũng sẽ bị xoá.")) {
            $.ajax({
                url: '../api/products.php',
                method: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, true);
                        // Xoá hàng khỏi bảng
                        $(`#products-table tr[data-id="${id}"]`).fadeOut(500, function() {
                            $(this).remove();
                        });
                    } else {
                        showToast(response.message, false);
                    }
                },
                error: function() {
                     showToast('Lỗi khi xoá sản phẩm.', false);
                }
            });
        }
    });


    // ===================================
    // --- QUẢN LÝ NGƯỜI DÙNG (USERS) ---
    // ===================================

    // Hàm tải danh sách người dùng
    function loadUsers() {
        $.ajax({
            url: '../api/users.php',
            method: 'POST',
            data: { action: 'get_all' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const tbody = $('#users-table tbody');
                    tbody.empty();
                    response.data.forEach(function(user) {
                        tbody.append(renderUserRow(user));
                    });
                }
            },
            error: function() {
                showToast('Lỗi tải danh sách người dùng.', false);
            }
        });
    }
    
    // Hàm render một hàng user
    function renderUserRow(user) {
        return `
            <tr data-id="${user.id}">
                <td>${user.id}</td>
                <td>${user.username}</td>
                <td>${user.email}</td>
                <td>${formatBadge('role', user.role)}</td>
                <td>${user.join_date}</td>
                <td>
                    <button class="btn btn-edit btn-edit-user" data-id="${user.id}">Sửa</button>
                    <button class="btn btn-delete btn-delete-user" data-id="${user.id}">Xoá</button>
                </td>
            </tr>
        `;
    }

    // Mở Modal Thêm User
    $('#btn-add-user').on('click', function() {
        $('#user-form')[0].reset();
        $('#user-modal-title').text('Thêm Người Dùng Mới');
        $('#user-action').val('add');
        $('#user-id').val('');
        $('#user-modal').css('display', 'flex');
    });
    
    // Mở Modal Sửa User
    $(document).on('click', '.btn-edit-user', function() {
        const id = $(this).data('id');
        
        $.ajax({
            url: '../api/users.php',
            method: 'POST',
            data: { action: 'get_one', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const user = response.data;
                    $('#user-id').val(user.id);
                    $('#user-username').val(user.username);
                    $('#user-email').val(user.email);
                    $('#user-role').val(user.role);
                    
                    $('#user-modal-title').text('Sửa Người Dùng');
                    $('#user-action').val('update');
                    $('#user-modal').css('display', 'flex');
                } else {
                    showToast(response.message, false);
                }
            },
            error: function() {
                showToast('Lỗi khi lấy thông tin người dùng.', false);
            }
        });
    });

    // Xử lý Submit Form User (Thêm & Sửa)
    $('#user-form').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        
        $.ajax({
            url: '../api/users.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, true);
                    $('#user-modal').css('display', 'none');
                    
                    const action = $('#user-action').val();
                    if (action === 'add') {
                        // Thêm hàng mới
                        $('#users-table tbody').append(renderUserRow(response.data));
                    } else {
                        // Sửa hàng
                        const oldJoinDate = $(`#users-table tr[data-id="${response.data.id}"] td:nth-child(5)`).text();
                        response.data.join_date = oldJoinDate;
                        
                        const updatedRow = renderUserRow(response.data);
                        $(`#users-table tr[data-id="${response.data.id}"]`).replaceWith(updatedRow);
                    }
                } else {
                    showToast(response.message, false);
                }
            },
            error: function() {
                showToast('Có lỗi xảy ra, vui lòng thử lại.', false);
            }
        });
    });

    // Xử lý Xoá User
    $(document).on('click', '.btn-delete-user', function() {
        const id = $(this).data('id');
        
        if (confirm("Bạn có chắc chắn muốn xoá người dùng này?")) {
            $.ajax({
                url: '../api/users.php',
                method: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, true);
                        $(`#users-table tr[data-id="${id}"]`).fadeOut(500, function() { $(this).remove(); });
                    } else {
                        showToast(response.message, false);
                    }
                },
                error: function() { showToast('Lỗi khi xoá người dùng.', false); }
            });
        }
    });


    // ===================================
    // --- QUẢN LÝ ĐƠN HÀNG (ORDERS) ---
    // ===================================

    // Hàm tải danh sách đơn hàng
    function loadOrders() {
        $.ajax({
            url: '../api/orders.php',
            method: 'POST',
            data: { action: 'get_all' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const tbody = $('#orders-table tbody');
                    tbody.empty();
                    response.data.forEach(function(order) {
                        tbody.append(renderOrderRow(order));
                    });
                }
            },
            error: function() {
                showToast('Lỗi tải danh sách đơn hàng.', false);
            }
        });
    }
    
    // Hàm render một hàng order
    function renderOrderRow(order) {
        const formattedPrice = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(order.total_price);
        return `
            <tr data-id="${order.id}">
                <td>#${order.id}</td>
                <td>${order.customer_name}</td>
                <td>${formattedPrice}</td>
                <td>${formatBadge('status', order.status)}</td>
                <td>${order.order_date}</td>
                <td>
                    <button class="btn btn-edit-order" data-id="${order.id}">Sửa</button>
                    <button class="btn btn-delete btn-delete-order" data-id="${order.id}">Xoá</button>
                </td>
            </tr>
        `;
    }

    // Mở Modal Sửa Đơn Hàng
    $(document).on('click', '.btn-edit-order', function() {
        const id = $(this).data('id');
        
        $.ajax({
            url: '../api/orders.php',
            method: 'POST',
            data: { action: 'get_one', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const order = response.data;
                    $('#order-id').val(order.id);
                    $('#order-customer-name').val(order.customer_name);
                    $('#order-total-price').val(order.total_price);
                    $('#order-status').val(order.status);
                    
                    $('#order-modal').css('display', 'flex');
                } else {
                    showToast(response.message, false);
                }
            },
            error: function() {
                showToast('Lỗi khi lấy thông tin đơn hàng.', false);
            }
        });
    });

    // Xử lý Submit Form Sửa Đơn Hàng
    $('#order-form').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        
        $.ajax({
            url: '../api/orders.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, true);
                    $('#order-modal').css('display', 'none');
                    
                    const oldOrderDate = $(`#orders-table tr[data-id="${response.data.id}"] td:nth-child(5)`).text();
                    response.data.order_date = oldOrderDate;

                    const updatedRow = renderOrderRow(response.data);
                    $(`#orders-table tr[data-id="${response.data.id}"]`).replaceWith(updatedRow);
                } else {
                    showToast(response.message, false);
                }
            },
            error: function() {
                showToast('Có lỗi xảy ra, vui lòng thử lại.', false);
            }
        });
    });

    // Xử lý Xoá Đơn Hàng
    $(document).on('click', '.btn-delete-order', function() {
        const id = $(this).data('id');
        
        if (confirm("Bạn có chắc chắn muốn xoá đơn hàng này?")) {
            $.ajax({
                url: '../api/orders.php',
                method: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, true);
                        $(`#orders-table tr[data-id="${id}"]`).fadeOut(500, function() { $(this).remove(); });
                    } else {
                        showToast(response.message, false);
                    }
                },
                error: function() { showToast('Lỗi khi xoá đơn hàng.', false); }
            });
        }
    });

});

