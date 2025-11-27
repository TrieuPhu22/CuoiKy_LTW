<?php
// filepath: c:\xampp\htdocs\CuoiKy_LTW\Page\home\includes\chatbot.php

// ✅ XÁC ĐỊNH BASE PATH ĐỘNG - SỬA LẠI CHO PRODUCTS
$currentPath = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

// Debug path
error_log("Current Path: " . $currentPath);
error_log("Script Name: " . $scriptName);

$basePath = '';

// Xác định base path dựa trên script name
if (strpos($scriptName, '/Page/cart/') !== false) {
    $basePath = '../../';
} elseif (strpos($scriptName, '/Page/products/') !== false) {
    $basePath = '../../'; // ✅ FIX: products cũng cần ../../
} elseif (strpos($scriptName, '/Page/user/') !== false) {
    $basePath = '../../';
} elseif (strpos($scriptName, '/Page/search/') !== false) {
    $basePath = '../../';
} elseif (strpos($scriptName, '/Page/home/') !== false) {
    $basePath = '../../../';
} else {
    // Trang chủ hoặc root
    $basePath = './';
}

error_log("Base Path: " . $basePath);
?>

<!-- ✅ NÚT CHAT AI - GÓC DƯỚI PHẢI -->
<div class="ai-chat-floating-wrapper">
    <div class="ai-chat-greeting" id="ai-chat-greeting" style="display: none;">
        Bạn cần hỗ trợ gì ạ?
    </div>
    <div class="ai-chat-floating-btn" id="open-ai-chat">
        <i class="bi bi-chat-dots-fill"></i>
    </div>
</div>

<!-- ✅ CHATBOT MODAL - KÉO THẢ ĐƯỢC -->
<div id="ai-chatbot-modal" class="ai-chat-modal">
    <div class="ai-chat-container" id="ai-chat-draggable">
        <div class="ai-chat-header" id="ai-chat-header">
            <div class="ai-chat-title" id="ai-chat-title">
                <i class="bi bi-grip-vertical"></i>
                <i class="bi bi-robot"></i>
                <span>Trợ lý AI tư vấn hoa</span>
            </div>
            <button class="ai-chat-close" id="close-ai-chat">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        
        <div class="ai-chat-messages" id="ai-chat-messages">
            <div class="ai-message ai-bot-message">
                <div class="ai-message-avatar">
                    <i class="bi bi-robot"></i>
                </div>
                <div class="ai-message-content">
                    <p>👋 Xin chào! Tôi là trợ lý AI của shop hoa. Tôi có thể giúp bạn:</p>
                    <ul>
                        <li>🌹 Tư vấn chọn hoa theo dịp (sinh nhật, khai trương, cưới...)</li>
                        <li>💐 Gợi ý sản phẩm cụ thể trong kho</li>
                        <li>💰 Tư vấn giá và link xem chi tiết</li>
                    </ul>
                    <p><strong>Ví dụ:</strong></p>
                    <p>• "Tôi muốn mua hoa sinh nhật giá 500k"</p>
                    <p>• "Gợi ý hoa hồng tặng người yêu"</p>
                </div>
            </div>
        </div>
        
        <div class="ai-chat-products" id="ai-chat-products" style="display: none;"></div>
        
        <div class="ai-chat-input-area">
            <div class="ai-chat-input-wrapper">
                <input type="text" id="ai-chat-input" class="ai-chat-input" placeholder="VD: Hoa sinh nhật giá 500k" />
                <button class="ai-chat-send" id="ai-chat-send">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* ===== NÚT CHAT FLOATING - GÓC DƯỚI PHẢI ===== */
.ai-chat-floating-wrapper {
    position: fixed;
    bottom: 80px;
    right: 30px;
    z-index: 9998;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 10px;
    transition: transform 0.2s ease, z-index 0.2s ease;
    cursor: grab;
    /* ✅ THÊM THUỘC TÍNH NÀY ĐỂ FIX LAYOUT SHIFT */
    width: auto;
    min-width: 60px; /* Chiều rộng tối thiểu bằng nút chat */
}

