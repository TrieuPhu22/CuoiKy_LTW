<?php
// BƯỚC 1: Thêm "Người gác cổng"
// Tệp này sẽ kiểm tra session, nếu không phải Admin, sẽ đá về trang signin.php
include 'admin_auth_check.php';


?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Đã bảo vệ</title>
    <!-- Tải jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Tùy chỉnh font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Liên kết đến tệp CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-100">

    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <span class="sidebar-title">Admin Panel</span>
            </div>
            <nav class="sidebar-nav">
                <a class="nav-link active" href="#" data-target="dashboard-section">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span>Dashboard</span>
                </a>
                <a class="nav-link" href="#" data-target="products-section">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <span>Quản lý Sản phẩm</span>
                </a>
                <a class="nav-link" href="#" data-target="users-section">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span>Quản lý Người dùng</span>
                </a>
                <a class="nav-link" href="#" data-target="orders-section">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    <span>Quản lý Đơn hàng</span>
                </a>
            </nav>
        </div>

        <!-- Main content -->
        <div class="main-content">
            <!-- Header (Đã cập nhật) -->
            <header class="main-header">
                <div>
                    <!-- THAY ĐỔI: Hiển thị tên Admin đã đăng nhập -->
                    <h2>Chào mừng trở lại, <?php echo htmlspecialchars($_SESSION['user_username']); ?>!</h2>
                </div>
                <div class="header-right">
                    <button class="header-button">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341A6.002 6.002 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </button>
                    <!-- THAY ĐỔI: Chuyển nút Đăng xuất thành link -->
                    <a href="signout.php" class="logout-button">Đăng xuất</a>
                </div>
            </header>

            <!-- Content Area (Giữ nguyên) -->
            <main class="content-area">
                
                <!-- Section: Dashboard (Default) -->
                <div id="dashboard-section" class="content-section" style="display: block;">
                    <h3 class="content-title">Dashboard</h3>
                    <div class="dashboard-grid">
                        <div class="card">
                            <div class="card-title">Tổng doanh thu</div>
                            <div class="card-value">120.000.000 đ</div>
                            <div class="card-footer text-green-600">+15% so với tháng trước</div>
                        </div>
                        <div class="card">
                            <div class="card-title">Đơn hàng mới</div>
                            <div class="card-value">32</div>
                            <div class="card-footer text-gray-500">Trong 24 giờ qua</div>
                        </div>
                        <div class="card">
                            <div class="card-title">Người dùng đăng ký</div>
                            <div class="card-value">150</div>
                            <div class="card-footer text-green-600">+5 mới hôm nay</div>
                        </div>
                    </div>
                </div>

                <!-- Section: Quản lý Sản phẩm -->
                <div id="products-section" class="content-section">
                    <div class="table-header">
                        <h3 class="content-title">Quản lý Sản phẩm</h3>
                        <button class="btn-primary" id="btn-add-product">
                            Thêm sản phẩm mới
                        </button>
                    </div>
                    <div class="table-container">
                        <table class="data-table" id="products-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Giá</th>
                                    <th>Danh mục</th>
                                    <th>Tồn kho</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dữ liệu sản phẩm sẽ được chèn vào đây bằng jQuery -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section: Quản lý Người dùng -->
                <div id="users-section" class="content-section">
                    <div class="table-header">
                        <h3 class="content-title">Quản lý Người dùng</h3>
                        <button class="btn-primary" id="btn-add-user">
                            Thêm người dùng mới
                        </button>
                    </div>
                    <div class="table-container">
                        <table class="data-table" id="users-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên người dùng</th>
                                    <th>Email</th>
                                    <th>Vai trò</th>
                                    <th>Ngày tham gia</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dữ liệu người dùng sẽ được chèn vào đây bằng jQuery -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section: Quản lý Đơn hàng -->
                <div id="orders-section" class="content-section">
                    <div class="table-header">
                        <h3 class="content-title">Quản lý Đơn hàng</h3>
                    </div>
                    <div class="table-container">
                        <table class="data-table" id="orders-table">
                            <thead>
                                <tr>
                                    <th>ID Đơn hàng</th>
                                    <th>Tên khách hàng</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày đặt</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dữ liệu đơn hàng sẽ được chèn vào đây bằng jQuery -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- Modal cho Form Thêm/Sửa Sản Phẩm (Giữ nguyên) -->
    <div id="product-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="product-modal-title">Thêm Sản Phẩm Mới</h4>
                <button class="modal-close" data-dismiss="modal">&times;</button>
            </div>
            <form id="product-form" enctype="multipart/form-data">
                <input type="hidden" id="product-id" name="id">
                <input type="hidden" id="product-action" name="action" value="add">
                <input type="hidden" id="product-existing-image" name="existing_image_url">
                
                <div class="form-group">
                    <label for="product-name">Tên sản phẩm:</label>
                    <input type="text" id="product-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="product-description">Mô tả:</label>
                    <textarea id="product-description" name="description"></textarea>
                </div>
                <div class="form-group">
                    <label for="product-price">Giá (VNĐ):</label>
                    <input type="number" id="product-price" name="price" required>
                </div>
                
                <div class="form-group">
                    <label for="product-stock">Tồn kho:</label>
                    <input type="number" id="product-stock" name="stock" required>
                </div>

                <div class="form-group">
                    <label for="product-category">Danh mục:</label>
                    <select id="product-category" name="category" required>
                        <option value="" disabled selected>-- Chọn danh mục --</option>
                        <option value="hoa_sinh_nhat">Hoa Sinh Nhật</option>
                        <option value="hoa_khai_truong">Hoa Khai Trương</option>
                        <option value="chu_de">Chủ Đề</option>
                        <option value="thiet_ke">Thiết Kế</option>
                        <option value="hoa_tuoi">Hoa Tươi</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="product-image-file">Hình ảnh:</label>
                    <input type="file" id="product-image-file" name="image_file" accept="image/png, image/jpeg, image/gif">
                    <small style="color: #6B7280; margin-top: 4px; display: block;">
                        * Bỏ trống nếu không muốn thay đổi ảnh (khi sửa).
                    </small>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Huỷ</button>
                    <button type="submit" class="btn btn-submit">Lưu lại</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal cho Form Thêm/Sửa Người Dùng (Giữ nguyên) -->
    <div id="user-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="user-modal-title">Thêm Người Dùng Mới</h4>
                <button class="modal-close" data-dismiss="modal">&times;</button>
            </div>
            <form id="user-form">
                <input type="hidden" id="user-id" name="id">
                <input type="hidden" id="user-action" name="action" value="add">
                
                <div class="form-group">
                    <label for="user-username">Tên người dùng:</label>
                    <input type="text" id="user-username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="user-email">Email:</label>
                    <input type="email" id="user-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="user-role">Vai trò:</label>
                    <select id="user-role" name="role" required>
                        <option value="User">User</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Huỷ</button>
                    <button type="submit" class="btn btn-submit">Lưu lại</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal cho Form Sửa Đơn Hàng (Giữ nguyên) -->
    <div id="order-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="order-modal-title">Cập Nhật Đơn Hàng</h4>
                <button class="modal-close" data-dismiss="modal">&times;</button>
            </div>
            <form id="order-form">
                <input type="hidden" id="order-id" name="id">
                <input type="hidden" id="order-action" name="action" value="update">
                
                <div class="form-group">
                    <label for="order-customer-name">Tên khách hàng:</label>
                    <input type="text" id="order-customer-name" name="customer_name" required>
                </div>
                <div class="form-group">
                    <label for="order-total-price">Tổng tiền (VNĐ):</label>
                    <input type="number" id="order-total-price" name="total_price" required>
                </div>
                <div class="form-group">
                    <label for="order-status">Trạng thái:</label>
                    <select id="order-status" name="status" required>
                        <option value="Đang xử lý">Đang xử lý</option>
                        <option value="Đã giao">Đã giao</option>
                        <option value="Đã huỷ">Đã huỷ</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Huỷ</button>
                    <button type="submit" class="btn btn-submit">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>


    <!-- Thông báo Toast -->
    <div id="toast-message" class="toast-message"></div>

    <!-- Liên kết đến tệp JS (Phải đặt sau jQuery) -->
    <script src="app.js"></script>

</body>
</html>

