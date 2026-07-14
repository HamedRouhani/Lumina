<?php
// app/models/ServiceModel.php
class ServiceModel extends Model
{
    protected $table = 'Services';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getAllServices()
    {
        $sql = "SELECT 
                    s.*,
                    c.company_name,
                    c.full_name as customer_name,
                    c.is_active as customer_active,
                    ss.id as subscription_id,
                    ss.plan_id,
                    ss.is_active as subscription_is_active,
                    ss.start_date as subscription_start_date,
                    ss.chat_count,
                    sp.name as plan_name,
                    sp.chat_limit,
                    sp.duration_days
                FROM Services s
                LEFT JOIN Customers c ON s.customer_id = c.id
                LEFT JOIN ServiceSubscriptions ss ON s.id = ss.service_id
                LEFT JOIN SubscriptionPlans sp ON ss.plan_id = sp.id
                ORDER BY s.created_at DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    public function getServiceById($id)
    {
        $sql = "SELECT 
                    s.*,
                    c.company_name,
                    c.full_name as customer_name,
                    c.widget_config,
                    c.is_active as customer_active
                FROM Services s
                LEFT JOIN Customers c ON s.customer_id = c.id
                WHERE s.id = ?";
        
        $service = $this->db->fetch($sql, [$id]);
        
        if ($service && isset($service['assistant_id']) && !isset($service['assistant_ai'])) {
            $service['assistant_ai'] = $service['assistant_id'];
        }
        
        return $service;
    }
    
    public function getServiceByCode($code)
    {
        $sql = "SELECT 
                    s.*,
                    c.company_name,
                    c.full_name as customer_name
                FROM Services s
                LEFT JOIN Customers c ON s.customer_id = c.id
                WHERE s.service_code = ? AND s.is_active = 1";
        
        $service = $this->db->fetch($sql, [$code]);
        
        // لاگ برای دیباگ
        if (!$service) {
            error_log("ServiceModel::getServiceByCode - Service not found for code: " . $code);
        } else {
            error_log("ServiceModel::getServiceByCode - Found service: " . $code . ", assistant_id: " . ($service['assistant_id'] ?? 'empty'));
        }
        
        // اطمینان از وجود assistant_id
        if ($service && !isset($service['assistant_id'])) {
            $service['assistant_id'] = '';
            error_log("ServiceModel::getServiceByCode - assistant_id missing for service: " . $code);
        }
        
        return $service;
    }
        
    public function getServicesByCustomer($customerId)
    {
        $sql = "SELECT 
                    s.*,
                    c.company_name
                FROM Services s
                LEFT JOIN Customers c ON s.customer_id = c.id
                WHERE s.customer_id = ? 
                ORDER BY s.created_at DESC";
        
        return $this->db->fetchAll($sql, [$customerId]);
    }
    
    public function createService($data)
    {
        $serviceCode = generateServiceCode();
        
        // تنظیمات پیش‌فرض ویجت
        $defaultWidgetSettings = [
            'position' => 'bottom-right',
            'primaryColor' => '#667eea',
            'secondaryColor' => '#764ba2',
            'autoOpen' => false,
            'showAvatar' => true,
            'avatarUrl' => '/mylumina/assets/images/logo.png'
        ];
        
        $widgetSettings = !empty($data['widget_settings']) 
            ? json_encode(array_merge($defaultWidgetSettings, json_decode($data['widget_settings'], true) ?? []))
            : json_encode($defaultWidgetSettings);
        
        $sql = "INSERT INTO Services 
                (customer_id, service_code, url, channel, assistant_id, title, welcome_message, widget_settings, is_active, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $this->db->insert($sql, [
            $data['customer_id'],
            $serviceCode,
            $data['url'],
            $data['channel'] ?? 'webapp',
            $data['assistant_id'],
            $data['title'] ?? null,
            $data['welcome_message'] ?? null,
            $widgetSettings,
            $data['is_active'] ?? 1
        ]);
        
        $id = $this->db->getConnection()->lastInsertId();
        
        // ایجاد اشتراک پیش‌فرض
        $this->createDefaultSubscription($id);
        
        return $this->getServiceById($id);
    }
    
    public function updateService($id, $data)
    {
        $fields = [];
        $params = [];
        
        $allowedFields = ['title', 'url', 'channel', 'assistant_id', 'welcome_message', 'widget_settings', 'is_active'];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            error_log("updateService: No fields to update for service id: $id");
            return false;
        }
        
        $fields[] = "updated_at = NOW()";
        $params[] = $id;
        
        $sql = "UPDATE Services SET " . implode(', ', $fields) . " WHERE id = ?";
        error_log("updateService SQL: " . $sql);
        error_log("updateService Params: " . json_encode($params));
        
        try {
            $result = $this->db->execute($sql, $params);
            error_log("updateService result: " . $result);
            return $result > 0;
        } catch (Exception $e) {
            error_log("updateService error: " . $e->getMessage());
            return false;
        }
    }

    public function changeCustomer($serviceId, $newCustomerId)
    {
        $service = $this->getServiceById($serviceId);
        if (!$service) {
            throw new Exception('سرویس یافت نشد');
        }
        
        $sql = "UPDATE Services SET customer_id = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->execute($sql, [$newCustomerId, $serviceId]) > 0;
    }
    
    public function deleteService($id)
    {
        // فقط غیرفعال کردن
        $sql = "UPDATE Services SET is_active = 0 WHERE id = ?";
        return $this->db->execute($sql, [$id]) > 0;
    }
    
    public function activateService($id)
    {
        $sql = "UPDATE Services SET is_active = 1 WHERE id = ?";
        return $this->db->execute($sql, [$id]) > 0;
    }
    
    private function createDefaultSubscription($serviceId)
    {
        // بررسی وجود اشتراک
        $checkSql = "SELECT id FROM ServiceSubscriptions WHERE service_id = ?";
        $existing = $this->db->fetch($checkSql, [$serviceId]);
        
        if (!$existing) {
            $sql = "INSERT INTO ServiceSubscriptions (service_id, plan_id, is_active, start_date, chat_count) 
                    VALUES (?, 0, 1, NOW(), 0)";
            $this->db->insert($sql, [$serviceId]);
        }
    }
    
    public function getServiceStats($serviceId)
    {
        $sql = "SELECT 
                    COUNT(DISTINCT u.id) as unique_users,
                    COUNT(c.id) as total_messages,
                    COUNT(DISTINCT DATE(c.created_at)) as active_days,
                    SUM(CASE WHEN DATE(c.created_at) = CURDATE() THEN 1 ELSE 0 END) as today_messages
                FROM Services s
                LEFT JOIN Users u ON u.service_id = s.id
                LEFT JOIN Chats c ON c.user_id = u.id
                WHERE s.id = ?";
        
        return $this->db->fetch($sql, [$serviceId]);
    }
    
    public function getServiceChats($serviceId, $limit = 100)
    {
        $sql = "SELECT 
                    c.*,
                    u.session_id,
                    u.widget_id
                FROM Chats c
                JOIN Users u ON c.user_id = u.id
                WHERE u.service_id = ?
                ORDER BY c.created_at DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$serviceId, $limit]);
    }

    public function updateWidgetSettings($serviceId, $settings)
    {
        $widgetSettings = json_encode($settings);
        $sql = "UPDATE Services SET widget_settings = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->execute($sql, [$widgetSettings, $serviceId]) > 0;
    }
}