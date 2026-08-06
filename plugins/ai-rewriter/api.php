<?php
session_start();
if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    echo json_encode(['status' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../includes/db.php';

$content = $_POST['content'] ?? '';
if (empty(trim($content))) {
    echo json_encode(['status' => false, 'message' => 'Nội dung không được để trống.']);
    exit;
}

$settings = getSettings();
$provider = $settings['aiProvider'] ?? 'gemini';
$geminiKey = $settings['geminiApiKey'] ?? '';
$openaiKey = $settings['openaiApiKey'] ?? '';

$systemPrompt = "Bạn là một biên tập viên phim chuyên nghiệp. Nhiệm vụ của bạn là viết lại đoạn mô tả phim sau đây sao cho hấp dẫn hơn, thu hút người đọc hơn và chuẩn SEO. Chỉ trả về nội dung đã viết lại, KHÔNG giải thích, KHÔNG thêm tiêu đề, KHÔNG thêm định dạng Markdown (như in đậm, in nghiêng) nếu không cần thiết. Đoạn mô tả gốc:\n\n";

if ($provider === 'gemini') {
    if (empty($geminiKey)) {
        echo json_encode(['status' => false, 'message' => 'Chưa cấu hình Gemini API Key.']);
        exit;
    }
    
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key=" . $geminiKey;
    
    $data = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $systemPrompt . $content]
                ]
            ]
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $resData = json_decode($response, true);
    
    if ($httpCode === 200 && isset($resData['candidates'][0]['content']['parts'][0]['text'])) {
        $rewritten = trim($resData['candidates'][0]['content']['parts'][0]['text']);
        echo json_encode(['status' => true, 'result' => $rewritten]);
    } else {
        $error = $resData['error']['message'] ?? 'Lỗi không xác định từ API Gemini.';
        echo json_encode(['status' => false, 'message' => $error]);
    }
    
} elseif ($provider === 'openai') {
    if (empty($openaiKey)) {
        echo json_encode(['status' => false, 'message' => 'Chưa cấu hình OpenAI API Key.']);
        exit;
    }
    
    $url = "https://api.openai.com/v1/chat/completions";
    
    $data = [
        "model" => "gpt-4o-mini",
        "messages" => [
            ["role" => "system", "content" => "Bạn là biên tập viên phim chuyên nghiệp. Chỉ trả về mô tả đã được viết lại sao cho hấp dẫn và chuẩn SEO. KHÔNG dùng markdown. KHÔNG giải thích."],
            ["role" => "user", "content" => "Viết lại đoạn mô tả phim sau:\n\n" . $content]
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $openaiKey
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $resData = json_decode($response, true);
    
    if ($httpCode === 200 && isset($resData['choices'][0]['message']['content'])) {
        $rewritten = trim($resData['choices'][0]['message']['content']);
        echo json_encode(['status' => true, 'result' => $rewritten]);
    } else {
        $error = $resData['error']['message'] ?? 'Lỗi không xác định từ API OpenAI.';
        echo json_encode(['status' => false, 'message' => $error]);
    }
} else {
    echo json_encode(['status' => false, 'message' => 'Provider không hợp lệ.']);
}
