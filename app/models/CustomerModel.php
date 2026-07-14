<?php
// app/models/CustomerModel.php
class CustomerModel extends Model
{
    protected $table = 'Customers';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getAllCustomers()
    {
        $sql = "SELECT 
                    c.*,
                    (SELECT COUNT(*) FROM Services s WHERE s.customer_id = c.id AND s.is_active = 1) as service_count,
                    (SELECT COUNT(*) FROM Admins a WHERE a.customer_id = c.id AND a.is_active = 1) as admin_count,
                    (SELECT COUNT(*) FROM Users u 
                     JOIN Services s ON u.service_id = s.id 
                     WHERE s.customer_id = c.id) as total_users,
                    (SELECT COUNT(*) FROM Chats ch 
                     JOIN Users u ON ch.user_id = u.id 
                     JOIN Services s ON u.service_id = s.id 
                     WHERE s.customer_id = c.id) as total_chats
                FROM Customers c 
                ORDER BY c.created_at DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    public function getCustomerById($id)
    {
        $sql = "SELECT * FROM Customers WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    public function getCustomerByCode($code)
    {
        $sql = "SELECT * FROM Customers WHERE customer_code = ?";
        return $this->db->fetch($sql, [$code]);
    }
    
    public function getCustomerByPhone($phone)
    {
        $sql = "SELECT * FROM Customers WHERE phone = ?";
        return $this->db->fetch($sql, [$phone]);
    }
    
    public function createCustomer($data)
    {
        $customerCode = generateCustomerCode();
        
        $sql = "INSERT INTO Customers 
                (customer_code, full_name, phone, address, company_name, widget_config, is_active, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $widgetConfig = isset($data['widget_config']) ? json_encode($data['widget_config']) : '{}';
        $isActive = isset($data['is_active']) ? ($data['is_active'] ? 1 : 0) : 1;
        
        $this->db->insert($sql, [
            $customerCode,
            $data['full_name'],
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['company_name'] ?? null,
            $widgetConfig,
            $isActive
        ]);
        
        $id = $this->db->getConnection()->lastInsertId();
        return $this->getCustomerById($id);
    }
    
    public function updateCustomer($id, $data)
    {
        $fields = [];
        $params = [];
        
        $allowedFields = ['full_name', 'phone', 'address', 'company_name', 'widget_config', 'is_active'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = ?";
                if ($field === 'widget_config' && is_array($data[$field])) {
                    $params[] = json_encode($data[$field]);
                } else {
                    $params[] = $data[$field];
                }
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fields[] = "updated_at = NOW()";
        $params[] = $id;
        
        $sql = "UPDATE Customers SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->execute($sql, $params) > 0;
    }
    
    public function deleteCustomer($id)
    {
        // فقط غیرفعال کردن، نه حذف فیزیکی
        $sql = "UPDATE Customers SET is_active = 0 WHERE id = ?";
        return $this->db->execute($sql, [$id]) > 0;
    }

    public function activateCustomer($id)
    {
        $sql = "UPDATE Customers SET is_active = 1 WHERE id = ?";
        return $this->db->execute($sql, [$id]) > 0;
    }
    
    public function getCustomerStats($id)
    {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM Services WHERE customer_id = ? AND is_active = 1) as active_services,
                    (SELECT COUNT(*) FROM Services WHERE customer_id = ?) as total_services,
                    (SELECT COUNT(*) FROM Admins WHERE customer_id = ? AND is_active = 1) as active_admins,
                    (SELECT COUNT(*) FROM Users u 
                     JOIN Services s ON u.service_id = s.id 
                     WHERE s.customer_id = ?) as total_users,
                    (SELECT COUNT(*) FROM Chats ch 
                     JOIN Users u ON ch.user_id = u.id 
                     JOIN Services s ON u.service_id = s.id 
                     WHERE s.customer_id = ?) as total_chats
                FROM DUAL";
        
        return $this->db->fetch($sql, [$id, $id, $id, $id, $id]);
    }
}