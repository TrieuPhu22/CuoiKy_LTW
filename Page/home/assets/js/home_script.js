/**
 * Hàm render danh sách sản phẩm
 * @param {Array} data - Mảng dữ liệu sản phẩm (từ allProductsFromDB)
 * @param {string} selector - CSS Selector của nơi cần chèn HTML
 */
function renderProductList(data, selector) {
  // Lọc/sắp xếp dữ liệu nếu cần.
  // Hiện tại, chúng ta chỉ hiển thị 4 sản phẩm đầu tiên cho mỗi danh mục
  const productsToShow = data

  var html = productsToShow
    .map(function (product) {
      // === SỬA LỖI Ở ĐÂY ===

      // 1. Dùng 'image_url' (đã đúng)
      let imageUrl = product.image_url; 
      console.log("Original image path from DB:", imageUrl); // Dòng này để debug

      // 2. Sửa lại logic đường dẫn
      const isExternal = imageUrl && imageUrl.startsWith("http");
      // Kiểm tra xem nó có phải là đường dẫn 'uploads/' hợp lệ hay không
      const isLocalFile = !isExternal && imageUrl && (imageUrl.startsWith("uploads/") || imageUrl.startsWith("../uploads/"));

      if (isLocalFile) {
       let relativePath = imageUrl;

        if (imageUrl.startsWith("../uploads/")) {
          // Nếu là đường dẫn sai ('../uploads/'), cắt bỏ 3 ký tự đầu ('../')
          relativePath = imageUrl.substring(3); // Giờ nó là 'uploads/...'
        }
        
        // Thêm '../../' ở đầu để đi từ 'page/home/' ra thư mục gốc 'WEB_DAT_HOA/' rồi vào 'uploads/'
        // Kết quả cuối cùng luôn là '../../uploads/ten_anh.jpg'
        imageUrl = "../../" + relativePath;
      }
      
      if (!isExternal && !isLocalFile) {
        // Chỉ dùng ảnh mặc định nếu không phải link ngoài VÀ cũng không phải 'uploads/'
        imageUrl = "https://placehold.co/300x300/E2E8F0/A0AEC0?text=Hoa";
      }
      
      // === KẾT THÚC SỬA LỖI ===

      // 2b. Định dạng giá tiền
      const formattedPrice = new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
      }).format(product.price);

      // 2c. Tên sản phẩm
      const productName = product.name; // Lấy từ CSDL

      // 2d. Trả về HTML
      return `
      <div class="home-list-product-item">
        <img src="${imageUrl}" alt="${productName}" />
        <a class="name" href="#">${productName}</a>
        <div class="price">
          <span class="price-new">${formattedPrice}</span>
        </div>
        <a class="order-btn" href="#">Đặt hàng</a>
      </div>
    `;
    })
    .join("");

  // Đổ HTML vào selector
  $(selector).html(html);
}

// BƯỚC 3: Dùng $(document).ready
$(document).ready(function () {
  // GỌI HÀM RENDER, TRUYỀN DỮ LIỆU TỪ CSDL VÀO
  // (Biến 'allProductsFromDB' được tạo trong tệp home.php)

  // Hiện tại, chúng ta dùng chung 1 danh sách sản phẩm cho tất cả các mục
  // Bạn có thể cải tiến bằng cách thêm cột "category" vào CSDL
  // và lọc (filter) mảng 'allProductsFromDB' trước khi truyền vào

  // 1. Lọc Hoa Sinh Nhật
  const birthdayProducts = allProductsFromDB.filter(function(product) {
    return product.category === 'hoa_sinh_nhat';
  });
  renderProductList(birthdayProducts, "#product-list-birthday");
  // 2. Lọc Hoa Khai Trương
  const openingProducts = allProductsFromDB.filter(function(product) {
    return product.category === 'hoa_khai_truong';
  });
  renderProductList(openingProducts, "#product-list-opening");
  // 3. Lọc Chủ Đề
  const themeProducts = allProductsFromDB.filter(function(product) {
    return product.category === 'chu_de';
  });
  renderProductList(themeProducts, "#product-list-theme");
  // 4. Lọc Thiết Kế
  const designProducts = allProductsFromDB.filter(function(product) {
    return product.category === 'thiet_ke';
  });
  renderProductList(designProducts, "#product-list-design");
  // 5. Lọc Hoa Tươi
  const freshProducts = allProductsFromDB.filter(function(product) {
    return product.category === 'hoa_tuoi';
  });
  renderProductList(freshProducts, "#product-list-fresh");

  // renderProductList(allProductsFromDB, "#product-list-birthday");
  // renderProductList(allProductsFromDB, "#product-list-opening");
  // renderProductList(allProductsFromDB, "#product-list-theme");
  // renderProductList(allProductsFromDB, "#product-list-design");
  // renderProductList(allProductsFromDB, "#product-list-fresh");
});

