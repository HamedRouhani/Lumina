<?php
// app/controllers/WidgetController.php

class WidgetController extends Controller
{
    private $serviceModel;
    private $chatModel;
    private $openaiApiKey;
    private $isTestMode = false;
    
    public function __construct()
    {
        parent::__construct();
        $this->serviceModel = new ServiceModel();
        $this->chatModel = new ChatModel();
        
        // بارگذاری OpenAI API Key
        $this->openaiApiKey = '';
        
        // روش 1: از getenv
        $envKey = getenv('OPENAI_API_KEY');
        if (!empty($envKey)) {
            $this->openaiApiKey = $envKey;
        }
        
        // روش 2: از $_ENV
        if (empty($this->openaiApiKey) && isset($_ENV['OPENAI_API_KEY'])) {
            $this->openaiApiKey = $_ENV['OPENAI_API_KEY'];
        }
        
        // روش 3: از فایل config.php
        $configPath = ROOT_PATH . '/config.php';
        if (empty($this->openaiApiKey) && file_exists($configPath)) {
            require_once $configPath;
            if (defined('OPENAI_API_KEY')) {
                $this->openaiApiKey = OPENAI_API_KEY;
            }
        }
        
        if (empty($this->openaiApiKey)) {
            $this->isTestMode = true;
        }
        
        error_log("WidgetController initialized - API Key: " . (empty($this->openaiApiKey) ? "NO" : "YES"));
    }
    
    // ==================== صفحات ویجت ====================
    
    public function chatWidget()
    {
        $serviceCode = $_GET['service_code'] ?? '';
        $primaryColor = $_GET['primary_color'] ?? '#667eea';
        $title = $_GET['title'] ?? 'پشتیبانی آنلاین';
        $welcomeMessage = $_GET['welcome_message'] ?? 'سلام! چطور می‌توانم به شما کمک کنم؟';
        $position = $_GET['position'] ?? 'bottom-right';
        $autoOpen = isset($_GET['auto_open']) && $_GET['auto_open'] == 'true';
        
        if (!empty($serviceCode)) {
            $service = $this->serviceModel->getServiceByCode($serviceCode);
            if ($service) {
                $title = $service['title'] ?? $title;
                $welcomeMessage = $service['welcome_message'] ?? $welcomeMessage;
                if (!empty($service['widget_settings'])) {
                    $settings = json_decode($service['widget_settings'], true);
                    $primaryColor = $settings['primaryColor'] ?? $primaryColor;
                    $position = $settings['position'] ?? $position;
                }
            }
        }
        
        $this->view('widget.chat-widget', [
            'serviceCode' => $serviceCode,
            'primaryColor' => $primaryColor,
            'title' => $title,
            'welcomeMessage' => $welcomeMessage,
            'position' => $position,
            'autoOpen' => $autoOpen,
            'apiBaseUrl' => rtrim($_ENV['APP_URL'] ?? 'https://lifyai.com/mylumina', '/')
        ]);
    }
    
    public function chatInline()
    {
        $serviceCode = $_GET['service_code'] ?? '';
        $primaryColor = $_GET['primary_color'] ?? '#667eea';
        $buttonColor = $_GET['button_color'] ?? $primaryColor;
        $title = $_GET['title'] ?? 'پشتیبانی آنلاین';
        
        // ========== دریافت welcome_message از دیتابیس ==========
        $welcomeMessage = 'سلام! چطور می‌توانم به شما کمک کنم؟';
        if (!empty($serviceCode)) {
            try {
                $service = $this->serviceModel->getServiceByCode($serviceCode);
                if ($service && !empty($service['welcome_message'])) {
                    $welcomeMessage = $service['welcome_message'];
                    error_log("chatInline - Loaded welcome_message from DB: " . substr($welcomeMessage, 0, 50));
                } else {
                    error_log("chatInline - No welcome_message found in DB for service: " . $serviceCode);
                }
            } catch (Exception $e) {
                error_log("chatInline - Error loading welcome_message: " . $e->getMessage());
            }
        }
        
        $this->view('widget.chat-inline', [
            'serviceCode' => $serviceCode,
            'primaryColor' => $primaryColor,
            'buttonColor' => $buttonColor,
            'title' => $title,
            'welcomeMessage' => $welcomeMessage,  // <-- از دیتابیس می‌آید
            'apiBaseUrl' => rtrim($_ENV['APP_URL'] ?? 'https://lifyai.com/mylumina', '/')
        ]);
    }
    
