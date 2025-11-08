<?php
?>
<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Hoa Sinh Nhật Tặng Người Yêu</title>
    <base href="http://localhost/CuoiKy_LTW/">
    <link rel="stylesheet" href="./Page/home/assets/css/reset.css" />
    <link rel="stylesheet" href="./node_modules/bootstrap/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="./node_modules/bootstrap-icons/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="./Page/home/assets/css/breakpoint.css">
    <link rel="stylesheet" href="./Page/home/assets/css/style.css" />
    <link rel="stylesheet" href="./Page/home/assets/css/hoa_sinh_nhat_sang_trong.css" />
</head>

<body>
    <?php include __DIR__ . '/../includes/Header.php'; ?>



    <main>
        <div class="homeContainer my-5">
            <?php include __DIR__ . '/../includes/Menu.php'; ?>
            <h1 class="text-center category-title mb-3">Hoa Sinh Nhật Tặng Người Yêu</h1>
            <hr class="mb-4">

            <div class="row align-items-center mb-4">
                <div class="col-auto">
                    <button id="button-grid" class="btn btn-outline-secondary"><i class="bi bi-grid-3x3-gap"></i></button>
                </div>
                <div class="col"></div>
                <div class="col-auto d-flex">
                    <div class="me-2">
                        <select id="input-sort" class="form-select" style="width:200px;">
                            <option value="">Giá (Thấp &gt; Cao)</option>
                        </select>
                    </div>
                    <div>
                        <select id="input-limit" class="form-select" style="width:100px;">
                            <option>24</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row" id="product-grid">
                <div class="col-12">
                    <p>Không có sản phẩm để hiển thị.</p>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <nav aria-label="Page navigation" class="d-flex justify-content-center mt-4">
                        <ul class="pagination">
                            <li class="page-item active"><a class="page-link">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/Footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="/Page/home/assets/js/main.js"></script>
</body>

</html>