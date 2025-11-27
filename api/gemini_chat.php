<?php
// filepath: c:\xampp\htdocs\CuoiKy_LTW\api\gemini_chat.php
header('Content-Type: application/json; charset=utf-8');

// Nhận message từ client
$input = file_get_contents('php://input');
$request = json_decode($input, true);

if (!$request) {
    parse_str($input, $request);
}

$message = isset($request['message']) ? trim($request['message']) : '';

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập tin nhắn']);
    exit;
}

// ✅ HÀM TƯ VẤN THÔNG MINH - MỚI
function handleSmartConsultation($message) {
    $messageLower = mb_strtolower($message);
    
    // ===== TƯ VẤN THEO ĐỐI TƯỢNG + DỊP =====
    
    // HỎI TƯ VẤN CHUNG
    $consultationKeywords = ['tư vấn', 'tu van', 'gợi ý', 'goi y', 'chọn giúp', 'chon giup', 'không biết', 'khong biet'];
    $isConsultation = false;
    foreach ($consultationKeywords as $keyword) {
        if (mb_strpos($messageLower, $keyword) !== false) {
            $isConsultation = true;
            break;
        }
    }
    
    if ($isConsultation) {
        // 1. TƯ VẤN HỌA TẶNG MẸ
        if (mb_strpos($messageLower, 'mẹ') !== false || mb_strpos($messageLower, 'me') !== false || mb_strpos($messageLower, 'mama') !== false) {
            if (mb_strpos($messageLower, 'sinh nhật') !== false) {
                return "👩‍👧 **Tư vấn hoa sinh nhật tặng mẹ:**\n\n" .
                       "🌸 **Loại hoa phù hợp:**\n" .
                       "• **Hoa hồng phấn/hồng nhạt:** Thể hiện tình yêu thương dịu dàng\n" .
                       "• **Hoa cẩm chướng:** Tượng trưng lòng biết ơn với mẹ\n" .
                       "• **Hoa baby trắng:** Sự trong sáng, tinh khiết\n" .
                       "• **Hoa hướng dương:** Niềm vui, sự lạc quan\n\n" .
                       "💰 **Gợi ý ngân sách:**\n" .
                       "• **300-500k:** Bó hoa vừa phải, ý nghĩa\n" .
                       "• **500-800k:** Bó hoa đẹp, sang trọng\n" .
                       "• **800k+:** Giỏ hoa hoặc bó hoa premium\n\n" .
                       "🎨 **Màu sắc nên chọn:** Hồng nhạt, trắng, vàng pastel\n" .
                       "🚫 **Tránh:** Màu đỏ quá nổi, màu tím đậm\n\n" .
                       "💡 Bạn có ngân sách khoảng bao nhiêu để tôi gợi ý cụ thể?";
            }
            
            return "👩‍👧 **Tư vấn hoa tặng mẹ:**\n\n" .
                   "🌺 **Những loại hoa mẹ yêu thích:**\n" .
                   "• **Hoa hồng phấn:** Tình yêu gia đình ấm áp\n" .
                   "• **Hoa baby:** Sự quan tâm, chăm sóc\n" .
                   "• **Hoa cẩm chướng:** Lòng biết ơn sâu sắc\n" .
                   "• **Hoa lan:** Sự thanh lịch, quý phái\n\n" .
                   "🎯 **Theo dịp:**\n" .
                   "• Sinh nhật mẹ: Hoa hồng + baby\n" .
                   "• Ngày của mẹ: Cẩm chướng hồng\n" .
                   "• Xin lỗi mẹ: Hoa hồng trắng\n" .
                   "• Cảm ơn mẹ: Hướng dương + baby\n\n" .
                   "💡 Dịp nào bạn muốn tặng mẹ?";
        }
        
        // 2. TƯ VẤN HỌA TẶNG NGƯỜI YÊU
        if (mb_strpos($messageLower, 'người yêu') !== false || mb_strpos($messageLower, 'bạn gái') !== false || 
            mb_strpos($messageLower, 'bạn trai') !== false || mb_strpos($messageLower, 'crush') !== false) {
            
            if (mb_strpos($messageLower, 'sinh nhật') !== false) {
                return "💕 **Tư vấn hoa sinh nhật tặng người yêu:**\n\n" .
                       "🌹 **Hoa hồng đỏ:** Tình yêu nồng nàn, đam mê\n" .
                       "🌸 **Hoa hồng hồng:** Lãng mạn, ngọt ngào\n" .
                       "🤍 **Hoa hồng trắng + hồng:** Tình yêu trong sáng\n" .
                       "🌻 **Hướng dương:** Tình yêu tươi mới, rạng rỡ\n\n" .
                       "📏 **Kích cỡ gợi ý:**\n" .
                       "• **Mới yêu (1-6 tháng):** 5-10 bông hồng\n" .
                       "• **Yêu lâu (6 tháng+):** 15-25 bông\n" .
                       "• **Tỏ tình:** 99 bông hồng đỏ\n" .
                       "• **Xin lỗi:** 21 bông hồng trắng\n\n" .
                       "💝 **Combo đặc biệt:** Hoa + socola + thiệp\n" .
                       "💰 **Ngân sách:** 400k-1.2tr tùy số lượng\n\n" .
                       "💡 Bạn yêu nhau được bao lâu rồi?";
            }
            
            return "💕 **Tư vấn hoa tặng người yêu:**\n\n" .
                   "🌹 **Theo giai đoạn tình yêu:**\n" .
                   "• **Mới quen:** Baby trắng, hoa nhỏ xinh\n" .
                   "• **Đang yêu:** Hoa hồng hồng, mix baby\n" .
                   "• **Yêu sâu đậm:** Hoa hồng đỏ sang trọng\n" .
                   "• **Sắp cưới:** Hoa hồng trắng + hồng\n\n" .
                   "🎯 **Theo tính cách:**\n" .
                   "• **Cô gái dễ thương:** Baby, hồng pastel\n" .
                   "• **Cô gái cá tính:** Hồng đỏ, tulip\n" .
                   "• **Cô gái thanh lịch:** Hồng trắng, lan\n\n" .
                   "💡 Người yêu bạn thích phong cách nào?";
        }
        
        // 3. TƯ VẤN HỌA TẶNG BẠN
        if (mb_strpos($messageLower, 'bạn') !== false && mb_strpos($messageLower, 'bạn gái') === false && mb_strpos($messageLower, 'bạn trai') === false) {
            return "👫 **Tư vấn hoa tặng bạn:**\n\n" .
                   "🌼 **Loại hoa thân thiện:**\n" .
                   "• **Hướng dương:** Tình bạn tươi sáng\n" .
                   "• **Baby trắng/vàng:** Tình cảm trong sáng\n" .
                   "• **Cúc họa mi:** Sự chân thành\n" .
                   "• **Thạch thảo:** Tình bạn bền vững\n\n" .
                   "🎨 **Màu sắc phù hợp:**\n" .
                   "• **Vàng/cam:** Vui tươi, năng động\n" .
                   "• **Trắng:** Trong sáng, chân thành\n" .
                   "• **Tím nhạt:** Ngọt ngào, thân thiện\n" .
                   "🚫 **Tránh đỏ đậm** (dễ hiểu nhầm tình cảm)\n\n" .
                   "💰 **Ngân sách hợp lý:** 200k-500k\n" .
                   "💡 Dịp gì bạn tặng bạn?";
        }
        
        // 4. TƯ VẤN HỌA KHAI TRƯƠNG
        if (mb_strpos($messageLower, 'khai trương') !== false || mb_strpos($messageLower, 'khai truong') !== false) {
            return "🎊 **Tư vấn hoa khai trương:**\n\n" .
                   "🌻 **Loại hoa may mắn:**\n" .
                   "• **Hướng dương:** Thành công, phát đạt\n" .
                   "• **Lan vàng:** Sang trọng, thịnh vượng\n" .
                   "• **Hoa hồng vàng:** Tài lộc, may mắn\n" .
                   "• **Cúc vàng:** Phú quý, thành đạt\n\n" .
                   "🎨 **Màu sắc vàng/đỏ:** Tượng trưng may mắn\n" .
                   "📏 **Kích cỡ:**\n" .
                   "• **Để bàn:** 500k-800k\n" .
                   "• **Kệ hoa lớn:** 1-3tr\n" .
                   "• **Vòng hoa:** 2-5tr\n\n" .
                   "💡 **Lưu ý:** Tránh màu trắng (tang lễ)\n" .
                   "🎁 **Kèm băng rôn chúc mừng**\n\n" .
                   "💡 Bạn tặng cho loại hình kinh doanh gì?";
        }
        
        // 5. TƯ VẤN HỌA CƯỚI
        if (mb_strpos($messageLower, 'cưới') !== false || mb_strpos($messageLower, 'cầm tay') !== false || mb_strpos($messageLower, 'cô dâu') !== false) {
            return "👰 **Tư vấn hoa cưới:**\n\n" .
                   "💒 **Hoa cầm tay cô dâu:**\n" .
                   "• **Hoa hồng trắng:** Tinh khôi, trong sáng\n" .
                   "• **Baby trắng:** Sự ngây thơ, trong trẻo\n" .
                   "• **Hoa sen:** Sự thanh tao, cao quý\n" .
                   "• **Hoa mẫu đơn:** Thịnh vượng, hạnh phúc\n\n" .
                   "🎨 **Phối màu cưới:**\n" .
                   "• **Trắng chủ đạo + hồng nhạt**\n" .
                   "• **Trắng + vàng gold (sang trọng)**\n" .
                   "• **Trắng + xanh mint (hiện đại)**\n\n" .
                   "💝 **Set hoa cưới đầy đủ:**\n" .
                   "• Hoa cầm tay: 300-800k\n" .
                   "• Hoa cài áo: 50-100k\n" .
                   "• Hoa để bàn: 200-500k/bàn\n\n" .
                   "💡 Đám cưới phong cách nào? (Truyền thống/hiện đại/vintage?)";
        }
        
        // 6. TƯ VẤN HỎA XIN LỖI
        if (mb_strpos($messageLower, 'xin lỗi') !== false || mb_strpos($messageLower, 'lỗi') !== false || mb_strpos($messageLower, 'sorry') !== false) {
            return "🙏 **Tư vấn hoa xin lỗi:**\n\n" .
                   "🤍 **Hoa hồng trắng:** Sự tha thứ, trong sáng\n" .
                   "🌸 **Hoa hồng hồng nhạt:** Xin lỗi dịu dàng\n" .
                   "💜 **Hoa baby tím:** Sự hối hận chân thành\n" .
                   "🌺 **Hoa cẩm tú cầu:** Lời xin lỗi từ đáy lòng\n\n" .
                   "📝 **Lời nhắn nên viết:**\n" .
                   "• 'Anh/em xin lỗi vì...'\n" .
                   "• 'Mong bạn tha thứ cho...'\n" .
                   "• 'Anh/em sẽ không tái phạm'\n\n" .
                   "🚫 **Tránh:** Hoa đỏ (không phù hợp)\n" .
                   "💰 **Ngân sách:** 300-600k\n" .
                   "🎁 **Kèm theo:** Thiệp viết tay + kẹo\n\n" .
                   "💡 Bạn xin lỗi ai? (Người yêu/bạn/đồng nghiệp?)";
        }
        
        // 7. TƯ VẤN THEO NGÂN SÁCH
        if (preg_match('/(\d+)k|(\d+)\.(\d+)tr|(\d+)tr/i', $message, $matches)) {
            $budget = 0;
            if (isset($matches[1]) && !empty($matches[1])) {
                $budget = (int)$matches[1] * 1000;
            } elseif (isset($matches[4]) && !empty($matches[4])) {
                $budget = (int)$matches[4] * 1000000;
            }
            
            if ($budget > 0) {
                if ($budget <= 300000) {
                    return "💰 **Tư vấn hoa ngân sách " . number_format($budget) . "đ:**\n\n" .
                           "🌸 **Gợi ý phù hợp:**\n" .
                           "• **Bó baby nhỏ:** Tinh tế, ý nghĩa\n" .
                           "• **5-7 bông hồng:** Đơn giản mà đẹp\n" .
                           "• **Hoa thạch thảo:** Bền, lâu tàn\n" .
                           "• **Mix hướng dương + baby:** Vui tươi\n\n" .
                           "✨ **Mẹo tiết kiệm:** Chọn hoa bền như baby, thạch thảo\n" .
                           "🎁 **Tặng kèm:** Thiệp handmade, kẹo\n\n" .
                           "💡 Bạn tặng cho ai trong dịp gì?";
                } elseif ($budget <= 600000) {
                    return "💰 **Tư vấn hoa ngân sách " . number_format($budget) . "đ:**\n\n" .
                           "🌹 **Lựa chọn đa dạng:**\n" .
                           "• **10-15 bông hồng:** Đẹp, ý nghĩa\n" .
                           "• **Bó hoa mix 3-4 loại:** Phong phú\n" .
                           "• **Giỏ hoa nhỏ:** Sang trọng hơn\n" .
                           "• **Hoa baby size M:** Trắng tinh khôi\n\n" .
                           "🎨 **Có thể phối màu:** 2-3 màu hài hòa\n" .
                           "📦 **Đóng gói đẹp:** Giấy gói, nơ cao cấp\n\n" .
                           "💡 Bạn muốn bó hoa hay giỏ hoa?";
                } else {
                    return "💰 **Tư vấn hoa ngân sách " . number_format($budget) . "đ:**\n\n" .
                           "👑 **Chọn lựa cao cấp:**\n" .
                           "• **20+ bông hồng premium:** Sang trọng\n" .
                           "• **Giỏ hoa lớn mix đa dạng:** Ấn tượng\n" .
                           "• **Hoa nhập khẩu:** Holland, Ecuador\n" .
                           "• **Kệ hoa khai trương:** Hoành tráng\n\n" .
                           "✨ **Dịch vụ VIP:**\n" .
                           "• Thiết kế theo yêu cầu\n" .
                           "• Giao hàng VIP\n" .
                           "• Thiệp cao cấp\n" .
                           "• Đóng gói luxury\n\n" .
                           "💡 Dịp đặc biệt gì mà bạn đầu tư nhiều thế?";
                }
            }
        }
        
        // 8. TƯ VẤN CHUNG - NẾU KHÔNG MATCH TRƯỜNG HỢP NÀO
        return "🎯 **Để tư vấn chính xác nhất, cho tôi biết:**\n\n" .
               "👥 **Tặng cho ai?**\n• Mẹ, người yêu, bạn, đồng nghiệp, sếp...\n\n" .
               "🎉 **Dịp gì?**\n• Sinh nhật, xin lỗi, cảm ơn, khai trương, cưới...\n\n" .
               "💰 **Ngân sách?**\n• 200k, 500k, 1tr... (để gợi ý phù hợp)\n\n" .
               "🎨 **Sở thích màu sắc?**\n• Đỏ, hồng, trắng, vàng, tím...\n\n" .
               "💡 **Ví dụ hay:** 'Tôi muốn tặng mẹ hoa sinh nhật, màu hồng nhạt, khoảng 500k'\n\n" .
               "Hãy nói chi tiết để tôi tư vấn tận tâm nhất! 😊";
    }
    
    return null;
}

