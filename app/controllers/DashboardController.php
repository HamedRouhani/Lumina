<?php
// app/controllers/DashboardController.php
class DashboardController extends Controller
{
    private $adminModel;
    private $customerModel;
    private $serviceModel;
    private $chatModel;
    private $subscriptionModel;
    
    public function __construct()
    {
        if (method_exists(get_parent_class($this), '__construct')) {
            parent::__construct();
        }
        
        $this->adminModel = new AdminModel();
        $this->customerModel = new CustomerModel();
        $this->serviceModel = new ServiceModel();
        $this->chatModel = new ChatModel();
        $this->subscriptionModel = new ServiceSubscriptionModel();
    }
    
    // نمایش داشبورد
    public function index()
    {
        $admin = $this->getCurrentAdmin();
        if (!$admin) {
            $this->redirect('login');
            return;
        }
        
        // دریافت آمار بر اساس نقش کاربر
        $stats = $this->getStatsByRole($admin);
        
        $this->view('dashboard.index', [
            'title' => 'داشبورد',
            'admin' => $admin,
            'stats' => $stats
        ]);
    }
    
    // دریافت آمار برای AJAX (رفرش实时)
    public function getStats()
    {
        $admin = $this->getCurrentAdmin();
        if (!$admin) {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 401);
            return;
        }
        
