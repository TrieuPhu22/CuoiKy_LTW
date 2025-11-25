/**
 * Hàm render danh sách sản phẩm dạng Swiper Slider
 * @param {Array} data - Mảng dữ liệu sản phẩm (đã được lọc)
 * @param {string} wrapperId - ID của thẻ div chứa các slide (swiper-wrapper)
 * @param {string} swiperContainerClass - Class của container chính (vd: .swiper-birthday)
 * @param {string} nextBtnClass - Class nút Next (tùy chọn)
 * @param {string} prevBtnClass - Class nút Prev (tùy chọn)
 */
function renderSwiperSection(data, wrapperId, swiperContainerClass, nextBtnClass, prevBtnClass) {
  const productsToShow = data;
  const wrapper = $(wrapperId);

  // Kiểm tra nếu không tìm thấy thẻ wrapper thì dừng lại
  if (wrapper.length === 0) return;

  if (productsToShow.length === 0) {
      wrapper.html('<div class="swiper-slide"><p class="text-center w-100">Chưa có sản phẩm</p></div>');
      return;
  }

  var html = productsToShow
    .map(function (product) {
      // === GIỮ NGUYÊN LOGIC XỬ LÝ ẢNH CỦA BẠN ===
      let imageUrl = product.image_url;
      const isExternal = imageUrl && imageUrl.startsWith("http");
      
      // Mặc định ảnh placeholder
      let finalImageUrl = "https://placehold.co/300x300/E2E8F0/A0AEC0?text=Hoa";

      if (isExternal) {
        finalImageUrl = imageUrl;
      } else if (imageUrl) {
        let cleanPath = imageUrl;
        if (imageUrl.startsWith("../uploads/")) {
          cleanPath = imageUrl.substring(3);
        } else if (imageUrl.startsWith("uploads/")) {
          cleanPath = imageUrl;
        }
        finalImageUrl = cleanPath;
      }
      // === KẾT THÚC LOGIC ẢNH ===

      const formattedPrice = new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
      }).format(product.price);

      const productName = product.name;
      const productDetailUrl = `Page/products/products.php?id=${product.id}`;
      return `
      <div class="swiper-slide">
          <div class="home-list-product-item w-full card h-100 border-0 shadow-sm">
            <div class="position-relative overflow-hidden">
                <a href="${productDetailUrl}" style="cursor: pointer;">
                  <img src="${finalImageUrl}" class="card-img-top" alt="${productName}"/>
                </a>
            </div>
            <div class="card-body d-flex flex-column p-3 align-items-center">
                <a class="name card-title text-truncate fw-bold mb-2" href="${productDetailUrl}" >${productName}</a>
                <div class="price mb-3">
                  <span class="price-new text-danger fw-bold fs-5">${formattedPrice}</span>
                </div>
                <a class="order-btn btn btn-outline-primary w-full mt-auto rounded-pill" href="${productDetailUrl}" >Đặt hàng</a>
            </div>
          </div>
      </div>
    `;
    })
    .join("");


  wrapper.html(html);


  new Swiper(swiperContainerClass, {
    slidesPerView: 1,      
    spaceBetween: 15,      
    loop: true,           
    

    navigation: {
        nextEl: nextBtnClass,
        prevEl: prevBtnClass,
    },
    
    autoplay: {
   delay: 5000,
 },
    
    breakpoints: {
        576: {
            slidesPerView: 2, 
            spaceBetween: 15,
        },
        768: {
            slidesPerView: 3, 
            spaceBetween: 20,
        },
        1200: {
            slidesPerView: 4, 
            spaceBetween: 25,
        }
    }
  });
}

// Lọc sản phẩm và render khi tài liệu sẵn sàng
$(document).ready(function () {
  // Đảm bảo biến allProductsFromDB tồn tại
  if (typeof allProductsFromDB === 'undefined') {
      console.error("Chưa có dữ liệu sản phẩm từ PHP");
      return;
  }

  // 1. Lọc Hoa Sinh Nhật
  const birthdayProducts = allProductsFromDB.filter(function (product) {
    // Lưu ý: So sánh này phải khớp chính xác với cột category trong DB
    return product.category === "Hoa Sinh Nhật" || product.category === "hoa_sinh_nhat"; 
  });
  renderSwiperSection(
      birthdayProducts, 
      "#product-list-birthday", // ID của swiper-wrapper
      ".swiper-birthday",       // Class của swiper container
      ".swiper-next-birthday",  // Class nút Next
      ".swiper-prev-birthday"   // Class nút Prev
  );

  // 2. Lọc Hoa Khai Trương
  const openingProducts = allProductsFromDB.filter(function (product) {
    return product.category === "Hoa Khai Trương" || product.category === "hoa_khai_truong";
  });
  renderSwiperSection(
      openingProducts, 
      "#product-list-opening", 
      ".swiper-opening",
      ".swiper-next-opening",
      ".swiper-prev-opening"
  );

  // 3. Lọc Chủ Đề
  const themeProducts = allProductsFromDB.filter(function (product) {
    return product.category === "Chủ Đề" || product.category === "chu_de";
  });
  renderSwiperSection(
      themeProducts, 
      "#product-list-theme", 
      ".swiper-theme",
      ".swiper-next-theme",
      ".swiper-prev-theme"
  );

  // 4. Lọc Thiết Kế
  const designProducts = allProductsFromDB.filter(function (product) {
    return product.category === "Thiết Kế" || product.category === "thiet_ke";
  });
  renderSwiperSection(
      designProducts, 
      "#product-list-design", 
      ".swiper-design",
      ".swiper-next-design", // Lưu ý: Bạn cần thêm nút này vào HTML nếu muốn có nút bấm
      ".swiper-prev-design"
  );

  // 5. Lọc Hoa Tươi
  const freshProducts = allProductsFromDB.filter(function (product) {
    return product.category === "Hoa Tươi" || product.category === "hoa_tuoi";
  });
  renderSwiperSection(
      freshProducts, 
      "#product-list-fresh", 
      ".swiper-fresh",
      ".swiper-next-fresh",
      ".swiper-prev-fresh"
  );
});