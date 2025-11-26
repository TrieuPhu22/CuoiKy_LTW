<?php
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

// ✅ HÀM PHẢN HỒI CHAT BÌNH THƯỜNG
function handleGeneralChat($message) {
    $messageLower = mb_strtolower($message);
    
    $greetings = ['xin chào', 'chào', 'hello', 'hi', 'hey', 'chào bạn'];
    $thanks = ['cảm ơn', 'cám ơn', 'thank', 'thanks'];
    $questions = ['bạn là ai', 'bạn có thể', 'giúp gì', 'làm gì được'];
    
    foreach ($greetings as $greeting) {
        if (mb_strpos($messageLower, $greeting) !== false) {
            return "👋 Xin chào! Tôi là trợ lý AI của shop hoa. Tôi có thể giúp bạn:\n\n" .
                   "🌹 Tư vấn chọn hoa theo dịp\n" .
                   "💐 Tìm kiếm sản phẩm cụ thể\n" .
                   "💰 Gợi ý theo ngân sách\n\n" .
                   "Hãy nói cho tôi biết bạn cần gì nhé!";
        }
    }
    
    foreach ($thanks as $thank) {
        if (mb_strpos($messageLower, $thank) !== false) {
            return "😊 Không có gì! Tôi rất vui được giúp đỡ bạn. Còn gì khác tôi có thể hỗ trợ không?";
        }
    }
    
    foreach ($questions as $question) {
        if (mb_strpos($messageLower, $question) !== false) {
            return "🤖 Tôi là trợ lý AI chuyên về hoa tươi! Tôi có thể:\n\n" .
                   "✅ Tư vấn hoa sinh nhật, khai trương, cưới hỏi\n" .
                   "✅ Gợi ý sản phẩm theo giá và sở thích\n" .
                   "✅ Giải đáp thắc mắc về hoa tươi\n\n" .
                   "Hãy thử hỏi tôi: 'Tôi muốn mua hoa sinh nhật 500k'";
        }
    }
    
    return null;
}

// ✅ HÀM TÌM SẢN PHẨM TỪ JSON - SỬA LẠI THUẬT TOÁN TÌM KIẾM
function findRelatedProductsFromJson($message) {
    $messageLower = mb_strtolower($message);
    
    // ✅ ĐỌC DỮ LIỆU TỪ JSON FILE
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
        
        // ✅ SỬA ĐƯỜNG DẪN ẢNH ĐÚNG
        if (isset($product['image_url']) && !empty($product['image_url'])) {
            if (strpos($product['image_url'], '../uploads/') === 0) {
                $product['image_url'] = './uploads/' . substr($product['image_url'], 11);
            } elseif (strpos($product['image_url'], './uploads/') !== 0 && strpos($product['image_url'], '/uploads/') !== 0) {
                $product['image_url'] = './uploads/' . basename($product['image_url']);
            }
        } else {
            $product['image_url'] = './img/web/hoahong/default.jpg';
        }
        
        // ✅ CHUẨN HÓA DỮ LIỆU TÌM KIẾM
        $productName = mb_strtolower($product['name'] ?? '');
        $productDescription = mb_strtolower($product['description'] ?? '');
        $productCategory = mb_strtolower($product['category'] ?? '');
        $productSubcategory = mb_strtolower($product['subcategory'] ?? '');
        
        // ✅ THUẬT TOÁN TÌM KIẾM MỚI - CHÍNH XÁC HƠN
        
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
        
        // 3. ✅ TÌM THEO CATEGORY - SỬA LỖI QUAN TRỌNG
        $categoryMatches = [
            // Hoa sinh nhật
            ['keywords' => ['sinh nhật', 'sinh nhat', 'birthday'], 'category' => 'hoa_sinh_nhat', 'score' => 7],
            
            // Khai trương  
            ['keywords' => ['khai trương', 'khai truong', 'opening', 'chúc mừng khai trương'], 'category' => 'hoa_khai_truong', 'score' => 7],
            
            // Chủ đề - Quan trọng!
            ['keywords' => ['tang lễ', 'tang le', 'chia buồn', 'chia buon', 'funeral'], 'category' => 'chu_de', 'subcategory' => 'Hoa Tang Lễ', 'score' => 8],
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
        
        // 4. ✅ TÌM THEO SUBCATEGORY - CHI TIẾT HƠN
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
        
        // ✅ THÊM VÀO KẾT QUẢ NẾU CÓ ĐIỂM
        if ($score > 0) {
            $product['score'] = $score;
            $results[] = $product;
        }
    }
    
    // ✅ NẾU KHÔNG TÌM THẤY GÌ, TRẢ VỀ SẢN PHẨM NGẪU NHIÊN
    if (empty($results) && count($products) > 0) {
        $results = array_slice($products, 0, 6);
        foreach ($results as &$product) {
            if (isset($product['image_url']) && strpos($product['image_url'], '../uploads/') === 0) {
                $product['image_url'] = './uploads/' . substr($product['image_url'], 11);
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

// ✅ XỬ LÝ LOGIC CHÍNH
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
                              "💡 Bạn có thể thử:\n" .
                              "• 'hoa sinh nhật' hoặc 'sinh nhật'\n" .
                              "• 'hoa tang lễ' hoặc 'chia buồn'\n" .
                              "• 'hoa cưới' hoặc 'cầm tay cô dâu'\n" .
                              "• 'hoa khai trương'\n" .
                              "• 'hoa hồng' hoặc 'baby'\n" .
                              "• 'sang trọng' hoặc '500k'";
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