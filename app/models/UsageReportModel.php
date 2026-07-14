<?php

class UsageReportModel extends Model
{
    /**
     * دریافت لیست کاربران به همراه تعداد چت‌ها و آخرین فعالیت
     * برای یک moderator خاص بر اساس customer_id
     */
    public function getUsersUsageReport($moderatorId)
    {
        $db = Database::getInstance();
        
        $sql = "
            SELECT 
                u.id AS user_id,
                u.session_id,
                u.widget_id,
                u.created_at AS user_created_at,
                u.last_activity,
                u.is_active,
                COUNT(c.id) AS chat_count,
                MAX(c.created_at) AS last_chat_date,
                MIN(c.created_at) AS first_chat_date
            FROM 
                Users u
            LEFT JOIN 
                Chats c ON u.id = c.user_id
            WHERE 
                u.customer_id = (SELECT customer_id FROM Admins WHERE id = ?)
            GROUP BY 
                u.id
            ORDER BY 
                chat_count DESC, u.last_activity DESC
        ";

        return $db->fetchAll($sql, [$moderatorId]);
    }

    /**
     * دریافت تمام چت‌های یک کاربر خاص
     */
    public function getUserChats($userId)
    {
        $db = Database::getInstance();
        
        $sql = "
            SELECT 
                id,
                user_id,
                chat_user,
                chat_bot,
                message_type,
                created_at
            FROM 
                Chats
            WHERE 
                user_id = ?
            ORDER BY 
                created_at ASC
        ";

        return $db->fetchAll($sql, [$userId]);
    }

    /**
     * دریافت آمار تفکیکی برای هر کاربر
     */
    public function getUserStats($userId)
    {
        $db = Database::getInstance();
        
        $sql = "
            SELECT 
                COUNT(*) AS total_chats,
                COUNT(DISTINCT DATE(created_at)) AS active_days,
                MIN(created_at) AS first_chat,
                MAX(created_at) AS last_chat
            FROM 
                Chats
            WHERE 
                user_id = ?
        ";
        
        return $db->fetch($sql, [$userId]);
    }
}