    // ==================== APIها ====================
    
    public function serviceInfo()
    {
        $serviceCode = $_GET['service_code'] ?? '';
        
        if (!$serviceCode) {
            $this->json(['success' => false, 'error' => 'service_code required'], 400);
            return;
        }
        
        $service = $this->serviceModel->getServiceByCode($serviceCode);
        
        if (!$service) {
            $this->json(['success' => false, 'error' => 'Service not found'], 404);
            return;
        }
        
        $this->json([
            'success' => true,
            'service' => [
                'id' => $service['id'],
                'service_code' => $service['service_code'],
                'title' => $service['title'],
                'welcome_message' => $service['welcome_message'],
                'assistant_id' => $service['assistant_id'] ?? ''
            ]
        ]);
    }
    
    public function chatStream()
    {
        // تنظیم هدرهای استریم
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type');
        
        // پاک کردن بافرها
        while (ob_get_level()) {
            ob_end_clean();
        }
        ob_implicit_flush(true);
        
        // دریافت ورودی
        $input = json_decode(file_get_contents('php://input'), true);
        $message = trim($input['message'] ?? '');
        $serviceCode = $input['service_code'] ?? '';
        $visitorId = $input['visitor_id'] ?? '';
        $widgetId = $input['widget_id'] ?? '';
        
        error_log("chatStream - serviceCode: $serviceCode, message: " . substr($message, 0, 50));
        
        // اعتبارسنجی
        if (empty($message)) {
            $this->sendStreamEvent(['error' => 'پیام نمی‌تواند خالی باشد']);
            return;
        }
        
        if (empty($serviceCode)) {
            $this->sendStreamEvent(['error' => 'کد سرویس الزامی است']);
            return;
        }
        
        // بررسی API Key
        if (empty($this->openaiApiKey)) {
            error_log("chatStream ERROR: OpenAI API Key is empty");
            $this->sendStreamEvent(['error' => 'سیستم در حال بروزرسانی است. لطفاً چند لحظه دیگر تلاش کنید.']);
            return;
        }
        
        // دریافت سرویس
        $service = $this->serviceModel->getServiceByCode($serviceCode);
        if (!$service) {
            error_log("chatStream ERROR: Service not found: " . $serviceCode);
            $this->sendStreamEvent(['error' => 'سرویس مورد نظر یافت نشد']);
            return;
        }
        
        // بررسی اشتراک
        $subscriptionModel = new ServiceSubscriptionModel();
        $isValid = $subscriptionModel->isSubscriptionValid($service['id']);
        
        if (!$isValid) {
            $status = $subscriptionModel->getSubscriptionStatus($service['id']);
            $errorMessage = 'اشتراک این سرویس به پایان رسیده است.';
            if ($status['status'] === 'limit_exceeded') {
                $errorMessage = 'محدودیت تعداد چت برای این سرویس به پایان رسیده است.';
            } elseif ($status['status'] === 'expired') {
                $errorMessage = 'مدت زمان اشتراک این سرویس به پایان رسیده است.';
            }
            $this->sendStreamEvent(['error' => $errorMessage]);
            return;
        }
        
        // بررسی Assistant ID
        $assistantId = $service['assistant_id'] ?? '';
        if (empty($assistantId)) {
            error_log("chatStream ERROR: Assistant ID empty for service: " . $serviceCode);
            $this->sendStreamEvent(['error' => 'شناسه دستیار تنظیم نشده است.']);
            return;
        }
        
        error_log("chatStream - Assistant ID: " . $assistantId);
        
        // دریافت یا ایجاد کاربر
        $user = $this->chatModel->getOrCreateUser($visitorId, $service['id'], $service['customer_id'], $widgetId);
        $chatId = $this->chatModel->saveUserMessage($user['id'], $message);
        
        // مدیریت thread
        $threadId = $user['thread_id'];
        
        if (empty($threadId)) {
            $threadId = $this->createThread();
            if ($threadId) {
                $this->chatModel->updateUserThreadId($user['id'], $threadId);
                error_log("chatStream - Created thread: " . $threadId);
            } else {
                $this->sendStreamEvent(['error' => 'خطا در ایجاد نشست چت']);
                return;
            }
        }
        
        // ارسال پیام و دریافت پاسخ
        $fullResponse = $this->sendAndStreamMessages($threadId, $message, $assistantId);
        
        // ذخیره پاسخ
        if (!empty($fullResponse) && $chatId) {
            $this->chatModel->saveBotResponse($chatId, $fullResponse);
            $subscriptionModel->incrementChatCount($service['id']);
            error_log("chatStream - Saved response, length: " . strlen($fullResponse));
        }
        
        if (empty($fullResponse)) {
            $this->sendStreamEvent(['error' => 'متأسفانه قادر به پاسخگویی نیستم.']);
        } else {
            $this->sendStreamEvent(['complete' => true]);
        }
    }
    
