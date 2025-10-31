<?php
// Menu include - moved from home.php
?>
<div class="homeMenu">
    <!-- CHỦ ĐỀ (moved to front, styled pink) -->
    <div class="dropdown">
        <button
            class="btn dropdown-toggle text-pink fw-bold"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false">
            CHỦ ĐỀ
        </button>
        <ul class="dropdown-menu">
            <li>
                <a class="dropdown-item ajax-link" href="./Page/home/chu_de/hoa_chuc_mung.php">HOA CHÚC MỪNG</a>
            </li>
            <li>
                <a class="dropdown-item ajax-link" href="./Page/home/chu_de/hoa_cam_tay.php">HOA CẦM TAY</a>
            </li>
            <li>
                <a class="dropdown-item ajax-link" href="./Page/home/chu_de/hoa_tang_le.php">HOA TANG LỄ - HOA CHIA BUỒN</a>
            </li>
        </ul>
    </div>

    <!-- hoa sinh nhat -->
    <div class="dropdown">
        <button
            class="btn dropdown-toggle"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false">
            HOA SINH NHẬT
        </button>
        <ul class="dropdown-menu">
            <li>
                <a class="dropdown-item ajax-link" href="./Page/home/hoa_sinh_nhat/hoa_sinh_nhat_sang_trong.php">HOA SINH NHẬT SANG TRỌNG</a>
            </li>
            <li>
                <a class="dropdown-item ajax-link" href="./Page/home/hoa_sinh_nhat/hoa_sinh_nhat_gia_re.php">HOA SINH NHẬT GIÁ RẺ</a>
            </li>
            <li>
                <a class="dropdown-item ajax-link" href="./Page/home/hoa_sinh_nhat/hoa_sinh_nhat_tang_nguoi_yeu.php">HOA SINH NHẬT TẶNG NGƯỜI YÊU</a>
            </li>
        </ul>
    </div>

    <!-- HOA KHAI TRƯƠNG (order updated, added giá rẻ page) -->
    <div class="dropdown">
        <button
            class="btn dropdown-toggle"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false">
            HOA KHAI TRƯƠNG
        </button>
        <ul class="dropdown-menu">
            <li>
                <a class="dropdown-item ajax-link" href="./Page/home/hoa_khai_truong/hoa_khai_truong_de_ban.php">HOA KHAI TRƯƠNG ĐỂ BÀN</a>
            </li>
            <li>
                <a class="dropdown-item ajax-link" href="./Page/home/hoa_khai_truong/ke_hoa_khai_truong.php">KỆ HOA KHAI TRƯƠNG HIỆN ĐẠI</a>
            </li>
            <li>
                <a class="dropdown-item ajax-link" href="./Page/home/hoa_khai_truong/hoa_khai_truong_gia_re.php">HOA KHAI TRƯƠNG GIÁ RẺ</a>
            </li>
        </ul>
    </div>

    <!-- THIẾT KẾ -->
    <div class="dropdown">
        <button
            class="btn dropdown-toggle"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false">
            THIẾT KẾ
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="./Page/home/thiet_ke/bo_hoa.php">BÓ HOA</a></li>
            <li><a class="dropdown-item" href="./Page/home/thiet_ke/lang_hoa.php">LẴNG HOA</a></li>
            <li><a class="dropdown-item" href="./Page/home/thiet_ke/gio_hoa.php">GIỎ HOA</a></li>
        </ul>
    </div>

    <!-- HOA TƯƠI -->
    <div class="dropdown">
        <button
            class="btn dropdown-toggle"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false">
            HOA TƯƠI
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="./Page/home/hoa_tuoi/hoa_hong.php">HOA HỒNG</a></li>
            <li><a class="dropdown-item" href="./Page/home/hoa_tuoi/hoa_baby.php">HOA BABY</a></li>
            <li><a class="dropdown-item" href="./Page/home/hoa_tuoi/hoa_huong_duong.php">HOA HƯỚNG DƯƠNG</a></li>
        </ul>
    </div>
</div>