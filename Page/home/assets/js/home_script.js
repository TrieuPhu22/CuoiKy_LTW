/**
 * Hàm render danh sách sản phẩm
 * @param {Array} data - Mảng dữ liệu sản phẩm (đã được lọc)
 * @param {string} selector - CSS Selector của nơi cần chèn HTML
 */
function renderProductList(data, selector) {
  const productsToShow = data;

  var html = productsToShow
    .map(function (product) {
      // === SỬA LỖI ĐƯỜNG DẪN ẢNH (LOGIC MỚI) ===

      let imageUrl = product.image_url;

      const isExternal = imageUrl && imageUrl.startsWith("http");

      // Mặc định, giả sử đường dẫn không hợp lệ
      let finalImageUrl = "https://placehold.co/300x300/E2E8F0/A0AEC0?text=Hoa";

      if (isExternal) {
        // 1. Nếu là link ngoài (http...), dùng luôn
        finalImageUrl = imageUrl;
      } else if (imageUrl) {
        // 2. Nếu là link nội bộ
        let cleanPath = imageUrl;

        if (imageUrl.startsWith("../uploads/")) {
          // 2a. Sửa lỗi CSDL lưu sai: cắt bỏ '../'
          cleanPath = imageUrl.substring(3); // Giờ nó là 'uploads/ten_anh.jpg'
        } else if (imageUrl.startsWith("uploads/")) {
          // 2b. Đường dẫn CSDL lưu đúng
          cleanPath = imageUrl;
        }

        // 3. SỬA LỖI ĐƯỜNG DẪN - Vì có <base>, chỉ cần đường dẫn từ root
        finalImageUrl = cleanPath;
      }

      // === KẾT THÚC SỬA LỖI ===

      // 2b. Định dạng giá tiền
      const formattedPrice = new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
      }).format(product.price);

      // 2c. Tên sản phẩm
      const productName = product.name;

      // 2d. Tạo link đến trang chi tiết sản phẩm
      const productDetailUrl = `Page/products/products.php?id=${product.id}`;

      // 2e. Trả về HTML với link điều hướng
      return `
      <div class="home-list-product-item">
        <a href="${productDetailUrl}" style="cursor: pointer;">
          <img src="${finalImageUrl}" alt="${productName}" />
        </a>
        <a class="name" href="${productDetailUrl}" style="cursor: pointer; text-decoration: none;">${productName}</a>
        <div class="price">
          <span class="price-new">${formattedPrice}</span>
        </div>
        <a class="order-btn" href="${productDetailUrl}" style="cursor: pointer; text-decoration: none;">Đặt hàng</a>
      </div>
    `;
    })
    .join("");

  // Đổ HTML vào selector
  $(selector).html(html);
}

// BƯỚC 3: Dùng $(document).ready
$(document).ready(function () {
  console.log("home_script.js loaded"); // DEBUG
  console.log("Products from DB:", allProductsFromDB); // DEBUG

  // 1. Lọc Hoa Sinh Nhật
  const birthdayProducts = allProductsFromDB.filter(function (product) {
    return product.category === "hoa_sinh_nhat";
  });
  renderProductList(birthdayProducts, "#product-list-birthday");

  // 2. Lọc Hoa Khai Trương
  const openingProducts = allProductsFromDB.filter(function (product) {
    return product.category === "hoa_khai_truong";
  });
  renderProductList(openingProducts, "#product-list-opening");

  // 3. Lọc Chủ Đề
  const themeProducts = allProductsFromDB.filter(function (product) {
    return product.category === "chu_de";
  });
  renderProductList(themeProducts, "#product-list-theme");

  // 4. Lọc Thiết Kế
  const designProducts = allProductsFromDB.filter(function (product) {
    return product.category === "thiet_ke";
  });
  renderProductList(designProducts, "#product-list-design");

  // 5. Lọc Hoa Tươi
  const freshProducts = allProductsFromDB.filter(function (product) {
    return product.category === "hoa_tuoi";
  });
  renderProductList(freshProducts, "#product-list-fresh");
});
