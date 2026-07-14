<?php
// app/middleware/AuthMiddleware.php
class AuthMiddleware
{
    public static function check()
    {
        if (!isset($_SESSION['admin']) || empty($_SESSION['admin']['id'])) {
            // ذخیره URL درخواستی برای redirect بعد از لاگین
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            
            header('Location: /mylumina/login');
            exit;
        }
        
        return true;
    }
}