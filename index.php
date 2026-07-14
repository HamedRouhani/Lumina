<?php
// index.php - ورودی اصلی اپلیکیشن mylumina

// فعال کردن نمایش خطاها برای دیباگ
error_reporting(E_ALL);
ini_set('display_errors', 1);

// شروع session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تنظیم charset
header('Content-Type: text/html; charset=utf-8');

// ==================== تعریف مسیرها ====================
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', dirname(__FILE__));
define('APP_PATH', ROOT_PATH . DS . 'app');

// ==================== Autoloader ====================
spl_autoload_register(function ($className) {
    $paths = [
        APP_PATH . DS . 'core' . DS . $className . '.php',
        APP_PATH . DS . 'controllers' . DS . $className . '.php',
        APP_PATH . DS . 'models' . DS . $className . '.php',
        APP_PATH . DS . 'middleware' . DS . $className . '.php',
        APP_PATH . DS . 'helpers' . DS . $className . '.php'
    ];
    
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ==================== بارگذاری فایل functions ====================
if (file_exists(APP_PATH . DS . 'helpers' . DS . 'functions.php')) {
    require_once APP_PATH . DS . 'helpers' . DS . 'functions.php';
}

// ==================== بارگذاری widget_helper ====================
if (file_exists(APP_PATH . DS . 'helpers' . DS . 'widget_helper.php')) {
    require_once APP_PATH . DS . 'helpers' . DS . 'widget_helper.php';
}

// ==================== بارگذاری متغیرهای محیطی ====================
$envFile = ROOT_PATH . DS . '.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, '"\'');
        
        if (!isset($_ENV[$key])) {
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}

// تنظیمات پیش‌فرض
date_default_timezone_set('Asia/Tehran');

// ==================== مسیریابی ====================
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

// حذف base path از URL
$basePath = '/mylumina';
if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}
$requestUri = trim($requestUri, '/');

// جدا کردن مسیر از query string
if (strpos($requestUri, '?') !== false) {
    $requestUri = substr($requestUri, 0, strpos($requestUri, '?'));
}

// متد درخواست
$method = $_SERVER['REQUEST_METHOD'];

// ==================== توابع کمکی ====================
function requireLogin() {
    if (!isset($_SESSION['admin'])) {
        header('Location: /mylumina/login');
        exit;
    }
}

function requireRole($roles) {
    requireLogin();
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    if (!in_array($_SESSION['admin']['role'], $roles)) {
        header('Location: /mylumina/dashboard');
        exit;
    }
}

// ==================== مسیرهای عمومی (بدون احراز هویت) ====================

// تست دیتابیس
if ($requestUri === 'test-db') {
    try {
        $db = Database::getInstance();
        $result = $db->fetch("SELECT 1 as test, NOW() as time, DATABASE() as db");
        echo "<pre>";
        echo "✅ Database connected successfully!\n";
        echo "Database: " . ($result['db'] ?? 'unknown') . "\n";
        echo "Time: " . ($result['time'] ?? 'unknown') . "\n";
        echo "</pre>";
    } catch (Exception $e) {
        echo "❌ Database error: " . $e->getMessage();
    }
    exit;
}

// خروج از سیستم
if ($requestUri === 'logout') {
    session_destroy();
    header('Location: /mylumina/login');
    exit;
}

// ==================== ویجت چت (مسیرهای عمومی) ====================

// ویجت چت (دکمه شناور) - آدرس: /widget
if ($requestUri === 'widget') {
    $controller = new WidgetController();
    $controller->chatWidget();
    exit;
}

// ویجت چت به صورت iFrame - آدرس: /widget-inline
if ($requestUri === 'widget-inline') {
    $controller = new WidgetController();
    $controller->chatInline();
    exit;
}

// آپدیت تنظیمات ویجت توسط ناظر
if ($requestUri === 'api/moderator/widget-settings' && $method === 'POST') {
    $controller = new ModeratorController();
    $controller->updateWidgetSettings();
    exit;
}

// تولید کد ویجت با تنظیمات سفارشی
if ($requestUri === 'api/moderator/generate-widget-code' && $method === 'POST') {
    $controller = new ModeratorController();
    $controller->generateCustomWidgetCode();
    exit;
}

// ==================== API Routes (عمومی) ====================

// API اطلاعات سرویس برای ویجت
if ($requestUri === 'api/widgets/service-info' && $method === 'GET') {
    $controller = new WidgetController();
    $controller->serviceInfo();
    exit;
}

// API استریم چت (مهم - برای ویجت)
if ($requestUri === 'api/widgets/chat-stream' && $method === 'POST') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Content-Type');
    
    $controller = new WidgetController();
    $controller->chatStream();
    exit;
}

