<?php
session_start();
require_once 'admin_auth_check.php';
require_once 'db_connect.php';

// Lấy danh sách sản phẩm
$sql = "SELECT * FROM products ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm - Admin</title>
    <link rel="stylesheet" href="../node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../node_modules/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block bg-light sidebar">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="admin_dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="manage_products.php">
                                <i class="bi bi-box-seam"></i> Sản phẩm
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_orders.php">
                                <i class="bi bi-cart"></i> Đơn hàng
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Quản lý sản phẩm</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="bi bi-plus-circle"></i> Thêm sản phẩm
                    </button>
                </div>

                <!-- Bảng sản phẩm -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Hình ảnh</th>
                                <th>Tên sản phẩm</th>
                                <th>Danh mục</th>
                                <th>Giá</th>
                                <th>Giá cũ</th>
                                <th>Tồn kho</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td>
                                            <img src="../<?php echo $row['image_path']; ?>"
                                                alt="<?php echo $row['name']; ?>"
                                                style="width: 50px; height: 50px; object-fit: cover;">
                                        </td>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                                        <td><?php echo number_format($row['price'], 0, ',', '.'); ?>đ</td>
                                        <td>
                                            <?php
                                            if ($row['old_price']) {
                                                echo number_format($row['old_price'], 0, ',', '.') . 'đ';
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo $row['stock']; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning edit-product"
                                                data-id="<?php echo $row['id']; ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-product"
                                                data-id="<?php echo $row['id']; ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">Chưa có sản phẩm nào</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal thêm sản phẩm -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm sản phẩm mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addProductForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tên sản phẩm *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Danh mục *</label>
                                <select class="form-select" name="category" required>
                                    <option value="">-- Chọn danh mục --</option>
                                    <option value="hoa_cam_tay">Hoa Cầm Tay</option>
                                    <option value="hoa_chuc_mung">Hoa Chúc Mừng</option>
                                    <option value="hoa_tang_le">Hoa Tang Lễ</option>
                                    <option value="hoa_sinh_nhat">Hoa Sinh Nhật</option>
                                    <option value="hoa_khai_truong">Hoa Khai Trương</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Giá *</label>
                                <input type="number" class="form-control" name="price" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Giá cũ (nếu có)</label>
                                <input type="number" class="form-control" name="old_price">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tồn kho *</label>
                                <input type="number" class="form-control" name="stock" value="0" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hình ảnh *</label>
                            <input type="file" class="form-control" name="image" accept="image/*" required>
                            <div class="mt-2">
                                <img id="imagePreview" src="" alt="Preview" style="max-width: 200px; display: none;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Thêm sản phẩm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal sửa sản phẩm -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa sản phẩm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editProductForm" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tên sản phẩm *</label>
                                <input type="text" class="form-control" name="name" id="edit_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Danh mục *</label>
                                <select class="form-select" name="category" id="edit_category" required>
                                    <option value="hoa_cam_tay">Hoa Cầm Tay</option>
                                    <option value="hoa_chuc_mung">Hoa Chúc Mừng</option>
                                    <option value="hoa_tang_le">Hoa Tang Lễ</option>
                                    <option value="hoa_sinh_nhat">Hoa Sinh Nhật</option>
                                    <option value="hoa_khai_truong">Hoa Khai Trương</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Giá *</label>
                                <input type="number" class="form-control" name="price" id="edit_price" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Giá cũ (nếu có)</label>
                                <input type="number" class="form-control" name="old_price" id="edit_old_price">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tồn kho *</label>
                                <input type="number" class="form-control" name="stock" id="edit_stock" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hình ảnh hiện tại</label>
                            <div>
                                <img id="edit_current_image" src="" alt="Current" style="max-width: 200px;">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Thay đổi hình ảnh (nếu muốn)</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Cập nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="manage_products.js"></script>
</body>

</html>