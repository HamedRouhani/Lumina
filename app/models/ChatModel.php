<?php
// app/models/ChatModel.php
class ChatModel extends Model
{
    protected $table = 'Chats';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getDb()
    {
        return $this->db;
    }
    
    /**
     * دریافت یا ایجاد کاربر بر اساس visitor_id
     */
    public function getOrCreateUser($visitorId, $serviceId, $customerId, $widgetId = null)
    {
        $sessionId = "visitor_{$visitorId}";
        
        // بررسی وجود کاربر
        $sql = "SELECT * FROM Users WHERE session_id = ? AND service_id = ?";
        $user = $this->db->fetch($sql, [$sessionId, $serviceId]);
        
        if ($user) {
            // به‌روزرسانی آخرین فعالیت
            $this->db->execute("UPDATE Users SET last_activity = NOW() WHERE id = ?", [$user['id']]);
            return $user;
        }
        
        // ایجاد کاربر جدید
        $sql = "INSERT INTO Users (session_id, widget_id, service_id, customer_id, created_at, last_activity) 
                VALUES (?, ?, ?, ?, NOW(), NOW())";
        $userId = $this->db->insert($sql, [$sessionId, $widgetId, $serviceId, $customerId]);
        
        // دریافت کاربر ایجاد شده
        $sql = "SELECT * FROM Users WHERE id = ?";
        return $this->db->fetch($sql, [$userId]);
    }
    
    /**
     * ذخیره پیام کاربر
     */
    public function saveUserMessage($userId, $message)
    {
        $sql = "INSERT INTO Chats (user_id, chat_user, created_at) VALUES (?, ?, NOW())";
        return $this->db->insert($sql, [$userId, $message]);
    }
    
    /**
     * ذخیره پاسخ ربات
     */
    public function saveBotResponse($chatId, $response)
    {
        $sql = "UPDATE Chats SET chat_bot = ? WHERE id = ?";
        return $this->db->execute($sql, [$response, $chatId]);
    }
    
    /**
     * به‌روزرسانی thread_id کاربر
     */
    public function updateUserThreadId($userId, $threadId)
    {
        $sql = "UPDATE Users SET thread_id = ? WHERE id = ?";
        return $this->db->execute($sql, [$threadId, $userId]);
    }
    
    /**
     * دریافت thread_id کاربر
     */
    public function getUserThreadId($userId)
    {
        $sql = "SELECT thread_id FROM Users WHERE id = ?";
        $result = $this->db->fetch($sql, [$userId]);
        return $result['thread_id'] ?? null;
    }
    
    /**
     * دریافت تاریخچه چت کاربر
     */
    public function getChatHistory($userId, $limit = 50)
    {
        $sql = "SELECT 
                    id,
                    chat_user as userMessage,
                    chat_bot as botMessage,
                    created_at
                FROM Chats 
                WHERE user_id = ? 
                ORDER BY created_at ASC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$userId, $limit]);
    }
}