// API تاریخچه چت
if ($requestUri === 'api/widgets/chat-history' && $method === 'POST') {
    $controller = new WidgetController();
    $controller->chatHistory();
    exit;
}

// ==================== API Routes - مشتریان ====================

// دریافت لیست مشتریان
if ($requestUri === 'api/customers/data' && $method === 'GET') {
    $controller = new CustomerController();
    $controller->getData();
    exit;
}

// ایجاد مشتری جدید
if ($requestUri === 'api/customers' && $method === 'POST') {
    $controller = new CustomerController();
    $controller->create();
    exit;
}

// دریافت یک مشتری
if (preg_match('/^api\/customers\/(\d+)$/', $requestUri, $matches) && $method === 'GET') {
    $controller = new CustomerController();
    $controller->show($matches[1]);
    exit;
}

// به‌روزرسانی مشتری
if (preg_match('/^api\/customers\/(\d+)$/', $requestUri, $matches) && $method === 'PUT') {
    $controller = new CustomerController();
    $controller->update($matches[1]);
    exit;
}

// حذف/غیرفعال کردن مشتری
if (preg_match('/^api\/customers\/(\d+)$/', $requestUri, $matches) && $method === 'DELETE') {
    $controller = new CustomerController();
    $controller->delete($matches[1]);
    exit;
}

// ==================== API Routes - سرویس‌ها ====================

// دریافت لیست سرویس‌ها
if ($requestUri === 'api/services/data' && $method === 'GET') {
    $controller = new ServiceController();
    $controller->getData();
    exit;
}

// دریافت لیست مشتریان برای سرویس
if ($requestUri === 'api/services/customers' && $method === 'GET') {
    $controller = new ServiceController();
    $controller->getCustomers();
    exit;
}

// دریافت یک سرویس
if (preg_match('/^api\/services\/(\d+)$/', $requestUri, $matches) && $method === 'GET') {
    $controller = new ServiceController();
    $controller->show($matches[1]);
    exit;
}

// ایجاد سرویس جدید
if ($requestUri === 'api/services' && $method === 'POST') {
    $controller = new ServiceController();
    $controller->create();
    exit;
}

// به‌روزرسانی سرویس
if (preg_match('/^api\/services\/(\d+)$/', $requestUri, $matches) && $method === 'PUT') {
    $controller = new ServiceController();
    $controller->update($matches[1]);
    exit;
}

// تغییر وضعیت سرویس
if (preg_match('/^api\/services\/(\d+)\/toggle$/', $requestUri, $matches) && $method === 'POST') {
    $controller = new ServiceController();
    $controller->toggleStatus($matches[1]);
    exit;
}

// دریافت کد ویجت
if (preg_match('/^api\/services\/(\d+)\/widget-code$/', $requestUri, $matches) && $method === 'GET') {
    $controller = new ServiceController();
    $controller->getWidgetCode($matches[1]);
    exit;
}

// تغییر مشتری سرویس
if (preg_match('/^api\/services\/(\d+)\/change-customer$/', $requestUri, $matches) && $method === 'POST') {
    $controller = new ServiceController();
    $controller->changeCustomer($matches[1]);
    exit;
}

// ==================== API Routes - کاربران (ادمین) ====================

// دریافت لیست کاربران
if ($requestUri === 'api/admin/users/data' && $method === 'GET') {
    $controller = new AdminController();
    $controller->getData();
    exit;
}

// دریافت لیست مشتریان برای فرم کاربر
if ($requestUri === 'api/admin/customers' && $method === 'GET') {
    $controller = new AdminController();
    $controller->getCustomers();
    exit;
}

// ایجاد کاربر جدید
if ($requestUri === 'api/admin/users' && $method === 'POST') {
    $controller = new AdminController();
    $controller->create();
    exit;
}

// به‌روزرسانی کاربر
if (preg_match('/^api\/admin\/users\/(\d+)$/', $requestUri, $matches) && $method === 'PUT') {
    $controller = new AdminController();
    $controller->update($matches[1]);
    exit;
}

// تغییر وضعیت کاربر
if (preg_match('/^api\/admin\/users\/(\d+)\/toggle$/', $requestUri, $matches) && $method === 'POST') {
    $controller = new AdminController();
    $controller->toggleStatus($matches[1]);
    exit;
}

// تغییر رمز عبور
if (preg_match('/^api\/admin\/users\/(\d+)\/change-password$/', $requestUri, $matches) && $method === 'POST') {
    $controller = new AdminController();
    $controller->changePassword($matches[1]);
    exit;
}

// آمار دشبورد
if ($requestUri === 'api/admin/stats' && $method === 'GET') {
    $controller = new AdminController();
    $controller->stats();
    exit;
}