    public function chatHistory()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $visitorId = $input['visitor_id'] ?? '';
        $serviceCode = $input['service_code'] ?? '';
        $widgetId = $input['widget_id'] ?? '';
        
        if (!$visitorId || !$serviceCode) {
            $this->json(['success' => false, 'error' => 'visitor_id and service_code required'], 400);
            return;
        }
        
        $service = $this->serviceModel->getServiceByCode($serviceCode);
        if (!$service) {
            $this->json(['success' => false, 'error' => 'Service not found'], 404);
            return;
        }
        
        $user = $this->chatModel->getOrCreateUser($visitorId, $service['id'], $service['customer_id'], $widgetId);
        
        $db = Database::getInstance();
        $history = $db->fetchAll(
            "SELECT chat_user as userMessage, chat_bot as botMessage, created_at 
             FROM Chats WHERE user_id = ? ORDER BY created_at ASC LIMIT 50",
            [$user['id']]
        );
        
        $this->json(['success' => true, 'history' => $history]);
    }
    
    // ==================== توابع کمکی OpenAI ====================
    
    private function createThread()
    {
        if (empty($this->openaiApiKey)) {
            return null;
        }
        
        $ch = curl_init('https://api.openai.com/v1/threads');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->openaiApiKey,
            'OpenAI-Beta: assistants=v2'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([]));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            error_log("createThread error: HTTP $httpCode");
            return null;
        }
        
        $data = json_decode($response, true);
        return $data['id'] ?? null;
    }
    
    private function sendAndStreamMessages($threadId, $message, $assistantId)
    {
        if (empty($this->openaiApiKey) || empty($threadId) || empty($assistantId)) {
            return '';
        }
        
        error_log("sendAndStreamMessages - threadId: $threadId, assistantId: $assistantId");
        
        // ========== 1. ارسال پیام ==========
        $ch = curl_init("https://api.openai.com/v1/threads/{$threadId}/messages");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->openaiApiKey,
            'OpenAI-Beta: assistants=v2'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'role' => 'user',
            'content' => $message
        ]));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        error_log("sendAndStreamMessages - Send message HTTP: $httpCode, Error: " . ($curlError ?: 'none'));
        
        if ($httpCode !== 200 && $httpCode !== 201) {
            error_log("sendAndStreamMessages - Error response: " . substr($response, 0, 200));
            $this->sendStreamEvent(['error' => 'خطا در ارسال پیام به هوش مصنوعی']);
            return '';
        }
        
        // ========== 2. اجرای run ==========
        $ch = curl_init("https://api.openai.com/v1/threads/{$threadId}/runs");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->openaiApiKey,
            'OpenAI-Beta: assistants=v2'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'assistant_id' => $assistantId
        ]));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        error_log("sendAndStreamMessages - Run HTTP: $httpCode, Error: " . ($curlError ?: 'none'));
        
        if ($httpCode !== 200) {
            error_log("sendAndStreamMessages - Run error: " . substr($response, 0, 200));
            $this->sendStreamEvent(['error' => 'خطا در پردازش درخواست']);
            return '';
        }
        
        $runData = json_decode($response, true);
        $runId = $runData['id'] ?? null;
        
        if (!$runId) {
            error_log("sendAndStreamMessages - No run ID received");
            $this->sendStreamEvent(['error' => 'شناسه پردازش دریافت نشد']);
            return '';
        }
        
        // ========== 3. Polling با تنظیمات بهتر ==========
        $maxAttempts = 60;
        $attempts = 0;
        $fullResponse = '';
        
        while ($attempts < $maxAttempts) {
            $ch = curl_init("https://api.openai.com/v1/threads/{$threadId}/runs/{$runId}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->openaiApiKey,
                'OpenAI-Beta: assistants=v2'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);  // <-- اضافه شده
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FORBID_REUSE, true);  // <-- اضافه شده
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, true); // <-- اضافه شده
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                error_log("sendAndStreamMessages - Polling error: HTTP $httpCode, Error: " . ($curlError ?: 'none'));
                // اگر خطای موقتی است، ادامه بده
                if ($attempts < 5) {
                    $attempts++;
                    usleep(1000000); // 1 ثانیه صبر کن
                    continue;
                }
                break;
            }
            
            $runStatus = json_decode($response, true);
            $status = $runStatus['status'] ?? 'unknown';
            
            error_log("sendAndStreamMessages - Run status: $status (attempt " . ($attempts + 1) . "/$maxAttempts)");
            
            if ($status === 'completed') {
                // دریافت پیام‌ها
                $ch = curl_init("https://api.openai.com/v1/threads/{$threadId}/messages");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->openaiApiKey,
                    'OpenAI-Beta: assistants=v2'
                ]);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode === 200) {
                    $messages = json_decode($response, true);
                    $messagesData = $messages['data'] ?? [];
                    
                    foreach ($messagesData as $msg) {
                        if ($msg['role'] === 'assistant') {
                            $content = $msg['content'][0]['text']['value'] ?? '';
                            if (!empty($content)) {
                                $fullResponse = $content;
                                
                                // استریم کردن پاسخ به صورت کلمه به کلمه
                                $words = preg_split('/\s+/', $content);
                                foreach ($words as $word) {
                                    $this->sendStreamEvent(['content' => $word . ' ']);
                                    usleep(20000);
                                }
                                break;
                            }
                        }
                    }
                }
                break;
            } elseif ($status === 'failed' || $status === 'cancelled' || $status === 'expired') {
                $lastError = $runStatus['last_error']['message'] ?? 'خطای ناشناخته';
                error_log("sendAndStreamMessages - Run failed: $lastError");
                $this->sendStreamEvent(['error' => 'پردازش با خطا مواجه شد: ' . $lastError]);
                return '';
            } elseif ($status === 'in_progress' || $status === 'queued') {
                // در حال پردازش، ادامه بده
                $attempts++;
                usleep(1000000); // 1 ثانیه
                continue;
            }
            
            $attempts++;
            usleep(500000);
        }
        
        if (empty($fullResponse) && $attempts >= $maxAttempts) {
            error_log("sendAndStreamMessages - Timeout waiting for response");
            $this->sendStreamEvent(['error' => 'زمان پاسخ‌دهی به پایان رسید']);
            return '';
        }
        
        error_log("sendAndStreamMessages - Response length: " . strlen($fullResponse));
        return $fullResponse;
    }
    
    private function sendStreamEvent($data)
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return;
        }
        
        echo "data: " . $json . "\n\n";
        flush();
        
        if (ob_get_level() > 0) {
            ob_flush();
        }
    }
}