/* ✅ SỬA CSS GREETING ĐỂ KHÔNG ẢNH HƯỞNG LAYOUT */
.ai-chat-greeting {
    background: white;
    color: #333;
    padding: 12px 20px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    white-space: nowrap;
    animation: slideInRight 0.5s ease, float 3s ease-in-out infinite;
    /* ✅ THÊM POSITION ABSOLUTE ĐỂ KHÔNG ẢNH HƯỞNG LAYOUT */
    position: absolute;
    top: -50px; /* Đẩy lên trên nút chat */
    right: 0;
    transform-origin: bottom right;
}

.ai-chat-floating-btn {
    background: linear-gradient(135deg, #e63946 0%, #f72585 100%);
    color: white;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(230, 57, 70, 0.5);
    transition: all 0.3s ease;
    animation: pulse 2s infinite;
    /* ✅ THÊM THUỘC TÍNH ĐỂ ĐẢM BẢO VỊ TRÍ CỐ ĐỊNH */
    position: relative;
    flex-shrink: 0;
}

/* ✅ SỬA ANIMATION SLIDE IN RIGHT */
@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(30px) scale(0.8);
    }
    to {
        opacity: 1;
        transform: translateX(0) scale(1);
    }
}

@keyframes float {
    0%, 100% {
        transform: translateX(0) translateY(0) scale(1);
    }
    50% {
        transform: translateX(0) translateY(-5px) scale(1);
    }
}

/* ✅ RESPONSIVE CHO GREETING */
@media (max-width: 768px) {
    .ai-chat-greeting {
        font-size: 12px;
        padding: 8px 15px;
        top: -45px; /* Điều chỉnh cho mobile */
        max-width: 200px; /* Giới hạn chiều rộng trên mobile */
        word-wrap: break-word;
    }
    
    .ai-chat-floating-wrapper {
        bottom: 70px;
        right: 20px;
        min-width: 50px;
    }
    
    .ai-chat-floating-btn {
        width: 50px;
        height: 50px;
    }
}

/* ✅ THÊM CSS CHO TRẠNG THÁI DRAGGING */
.ai-chat-floating-wrapper.dragging-floating {
    z-index: 10002 !important;
    cursor: grabbing !important;
}

.ai-chat-floating-btn.dragging {
    cursor: grabbing !important;
    transform: scale(1.15) !important;
    box-shadow: 0 8px 40px rgba(230, 57, 70, 0.9) !important;
    animation: none !important;
}

/* ✅ ĐẢM BẢO GREETING KHÔNG ẢNH HƯỞNG KHI DRAG */
.ai-chat-floating-wrapper.dragging-floating .ai-chat-greeting {
    opacity: 0.5;
    pointer-events: none;
}

/* Câu chào bên ngoài */
.ai-chat-greeting {
    background: white;
    color: #333;
    padding: 12px 20px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    white-space: nowrap;
    animation: slideInRight 0.5s ease, float 3s ease-in-out infinite;
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes float {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-5px);
    }
}

/* ===== MODAL CHATBOT - KÉO THẢ ĐƯỢC ===== */
.ai-chat-modal {
    display: none;
    position: fixed;
    bottom: 160px;
    right: 30px;
    z-index: 9999;
}

.ai-chat-modal.active {
    display: block;
    animation: slideInUp 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Chat Container - Draggable */
.ai-chat-container {
    width: 400px;
    height: 550px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    
}

/* ✅ DRAGGING STATE - THÊM VÀO ĐÂY */
.ai-chat-container.dragging {
    cursor: grabbing !important;
    user-select: none;
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4) !important;
    transform: scale(1.02);
    z-index: 10001;
}

.ai-chat-container.dragging .ai-chat-header {
    cursor: grabbing !important;
}

.ai-chat-container:not(.dragging) {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

/* Header - Có thể kéo */
.ai-chat-header {
    background: linear-gradient(135deg, #e63946 0%, #f72585 100%);
    color: white;
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: grab;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

.ai-chat-header:active {
    cursor: grabbing;
}

.ai-chat-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    font-size: 16px;
}

.ai-chat-title i.bi-grip-vertical {
    font-size: 16px;
    opacity: 0.7;
}

.ai-chat-title i.bi-robot {
    font-size: 24px;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-8px);
    }
    60% {
        transform: translateY(-4px);
    }
}