// ==================== API Routes - طرح‌های اشتراک ====================

// دریافت لیست طرح‌ها
if ($requestUri === 'api/subscription-plans/data' && $method === 'GET') {
    $controller = new SubscriptionPlanController();
    $controller->getData();
    exit;
}

// دریافت یک طرح
if (preg_match('/^api\/subscription-plans\/(\d+)$/', $requestUri, $matches) && $method === 'GET') {
    $controller = new SubscriptionPlanController();
    $controller->show($matches[1]);
    exit;
}

// ایجاد طرح جدید
if ($requestUri === 'api/subscription-plans' && $method === 'POST') {
    $controller = new SubscriptionPlanController();
    $controller->create();
    exit;
}

// به‌روزرسانی طرح
if (preg_match('/^api\/subscription-plans\/(\d+)$/', $requestUri, $matches) && $method === 'PUT') {
    $controller = new SubscriptionPlanController();
    $controller->update($matches[1]);
    exit;
}

// حذف طرح
if (preg_match('/^api\/subscription-plans\/(\d+)$/', $requestUri, $matches) && $method === 'DELETE') {
    $controller = new SubscriptionPlanController();
    $controller->delete($matches[1]);
    exit;
}

// تخصیص اشتراک به سرویس
if ($requestUri === 'api/subscription-plans/assign' && $method === 'POST') {
    $controller = new SubscriptionPlanController();
    $controller->assignToService();
    exit;
}

// دریافت اشتراک یک سرویس
if (preg_match('/^api\/subscription-plans\/service\/(\d+)$/', $requestUri, $matches) && $method === 'GET') {
    $controller = new SubscriptionPlanController();
    $controller->getServiceSubscription($matches[1]);
    exit;
}

// دریافت اشتراک‌های من (برای moderator)
if ($requestUri === 'api/subscription-plans/my' && $method === 'GET') {
    $controller = new SubscriptionPlanController();
    $controller->mySubscriptions();
    exit;
}

// تمدید اشتراک
if ($requestUri === 'api/subscription-plans/renew' && $method === 'POST') {
    $controller = new SubscriptionPlanController();
    $controller->renewSubscription();
    exit;
}

// ==================== API Routes - داشبورد و فعالیت‌ها ====================

// دریافت آمار داشبورد
if ($requestUri === 'api/dashboard/stats' && $method === 'GET') {
    $controller = new DashboardController();
    $controller->getStats();
    exit;
}

// دریافت فعالیت‌ها با صفحه‌بندی
if ($requestUri === 'api/dashboard/activities' && $method === 'GET') {
    $controller = new DashboardController();
    $controller->getActivities();
    exit;
}

// دریافت جزئیات یک فعالیت
if (preg_match('/^api\/dashboard\/activities\/(\d+)$/', $requestUri, $matches) && $method === 'GET') {
    $controller = new DashboardController();
    $controller->getActivityDetail($matches[1]);
    exit;
}

// ==================== API Routes - ناظر (moderator) ====================

// دریافت سرویس‌های ناظر
if ($requestUri === 'api/moderator/services' && $method === 'GET') {
    $controller = new ModeratorController();
    $controller->getServicesData();
    exit;
}

// دریافت کد ویجت برای ناظر
if (preg_match('/^api\/moderator\/widget-code\/(\d+)$/', $requestUri, $matches) && $method === 'GET') {
    $controller = new ModeratorController();
    $controller->getWidgetCode($matches[1]);
    exit;
}

// دریافت داده‌های گزارش استفاده
if ($requestUri === 'api/moderator/usage-data' && $method === 'GET') {
    $controller = new ModeratorController();
    $controller->getUsageData();
    exit;
}

// ========== دریافت لیست کاربران برای گزارش استفاده ==========
if ($requestUri === 'api/moderator/users-list' && $method === 'GET') {
    $controller = new ModeratorController();
    $controller->getUsersListAjax();
    exit;
}

// ========== دریافت چت‌های یک کاربر خاص ==========
if ($requestUri === 'api/moderator/user-chats' && $method === 'GET') {
    $controller = new ModeratorController();
    $controller->getUserChatsAjax();
    exit;
}

// ==================== صفحات مدیریت (ادمین) ====================

// مدیریت مشتریان
if ($requestUri === 'admin/customers') {
    requireRole(['super_admin', 'admin']);
    $controller = new CustomerController();
    $controller->index();
    exit;
}

// مدیریت کاربران
if ($requestUri === 'admin/users') {
    requireRole(['super_admin', 'admin']);
    $controller = new AdminController();
    $controller->index();
    exit;
}

// مدیریت سرویس‌ها
if ($requestUri === 'admin/services') {
    requireRole(['super_admin', 'admin']);
    $controller = new ServiceController();
    $controller->index();
    exit;
}

