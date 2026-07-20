<?php
header('Content-Type: application/json');

// ============================================
// إعدادات الاتصال بخادم Node.js
// ============================================
define('NODE_SERVER', 'http://localhost:3001');

// ============================================
// دوال مساعدة للتواصل مع Node.js
// ============================================

function callNodeApi($endpoint, $method = 'GET', $data = null) {
    $url = NODE_SERVER . $endpoint;
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response === false) {
        return [
            'success' => false,
            'message' => '❌ خادم واتساب غير متاح. تأكد من تشغيل Node.js'
        ];
    }
    
    return json_decode($response, true);
}

// ============================================
// 1. توليد QR Code
// ============================================
// ============================================
// 1. عرض QR Code كصورة
// ============================================
if (isset($_GET['action']) && $_GET['action'] === 'get_qr') {
    $result = callNodeApi('/qr');
    
    if ($result['success'] && isset($result['qr_code'])) {
        // تحويل النص إلى صورة QR Code باستخدام مكتبة خارجية
        $qrText = $result['qr_code'];
        
        // استخدام API مجاني لتوليد صورة QR
        $qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrText);
        
        // إعادة توجيه إلى الصورة
        header('Location: ' . $qrImageUrl);
        exit;
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'فشل توليد QR Code'
        ]);
    }
    exit;
}

// ============================================
// 2. إرسال رسالة
// ============================================
if (isset($_POST['action']) && $_POST['action'] === 'send') {
    $phone = $_POST['phone'] ?? '';
    $message = $_POST['message'] ?? '';
    
    if (empty($phone) || empty($message)) {
        echo json_encode([
            'success' => false,
            'message' => '⚠️ يجب إدخال رقم الجوال والرسالة'
        ]);
        exit;
    }
    
    $result = callNodeApi('/send', 'POST', [
        'phone' => $phone,
        'message' => $message
    ]);
    
    echo json_encode($result);
    exit;
}

// ============================================
// 3. حالة الاتصال
// ============================================
if (isset($_GET['action']) && $_GET['action'] === 'status') {
    $result = callNodeApi('/status');
    echo json_encode($result);
    exit;
}

// ============================================
// الإجراء الافتراضي
// ============================================
echo json_encode([
    'success' => false,
    'message' => '📌 الأوامر المتاحة:',
    'actions' => [
        'get_qr' => 'GET /wa.php?action=get_qr',
        'send' => 'POST /wa.php -d "action=send&phone=966501234568&message=نص"',
        'status' => 'GET /wa.php?action=status'
    ]
]);