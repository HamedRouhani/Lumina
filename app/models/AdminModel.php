<?php
// app/models/AdminModel.php
class AdminModel extends Model
{
    protected $table = 'Admins';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getAllAdmins()
    {
        $sql = "SELECT 
                    a.*,
                    c.company_name,
                    c.full_name as customer_full_name
                FROM Admins a
                LEFT JOIN Customers c ON a.customer_id = c.id
                ORDER BY a.created_at DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    public function getAdminById($id)
    {
        $sql = "SELECT 
                    a.*,
                    c.company_name,
                    c.customer_code
                FROM Admins a
                LEFT JOIN Customers c ON a.customer_id = c.id
                WHERE a.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    public function getAdminByUsername($username)
    {
        $sql = "SELECT * FROM Admins WHERE username = ?";
        return $this->db->fetch($sql, [$username]);
    }
    
    public function createAdmin($data)
    {
        $passwordHash = base64_encode($data['password']);
        
        $sql = "INSERT INTO Admins 
                (username, password_hash, full_name, email, role, customer_id, is_active, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $this->db->insert($sql, [
            $data['username'],
            $passwordHash,
            $data['full_name'],
            $data['email'] ?? null,
            $data['role'] ?? 'moderator',
            $data['customer_id'] ?? null,
            $data['is_active'] ?? 1
        ]);
        
        $id = $this->db->getConnection()->lastInsertId();
        return $this->getAdminById($id);
    }
    
    public function updateAdmin($id, $data)
    {
        $fields = [];
        $params = [];
        
        $allowedFields = ['full_name', 'email', 'role', 'customer_id', 'is_active'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fields[] = "updated_at = NOW()";
        $params[] = $id;
        
        $sql = "UPDATE Admins SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->execute($sql, $params) > 0;
    }
    
    public function changePassword($id, $newPassword)
    {
        $passwordHash = base64_encode($newPassword);
        
        $sql = "UPDATE Admins SET password_hash = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->execute($sql, [$passwordHash, $id]) > 0;
    }
    
    public function verifyPassword($password, $hash)
    {
        return base64_encode($password) === $hash;
    }
    
    public function deleteAdmin($id)
    {
        // فقط غیرفعال کردن
        $sql = "UPDATE Admins SET is_active = 0 WHERE id = ?";
        return $this->db->execute($sql, [$id]) > 0;
    }
    
    public function activateAdmin($id)
    {
        $sql = "UPDATE Admins SET is_active = 1 WHERE id = ?";
        return $this->db->execute($sql, [$id]) > 0;
    }
    
    public function getAdminsByCustomer($customerId)
    {
        $sql = "SELECT * FROM Admins WHERE customer_id = ? ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [$customerId]);
    }
    
    public function countActive()
    {
        $sql = "SELECT COUNT(*) as total FROM Admins WHERE is_active = 1";
        $result = $this->db->fetch($sql);
        return $result['total'] ?? 0;
    }
}