.ai-chat-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s;
}

.ai-chat-close:hover {
    background: rgba(255, 255, 255, 0.3);
}

/* Messages Area */
.ai-chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f8f9fa;
    max-height: 380px;
}

.ai-chat-messages::-webkit-scrollbar {
    width: 6px;
}

.ai-chat-messages::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.ai-chat-messages::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #e63946, #f72585);
    border-radius: 10px;
}

.ai-message {
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
    animation: slideInMessage 0.3s ease-out;
}

@keyframes slideInMessage {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.ai-message-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 20px;
}

.ai-bot-message .ai-message-avatar {
    background: linear-gradient(135deg, #e63946 0%, #f72585 100%);
    color: white;
}

.ai-user-message {
    flex-direction: row-reverse;
}

.ai-user-message .ai-message-avatar {
    background: #e9ecef;
    color: #495057;
}

.ai-message-content {
    flex: 1;
    padding: 12px 16px;
    border-radius: 14px;
    line-height: 1.6;
    font-size: 14px;
}

.ai-bot-message .ai-message-content {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 14px 14px 14px 4px;
}

.ai-user-message .ai-message-content {
    background: linear-gradient(135deg, #e63946 0%, #f72585 100%);
    color: white;
    border-radius: 14px 14px 4px 14px;
}

.ai-message-content ul {
    margin: 8px 0;
    padding-left: 20px;
}

.ai-message-content li {
    margin: 6px 0;
}

.ai-message-content p {
    margin: 6px 0;
}

/* Typing Indicator */
.ai-typing {
    display: flex;
    gap: 5px;
    padding: 8px 0;
}

.ai-typing span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #6c757d;
    animation: typing 1.4s infinite;
}

.ai-typing span:nth-child(2) {
    animation-delay: 0.2s;
}

.ai-typing span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing {
    0%, 60%, 100% {
        transform: translateY(0);
    }
    30% {
        transform: translateY(-10px);
    }
}

/* Product Cards */
.ai-chat-products {
    padding: 20px;
    background: #f1f1f1;
    border-top: 1px solid #e9ecef;
    max-height: 500px;
    overflow-x: auto;
}

.ai-chat-products::-webkit-scrollbar {
    height: 6px;
}

.ai-chat-products::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.ai-chat-products::-webkit-scrollbar-thumb {
    background: #e63946;
    border-radius: 10px;
}

.ai-product-carousel {
    display: flex;
    gap: 15px;
}

.ai-product-card {
    min-width: 190px;
    max-width: 190px;
    border: 1px solid #e9ecef;
    border-radius: 15px;
    overflow: hidden;
    background: white;
    transition: all 0.3s ease;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.ai-product-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 12px 35px rgba(230, 57, 70, 0.25);
    border-color: #e63946;
}

.ai-product-image {
    width: 100%;
    height: 100px;
    object-fit: cover;
    background: #f8f9fa;
    transition: transform 0.3s ease;
}

.ai-product-card:hover .ai-product-image {
    transform: scale(1.05);
}

.ai-product-info {
    padding: 18px;
}

.ai-product-name {
    font-size: 15px;
    font-weight: 600;
    color: #333;
    margin-bottom: 12px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.ai-product-price {
    font-size: 16px;
    font-weight: 700;
    color: #e63946;
    margin-bottom: 8px;
}

/* Input Area */
.ai-chat-input-wrapper {
    padding: 14px;
    background: white;
    border-top: 1px solid #e9ecef;
    display: flex;
    gap: 10px;
    
}
/* .ai-chat-input-area{
    max-height: 80px;
} */
.ai-chat-input {
    flex: 1;
    padding: 10px 16px;
    border: 2px solid #e9ecef;
    border-radius: 20px;
    outline: none;
    font-size: 14px;
    transition: all 0.3s;
}

.ai-chat-input:focus {
    border-color: #e63946;
    box-shadow: 0 0 0 3px rgba(230, 57, 70, 0.1);
}

.ai-chat-send {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e63946 0%, #f72585 100%);
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: all 0.3s;
}

.ai-chat-send:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(230, 57, 70, 0.4);
}

.ai-chat-send:active {
    transform: scale(0.95);
}

.ai-chat-send:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Link styling */
.ai-message-content a {
    color: #e63946 !important;
    text-decoration: underline;
    font-weight: 600;
}

.ai-message-content a:hover {
    color: #f72585 !important;
}

/* Clear button */
.ai-chat-clear {
    background: rgba(255, 255, 255, 0.2) !important;
    border: none !important;
    color: white !important;
    width: 32px !important;
    height: 32px !important;
    border-radius: 50% !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    margin-right: 8px !important;
    transition: background 0.3s !important;
    font-size: 14px !important;
}

.ai-chat-clear:hover {
    background: rgba(255, 255, 255, 0.3) !important;
}

/* Responsive */
@media (max-width: 768px) {
    .ai-chat-container {
        width: calc(100vw - 40px);
        max-height: calc(100vh - 120px);
    }
    
    .ai-chat-floating-wrapper {
        bottom: 70px;
        right: 20px;
    }
    
    .ai-chat-floating-btn {
        width: 50px;
        height: 50px;
    }
    
    .ai-chat-floating-btn i {
        font-size: 24px;
    }
    
    .ai-chat-greeting {
        font-size: 12px;
        padding: 10px 15px;
    }
    
    .ai-chat-modal {
        bottom: 140px;
        right: 20px;
    }
}
</style>

<!-- ✅ JAVASCRIPT CHO CHATBOT - LOAD CUỐI -->
<script>
// ✅ TRUYỀN BASE PATH VÀO JAVASCRIPT
window.CHATBOT_BASE_PATH = '<?php echo $basePath; ?>';
console.log('🔍 Chatbot Base Path from PHP:', '<?php echo $basePath; ?>');

// ✅ THÊM FLAG ĐỂ BIẾT CHATBOT ĐÃ READY
window.CHATBOT_READY = false;
</script>

<!-- ✅ TẢI JS FILE VỚI CALLBACK VÀ ERROR HANDLING -->
<script>
(function() {
    const basePath = window.CHATBOT_BASE_PATH || './';
    const scriptSrc = basePath + 'Page/home/assets/js/chatbot.js';
    
    console.log('🚀 Loading chatbot.js from:', scriptSrc);
    
    const script = document.createElement('script');
    script.src = scriptSrc;
    script.onload = function() {
        console.log('✅ Chatbot JS loaded successfully');
        window.CHATBOT_READY = true;
        
        // ✅ TRIGGER EVENT CHO PAGES KHÁC BIẾT CHATBOT ĐÃ READY
        if (typeof jQuery !== 'undefined') {
            $(document).trigger('chatbotReady');
            
            // ✅ THÊM TEST SYNC NGAY SAU KHI LOAD
            setTimeout(() => {
                console.log('🔍 Testing chat sync after chatbot load...');
                if (typeof window.loadGlobalChatHistory === 'function') {
                    console.log('✅ Chat history function available');
                } else {
                    console.log('⚠️ Chat history function not available');
                }
            }, 1000);
        }
    };
    script.onerror = function() {
        console.error('❌ Failed to load chatbot.js from:', scriptSrc);
        
        // ✅ FALLBACK: THỬ CÁC ĐƯỜNG DẪN KHÁC
        const fallbackPaths = [
            './Page/home/assets/js/chatbot.js',
            '../Page/home/assets/js/chatbot.js',
            '../../Page/home/assets/js/chatbot.js'
        ];
        
        let currentFallback = 0;
        
        function tryFallback() {
            if (currentFallback < fallbackPaths.length) {
                const fallbackScript = document.createElement('script');
                fallbackScript.src = fallbackPaths[currentFallback];
                fallbackScript.onload = function() {
                    console.log('✅ Chatbot loaded from fallback:', fallbackPaths[currentFallback]);
                    window.CHATBOT_READY = true;
                    if (typeof jQuery !== 'undefined') {
                        $(document).trigger('chatbotReady');
                    }
                };
                fallbackScript.onerror = function() {
                    currentFallback++;
                    tryFallback();
                };
                document.head.appendChild(fallbackScript);
            } else {
                console.error('❌ All chatbot fallback paths failed');
            }
        }
        
        tryFallback();
    };
    document.head.appendChild(script);
})();
</script>