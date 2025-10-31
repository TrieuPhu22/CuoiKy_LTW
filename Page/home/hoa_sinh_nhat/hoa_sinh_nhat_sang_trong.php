<?php
// Make this page a complete HTML page so direct visits show header/footer and
// the PJAX loader can still extract the <main> element.
?>
<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Hoa Sinh Nhật Sang Trọng</title>
    <link rel="stylesheet" href="/Page/home/assets/css/reset.css" />
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/node_modules/bootstrap-icons/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="/Page/home/assets/css/breakpoint.css">
    <link rel="stylesheet" href="/Page/home/assets/css/style.css" />
    <link rel="stylesheet" href="/Page/home/assets/css/hoa_sinh_nhat_sang_trong.css" />
</head>

<body>
    <?php include __DIR__ . '/../includes/Header.php'; ?>

    <!-- Menu: place outside <main> so layout stacks top->down like homepage -->
    <div class="homeContainer">
        <?php include __DIR__ . '/../includes/Menu.php'; ?>
    </div>

    <main>

        <div class="homeContainer my-5">
            <h1 class="text-center category-title mb-3"> Hoa Sinh Nhật Sang Trọng</h1>
            <hr class="mb-4">

            <div class="row align-items-center mb-4">
                <div class="col-auto">
                    <button id="button-grid" class="btn btn-outline-secondary"><i class="bi bi-grid-3x3-gap"></i></button>
                </div>
                <div class="col">
                    <!-- empty center spacer -->
                </div>
                <div class="col-auto d-flex">
                    <div class="me-2">
                        <label for="input-sort" class="form-label visually-hidden">Sắp xếp</label>
                        <select id="input-sort" class="form-select" style="width:200px;">
                            <option value="">Giá (Thấp &gt; Cao)</option>
                            <option value="">Giá (Cao &gt; Thấp)</option>
                            <option value="">Tên (A - Z)</option>
                        </select>
                    </div>
                    
                </div>
            </div>

            <div class="row" id="product-grid">
                <?php if (!empty($products) && is_array($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-6 col-md-3 mb-4">
                            <div class="card h-100 product-card">
                                <div class="position-relative">
                                    <a href="<?php echo htmlspecialchars($product['href'] ?? '#'); ?>">
                                        <img src="<?php echo htmlspecialchars($product['thumb'] ?? ''); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name'] ?? ''); ?>">
                                    </a>
                                    <?php if (!empty($product['percent'])): ?>
                                        <span class="badge bg-danger position-absolute" style="top:8px;right:8px;"><?php echo htmlspecialchars($product['percent']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body text-center">
                                    <h5 class="card-title"><a href="<?php echo htmlspecialchars($product['href'] ?? '#'); ?>" class="text-dark text-decoration-none"><?php echo htmlspecialchars($product['name'] ?? ''); ?></a></h5>
                                    <p class="card-text text-pink fw-bold mb-2"><?php echo $product['price'] ?? ''; ?> <?php if (!empty($product['old_price'])): ?><small class="text-muted text-decoration-line-through ms-2"><?php echo $product['old_price']; ?></small><?php endif; ?></p>
                                    <a href="<?php echo htmlspecialchars($product['href'] ?? '#'); ?>" class="btn btn-pink btn-sm">ĐẶT HÀNG</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <p>Không có sản phẩm để hiển thị.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="row">
                <div class="col-12">
                    <nav aria-label="Page navigation" class="d-flex justify-content-center mt-4">
                        <ul class="pagination">
                            <li class="page-item active"><a class="page-link">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item"><a class="page-link" href="#">&gt;</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/Footer.php'; ?>

    <script src="/node_modules/bootstrap/dist/js/bootstrap.bundle.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="/Page/home/assets/js/main.js"></script>
</body>

</html>