// ✅ HÀM PHẢN HỒI CHAT THÔNG MINH - ĐƯỢC CẬP NHẬT
function handleGeneralChat($message) {
    $messageLower = mb_strtolower($message);
    
    // ✅ KIỂM TRA TƯ VẤN THÔNG MINH TRƯỚC
    $smartResponse = handleSmartConsultation($message);
    if ($smartResponse) {
        return $smartResponse;
    }
    
    // ===== 1. CHÀO HỎI CƠ BẢN =====
    $greetings = ['xin chào', 'chào', 'hello', 'hi', 'hey', 'chào bạn', 'chào shop'];
    foreach ($greetings as $greeting) {
        if (mb_strpos($messageLower, $greeting) !== false) {
            return "👋 Xin chào! Tôi là trợ lý AI của shop hoa. Tôi có thể giúp bạn:\n\n" .
                   "🌹 Tư vấn chọn hoa theo dịp (sinh nhật, khai trương, cưới...)\n" .
                   "💐 Tìm sản phẩm theo giá và sở thích\n" .
                   "💰 Gợi ý ngân sách phù hợp\n" .
                   "📦 Thông tin giao hàng và thanh toán\n\n" .
                   "Hãy nói cho tôi biết bạn cần gì nhé! VD: 'Tôi muốn mua hoa sinh nhật 500k'";
        }
    }
    
    // ===== 2. CẢM ơN =====
    $thanks = ['cảm ơn', 'cám ơn', 'thank', 'thanks', 'tks'];
    foreach ($thanks as $thank) {
        if (mb_strpos($messageLower, $thank) !== false) {
            return "😊 Không có gì! Tôi rất vui được giúp đỡ bạn. Còn gì khác tôi có thể hỗ trợ không?\n\n" .
                   "💡 Bạn có thể hỏi thêm về:\n" .
                   "• Giao hàng và thanh toán\n" .
                   "• Tư vấn hoa cho dịp khác\n" .
                   "• Thông tin liên hệ shop";
        }
    }
    
    // ===== 3. HỎI VỀ BOT =====
    $botQuestions = ['bạn là ai', 'bot là gì', 'bạn có thể', 'giúp gì', 'làm gì được', 'ai tạo ra bạn'];
    foreach ($botQuestions as $question) {
        if (mb_strpos($messageLower, $question) !== false) {
            return "🤖 Tôi là trợ lý AI thông minh chuyên về hoa tươi! Tôi có thể:\n\n" .
                   "✅ Tư vấn hoa theo dịp: sinh nhật, khai trương, cưới hỏi, tang lễ\n" .
                   "✅ Gợi ý sản phẩm theo giá: 200k, 500k, 1tr...\n" .
                   "✅ Giải đáp về giao hàng, thanh toán\n" .
                   "✅ Tư vấn ý nghĩa các loại hoa\n\n" .
                   "🎯 **Thử hỏi tôi:**\n" .
                   "• 'Tư vấn hoa tặng mẹ sinh nhật 500k'\n" .
                   "• 'Hoa hồng đỏ có ý nghĩa gì?'\n" .
                   "• 'Shop giao hàng trong bao lâu?'";
        }
    }
    
    // ===== 4. HỎI VỀ GIÁ CẢ =====
    if (mb_strpos($messageLower, 'giá') !== false || mb_strpos($messageLower, 'bao nhiêu') !== false) {
        if (mb_strpos($messageLower, 'phí ship') !== false || mb_strpos($messageLower, 'ship') !== false) {
            return "🚚 **Thông tin giao hàng:**\n\n" .
                   "📍 **Nội thành:** Miễn phí ship (đơn > 300k)\n" .
                   "📍 **Ngoại thành:** 20k-50k tùy khoảng cách\n" .
                   "⚡ **Giao gấp 1-2h:** +30k phí ưu tiên\n" .
                   "🌙 **Giao đêm (19h-22h):** +20k\n\n" .
                   "⏰ **Thời gian giao:** 2-4h bình thường\n" .
                   "📞 **Đặt hàng:** 0123.456.789\n\n" .
                   "💡 Bạn cần giao ở khu vực nào để tôi báo giá chính xác?";
        } else {
            return "💰 **Bảng giá tham khảo:**\n\n" .
                   "🌹 **Bó hoa nhỏ:** 150k - 300k\n" .
                   "💐 **Bó hoa vừa:** 300k - 600k\n" .
                   "🎀 **Bó hoa lớn:** 600k - 1.2tr\n" .
                   "👑 **Bó hoa premium:** 1.2tr - 3tr\n\n" .
                   "🏢 **Kệ hoa khai trương:** 800k - 2tr\n" .
                   "💒 **Hoa cưới:** 500k - 1.5tr\n\n" .
                   "💡 Bạn có ngân sách khoảng bao nhiêu để tôi tư vấn phù hợp?";
        }
    }
    
    // ===== 5. HỎI VỀ GIAO HÀNG =====
    $shippingQuestions = ['ship', 'giao hàng', 'giao tận nơi', 'delivery', 'giao trong', 'bao lâu giao'];
    foreach ($shippingQuestions as $ship) {
        if (mb_strpos($messageLower, $ship) !== false) {
            return "🚚 **Dịch vụ giao hàng:**\n\n" .
                   "⚡ **Giao hàng nhanh:** 1-2 giờ (+30k)\n" .
                   "🕐 **Giao hàng thường:** 2-4 giờ\n" .
                   "🌙 **Giao buổi tối:** 19h-22h (+20k)\n" .
                   "📅 **Đặt trước:** Giao đúng giờ hẹn\n\n" .
                   "📍 **Khu vực giao:**\n" .
                   "• Nội thành: Miễn phí (đơn >300k)\n" .
                   "• Ngoại thành: 20k-50k\n" .
                   "• Tỉnh xa: 50k-100k\n\n" .
                   "📞 **Đặt hàng:** 0123.456.789\n" .
                   "💡 Bạn cần giao ở đâu và khi nào?";
        }
    }
    
    // ===== 6. HỎI VỀ THANH TOÁN =====
    $paymentQuestions = ['thanh toán', 'trả tiền', 'chuyển khoản', 'cod', 'tiền mặt', 'atm'];
    foreach ($paymentQuestions as $payment) {
        if (mb_strpos($messageLower, $payment) !== false) {
            return "💳 **Hình thức thanh toán:**\n\n" .
                   "💵 **COD:** Thanh toán khi nhận hàng\n" .
                   "🏦 **Chuyển khoản:** Vietcombank, Techcombank\n" .
                   "📱 **Ví điện tử:** MoMo, ZaloPay\n" .
                   "💸 **Tiền mặt:** Tại shop\n\n" .
                   "🧾 **Xuất hóa đơn:** VAT theo yêu cầu\n" .
                   "💰 **Đặt cọc:** 50% với đơn >2tr\n\n" .
                   "📞 **Đặt hàng:** 0123.456.789\n" .
                   "💡 Bạn muốn thanh toán bằng hình thức nào?";
        }
    }
    
    // ===== 7. HỎI VỀ THỜI GIAN LÀM VIỆC =====
    $timeQuestions = ['mấy giờ', 'mở cửa', 'đóng cửa', 'làm việc', 'hoạt động', 'có làm không'];
    foreach ($timeQuestions as $time) {
        if (mb_strpos($messageLower, $time) !== false) {
            return "🕐 **Giờ làm việc:**\n\n" .
                   "📅 **Thứ 2 - Chủ nhật:** 7:00 - 22:00\n" .
                   "🎊 **Lễ Tết:** 8:00 - 20:00\n" .
                   "🌙 **Nhận đơn đêm:** Qua hotline\n\n" .
                   "📍 **Địa chỉ:** 123 Đường ABC, Quận 1, TP.HCM\n" .
                   "📞 **Hotline:** 0123.456.789\n" .
                   "💬 **Zalo:** 0123.456.789\n\n" .
                   "💡 Bạn muốn đặt hàng vào thời gian nào?";
        }
    }
    
    // ===== 8. HỎI VỀ Ý NGHĨA HOA =====
    if (mb_strpos($messageLower, 'ý nghĩa') !== false || mb_strpos($messageLower, 'có nghĩa') !== false) {
        return "🌸 **Ý nghĩa các loại hoa:**\n\n" .
               "🌹 **Hoa hồng đỏ:** Tình yêu nồng nàn\n" .
               "🤍 **Hoa hồng trắng:** Tình yêu trong sáng\n" .
               "💗 **Hoa hồng hồng:** Lãng mạn, ngọt ngào\n" .
               "💛 **Hoa hướng dương:** Lạc quan, may mắn\n" .
               "🤍 **Hoa baby:** Tinh khôi, trong trẻo\n" .
               "💜 **Hoa cẩm tú cầu:** Lòng biết ơn\n\n" .
               "🎯 **Theo dịp:**\n" .
               "• Sinh nhật: Hoa hồng, hướng dương\n" .
               "• Tình yêu: Hoa hồng đỏ, hồng\n" .
               "• Xin lỗi: Hoa hồng trắng\n" .
               "• Chúc mừng: Hoa baby, cẩm chướng\n\n" .
               "💡 Bạn tặng cho ai và trong dịp gì?";
    }
    
    // ===== 9. TƯ VẤN TỔNG QUÁT =====
    if (mb_strpos($messageLower, 'tư vấn') !== false || mb_strpos($messageLower, 'không biết chọn') !== false || 
        mb_strpos($messageLower, 'chọn giúp') !== false || mb_strpos($messageLower, 'gợi ý') !== false) {
        return "🎯 **Để tư vấn chính xác, cho tôi biết:**\n\n" .
               "👥 **Tặng cho ai?** (Người yêu, mẹ, sếp, bạn...)\n" .
               "🎉 **Dịp gì?** (Sinh nhật, xin lỗi, cảm ơn, khai trương...)\n" .
               "💰 **Ngân sách?** (300k, 500k, 1tr...)\n" .
               "🎨 **Màu yêu thích?** (Đỏ, hồng, trắng, vàng...)\n" .
               "📏 **Size?** (Nhỏ gọn, vừa phải, to đẹp)\n\n" .
               "💡 **Ví dụ:** 'Tôi muốn tặng mẹ hoa sinh nhật, màu hồng nhạt, khoảng 500k'\n\n" .
               "Hãy cho tôi thêm thông tin để tư vấn tốt nhất! 😊";
    }
    
    // ===== 10. HỎI VỀ ĐỘ BỀN HOA =====
    $durabilityQuestions = ['bao lâu', 'mấy ngày', 'có bị héo', 'giữ được', 'tươi được', 'tàn nhanh'];
    foreach ($durabilityQuestions as $duration) {
        if (mb_strpos($messageLower, $duration) !== false && 
            (mb_strpos($messageLower, 'hoa') !== false || mb_strpos($messageLower, 'tươi') !== false)) {
            return "🌺 **Độ bền hoa tươi:**\n\n" .
                   "🌹 **Hoa hồng:** 5-7 ngày\n" .
                   "🌻 **Hoa hướng dương:** 4-6 ngày\n" .
                   "🤍 **Hoa baby:** 7-10 ngày\n" .
                   "💜 **Hoa cẩm chướng:** 7-12 ngày\n" .
                   "🌸 **Hoa cúc:** 10-14 ngày\n\n" .
                   "💡 **Cách giữ hoa tươi lâu:**\n" .
                   "• Cắt chéo cuống hoa dưới nước\n" .
                   "• Thay nước 2 ngày/lần\n" .
                   "• Để nơi mát mẻ, tránh nắng\n" .
                   "• Bỏ lá héo, hoa tàn\n\n" .
                   "✨ Shop cam kết hoa tươi 100% khi giao!";
        }
    }
    
    // ===== 11. THÔNG TIN LIÊN HỆ =====
    $contactQuestions = ['địa chỉ', 'ở đâu', 'sdt', 'số điện thoại', 'liên hệ', 'fanpage', 'facebook'];
    foreach ($contactQuestions as $contact) {
        if (mb_strpos($messageLower, $contact) !== false) {
            return "📞 **Thông tin liên hệ:**\n\n" .
                   "🏪 **Shop hoa Fresh Flower**\n" .
                   "📍 **Địa chỉ:** 123 Đường ABC, P.Bến Nghé, Q.1, TP.HCM\n" .
                   "☎️ **Hotline:** 0123.456.789\n" .
                   "💬 **Zalo:** 0123.456.789\n" .
                   "📘 **Facebook:** fb.com/freshflowershop\n" .
                   "📧 **Email:** info@freshflower.vn\n\n" .
                   "🕐 **Giờ làm việc:** 7:00 - 22:00 hàng ngày\n" .
                   "🚚 **Giao hàng:** Toàn TP.HCM và các tỉnh lân cận\n\n" .
                   "💡 Bạn có thể đặt hàng qua bất kỳ kênh nào ở trên!";
        }
    }
    
    // ===== 12. ĐẶT HÀNG THEO YÊU CẦU =====
    $customQuestions = ['làm theo yêu cầu', 'custom', 'thiết kế riêng', 'theo ý', 'như hình', 'giống mẫu'];
    foreach ($customQuestions as $custom) {
        if (mb_strpos($messageLower, $custom) !== false) {
            return "🎨 **Dịch vụ thiết kế theo yêu cầu:**\n\n" .
                   "✅ **Làm theo hình mẫu:** 90-95% giống\n" .
                   "✅ **Thay đổi màu sắc:** Theo sở thích\n" .
                   "✅ **Điều chỉnh size:** To hơn/nhỏ hơn\n" .
                   "✅ **Thêm/bớt loại hoa:** Linh hoạt\n" .
                   "✅ **Gắn thiệp viết tay:** Miễn phí\n\n" .
                   "⏰ **Thời gian:** 2-4h (bình thường), 1h (gấp +50k)\n" .
                   "💰 **Phụ thu:** 0-100k tùy độ phức tạp\n\n" .
                   "📞 **Đặt hàng:** 0123.456.789\n" .
                   "💡 Gửi hình mẫu qua Zalo để báo giá chính xác!";
        }
    }
    
    // ===== 13. KHUYẾN MÃI =====
    $promoQuestions = ['giảm giá', 'khuyến mãi', 'sale', 'ưu đãi', 'voucher', 'discount'];
    foreach ($promoQuestions as $promo) {
        if (mb_strpos($messageLower, $promo) !== false) {
            return "🎉 **Ưu đãi đang có:**\n\n" .
                   "🆓 **Miễn phí ship:** Đơn hàng từ 300k\n" .
                   "💐 **Giảm 10%:** Đơn từ 1tr (tối đa 200k)\n" .
                   "🎁 **Tặng thiệp + kẹo:** Mọi đơn hàng\n" .
                   "⚡ **Ưu đãi sinh nhật:** Giảm 15% (có CMND)\n" .
                   "👥 **Khách cũ:** Tích điểm đổi quà\n\n" .
                   "🔥 **Flash sale cuối tuần:**\n" .
                   "• Thứ 7: Giảm 20% bó hoa hồng\n" .
                   "• Chủ nhật: Buy 2 get 1 hoa baby\n\n" .
                   "📞 **Đặt hàng:** 0123.456.789\n" .
                   "💡 Nhắn 'UUDAI' để nhận thêm voucher!";
        }
    }
    
    return null; // Không match câu nào thì return null để tìm sản phẩm
}