        try {
            $stats = $this->getStatsByRole($admin);
            $this->json([
                'success' => true,
                'stats' => $stats,
                'admin' => [
                    'full_name' => $admin['full_name'],
                    'role' => $admin['role'],
                    'role_name' => $this->getRoleName($admin['role'])
                ]
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // دریافت آمار بر اساس نقش کاربر
    private function getStatsByRole($admin)
    {
        $db = Database::getInstance();
        $stats = [];
        
        if (in_array($admin['role'], ['super_admin', 'admin'])) {
            // آمار کامل برای مدیران
            
            // تعداد مشتریان
            $result = $db->fetch("SELECT COUNT(*) as total FROM Customers");
            $totalCustomers = $result['total'] ?? 0;
            
            // تعداد سرویس‌ها
            $result = $db->fetch("SELECT COUNT(*) as total FROM Services");
            $totalServices = $result['total'] ?? 0;
            
            // تعداد کاربران فعال
            $result = $db->fetch("SELECT COUNT(*) as total FROM Admins WHERE is_active = 1");
            $totalUsers = $result['total'] ?? 0;
            
            // تعداد کل چت‌ها
            $result = $db->fetch("SELECT COUNT(*) as total FROM Chats");
            $totalChats = $result['total'] ?? 0;
            
            // چت‌های امروز
            $result = $db->fetch("SELECT COUNT(*) as total FROM Chats WHERE DATE(created_at) = CURDATE()");
            $todayChats = $result['total'] ?? 0;
            
            // سرویس‌های فعال
            $result = $db->fetch("SELECT COUNT(*) as total FROM Services WHERE is_active = 1");
            $activeServices = $result['total'] ?? 0;
            
            // اشتراک‌های فعال
            $result = $db->fetch("SELECT COUNT(*) as total FROM ServiceSubscriptions WHERE is_active = 1");
            $activeSubscriptions = $result['total'] ?? 0;
            
            // فعالیت‌های اخیر
            $recentActivities = $db->fetchAll(
                "SELECT 
                    c.id,
                    c.chat_user,
                    c.chat_bot,
                    c.created_at,
                    u.session_id,
                    s.title as service_title,
                    s.service_code,
                    cust.company_name
                FROM Chats c
                JOIN Users u ON c.user_id = u.id
                JOIN Services s ON u.service_id = s.id
                LEFT JOIN Customers cust ON s.customer_id = cust.id
                ORDER BY c.created_at DESC
                LIMIT 10"
            );
            
            // آمار چت‌های هفتگی
            $weeklyStats = $db->fetchAll(
                "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as count
                FROM Chats
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC"
            );
            
            $dates = [];
            $counts = [];
            foreach ($weeklyStats as $row) {
                $dates[] = $this->formatPersianDate($row['date']);
                $counts[] = $row['count'];
            }
            
            $stats = [
                'total_customers' => $totalCustomers,
                'total_services' => $totalServices,
                'total_users' => $totalUsers,
                'total_chats' => $totalChats,
                'today_chats' => $todayChats,
                'active_services' => $activeServices,
                'active_subscriptions' => $activeSubscriptions,
                'recent_activities' => $recentActivities,
                'chat_stats' => [
                    'dates' => $dates,
                    'counts' => $counts
                ]
            ];
            
        } elseif ($admin['role'] === 'moderator' && $admin['customer_id']) {
            // آمار محدود برای ناظران (فقط مربوط به مشتری خودش)
            $customerId = $admin['customer_id'];
            
            // تعداد سرویس‌های مشتری
            $result = $db->fetch("SELECT COUNT(*) as total FROM Services WHERE customer_id = ?", [$customerId]);
            $totalServices = $result['total'] ?? 0;
            
            // تعداد کل چت‌های مشتری
            $result = $db->fetch(
                "SELECT COUNT(c.id) as total 
                FROM Chats c
                JOIN Users u ON c.user_id = u.id
                JOIN Services s ON u.service_id = s.id
                WHERE s.customer_id = ?",
                [$customerId]
            );
            $totalChats = $result['total'] ?? 0;
            
            // چت‌های امروز مشتری
            $result = $db->fetch(
                "SELECT COUNT(c.id) as total 
                FROM Chats c
                JOIN Users u ON c.user_id = u.id
                JOIN Services s ON u.service_id = s.id
                WHERE s.customer_id = ? AND DATE(c.created_at) = CURDATE()",
                [$customerId]
            );
            $todayChats = $result['total'] ?? 0;
            
            // سرویس‌های فعال مشتری
            $result = $db->fetch(
                "SELECT COUNT(*) as total FROM Services WHERE customer_id = ? AND is_active = 1",
                [$customerId]
            );
            $activeServices = $result['total'] ?? 0;
            
            // فعالیت‌های اخیر مشتری
            $recentActivities = $db->fetchAll(
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
                LIMIT 10",
                [$customerId]
            );
            
            // آمار چت‌های هفتگی مشتری
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
            
            // وضعیت اشتراک‌ها
            $subscriptionStatus = $db->fetch(
                "SELECT 
                    COUNT(*) as total_services,
                    SUM(CASE WHEN ss.is_active = 1 THEN 1 ELSE 0 END) as active_subscriptions,
                    SUM(CASE WHEN ss.is_active = 0 AND ss.plan_id > 0 THEN 1 ELSE 0 END) as expired_subscriptions,
                    SUM(CASE WHEN ss.plan_id = 0 OR ss.plan_id IS NULL THEN 1 ELSE 0 END) as no_subscription
                FROM Services s
                LEFT JOIN ServiceSubscriptions ss ON s.id = ss.service_id
                WHERE s.customer_id = ?",
                [$customerId]
            );
            
            $stats = [
                'total_services' => $totalServices,
                'total_chats' => $totalChats,
                'today_chats' => $todayChats,
                'active_services' => $activeServices,
                'recent_activities' => $recentActivities,
                'chat_stats' => [
                    'dates' => $dates,
                    'counts' => $counts
                ],
                'subscription_status' => $subscriptionStatus
            ];
        } else {
            // کاربر بدون مشتری
            $stats = [
                'message' => 'شما به هیچ مشتری متصل نیستید',
                'total_services' => 0,
                'total_chats' => 0
            ];
        }
        
        return $stats;
    }
    
    // ==================== توابع کمکی ====================
    
    private function getRoleName($role)
    {
        $roles = [
            'super_admin' => 'مدیر کل',
            'admin' => 'مدیر',
            'moderator' => 'ناظر'
        ];
        return $roles[$role] ?? $role;
    }
    
    private function formatPersianDate($date)
    {
        if (!$date) return '-';
        $timestamp = strtotime($date);
        if (function_exists('jdate')) {
            return jdate('Y/m/d', $timestamp);
        }
        return date('Y/m/d', $timestamp);
    }

    // دریافت فعالیت‌ها با صفحه‌بندی
    public function getActivities()
    {
        $admin = $this->getCurrentAdmin();
        
        // لاگ برای دیباگ
        error_log("DashboardController::getActivities called by user: " . ($admin['username'] ?? 'guest'));
        
        if (!$admin) {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 401);
            return;
        }
        
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = isset($_GET['limit']) ? min(50, max(10, intval($_GET['limit']))) : 20;
        $offset = ($page - 1) * $limit;
        
        try {
            $db = Database::getInstance();
            
            // شرط فیلتر بر اساس نقش
            $whereClause = "";
            $params = [];
            
            if ($admin['role'] === 'moderator' && !empty($admin['customer_id'])) {
                $whereClause = "WHERE s.customer_id = ?";
                $params[] = $admin['customer_id'];
            }
            
            // دریافت تعداد کل رکوردها
            $countSql = "SELECT COUNT(c.id) as total 
                        FROM Chats c
                        JOIN Users u ON c.user_id = u.id
                        JOIN Services s ON u.service_id = s.id
                        {$whereClause}";
            
            $totalResult = $db->fetch($countSql, $params);
            $totalRecords = $totalResult['total'] ?? 0;
            $totalPages = ceil($totalRecords / $limit);
            
            // دریافت فعالیت‌ها با صفحه‌بندی
            $sql = "SELECT 
                        c.id,
                        c.chat_user,
                        c.chat_bot,
                        c.created_at,
                        s.title as service_title,
                        s.service_code,
                        cust.company_name
                    FROM Chats c
                    JOIN Users u ON c.user_id = u.id
                    JOIN Services s ON u.service_id = s.id
                    LEFT JOIN Customers cust ON s.customer_id = cust.id
                    {$whereClause}
                    ORDER BY c.created_at DESC
                    LIMIT ? OFFSET ?";
            
            $queryParams = array_merge($params, [$limit, $offset]);
            $activities = $db->fetchAll($sql, $queryParams);
            
            $this->json([
                'success' => true,
                'activities' => $activities,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total_records' => $totalRecords,
                    'total_pages' => $totalPages,
                    'has_prev' => $page > 1,
                    'has_next' => $page < $totalPages
                ]
            ]);
        } catch (Exception $e) {
            error_log("Error in getActivities: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // دریافت جزئیات کامل یک فعالیت
    public function getActivityDetail($id)
    {
        $admin = $this->getCurrentAdmin();
        
        if (!$admin) {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 401);
            return;
        }
        
        try {
            $db = Database::getInstance();
            
            $sql = "SELECT 
                        c.id,
                        c.chat_user,
                        c.chat_bot,
                        c.created_at,
                        u.session_id,
                        u.widget_id,
                        s.id as service_id,
                        s.title as service_title,
                        s.service_code,
                        s.url as service_url,
                        cust.id as customer_id,
                        cust.full_name as customer_name,
                        cust.company_name
                    FROM Chats c
                    JOIN Users u ON c.user_id = u.id
                    JOIN Services s ON u.service_id = s.id
                    LEFT JOIN Customers cust ON s.customer_id = cust.id
                    WHERE c.id = ?";
            
            $activity = $db->fetch($sql, [$id]);
            
            if (!$activity) {
                $this->json(['success' => false, 'error' => 'فعالیت یافت نشد'], 404);
                return;
            }
            
            // بررسی دسترسی
            if ($admin['role'] === 'moderator' && !empty($admin['customer_id'])) {
                if ($activity['customer_id'] != $admin['customer_id']) {
                    $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
                    return;
                }
            }
            
            $this->json([
                'success' => true,
                'activity' => $activity
            ]);
        } catch (Exception $e) {
            error_log("Error in getActivityDetail: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}