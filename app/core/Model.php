<?php
// app/core/Model.php
class Model
{
    protected $db;
    protected $table;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    // تغییر از protected به public برای دسترسی در کنترلرها
    public function getDb()
    {
        return $this->db;
    }
    
    public function findAll($orderBy = 'id DESC')
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy}";
        return $this->db->fetchAll($sql);
    }
    
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    public function count($where = '1=1', $params = [])
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$where}";
        $result = $this->db->fetch($sql, $params);
        return (int)$result['total'];
    }
}