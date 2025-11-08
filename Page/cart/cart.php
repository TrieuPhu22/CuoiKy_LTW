<?php
// (Tuỳ chọn) Nếu bạn có dùng session cho giỏ hàng thì bật:
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <base href="http://localhost/CuoiKy_LTW/">

    <!-- Reset CSS -->
    <link rel="stylesheet" href="Page/cart/assets/css/reset.css" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap"
      rel="stylesheet"
    />

    <!-- Bootstrap CSS -->
    <link
      rel="stylesheet"
      href="node_modules/bootstrap/dist/css/bootstrap.min.css"
    />
    <link
      rel="stylesheet"
      href="node_modules/bootstrap-icons/font/bootstrap-icons.css"
    />

    <!-- Your CSS -->
    
    <link rel="stylesheet" href="Page/cart/assets/css/style.css" />
    <link rel="stylesheet" href="Page/cart/assets/css/breakpoint.css" />

    <title>Giỏ hàng</title>
  </head>
  <body>
    <!-- ======== Header (include) ======== -->
      <!-- // includes nằm ở: Page/home/includes/header.php & footer.php
      // Từ /Page/cart/cart.php -> -->
    <?php
      require_once __DIR__ . '/../home/includes/header.php';
    ?>

    <!-- ======== Main ======== -->
    <main class="container my-5 cart-page">
      <h1 class="mb-4">Giỏ hàng của bạn</h1>

      <div class="row">
        <!-- Trái: danh sách sản phẩm -->
        <div class="col-lg-8">
          <div class="list-group mb-4">
            <!-- 1 sản phẩm (demo) -->
            <div class="list-group-item d-flex align-items-center">
              <img
                src="path/to/image.jpg"
                alt="Sản phẩm"
                class="img-fluid me-3"
                style="width: 80px"
              />
              <div class="flex-grow-1">
                <h5 class="mb-1">Tên sản phẩm</h5>
                <p class="mb-1 text-muted">Size: M / Màu: Xanh</p>
                <p class="mb-0">Giá: <strong>₫XXX.XXX</strong></p>
              </div>
              <div class="d-flex align-items-center">
                <button
                  class="btn btn-outline-secondary btn-sm quantity-minus me-2"
                >
                  -
                </button>
                <input
                  type="number"
                  class="form-control form-control-sm quantity-input me-2"
                  value="1"
                  style="width: 60px"
                />
                <button
                  class="btn btn-outline-secondary btn-sm quantity-plus me-3"
                >
                  +
                </button>
                <button class="btn btn-danger btn-sm cart-item__remove-btn">
                  Xoá
                </button>
              </div>
            </div>
            <!-- TODO: Lặp qua các sản phẩm trong session/db của bạn -->
          </div>

          <a href="../home/home.php" class="btn btn-outline-primary"
            >Tiếp tục mua sắm</a
          >
        </div>

        <!-- Phải: tóm tắt & thanh toán -->
        <div class="col-lg-4">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Tóm tắt đơn hàng</h5>
              <p class="mb-1">
                Tạm tính: <span class="float-end">₫XXX.XXX</span>
              </p>
              <p class="mb-1">
                Phí vận chuyển: <span class="float-end">₫XX.XXX</span>
              </p>
              <p class="mb-1">
                Giảm giá: <span class="float-end">₫XX.XXX</span>
              </p>
              <hr />
              <p class="fw-bold">
                Tổng: <span class="float-end">₫XXX.XXX</span>
              </p>
              <button class="btn btn-success w-100 mt-3">Đến thanh toán</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Trạng thái giỏ hàng trống -->
      <div class="cart-empty-state text-center py-5" style="display: none">
        <p class="fs-4">Giỏ hàng hiện đang trống.</p>
        <a href="../home/home.php" class="btn btn-outline-primary"
          >Tiếp tục mua sắm</a
        >
      </div>
    </main>

    <!-- ======== Footer (include) ======== -->
    <?php
      require_once __DIR__ . '/../home/includes/footer.php';
    ?>

    <!-- Bootstrap JS & deps -->
    <script src="node_modules/bootstrap/dist/js/bootstrap.bundle.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Custom JS -->
    <script src="Page/cart/assets/js/script.js"></script>
  </body>
</html>
