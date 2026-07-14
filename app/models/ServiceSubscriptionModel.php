<?php
// app/models/ServiceSubscriptionModel.php

class ServiceSubscriptionModel extends Model
{
    protected $table = 'ServiceSubscriptions';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * دریافت اشتراک یک سرویس به همراه اطلاعات کامل
     */
    public function getSubscriptionByServiceId($serviceId)
    {
        $db = Database::getInstance();
        
        // دریافت اطلاعات اشتراک
        $sql = "SELECT 
                    ss.*,
                    sp.name as plan_name,
                    sp.chat_limit,
                    sp.duration_days,
                    DATE_ADD(ss.start_date, INTERVAL sp.duration_days DAY) as expiry_date,
                    DATEDIFF(DATE_ADD(ss.start_date, INTERVAL sp.duration_days DAY), NOW()) as days_remaining,
                    CASE 
                        WHEN sp.chat_limit > 0 AND ss.chat_count >= sp.chat_limit THEN 0
                        WHEN sp.duration_days > 0 AND DATEDIFF(DATE_ADD(ss.start_date, INTERVAL sp.duration_days DAY), NOW()) < 0 THEN 0
                        ELSE ss.is_active
                    END as is_valid
                FROM ServiceSubscriptions ss
                LEFT JOIN SubscriptionPlans sp ON ss.plan_id = sp.id
                WHERE ss.service_id = ?";
        
        $subscription = $db->fetch($sql, [$serviceId]);
        
        if ($subscription) {
            // ============================================================
            // اصلاح: محاسبه تعداد واقعی چت‌ها از جدول Chats
            // ============================================================
            $realChatCount = $db->fetch(
                "SELECT COUNT(c.id) as total 
                FROM Chats c
                JOIN Users u ON c.user_id = u.id
                WHERE u.service_id = ?",
                [$serviceId]
            );
            $realChatCount = $realChatCount['total'] ?? 0;
            
            // اضافه کردن تعداد واقعی به خروجی
            $subscription['real_chat_count'] = $realChatCount;
            
            // به‌روزرسانی is_valid بر اساس تعداد واقعی
            if ($subscription['chat_limit'] > 0 && $realChatCount >= $subscription['chat_limit']) {
                $subscription['is_valid'] = 0;
            }
        }
        
        return $subscription;
    }
    
    /**
     * بررسی اعتبار اشتراک سرویس
     */
    public function isSubscriptionValid($serviceId)
    {
        $subscription = $this->getSubscriptionByServiceId($serviceId);
        
        // اگر اشتراکی وجود نداشت، سرویس بدون اشتراک است (نامحدود)
        if (!$subscription) {
            return true;
        }
        
        // بررسی فعال بودن
        if ($subscription['is_active'] != 1) {
            return false;
        }
        
        // بررسی محدودیت چت
        if ($subscription['chat_limit'] > 0 && $subscription['chat_count'] >= $subscription['chat_limit']) {
            return false;
        }
        
        // بررسی انقضای زمان
        if ($subscription['duration_days'] > 0 && $subscription['days_remaining'] < 0) {
            return false;
        }
        
        return true;
    }
    
    /**
     * دریافت وضعیت اشتراک به صورت متنی
     */
    public function getSubscriptionStatus($serviceId)
    {
        $subscription = $this->getSubscriptionByServiceId($serviceId);
        
        if (!$subscription) {
            return [
                'status' => 'no_subscription',
                'message' => 'بدون اشتراک (نامحدود)',
                'color' => 'warning'
            ];
        }
        
        if ($subscription['is_active'] != 1) {
            return [
                'status' => 'inactive',
                'message' => 'غیرفعال',
                'color' => 'danger'
            ];
        }
        
        if ($subscription['chat_limit'] > 0 && $subscription['chat_count'] >= $subscription['chat_limit']) {
            return [
                'status' => 'limit_exceeded',
                'message' => 'محدودیت چت تمام شده',
                'color' => 'danger'
            ];
        }
        
        if ($subscription['duration_days'] > 0 && $subscription['days_remaining'] < 0) {
            return [
                'status' => 'expired',
                'message' => 'منقضی شده',
                'color' => 'danger'
            ];
        }
        
        if ($subscription['duration_days'] > 0 && $subscription['days_remaining'] <= 7) {
            return [
                'status' => 'expiring_soon',
                'message' => 'در حال اتمام (' . $subscription['days_remaining'] . ' روز باقی مانده)',
                'color' => 'warning'
            ];
        }
        
        return [
            'status' => 'active',
            'message' => 'فعال',
            'color' => 'success'
        ];
    }
    
    /**
     * ایجاد اشتراک جدید برای سرویس
     */
    public function createSubscription($serviceId, $planId)
    {
        // حذف اشتراک قبلی
        $this->db->execute("DELETE FROM ServiceSubscriptions WHERE service_id = ?", [$serviceId]);
        
        $sql = "INSERT INTO ServiceSubscriptions 
                (service_id, plan_id, start_date, is_active, chat_count, created_at, updated_at) 
                VALUES (?, ?, NOW(), 1, 0, NOW(), NOW())";
        
        $this->db->insert($sql, [$serviceId, $planId]);
        
        return $this->getSubscriptionByServiceId($serviceId);
    }
    