// مدیریت طرح‌های اشتراک
if ($requestUri === 'admin/subscription-plans') {
    requireRole(['super_admin', 'admin']);
    $controller = new SubscriptionPlanController();
    $controller->index();
    exit;
}

// ==================== صفحات ناظر (moderator) ====================

// سرویس‌های من
if ($requestUri === 'moderator/services') {
    requireRole(['moderator']);
    $controller = new ModeratorController();
    $controller->services();
    exit;
}

// کد ویجت
if ($requestUri === 'moderator/widget-code') {
    requireRole(['moderator']);
    $controller = new ModeratorController();
    $controller->widgetCode();
    exit;
}

// گزارش استفاده
if ($requestUri === 'moderator/usage-report') {
    requireRole(['moderator']);
    $controller = new ModeratorController();
    $controller->usageReport();
    exit;
}

// اشتراک‌های من (مشترک بین ادمین و ناظر)
if ($requestUri === 'subscriptions') {
    requireLogin();
    $controller = new SubscriptionPlanController();
    $controller->mySubscriptionsPage();
    exit;
}

// ==================== صفحات عمومی ====================

// داشبورد
if ($requestUri === 'dashboard') {
    requireLogin();
    $controller = new DashboardController();
    $controller->index();
    exit;
}

// صفحه لاگین
if ($requestUri === '' || $requestUri === 'login') {
    // اگر قبلاً لاگین کرده، به داشبورد برو
    if (isset($_SESSION['admin'])) {
        header('Location: /mylumina/dashboard');
        exit;
    }
    
    if ($method === 'POST') {
        // پردازش لاگین
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        try {
            $db = Database::getInstance();
            $passwordHash = base64_encode($password);
            
            $admin = $db->fetch(
                "SELECT * FROM Admins WHERE username = ? AND password_hash = ? AND is_active = 1",
                [$username, $passwordHash]
            );
            
            if ($admin) {
                $_SESSION['admin'] = [
                    'id' => $admin['id'],
                    'username' => $admin['username'],
                    'full_name' => $admin['full_name'],
                    'role' => $admin['role'],
                    'customer_id' => $admin['customer_id']
                ];
                
                $db->execute("UPDATE Admins SET last_login = NOW() WHERE id = ?", [$admin['id']]);
                
                header('Location: /mylumina/dashboard');
                exit;
            } else {
                $_SESSION['login_error'] = 'نام کاربری یا رمز عبور اشتباه است';
                header('Location: /mylumina/login');
                exit;
            }
        } catch (Exception $e) {
            $_SESSION['login_error'] = 'خطا در اتصال به دیتابیس: ' . $e->getMessage();
            header('Location: /mylumina/login');
            exit;
        }
    } else {
        // نمایش فرم لاگین
        $loginView = APP_PATH . DS . 'views' . DS . 'auth' . DS . 'login.php';
        if (!file_exists($loginView)) {
            die("فایل لاگین یافت نشد: " . $loginView);
        }
        require_once $loginView;
        exit;
    }
}

// ==================== 404 - صفحه یافت نشد ====================
http_response_code(404);

// بررسی درخواست AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'API مورد نظر یافت نشد',
        'request_uri' => $requestUri,
        'method' => $method
    ]);
} else {
    ?>
    <!DOCTYPE html>
    <html lang="fa" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>خطای 404 - صفحه یافت نشد</title>
        <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Vazirmatn', sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            .error-container {
                text-align: center;
                background: white;
                padding: 50px;
                border-radius: 20px;
                box-shadow: 0 15px 35px rgba(0,0,0,0.2);
                max-width: 500px;
                margin: 20px;
            }
            .error-code { font-size: 6rem; font-weight: bold; color: #667eea; margin-bottom: 20px; }
            .error-title { font-size: 1.5rem; color: #333; margin-bottom: 15px; }
            .error-message { color: #6c757d; margin-bottom: 30px; }
            .btn-home {
                display: inline-block;
                padding: 12px 30px;
                background: #667eea;
                color: white;
                text-decoration: none;
                border-radius: 8px;
                transition: all 0.3s;
            }
            .btn-home:hover { background: #5a67d8; transform: translateY(-2px); }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-code">404</div>
            <div class="error-title">صفحه مورد نظر یافت نشد</div>
            <div class="error-message">
                آدرس: /mylumina/<?php echo htmlspecialchars($requestUri); ?>
                <br>
                متد: <?php echo $method; ?>
            </div>
            <a href="/mylumina/dashboard" class="btn-home">بازگشت به داشبورد</a>
        </div>
    </body>
    </html>
    <?php
}
exit;