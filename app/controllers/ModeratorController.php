<?php
// app/controllers/ModeratorController.php

class ModeratorController extends Controller
{
    private $serviceModel;
    private $customerModel;
    private $subscriptionModel;
    
    public function __construct()
    {
        if (method_exists(get_parent_class($this), '__construct')) {
            parent::__construct();
        }
        $this->serviceModel = new ServiceModel();
        $this->customerModel = new CustomerModel();
        $this->subscriptionModel = new ServiceSubscriptionModel();
    }
    
    // ============================================================
    // صفحه سرویس‌های من
    // ============================================================
    public function services()
    {
        $admin = $this->getCurrentAdmin();
        if ($admin['role'] !== 'moderator') {
            $this->redirect('dashboard');
            return;
        }
        
        $this->view('moderator.services', [
            'title' => 'سرویس‌های من',
            'admin' => $admin
        ]);
    }
    
    // ============================================================
    // دریافت داده‌های سرویس‌ها برای AJAX
    // ============================================================
    public function getServicesData()
    {
        $admin = $this->getCurrentAdmin();
        
        if (!$admin) {
            $this->json(['success' => false, 'error' => 'لطفاً ابتدا وارد شوید'], 401);
            return;
        }
        
        if ($admin['role'] !== 'moderator') {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
            return;
        }
        
        if (empty($admin['customer_id'])) {
            $this->json(['success' => true, 'services' => [], 'message' => 'شما به هیچ مشتری متصل نیستید']);
            return;
        }
        
        try {
            $db = Database::getInstance();
            $services = $this->serviceModel->getServicesByCustomer($admin['customer_id']);
            
            foreach ($services as &$service) {
                // دریافت اشتراک
                $subscription = $this->subscriptionModel->getSubscriptionByServiceId($service['id']);
                
                // ============================================================
                // اصلاح: محاسبه تعداد واقعی چت‌ها از جدول Chats
                // ============================================================
                $realChatCount = $db->fetch(
                    "SELECT COUNT(c.id) as total 
                    FROM Chats c
                    JOIN Users u ON c.user_id = u.id
                    WHERE u.service_id = ?",
                    [$service['id']]
                );
                $realChatCount = $realChatCount['total'] ?? 0;
                
                // استفاده از تعداد واقعی چت‌ها
                $service['real_chat_count'] = $realChatCount;
                
                if ($subscription) {
                    $service['plan_name'] = $subscription['plan_name'] ?? 'بدون اشتراک';
                    $service['chat_limit'] = $subscription['chat_limit'] ?? 0;
                    // استفاده از تعداد واقعی به جای chat_count از اشتراک
                    $service['chat_count'] = $realChatCount;
                    $service['subscription_chat_count'] = $subscription['chat_count'] ?? 0; // برای دیباگ
                    $service['days_remaining'] = $subscription['days_remaining'] ?? null;
                    $service['is_active'] = $subscription['is_valid'] ?? 0;
                } else {
                    $service['plan_name'] = 'بدون اشتراک';
                    $service['chat_limit'] = 0;
                    $service['chat_count'] = $realChatCount;
                    $service['days_remaining'] = null;
                }
            }
            
            $this->json([
                'success' => true,
                'services' => $services,
                'count' => count($services)
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // ============================================================
    // صفحه کد ویجت
    // ============================================================
    public function widgetCode()
    {
        $admin = $this->getCurrentAdmin();
        if ($admin['role'] !== 'moderator') {
            $this->redirect('dashboard');
            return;
        }
        
        $services = $this->serviceModel->getServicesByCustomer($admin['customer_id']);
        
        $this->view('moderator.widget-code', [
            'title' => 'کد ویجت',
            'admin' => $admin,
            'services' => $services
        ]);
    }
    
    // ============================================================
    // دریافت کد ویجت یک سرویس (AJAX)
    // ============================================================
    public function getWidgetCode($serviceId)
    {
        $admin = $this->getCurrentAdmin();
        if ($admin['role'] !== 'moderator') {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
            return;
        }
        
        try {
            $service = $this->serviceModel->getServiceById($serviceId);
            
            if (!$service || $service['customer_id'] != $admin['customer_id']) {
                $this->json(['success' => false, 'error' => 'سرویس یافت نشد'], 404);
                return;
            }
            
            // اطمینان از وجود widget_settings
            if (empty($service['widget_settings'])) {
                $service['widget_settings'] = json_encode([
                    'primaryColor' => '#667eea',
                    'buttonColor' => '#28a745',
                    'floatingPosition' => 'bottom-right',
                    'iframeWidth' => '500px',
                    'iframeHeight' => '550',
                    'iframePosition' => 'center'
                ]);
            }
            
            $this->json([
                'success' => true,
                'service' => [
                    'id' => $service['id'],
                    'service_code' => $service['service_code'],
                    'title' => $service['title'],
                    'url' => $service['url'],
                    'welcome_message' => $service['welcome_message'],
                    'widget_settings' => $service['widget_settings']
                ]
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // ============================================================
    // صفحه گزارش استفاده
    // ============================================================
    public function usageReport()
    {
        $admin = $this->getCurrentAdmin();
        if ($admin['role'] !== 'moderator') {
            $this->redirect('dashboard');
            return;
        }
        
        $this->view('moderator.usage-report', [
            'title' => 'گزارش استفاده',
            'admin' => $admin
        ]);
    }
    
    // ============================================================
    // دریافت داده‌های گزارش استفاده (AJAX)
    // ============================================================
    public function getUsageData()
    {
        $admin = $this->getCurrentAdmin();
        if ($admin['role'] !== 'moderator') {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
            return;
        }
        
        try {
            $db = Database::getInstance();
            $customerId = $admin['customer_id'];
            
            $stats = $db->fetch(
                "SELECT 
                    COUNT(DISTINCT c.id) as total_chats,
                    COUNT(DISTINCT DATE(c.created_at)) as active_days,
                    SUM(CASE WHEN DATE(c.created_at) = CURDATE() THEN 1 ELSE 0 END) as today_chats
                FROM Chats c
                JOIN Users u ON c.user_id = u.id
                JOIN Services s ON u.service_id = s.id
                WHERE s.customer_id = ?",
                [$customerId]
            );
            
            $recentChats = $db->fetchAll(
                "SELECT 
                    c.id,
                    c.chat_user,
                    c.chat_bot,
                    c.created_at,
                    s.title as service_title
                FROM Chats c
                JOIN Users u ON c.user_id = u.id
                JOIN Services s ON u.service_id = s.id
                WHERE s.customer_id = ?
                ORDER BY c.created_at DESC
                LIMIT 50",
                [$customerId]
            );
            
            $weeklyStats = $db->fetchAll(
                "SELECT 
                    DATE(c.created_at) as date,
                    COUNT(c.id) as count
                FROM Chats c
                JOIN Users u ON c.user_id = u.id
                JOIN Services s ON u.service_id = s.id
                WHERE s.customer_id = ? AND c.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY DATE(c.created_at)
                ORDER BY date ASC",
                [$customerId]
            );
            
            $dates = [];
            $counts = [];
            foreach ($weeklyStats as $row) {
                $dates[] = $this->formatPersianDate($row['date']);
                $counts[] = $row['count'];
            }
            
            $this->json([
                'success' => true,
                'stats' => $stats,
                'recent_chats' => $recentChats,
                'weekly' => [
                    'dates' => $dates,
                    'counts' => $counts
                ]
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // ============================================================
    // تولید کد ویجت با تنظیمات سفارشی (API جدید)
    // ============================================================
    public function generateCustomWidgetCode()
    {
        $admin = $this->getCurrentAdmin();
        if ($admin['role'] !== 'moderator') {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $serviceId = $input['service_id'] ?? null;
        $settings = $input['settings'] ?? [];

        if (!$serviceId) {
            $this->json(['success' => false, 'error' => 'شناسه سرویس الزامی است'], 400);
            return;
        }

        $service = $this->serviceModel->getServiceById($serviceId);
        if (!$service || $service['customer_id'] != $admin['customer_id']) {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
            return;
        }

        // تنظیمات پیش‌فرض
        $primaryColor = $settings['primaryColor'] ?? '#667eea';
        $buttonColor = $settings['buttonColor'] ?? '#28a745';
        $floatingPosition = $settings['floatingPosition'] ?? 'bottom-right';
        $iframeWidth = $settings['iframeWidth'] ?? '500px';
        $iframeHeight = $settings['iframeHeight'] ?? '550';
        $iframePosition = $settings['iframePosition'] ?? 'center';
        $title = $settings['title'] ?? $service['title'] ?? 'پشتیبانی آنلاین';
        $welcomeMessage = $settings['welcomeMessage'] ?? $service['welcome_message'] ?? 'سلام! چطور می‌توانم به شما کمک کنم؟';
        
        $serviceCode = $service['service_code'];
        
        // رنگ‌ها با # در URL ارسال شوند
        $primaryColorUrl = urlencode($primaryColor);
        $buttonColorUrl = urlencode($buttonColor);
        $titleEncoded = urlencode($title);
        $welcomeEncoded = urlencode($welcomeMessage);
        
        $baseUrl = rtrim($_ENV['APP_URL'] ?? 'https://lifyai.com/mylumina', '/');
        
        // ساخت URL پایه ویجت
        $widgetUrl = $baseUrl . '/widget-inline?service_code=' . urlencode($serviceCode) . 
                    '&primary_color=' . $primaryColorUrl . 
                    '&button_color=' . $buttonColorUrl . 
                    '&title=' . $titleEncoded . 
                    '&welcome_message=' . $welcomeEncoded;
        
        // ================================================================
        // تولید کد شناور
        // ================================================================
        $floatingPosCSS = '';
        $windowPosCSS = '';
        if ($floatingPosition === 'bottom-right') {
            $floatingPosCSS = 'right: 20px; bottom: 20px;';
            $windowPosCSS = 'right: 20px; bottom: 90px;';
        } else if ($floatingPosition === 'bottom-left') {
            $floatingPosCSS = 'left: 20px; bottom: 20px;';
            $windowPosCSS = 'left: 20px; bottom: 90px;';
        }
        
        $floatingCode = '<!-- لومینا - ویجت چت شناور ' . $title . ' -->' . "\n";
        $floatingCode .= '<style>' . "\n";
        $floatingCode .= '    .lumina-chat-btn-' . $serviceCode . ' {' . "\n";
        $floatingCode .= '        position: fixed;' . "\n";
        $floatingCode .= '        ' . $floatingPosCSS . "\n";
        $floatingCode .= '        width: 56px;' . "\n";
        $floatingCode .= '        height: 56px;' . "\n";
        $floatingCode .= '        border-radius: 50%;' . "\n";
        $floatingCode .= '        background: linear-gradient(135deg, ' . $primaryColor . ' 0%, #764ba2 100%);' . "\n";
        $floatingCode .= '        cursor: pointer;' . "\n";
        $floatingCode .= '        box-shadow: 0 4px 15px rgba(0,0,0,0.2);' . "\n";
        $floatingCode .= '        z-index: 999999;' . "\n";
        $floatingCode .= '        border: none;' . "\n";
        $floatingCode .= '        display: flex;' . "\n";
        $floatingCode .= '        align-items: center;' . "\n";
        $floatingCode .= '        justify-content: center;' . "\n";
        $floatingCode .= '        transition: transform 0.3s ease;' . "\n";
        $floatingCode .= '    }' . "\n";
        $floatingCode .= '    .lumina-chat-btn-' . $serviceCode . ':hover { transform: scale(1.1); }' . "\n";
        $floatingCode .= '    .lumina-chat-btn-' . $serviceCode . ' svg { width: 28px; height: 28px; fill: white; }' . "\n";
        $floatingCode .= '    .lumina-chat-window-' . $serviceCode . ' {' . "\n";
        $floatingCode .= '        position: fixed;' . "\n";
        $floatingCode .= '        ' . $windowPosCSS . "\n";
        $floatingCode .= '        width: 380px;' . "\n";
        $floatingCode .= '        height: 500px;' . "\n";
        $floatingCode .= '        background: white;' . "\n";
        $floatingCode .= '        border-radius: 16px;' . "\n";
        $floatingCode .= '        box-shadow: 0 10px 40px rgba(0,0,0,0.15);' . "\n";
        $floatingCode .= '        display: none;' . "\n";
        $floatingCode .= '        z-index: 999998;' . "\n";
        $floatingCode .= '        border: none;' . "\n";
        $floatingCode .= '    }' . "\n";
        $floatingCode .= '    @media (max-width: 768px) {' . "\n";
        $floatingCode .= '        .lumina-chat-window-' . $serviceCode . ' { width: calc(100vw - 40px); height: 70vh; }' . "\n";
        $floatingCode .= '    }' . "\n";
        $floatingCode .= '</style>' . "\n";
        $floatingCode .= '<div id="luminaChatContainer">' . "\n";
        $floatingCode .= '    <button class="lumina-chat-btn-' . $serviceCode . '" id="luminaChatBtn">' . "\n";
        $floatingCode .= '        <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4V4c0-1.1-.9-2-2-2z"/></svg>' . "\n";
        $floatingCode .= '    </button>' . "\n";
        $floatingCode .= '    <iframe class="lumina-chat-window-' . $serviceCode . '" id="luminaChatIframe" ' . "\n";
        $floatingCode .= '        src="' . $widgetUrl . '"' . "\n";
        $floatingCode .= '        title="' . $title . '">' . "\n";
        $floatingCode .= '    </iframe>' . "\n";
        $floatingCode .= '</div>' . "\n";
        $floatingCode .= '<script>' . "\n";
        $floatingCode .= '(function() {' . "\n";
        $floatingCode .= '    const btn = document.getElementById("luminaChatBtn");' . "\n";
        $floatingCode .= '    const iframe = document.getElementById("luminaChatIframe");' . "\n";
        $floatingCode .= '    let isOpen = false;' . "\n";
        $floatingCode .= '    if (btn) {' . "\n";
        $floatingCode .= '        btn.addEventListener("click", function(e) {' . "\n";
        $floatingCode .= '            e.stopPropagation();' . "\n";
        $floatingCode .= '            if (isOpen) { iframe.style.display = "none"; isOpen = false; }' . "\n";
        $floatingCode .= '            else { iframe.style.display = "block"; isOpen = true; }' . "\n";
        $floatingCode .= '        });' . "\n";
        $floatingCode .= '        document.addEventListener("click", function(e) {' . "\n";
        $floatingCode .= '            if (isOpen && btn && iframe && !btn.contains(e.target) && !iframe.contains(e.target)) {' . "\n";
        $floatingCode .= '                iframe.style.display = "none"; isOpen = false;' . "\n";
        $floatingCode .= '            }' . "\n";
        $floatingCode .= '        });' . "\n";
        $floatingCode .= '    }' . "\n";
        $floatingCode .= '})();' . "\n";
        $floatingCode .= '</script>';
        
        // ================================================================
        // تولید کد iFrame
        // ================================================================
        $iframeCode = '';
        
        if ($iframePosition === 'center') {
            $iframeCode = '<!-- لومینا - ویجت چت ' . $title . ' -->' . "\n";
            $iframeCode .= '<div style="display: flex; justify-content: center; align-items: center; height: auto; padding: 20px 15px; background: transparent; width: 100%; box-sizing: border-box; overflow: hidden;">' . "\n";
            $iframeCode .= '    <div style="width: ' . $iframeWidth . '; max-width: 100%; height: ' . $iframeHeight . 'px;">' . "\n";
            $iframeCode .= '        <iframe ' . "\n";
            $iframeCode .= '            src="' . $widgetUrl . '"' . "\n";
            $iframeCode .= '            width="100%"' . "\n";
            $iframeCode .= '            height="100%"' . "\n";
            $iframeCode .= '            frameborder="0"' . "\n";
            $iframeCode .= '            style="border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); display: block; overflow: hidden;"' . "\n";
            $iframeCode .= '            title="' . $title . '"' . "\n";
            $iframeCode .= '            scrolling="no">' . "\n";
            $iframeCode .= '        </iframe>' . "\n";
            $iframeCode .= '    </div>' . "\n";
            $iframeCode .= '</div>';
        } else {
            $positionStyle = '';
            switch($iframePosition) {
                case 'bottom-right': $positionStyle = 'position: fixed; bottom: 20px; right: 20px;'; break;
                case 'bottom-left': $positionStyle = 'position: fixed; bottom: 20px; left: 20px;'; break;
                case 'top-right': $positionStyle = 'position: fixed; top: 20px; right: 20px;'; break;
                case 'top-left': $positionStyle = 'position: fixed; top: 20px; left: 20px;'; break;
                default: $positionStyle = 'position: relative; margin: 0 auto;';
            }
            
            $iframeCode = '<!-- لومینا - ویجت چت ' . $title . ' -->' . "\n";
            $iframeCode .= '<div style="' . $positionStyle . ' width: ' . $iframeWidth . '; max-width: 100%; height: ' . $iframeHeight . 'px;">' . "\n";
            $iframeCode .= '    <iframe ' . "\n";
            $iframeCode .= '        src="' . $widgetUrl . '"' . "\n";
            $iframeCode .= '        width="100%"' . "\n";
            $iframeCode .= '        height="100%"' . "\n";
            $iframeCode .= '        frameborder="0"' . "\n";
            $iframeCode .= '        style="border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); display: block; overflow: hidden;"' . "\n";
            $iframeCode .= '        title="' . $title . '"' . "\n";
            $iframeCode .= '        scrolling="no">' . "\n";
            $iframeCode .= '    </iframe>' . "\n";
            $iframeCode .= '</div>';
        }

        $this->json([
            'success' => true,
            'floating_code' => $floatingCode,
            'iframe_code' => $iframeCode,
            'widget_url' => $widgetUrl
        ]);
    }

    // ============================================================
    // ذخیره تنظیمات ویجت
    // ============================================================
    public function updateWidgetSettings()
    {
        $admin = $this->getCurrentAdmin();
        if ($admin['role'] !== 'moderator') {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        error_log("updateWidgetSettings - Input: " . json_encode($input));
        
        $serviceId = $input['service_id'] ?? null;
        $settings = $input['settings'] ?? [];

        if (!$serviceId || empty($settings)) {
            $this->json(['success' => false, 'error' => 'اطلاعات ناقص است'], 400);
            return;
        }

        $service = $this->serviceModel->getServiceById($serviceId);
        if (!$service || $service['customer_id'] != $admin['customer_id']) {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
            return;
        }

        // به‌روزرسانی عنوان و پیام خوشامدگویی
        $updateData = [];
        
        if (isset($settings['title'])) {
            $updateData['title'] = trim($settings['title']);
        }
        
        if (isset($settings['welcome_message'])) {
            $updateData['welcome_message'] = trim($settings['welcome_message']);
        } elseif (isset($settings['welcomeMessage'])) {
            $updateData['welcome_message'] = trim($settings['welcomeMessage']);
        }
        
        error_log("updateWidgetSettings - updateData: " . json_encode($updateData));
        
        $result1 = false;
        if (!empty($updateData)) {
            $result1 = $this->serviceModel->updateService($serviceId, $updateData);
            error_log("updateWidgetSettings - updateService result: " . ($result1 ? 'true' : 'false'));
        }

        // به‌روزرسانی تنظیمات ویجت
        $widgetSettings = [
            'primaryColor' => $settings['primaryColor'] ?? '#667eea',
            'buttonColor' => $settings['buttonColor'] ?? '#28a745',
            'floatingPosition' => $settings['floatingPosition'] ?? 'bottom-right',
            'iframeWidth' => $settings['iframeWidth'] ?? '500px',
            'iframeHeight' => $settings['iframeHeight'] ?? '550',
            'iframePosition' => $settings['iframePosition'] ?? 'center'
        ];
        
        $result2 = $this->serviceModel->updateWidgetSettings($serviceId, $widgetSettings);
        error_log("updateWidgetSettings - updateWidgetSettings result: " . ($result2 ? 'true' : 'false'));

        if ($result1 || $result2) {
            $this->json(['success' => true, 'message' => 'تنظیمات ویجت با موفقیت ذخیره شد']);
        } else {
            // اگر هیچ تغییری اعمال نشد، اما خطایی هم نبود
            $this->json(['success' => true, 'message' => 'تنظیمات با موفقیت ذخیره شد']);
        }
    }
    
    // ============================================================
    // دریافت لیست کاربران برای گزارش استفاده (AJAX)
    // ============================================================
    public function getUsersListAjax()
    {
        $admin = $this->getCurrentAdmin();
        if ($admin['role'] !== 'moderator') {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
            return;
        }

        try {
            $db = Database::getInstance();
            $customerId = $admin['customer_id'];
            
            if (empty($customerId)) {
                $this->json(['success' => true, 'users' => [], 'count' => 0]);
                return;
            }
            
            // دریافت لیست کاربران با تعداد چت‌ها
            $users = $db->fetchAll(
                "SELECT 
                    u.id AS user_id,
                    u.session_id,
                    u.widget_id,
                    u.created_at AS user_created_at,
                    u.last_activity,
                    u.is_active,
                    COUNT(c.id) AS chat_count,
                    MAX(c.created_at) AS last_chat_date,
                    MIN(c.created_at) AS first_chat_date
                FROM Users u
                LEFT JOIN Chats c ON u.id = c.user_id
                WHERE u.customer_id = ?
                GROUP BY u.id
                ORDER BY chat_count DESC, u.last_activity DESC",
                [$customerId]
            );
            
            // اضافه کردن اطلاعات تکمیلی برای هر کاربر
            foreach ($users as &$user) {
                $user['user_identifier'] = $user['session_id'] ?? 'کاربر #' . $user['user_id'];
                $user['user_display_name'] = 'کاربر ' . $user['user_id'];
                
                // محاسبه تعداد روزهای فعالیت
                if ($user['first_chat_date'] && $user['last_chat_date']) {
                    $first = strtotime($user['first_chat_date']);
                    $last = strtotime($user['last_chat_date']);
                    $days = ceil(($last - $first) / (60 * 60 * 24)) + 1;
                    $user['active_days'] = $days;
                } else {
                    $user['active_days'] = 0;
                }
            }
            
            $this->json([
                'success' => true,
                'users' => $users,
                'count' => count($users),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            error_log("getUsersListAjax error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ============================================================
    // دریافت چت‌های یک کاربر خاص (AJAX)
    // ============================================================
    public function getUserChatsAjax()
    {
        $admin = $this->getCurrentAdmin();
        if ($admin['role'] !== 'moderator') {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
            return;
        }

        $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = isset($_GET['limit']) ? min(100, max(10, intval($_GET['limit']))) : 50;
        $offset = ($page - 1) * $limit;
        $format = isset($_GET['format']) ? $_GET['format'] : 'json';

        if (!$userId) {
            $this->json(['success' => false, 'error' => 'شناسه کاربر الزامی است'], 400);
            return;
        }

        try {
            $db = Database::getInstance();
            $customerId = $admin['customer_id'];
            
            // بررسی می‌کنیم که این کاربر متعلق به همین مشتری باشد
            $check = $db->fetch(
                "SELECT u.id FROM Users u WHERE u.id = ? AND u.customer_id = ?",
                [$userId, $customerId]
            );
            
            if (!$check) {
                $this->json(['success' => false, 'error' => 'دسترسی به چت‌های این کاربر مجاز نیست'], 403);
                return;
            }
            
            // دریافت اطلاعات کاربر
            $userInfo = $db->fetch(
                "SELECT id, session_id, widget_id, created_at, last_activity, is_active 
                FROM Users WHERE id = ?",
                [$userId]
            );
            
            // دریافت تعداد کل چت‌ها
            $countResult = $db->fetch(
                "SELECT COUNT(*) as total FROM Chats WHERE user_id = ?",
                [$userId]
            );
            $totalChats = $countResult['total'] ?? 0;
            
            // دریافت چت‌ها با صفحه‌بندی
            $chats = $db->fetchAll(
                "SELECT 
                    id,
                    user_id,
                    chat_user,
                    chat_bot,
                    message_type,
                    created_at
                FROM Chats
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?",
                [$userId, $limit, $offset]
            );
            
            // محاسبه آمار کاربر
            $stats = $db->fetch(
                "SELECT 
                    COUNT(*) as total_chats,
                    COUNT(DISTINCT DATE(created_at)) as active_days,
                    MIN(created_at) as first_chat,
                    MAX(created_at) as last_chat
                FROM Chats WHERE user_id = ?",
                [$userId]
            );
            
            // خروجی اکسل برای یک کاربر
            if ($format === 'excel') {
                // دریافت تمام چت‌ها برای اکسل
                $allChats = $db->fetchAll(
                    "SELECT 
                        id,
                        chat_user,
                        chat_bot,
                        message_type,
                        created_at
                    FROM Chats 
                    WHERE user_id = ?
                    ORDER BY created_at ASC",
                    [$userId]
                );
                
                $this->exportUserChatsExcel($userId, $userInfo, $allChats);
                return;
            }
            
            $response = [
                'success' => true,
                'user' => $userInfo,
                'stats' => [
                    'total_chats' => intval($stats['total_chats'] ?? 0),
                    'active_days' => intval($stats['active_days'] ?? 0),
                    'first_chat' => $stats['first_chat'] ?? null,
                    'last_chat' => $stats['last_chat'] ?? null
                ],
                'chats' => $chats,
                'total' => intval($totalChats),
                'page' => $page,
                'per_page' => $limit,
                'total_pages' => ceil($totalChats / $limit)
            ];
            
            $this->json($response);
            
        } catch (Exception $e) {
            error_log("getUserChatsAjax error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ============================================================
    // خروجی اکسل چت‌های یک کاربر
    // ============================================================
    private function exportUserChatsExcel($userId, $userInfo, $chats)
    {
        // تنظیم هدرها برای دانلود
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="chats_user_' . $userId . '_' . date('Y-m-d') . '.xlsx"');
        
        // استفاده از کتابخانه PhpSpreadsheet
        // مسیر صحیح را برای پروژه خود تنظیم کنید
        $vendorPath = ROOT_PATH . '/vendor/autoload.php';
        if (file_exists($vendorPath)) {
            require_once $vendorPath;
        } else {
            // اگر PhpSpreadsheet نصب نیست، از روش ساده CSV استفاده کن
            $this->exportUserChatsCsv($userId, $userInfo, $chats);
            return;
        }
        
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // تنظیم عنوان
            $sheet->setCellValue('A1', 'گزارش چت‌های کاربر');
            $sheet->mergeCells('A1:E1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            
            // اطلاعات کاربر
            $row = 3;
            $sheet->setCellValue('A' . $row, 'شناسه کاربر:');
            $sheet->setCellValue('B' . $row, $userId);
            $sheet->setCellValue('D' . $row, 'Session ID:');
            $sheet->setCellValue('E' . $row, $userInfo['session_id'] ?? '-');
            
            $row++;
            $sheet->setCellValue('A' . $row, 'تعداد چت‌ها:');
            $sheet->setCellValue('B' . $row, count($chats));
            $sheet->setCellValue('D' . $row, 'تاریخ ایجاد:');
            $sheet->setCellValue('E' . $row, $userInfo['created_at'] ?? '-');
            
            $row += 2;
            
            // هدرهای جدول
            $sheet->setCellValue('A' . $row, 'شناسه چت');
            $sheet->setCellValue('B' . $row, 'پیام کاربر');
            $sheet->setCellValue('C' . $row, 'پاسخ ربات');
            $sheet->setCellValue('D' . $row, 'نوع');
            $sheet->setCellValue('E' . $row, 'زمان');
            
            $headerStyle = $sheet->getStyle('A' . $row . ':E' . $row);
            $headerStyle->getFont()->setBold(true);
            $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE9ECEF');
            
            $row++;
            
            // داده‌ها
            foreach ($chats as $chat) {
                $sheet->setCellValue('A' . $row, $chat['id']);
                $sheet->setCellValue('B' . $row, $chat['chat_user'] ?? '');
                $sheet->setCellValue('C' . $row, $chat['chat_bot'] ?? '');
                $sheet->setCellValue('D' . $row, $chat['message_type'] ?? 'text');
                $sheet->setCellValue('E' . $row, $chat['created_at']);
                $row++;
            }
            
            // تنظیم عرض ستون‌ها
            $sheet->getColumnDimension('A')->setWidth(12);
            $sheet->getColumnDimension('B')->setWidth(60);
            $sheet->getColumnDimension('C')->setWidth(60);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(25);
            
            // تنظیم auto filter
            $sheet->setAutoFilter('A' . ($row - count($chats) - 1) . ':E' . ($row - 1));
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            
        } catch (Exception $e) {
            error_log("exportUserChatsExcel error: " . $e->getMessage());
            // در صورت خطا، به CSV برگرد
            $this->exportUserChatsCsv($userId, $userInfo, $chats);
        }
        exit;
    }
    
    // ============================================================
    // خروجی CSV چت‌های یک کاربر (در صورت نبود PhpSpreadsheet)
    // ============================================================
    private function exportUserChatsCsv($userId, $userInfo, $chats)
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="chats_user_' . $userId . '_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM برای UTF-8
        
        // هدر
        fputcsv($output, ['شناسه چت', 'پیام کاربر', 'پاسخ ربات', 'نوع', 'زمان']);
        
        // داده‌ها
        foreach ($chats as $chat) {
            fputcsv($output, [
                $chat['id'],
                $chat['chat_user'] ?? '',
                $chat['chat_bot'] ?? '',
                $chat['message_type'] ?? 'text',
                $chat['created_at']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    // ============================================================
    // توابع کمکی
    // ============================================================
    private function formatPersianDate($date)
    {
        if (!$date) return '-';
        $timestamp = strtotime($date);
        if (function_exists('jdate')) {
            return jdate('Y/m/d', $timestamp);
        }
        return date('Y/m/d', $timestamp);
    }
}