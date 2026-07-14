<?php
// app/models/SubscriptionPlanModel.php
class SubscriptionPlanModel extends Model
{
    protected $table = 'SubscriptionPlans';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getAllPlans()
    {
        $sql = "SELECT * FROM SubscriptionPlans ORDER BY 
                    CASE 
                        WHEN id = 0 THEN 0 
                        ELSE 1 
                    END, 
                    id ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    public function getPlanById($id)
    {
        $sql = "SELECT * FROM SubscriptionPlans WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    public function createPlan($data)
    {
        $sql = "INSERT INTO SubscriptionPlans (name, chat_limit, duration_days, created_at) 
                VALUES (?, ?, ?, NOW())";
        
        $this->db->insert($sql, [
            $data['name'],
            $data['chat_limit'] ?? 0,
            $data['duration_days'] ?? 0
        ]);
        
        $id = $this->db->getConnection()->lastInsertId();
        return $this->getPlanById($id);
    }
    
    public function updatePlan($id, $data)
    {
        // حذف updated_at از کوئری چون در جدول وجود ندارد
        $sql = "UPDATE SubscriptionPlans 
                SET name = ?, chat_limit = ?, duration_days = ? 
                WHERE id = ?";
        
        return $this->db->execute($sql, [
            $data['name'],
            $data['chat_limit'] ?? 0,
            $data['duration_days'] ?? 0,
            $id
        ]) > 0;
    }
    
    public function deletePlan($id)
    {
        // بررسی استفاده از طرح در اشتراک‌ها
        $checkSql = "SELECT COUNT(*) as count FROM ServiceSubscriptions WHERE plan_id = ?";
        $result = $this->db->fetch($checkSql, [$id]);
        
        if ($result['count'] > 0) {
            throw new Exception('این طرح در حال استفاده است و قابل حذف نمی‌باشد');
        }
        
        $sql = "DELETE FROM SubscriptionPlans WHERE id = ?";
        return $this->db->execute($sql, [$id]) > 0;
    }
    
    public function getPlansCount()
    {
        $sql = "SELECT COUNT(*) as total FROM SubscriptionPlans WHERE id > 0";
        $result = $this->db->fetch($sql);
        return $result['total'] ?? 0;
    }
    
    public function getDefaultPlan()
    {
        $sql = "SELECT * FROM SubscriptionPlans WHERE id = 0";
        return $this->db->fetch($sql);
    }
}