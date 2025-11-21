<?php
// filepath: c:\xampp12\htdocs\CuoiKy_LTW\Page\search\search.php
session_start();
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Kết quả tìm kiếm</title>
    <?php
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $project_root = '/CuoiKy_LTW/';
    echo "<base href='{$protocol}://{$host}{$project_root}'>";
    ?>
    <link rel="stylesheet" href="./Page/home/assets/css/reset.css" />
    <link rel="stylesheet" href="./node_modules/bootstrap/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="./node_modules/bootstrap-icons/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="./Page/home/assets/css/style.css" />
</head>

<body>
    <?php include __DIR__ . '/../home/includes/Header.php'; ?>

    <main>
        <div class="homeContainer my-5">
            <?php include __DIR__ . '/../home/includes/Menu.php'; ?>
            
            <?php
            $keyword = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';
            ?>
            
            <h1 class="text-center mb-3">Kết quả tìm kiếm cho: "<strong><?php echo $keyword; ?></strong>"</h1>
            <hr class="mb-4">

            <div class="home-list-product" id="product-grid">
                <div class="col-12">
                    <p class="text-center">Đang tải sản phẩm...</p>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../home/includes/Footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="./Page/home/assets/js/home_script.js"></script>
    <script>
        $(document).ready(function() {
            const keyword = '<?php echo addslashes($keyword); ?>';

            if (keyword.length >= 2) {
                $.ajax({
                    url: './api/products.php',
                    method: 'POST',
                    data: { action: 'search', keyword: keyword },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data.length > 0) {
                            renderProductList(response.data, '#product-grid');
                        } else {
                            $('#product-grid').html(
                                '<div class="col-12"><p class="text-center text-muted">Không tìm thấy sản phẩm nào.</p></div>'
                            );
                        }
                    }
                });
            } else {
                $('#product-grid').html(
                    '<div class="col-12"><p class="text-center text-muted">Vui lòng nhập từ khóa để tìm kiếm.</p></div>'
                );
            }
        });
    </script>
</body>
</html>