// ===== HÀM TÌM SẢN PHẨM - GIỮ NGUYÊN =====
function findRelatedProductsFromJson($message) {
    $messageLower = mb_strtolower($message);
    
    // ĐỌC DỮ LIỆU TỪ JSON FILE
    $dataFile = __DIR__ . '/../aidata/data.json';
    $products = [];
    
    if (file_exists($dataFile)) {
        $jsonData = json_decode(file_get_contents($dataFile), true);
        if ($jsonData && is_array($jsonData)) {
            $products = $jsonData;
        }
    }
    
    if (empty($products)) {
        error_log("Không tìm thấy sản phẩm trong JSON hoặc file không tồn tại: " . $dataFile);
        return [];
    }
    
    $results = [];
    
    foreach ($products as $product) {
        $score = 0;
        
        // ✅ SỬA ĐƯỜNG DẪN ẢNH 
        if (isset($product['image_url']) && !empty($product['image_url'])) {
            if (strpos($product['image_url'], '../uploads/') === 0) {
                $product['image_url'] = './uploads/' . substr($product['image_url'], 11);
            } elseif (strpos($product['image_url'], './uploads/') !== 0 && strpos($product['image_url'], '/uploads/') !== 0) {
                $product['image_url'] = './uploads/' . basename($product['image_url']);
            }
        } else {
            $product['image_url'] = './img/web/hoahong/default.jpg';
        }
        
        // ✅ FIX CHUẨN HÓA GIÁ - KHÔNG THAY ĐỔI GIÁ GỐC
        if (isset($product['price']) && !empty($product['price'])) {
            // Nếu là chuỗi thì chuyển sang số
            if (is_string($product['price'])) {
                // Loại bỏ ký tự không phải số, giữ dấu chấm
                $cleanPrice = preg_replace('/[^\d.]/', '', $product['price']);
                if (!empty($cleanPrice) && is_numeric($cleanPrice)) {
                    $product['price'] = (float)$cleanPrice;
                }
            }
            
            // Đảm bảo price là số và > 0
            $product['price'] = (float)$product['price'];
            
            // ✅ CHỈ SET GIÁ MẶC ĐỊNH KHI THỰC SỰ = 0 HOẶC INVALID
            if ($product['price'] <= 0 || !is_numeric($product['price'])) {
                $product['price'] = 500000; // ✅ TĂNG GIÁ MẶC ĐỊNH LÊN 500K
            }
        } else {
            // ✅ GIÁ MẶC ĐỊNH CHO SẢN PHẨM KHÔNG CÓ GIÁ
            $product['price'] = 500000; 
        }
        
        // ✅ CHUẨN HÓA TÊN SẢN PHẨM
        if (!isset($product['name']) || empty($product['name'])) {
            $product['name'] = $product['description'] ?? 'Sản phẩm hoa';
        }
        
        // CHUẨN HÓA DỮ LIỆU TÌM KIẾM
        $productName = mb_strtolower($product['name'] ?? '');
        $productDescription = mb_strtolower($product['description'] ?? '');
        $productCategory = mb_strtolower($product['category'] ?? '');
        $productSubcategory = mb_strtolower($product['subcategory'] ?? '');
        
        // THUẬT TOÁN TÌM KIẾM
        
        // 1. TÌM THEO TÊN SẢN PHẨM (ưu tiên cao nhất)
        if (!empty($productName)) {
            if (mb_strpos($productName, $messageLower) !== false) {
                $score += 10;
            }
            // Tìm từng từ trong tên
            $messageWords = explode(' ', $messageLower);
            foreach ($messageWords as $word) {
                if (strlen($word) > 2 && mb_strpos($productName, $word) !== false) {
                    $score += 3;
                }
            }
        }
        
        // 2. TÌM THEO MÔ TẢ
        if (!empty($productDescription)) {
            if (mb_strpos($productDescription, $messageLower) !== false) {
                $score += 8;
            }
            // Tìm từng từ trong mô tả
            $messageWords = explode(' ', $messageLower);
            foreach ($messageWords as $word) {
                if (strlen($word) > 2 && mb_strpos($productDescription, $word) !== false) {
                    $score += 2;
                }
            }
        }
        
        // 3. TÌM THEO CATEGORY
        $categoryMatches = [
            // Hoa sinh nhật
            ['keywords' => ['sinh nhật', 'sinh nhat', 'birthday'], 'category' => 'hoa_sinh_nhat', 'score' => 7],
            
            // Khai trương  
            ['keywords' => ['khai trương', 'khai truong', 'opening', 'chúc mừng khai trương'], 'category' => 'hoa_khai_truong', 'score' => 7],
            
            // Chủ đề
            ['keywords' => ['tang lễ', 'tang le', 'chia buồn', 'chia buon', 'funeral', 'tang'], 'category' => 'chu_de', 'subcategory' => 'Hoa Tang Lễ', 'score' => 8],
            ['keywords' => ['cưới', 'cuoi', 'cầm tay', 'cam tay', 'cô dâu', 'co dau', 'wedding'], 'category' => 'chu_de', 'subcategory' => 'Hoa Cầm Tay', 'score' => 8],
            ['keywords' => ['chúc mừng', 'chuc mung', 'congratulation'], 'category' => 'chu_de', 'subcategory' => 'Hoa Chúc Mừng', 'score' => 7],
            
            // Thiết kế
            ['keywords' => ['thiết kế', 'thiet ke', 'bó hoa', 'bo hoa', 'giỏ hoa', 'gio hoa'], 'category' => 'thiet_ke', 'score' => 6],
            
            // Hoa tươi
            ['keywords' => ['hoa tươi', 'hoa tuoi', 'fresh flower'], 'category' => 'hoa_tuoi', 'score' => 6]
        ];
        
        foreach ($categoryMatches as $match) {
            foreach ($match['keywords'] as $keyword) {
                if (mb_strpos($messageLower, $keyword) !== false) {
                    // Kiểm tra category
                    if ($productCategory === $match['category']) {
                        $score += $match['score'];
                        
                        // Kiểm tra subcategory nếu có
                        if (isset($match['subcategory']) && mb_strtolower($productSubcategory) === mb_strtolower($match['subcategory'])) {
                            $score += 3; // Bonus cho đúng subcategory
                        }
                    }
                }
            }
        }
        
        // 4. TÌM THEO SUBCATEGORY
        $subcategoryMatches = [
            // Hoa sinh nhật
            ['keywords' => ['sang trọng', 'sang trong', 'luxury', 'premium'], 'subcategory' => 'Sang Trọng', 'score' => 5],
            ['keywords' => ['người yêu', 'nguoi yeu', 'tặng người yêu', 'tang nguoi yeu', 'lover'], 'subcategory' => 'Tặng Người Yêu', 'score' => 5],
            
            // Khai trương
            ['keywords' => ['để bàn', 'de ban', 'desktop'], 'subcategory' => 'Để Bàn', 'score' => 5],
            ['keywords' => ['kệ hoa', 'ke hoa', 'flower stand'], 'subcategory' => 'Kệ Hoa', 'score' => 5],
            
            // Hoa tươi
            ['keywords' => ['hoa hồng', 'hoa hong', 'hồng', 'hong', 'rose'], 'subcategory' => 'Hoa Hồng', 'score' => 5],
            ['keywords' => ['baby', 'hoa baby'], 'subcategory' => 'Hoa Baby', 'score' => 5],
            ['keywords' => ['hướng dương', 'huong duong', 'sunflower'], 'subcategory' => 'Hoa Hướng Dương', 'score' => 5],
            
            // Thiết kế
            ['keywords' => ['bó', 'bo', 'bouquet'], 'subcategory' => 'Bó Hoa', 'score' => 4],
            ['keywords' => ['giỏ', 'gio', 'basket'], 'subcategory' => 'Giỏ Hoa', 'score' => 4]
        ];
        
        foreach ($subcategoryMatches as $match) {
            foreach ($match['keywords'] as $keyword) {
                if (mb_strpos($messageLower, $keyword) !== false && 
                    mb_strtolower($productSubcategory) === mb_strtolower($match['subcategory'])) {
                    $score += $match['score'];
                }
            }
        }
        
        // 5. TÌM THEO TỪ KHÓA CHI TIẾT
        $detailKeywords = [
            // Loại hoa
            'hồng' => 3, 'hong' => 3, 'rose' => 3,
            'baby' => 3,
            'hướng dương' => 3, 'huong duong' => 3, 'sunflower' => 3,
            'cẩm chướng' => 3, 'cam chuong' => 3, 'carnation' => 3,
            'cẩm tú cầu' => 3, 'cam tu cau' => 3, 'hydrangea' => 3,
            'thạch thảo' => 3, 'thach thao' => 3,
            'cúc' => 2, 'cuc' => 2, 'chrysanthemum' => 2,
            'lan' => 2, 'orchid' => 2,
            'sen' => 2, 'lotus' => 2,
            
            // Màu sắc
            'đỏ' => 2, 'do' => 2, 'red' => 2,
            'trắng' => 2, 'trang' => 2, 'white' => 2,
            'hồng' => 2, 'pink' => 2,
            'tím' => 2, 'tim' => 2, 'purple' => 2,
            'vàng' => 2, 'vang' => 2, 'yellow' => 2,
            'cam' => 2, 'orange' => 2,
            'xanh' => 2, 'blue' => 2, 'green' => 2,
            
            // Đặc tính
            'premium' => 2, 'high-end' => 2,
            'đẹp' => 1, 'dep' => 1, 'beautiful' => 1,
            'tươi' => 1, 'tuoi' => 1, 'fresh' => 1
        ];
        
        foreach ($detailKeywords as $keyword => $keywordScore) {
            if (mb_strpos($messageLower, $keyword) !== false && 
                (mb_strpos($productDescription, $keyword) !== false || mb_strpos($productName, $keyword) !== false)) {
                $score += $keywordScore;
            }
        }
        
        // 6. TÌM THEO GIÁ
        if (preg_match('/(\d{2,4})k/i', $message, $matches)) {
            $targetPrice = (int)$matches[1] * 1000;
            $priceRange = 200000;
            if (isset($product['price']) && abs($product['price'] - $targetPrice) <= $priceRange) {
                $score += 4;
            }
        } elseif (preg_match('/(\d+)tr/i', $message, $matches)) {
            $targetPrice = (int)$matches[1] * 1000000;
            $priceRange = 300000;
            if (isset($product['price']) && abs($product['price'] - $targetPrice) <= $priceRange) {
                $score += 4;
            }
        }
        
        // 7. Khoảng giá mô tả
        if (mb_strpos($messageLower, 'rẻ') !== false && isset($product['price']) && $product['price'] <= 500000) {
            $score += 3;
        }
        if (mb_strpos($messageLower, 'đắt') !== false && isset($product['price']) && $product['price'] >= 1000000) {
            $score += 3;
        }
        
        // THÊM VÀO KẾT QUẢ NẾU CÓ ĐIỂM
        if ($score > 0) {
            $product['score'] = $score;
            $results[] = $product;
        }
    }
    
    // NẾU KHÔNG TÌM THẤY GÌ, TRẢ VỀ SẢN PHẨM NGẪU NHIÊN
    if (empty($results) && count($products) > 0) {
        $results = array_slice($products, 0, 6);
        foreach ($results as &$product) {
            // Fix đường dẫn ảnh
            if (isset($product['image_url']) && strpos($product['image_url'], '../uploads/') === 0) {
                $product['image_url'] = './uploads/' . substr($product['image_url'], 11);
            }
            
            // ✅ FIX GIÁ CHO SẢN PHẨM NGẪU NHIÊN
            if (isset($product['price']) && !empty($product['price'])) {
                if (is_string($product['price'])) {
                    $cleanPrice = preg_replace('/[^\d.]/', '', $product['price']);
                    if (!empty($cleanPrice) && is_numeric($cleanPrice)) {
                        $product['price'] = (float)$cleanPrice;
                    } else {
                        $product['price'] = 500000;
                    }
                } else {
                    $product['price'] = (float)$product['price'];
                }
                
                if ($product['price'] <= 0) {
                    $product['price'] = 500000;
                }
            } else {
                $product['price'] = 500000;
            }
            
            if (!isset($product['name']) || empty($product['name'])) {
                $product['name'] = $product['description'] ?? 'Sản phẩm hoa';
            }
            
            $product['score'] = 1;
        }
    }
    
    // Sắp xếp theo điểm số (cao nhất trước)
    usort($results, function($a, $b) {
        return ($b['score'] ?? 0) - ($a['score'] ?? 0);
    });
    
    return array_slice($results, 0, 8);
}

