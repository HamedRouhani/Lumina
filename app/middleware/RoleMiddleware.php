<?php
// app/middleware/RoleMiddleware.php
class RoleMiddleware
{
    public static function check($allowedRoles)
    {
        if (!isset($_SESSION['admin']) || empty($_SESSION['admin']['id'])) {
            header('Location: /mylumina/login');
            exit;
        }
        
        $userRole = $_SESSION['admin']['role'] ?? '';
        
        if (!in_array($userRole, $allowedRoles)) {
            // دسترسی غیرمجاز - redirect به دشبورد
            header('Location: /mylumina/dashboard');
            exit;
        }
        
        return true;
    }
}