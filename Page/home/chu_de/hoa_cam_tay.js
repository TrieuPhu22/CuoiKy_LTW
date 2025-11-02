// JavaScript để load sản phẩm từ database cho trang Hoa Cầm Tay
document.addEventListener('DOMContentLoaded', function () {
    const productGrid = document.getElementById('product-grid');
    const sortSelect = document.getElementById('input-sort');

    // Cấu hình
    const CATEGORY = 'hoa_cam_tay'; // Category cho trang này
    const BASE_URL = 'http://localhost/CuoiKy_LTW/';

    // Load sản phẩm
    function loadProducts(sortBy = 'price_asc') {
        // Hiển thị loading
        productGrid.innerHTML = `
            <div class="col-12 text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Đang tải...</span>
                </div>
            </div>
        `;

        // Gọi API
        fetch(`${BASE_URL}api/get_products.php?category=${CATEGORY}&sort=${sortBy}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    displayProducts(data.data);
                } else {
                    productGrid.innerHTML = `
                        <div class="col-12 text-center">
                            <p class="text-muted">Không có sản phẩm để hiển thị</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Lỗi khi load sản phẩm:', error);
                productGrid.innerHTML = `
                    <div class="col-12 text-center">
                        <p class="text-danger">Có lỗi xảy ra khi tải sản phẩm. Vui lòng thử lại sau.</p>
                    </div>
                `;
            });
    }

    // Hiển thị danh sách sản phẩm
    function displayProducts(products) {
        let html = '';

        products.forEach(product => {
            const priceHtml = product.old_price
                ? `<p class="product-price">
                       <span class="new-price">${formatPrice(product.price)}</span>
                       <span class="old-price">${formatPrice(product.old_price)}</span>
                   </p>`
                : `<p class="product-price">${formatPrice(product.price)}</p>`;

            html += `
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="product-card">
                        <div class="product-image">
                            <img src="${BASE_URL}${product.image_path}" 
                                 alt="${product.name}" 
                                 onerror="this.src='${BASE_URL}img/placeholder.jpg'">
                        </div>
                        <div class="product-info">
                            <h3 class="product-name">${product.name}</h3>
                            ${priceHtml}
                            <button class="btn btn-primary w-100 add-to-cart" 
                                    data-product-id="${product.id}">
                                <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        productGrid.innerHTML = html;

        // Thêm event listener cho nút thêm giỏ hàng
        addCartEventListeners();
    }

    // Format giá tiền
    function formatPrice(price) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(price);
    }

    // Thêm event listener cho nút thêm giỏ hàng
    function addCartEventListeners() {
        const addToCartButtons = document.querySelectorAll('.add-to-cart');
        addToCartButtons.forEach(button => {
            button.addEventListener('click', function () {
                const productId = this.getAttribute('data-product-id');
                addToCart(productId);
            });
        });
    }

    // Thêm sản phẩm vào giỏ hàng
    function addToCart(productId) {
        // Lấy giỏ hàng từ localStorage
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');

        // Kiểm tra sản phẩm đã có trong giỏ chưa
        const existingItem = cart.find(item => item.id === productId);

        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({
                id: productId,
                quantity: 1
            });
        }

        // Lưu lại vào localStorage
        localStorage.setItem('cart', JSON.stringify(cart));

        // Hiển thị thông báo
        alert('Đã thêm sản phẩm vào giỏ hàng!');

        // Cập nhật số lượng giỏ hàng trên header (nếu có)
        updateCartCount();
    }

    // Cập nhật số lượng giỏ hàng
    function updateCartCount() {
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);

        const cartBadge = document.querySelector('.cart-count');
        if (cartBadge) {
            cartBadge.textContent = totalItems;
        }
    }

    // Event listener cho sort
    sortSelect.addEventListener('change', function () {
        loadProducts(this.value);
    });

    // Load sản phẩm lần đầu
    loadProducts();

    // Cập nhật cart count khi load trang
    updateCartCount();
});
