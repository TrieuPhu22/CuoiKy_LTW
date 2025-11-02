// JavaScript cho trang quản lý sản phẩm
$(document).ready(function () {
    const BASE_URL = 'http://localhost/CuoiKy_LTW/';

    // Preview ảnh khi chọn file (form thêm)
    $('input[name="image"]').on('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#imagePreview').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });

    // Submit form thêm sản phẩm
    $('#addProductForm').on('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('action', 'add');

        $.ajax({
            url: BASE_URL + 'api/manage_products_api.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    alert(response.message);
                    $('#addProductModal').modal('hide');
                    location.reload();
                } else {
                    alert('Lỗi: ' + response.message);
                }
            },
            error: function () {
                alert('Có lỗi xảy ra khi thêm sản phẩm');
            }
        });
    });

    // Click nút sửa
    $('.edit-product').on('click', function () {
        const productId = $(this).data('id');

        // Lấy thông tin sản phẩm
        $.ajax({
            url: BASE_URL + 'api/manage_products_api.php',
            type: 'POST',
            data: {
                action: 'get',
                id: productId
            },
            success: function (response) {
                if (response.success) {
                    const product = response.data;

                    // Điền dữ liệu vào form
                    $('#edit_id').val(product.id);
                    $('#edit_name').val(product.name);
                    $('#edit_category').val(product.category);
                    $('#edit_price').val(product.price);
                    $('#edit_old_price').val(product.old_price);
                    $('#edit_stock').val(product.stock);
                    $('#edit_description').val(product.description);
                    $('#edit_current_image').attr('src', BASE_URL + product.image_path);

                    // Hiển thị modal
                    $('#editProductModal').modal('show');
                } else {
                    alert('Lỗi: ' + response.message);
                }
            },
            error: function () {
                alert('Có lỗi xảy ra khi lấy thông tin sản phẩm');
            }
        });
    });

    // Submit form sửa sản phẩm
    $('#editProductForm').on('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('action', 'edit');

        $.ajax({
            url: BASE_URL + 'api/manage_products_api.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    alert(response.message);
                    $('#editProductModal').modal('hide');
                    location.reload();
                } else {
                    alert('Lỗi: ' + response.message);
                }
            },
            error: function () {
                alert('Có lỗi xảy ra khi cập nhật sản phẩm');
            }
        });
    });

    // Click nút xóa
    $('.delete-product').on('click', function () {
        if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
            return;
        }

        const productId = $(this).data('id');

        $.ajax({
            url: BASE_URL + 'api/manage_products_api.php',
            type: 'POST',
            data: {
                action: 'delete',
                id: productId
            },
            success: function (response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Lỗi: ' + response.message);
                }
            },
            error: function () {
                alert('Có lỗi xảy ra khi xóa sản phẩm');
            }
        });
    });
});