    /**
     * به‌روزرسانی اشتراک سرویس
     */
    public function updateSubscription($serviceId, $planId)
    {
        $existing = $this->getSubscriptionByServiceId($serviceId);
        
        if ($existing) {
            $sql = "UPDATE ServiceSubscriptions 
                    SET plan_id = ?, start_date = NOW(), is_active = 1, chat_count = 0, updated_at = NOW() 
                    WHERE service_id = ?";
            $this->db->execute($sql, [$planId, $serviceId]);
        } else {
            $sql = "INSERT INTO ServiceSubscriptions 
                    (service_id, plan_id, start_date, is_active, chat_count, created_at, updated_at) 
                    VALUES (?, ?, NOW(), 1, 0, NOW(), NOW())";
            $this->db->insert($sql, [$serviceId, $planId]);
        }
        
        return $this->getSubscriptionByServiceId($serviceId);
    }
    
    /**
     * غیرفعال کردن اشتراک سرویس
     */
    public function deactivateSubscription($serviceId)
    {
        $sql = "UPDATE ServiceSubscriptions SET is_active = 0, updated_at = NOW() WHERE service_id = ?";
        return $this->db->execute($sql, [$serviceId]) > 0;
    }
    
    /**
     * افزایش تعداد چت‌های استفاده شده
     */
    public function incrementChatCount($serviceId)
    {
        $db = Database::getInstance();
        
        // ============================================================
        // ابتدا تعداد واقعی چت‌ها را از جدول Chats محاسبه کن
        // ============================================================
        $realChatCount = $db->fetch(
            "SELECT COUNT(c.id) as total 
            FROM Chats c
            JOIN Users u ON c.user_id = u.id
            WHERE u.service_id = ?",
            [$serviceId]
        );
        $realChatCount = $realChatCount['total'] ?? 0;
        
        // ============================================================
        // سپس مقدار chat_count در ServiceSubscriptions را با تعداد واقعی همگام کن
        // ============================================================
        $sql = "UPDATE ServiceSubscriptions 
                SET chat_count = ?, updated_at = NOW()
                WHERE service_id = ? AND is_active = 1";
        
        return $db->execute($sql, [$realChatCount, $serviceId]) > 0;
    }
    
    /**
     * تمدید اشتراک (ریست کردن چت‌ها و شروع مجدد)
     */
    public function renewSubscription($serviceId)
    {
        $sql = "UPDATE ServiceSubscriptions 
                SET chat_count = 0, start_date = NOW(), is_active = 1, updated_at = NOW() 
                WHERE service_id = ?";
        return $this->db->execute($sql, [$serviceId]) > 0;
    }
    
    /**
     * دریافت اشتراک‌های یک مشتری
     */
    public function getSubscriptionsByCustomer($customerId)
    {
        $sql = "SELECT 
                    s.id as service_id,
                    s.title as service_title,
                    s.service_code,
                    s.url,
                    s.created_at as service_created,
                    s.is_active as service_active,
                    ss.id as subscription_id,
                    ss.is_active as subscription_active,
                    ss.chat_count,
                    ss.start_date,
                    sp.name as plan_name,
                    sp.chat_limit,
                    sp.duration_days,
                    DATE_ADD(ss.start_date, INTERVAL sp.duration_days DAY) as expiry_date,
                    CASE 
                        WHEN sp.duration_days > 0 THEN
                            DATEDIFF(DATE_ADD(ss.start_date, INTERVAL sp.duration_days DAY), NOW())
                        ELSE NULL
                    END as days_remaining,
                    CASE 
                        WHEN sp.chat_limit > 0 THEN
                            GREATEST(0, sp.chat_limit - ss.chat_count)
                        ELSE NULL
                    END as remaining_chats
                FROM Services s
                LEFT JOIN ServiceSubscriptions ss ON s.id = ss.service_id
                LEFT JOIN SubscriptionPlans sp ON ss.plan_id = sp.id
                WHERE s.customer_id = ?
                ORDER BY s.created_at DESC";
        
        return $this->db->fetchAll($sql, [$customerId]);
    }
    
    /**
     * دریافت اشتراک‌های در حال انقضا
     */
    public function getExpiringSubscriptions($daysThreshold = 7)
    {
        $sql = "SELECT 
                    ss.*,
                    s.title as service_title,
                    s.customer_id,
                    c.full_name as customer_name,
                    c.company_name,
                    sp.name as plan_name,
                    sp.duration_days,
                    DATEDIFF(DATE_ADD(ss.start_date, INTERVAL sp.duration_days DAY), NOW()) as days_remaining
                FROM ServiceSubscriptions ss
                JOIN Services s ON ss.service_id = s.id
                JOIN Customers c ON s.customer_id = c.id
                JOIN SubscriptionPlans sp ON ss.plan_id = sp.id
                WHERE ss.is_active = 1 
                AND sp.duration_days > 0
                AND DATEDIFF(DATE_ADD(ss.start_date, INTERVAL sp.duration_days DAY), NOW()) <= ?
                AND DATEDIFF(DATE_ADD(ss.start_date, INTERVAL sp.duration_days DAY), NOW()) > 0
                ORDER BY days_remaining ASC";
        
        return $this->db->fetchAll($sql, [$daysThreshold]);
    }
    
    /**
     * آمار کلی اشتراک‌ها
     */
    public function getSubscriptionStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_subscriptions,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_subscriptions,
                    SUM(chat_count) as total_chats_used
                FROM ServiceSubscriptions";
        
        return $this->db->fetch($sql);
    }
    
    /**
     * حذف اشتراک سرویس
     */
    public function deleteSubscription($serviceId)
    {
        $sql = "DELETE FROM ServiceSubscriptions WHERE service_id = ?";
        return $this->db->execute($sql, [$serviceId]) > 0;
    }
}