// XỬ LÝ LOGIC CHÍNH
$generalResponse = handleGeneralChat($message);

if ($generalResponse) {
    // Phản hồi chat bình thường
    echo json_encode([
        'success' => true,
        'message' => $generalResponse,
        'products' => []
    ], JSON_UNESCAPED_UNICODE);
} else {
    // Tìm kiếm sản phẩm từ JSON thực tế
    try {
        $relatedProducts = findRelatedProductsFromJson($message);
        
        $responseMessage = '';
        if (count($relatedProducts) > 0) {
            $responseMessage = "🌸 Tôi tìm thấy " . count($relatedProducts) . " sản phẩm phù hợp với yêu cầu của bạn:";
        } else {
            $responseMessage = "😊 Xin lỗi, tôi không tìm thấy sản phẩm nào phù hợp với \"$message\".\n\n" .
                              "💡 **Bạn có thể thử:**\n" .
                              "• 'hoa sinh nhật 500k' - Tìm theo dịp và giá\n" .
                              "• 'hoa hồng đỏ' - Tìm theo loại và màu\n" .
                              "• 'bó hoa tặng mẹ' - Tìm theo đối tượng\n" .
                              "• 'hoa khai trương' - Tìm theo sự kiện\n\n" .
                              "📞 **Hoặc gọi trực tiếp:** 0123.456.789\n" .
                              "💬 **Zalo tư vấn:** 0123.456.789";
        }
        
        echo json_encode([
            'success' => true,
            'message' => $responseMessage,
            'products' => $relatedProducts
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        error_log("Lỗi trong gemini_chat.php: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi hệ thống: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}
?>