-- =====================================================
-- Database schema cho hệ thống quản lý hoa
-- Chạy script này trong phpMyAdmin (Database: flower-shop)
-- =====================================================

-- Tạo bảng products (CHỈ 1 BẢNG cho TẤT CẢ loại hoa)
CREATE TABLE IF NOT EXISTS `products` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL COMMENT 'Tên sản phẩm',
    `category` varchar(100) NOT NULL COMMENT 'Danh mục: hoa_cam_tay, hoa_chuc_mung, hoa_sinh_nhat...',
    `price` decimal(10, 2) NOT NULL COMMENT 'Giá bán',
    `old_price` decimal(10, 2) DEFAULT NULL COMMENT 'Giá cũ (để hiển thị giảm giá)',
    `description` text COMMENT 'Mô tả sản phẩm',
    `image_path` varchar(255) NOT NULL COMMENT 'Đường dẫn ảnh: uploads/xxx.jpg',
    `stock` int(11) DEFAULT 0 COMMENT 'Số lượng tồn kho',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_category` (`category`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Bảng quản lý tất cả sản phẩm hoa';

-- =====================================================
-- Dữ liệu mẫu cho từng danh mục
-- =====================================================

INSERT INTO
    `products` (
        `name`,
        `category`,
        `price`,
        `old_price`,
        `description`,
        `image_path`,
        `stock`
    )
VALUES
    -- CHỦ ĐỀ: Hoa Cầm Tay
    (
        'Hoa Cẩm Tú Cầu Tím',
        'hoa_cam_tay',
        450000.00,
        550000.00,
        'Bó hoa cẩm tú cầu tím tươi đẹp, thích hợp làm quà tặng',
        'uploads/hoa_cam_tay_1.jpg',
        10
    ),
    (
        'Hoa Hồng Đỏ Cầm Tay',
        'hoa_cam_tay',
        350000.00,
        NULL,
        'Bó hoa hồng đỏ cầm tay sang trọng',
        'uploads/hoa_cam_tay_2.jpg',
        15
    ),
    (
        'Hoa Baby Trắng Cầm Tay',
        'hoa_cam_tay',
        280000.00,
        NULL,
        'Bó hoa baby trắng tinh khôi',
        'uploads/hoa_cam_tay_3.jpg',
        20
    ),

-- CHỦ ĐỀ: Hoa Chúc Mừng
(
    'Hoa Chúc Mừng Khai Trương',
    'hoa_chuc_mung',
    650000.00,
    750000.00,
    'Kệ hoa chúc mừng khai trương đẹp mắt',
    'uploads/hoa_chuc_mung_1.jpg',
    8
),
(
    'Hoa Chúc Mừng Thành Công',
    'hoa_chuc_mung',
    550000.00,
    NULL,
    'Bó hoa chúc mừng thành công rực rỡ',
    'uploads/hoa_chuc_mung_2.jpg',
    12
),

-- CHỦ ĐỀ: Hoa Tang Lễ
(
    'Hoa Tang Lễ Trắng',
    'hoa_tang_le',
    800000.00,
    NULL,
    'Vòng hoa tang lễ trang nghiêm',
    'uploads/hoa_tang_le_1.jpg',
    5
),
(
    'Hoa Chia Buồn',
    'hoa_tang_le',
    700000.00,
    NULL,
    'Kệ hoa chia buồn trang trọng',
    'uploads/hoa_tang_le_2.jpg',
    6
),

-- HOA SINH NHẬT
(
    'Hoa Sinh Nhật Sang Trọng',
    'hoa_sinh_nhat_sang_trong',
    550000.00,
    650000.00,
    'Hoa sinh nhật cao cấp với thiết kế sang trọng',
    'uploads/hoa_sinh_nhat_sang_trong_1.jpg',
    8
),
(
    'Hoa Sinh Nhật Đỏ Rực',
    'hoa_sinh_nhat_sang_trong',
    600000.00,
    NULL,
    'Hoa sinh nhật màu đỏ rực rỡ',
    'uploads/hoa_sinh_nhat_sang_trong_2.jpg',
    7
),
(
    'Hoa Sinh Nhật Giá Rẻ',
    'hoa_sinh_nhat_gia_re',
    250000.00,
    300000.00,
    'Hoa sinh nhật đẹp giá phải chăng',
    'uploads/hoa_sinh_nhat_gia_re_1.jpg',
    25
),
(
    'Hoa Sinh Nhật Tặng Người Yêu',
    'hoa_sinh_nhat_tang_nguoi_yeu',
    450000.00,
    NULL,
    'Hoa hồng đỏ tặng người yêu ngày sinh nhật',
    'uploads/hoa_sinh_nhat_nguoi_yeu_1.jpg',
    15
),

-- HOA KHAI TRƯƠNG
(
    'Kệ Hoa Khai Trương',
    'ke_hoa_khai_truong',
    1200000.00,
    1500000.00,
    'Kệ hoa khai trương lớn, sang trọng',
    'uploads/ke_hoa_khai_truong_1.jpg',
    3
),
(
    'Hoa Khai Trương Để Bàn',
    'hoa_khai_truong_de_ban',
    400000.00,
    NULL,
    'Hoa khai trương nhỏ gọn để bàn',
    'uploads/hoa_khai_truong_de_ban_1.jpg',
    10
),
(
    'Hoa Khai Trương Giá Rẻ',
    'hoa_khai_truong_gia_re',
    500000.00,
    600000.00,
    'Hoa khai trương giá rẻ nhưng đẹp',
    'uploads/hoa_khai_truong_gia_re_1.jpg',
    12
),

-- HOA TƯƠI
(
    'Hoa Hồng Đỏ Nhập',
    'hoa_hong',
    380000.00,
    NULL,
    'Hoa hồng đỏ nhập khẩu cao cấp',
    'uploads/hoa_hong_1.jpg',
    30
),
(
    'Hoa Hướng Dương Tươi',
    'hoa_huong_duong',
    320000.00,
    400000.00,
    'Hoa hướng dương tươi rạng rỡ',
    'uploads/hoa_huong_duong_1.jpg',
    20
),
(
    'Hoa Baby Trắng',
    'hoa_baby',
    180000.00,
    NULL,
    'Hoa baby trắng nhỏ xinh',
    'uploads/hoa_baby_1.jpg',
    40
),

-- THIẾT KẾ
(
    'Bó Hoa Cưới',
    'bo_hoa',
    800000.00,
    950000.00,
    'Bó hoa cưới đẹp lung linh',
    'uploads/bo_hoa_1.jpg',
    5
),
(
    'Giỏ Hoa Tươi',
    'gio_hoa',
    650000.00,
    NULL,
    'Giỏ hoa tươi đa dạng màu sắc',
    'uploads/gio_hoa_1.jpg',
    8
),
(
    'Lẵng Hoa Sang Trọng',
    'lang_hoa',
    900000.00,
    NULL,
    'Lẵng hoa sang trọng, quý phái',
    'uploads/lang_hoa_1.jpg',
    6
);