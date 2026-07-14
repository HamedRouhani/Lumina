<?php
// app/core/Controller.php
class Controller
{
    public function __construct()
    {
        // سازنده خالی - می‌توانید در آینده تنظیمات اضافه کنید
    }
    
    protected function view($view, $data = [])
    {
        extract($data);
        
        $viewPath = __DIR__ . '/../views/' . str_replace('.', '/', $view) . '.php';
        
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            throw new Exception("View not found: {$view}");
        }
    }
    
    protected function render($view, $data = [])
    {
        $this->view($view, $data);
    }
    
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    protected function redirect($url)
    {
        $basePath = '/mylumina';
        header("Location: {$basePath}/{$url}");
        exit;
    }
    
    protected function getCurrentAdmin()
    {
        return $_SESSION['admin'] ?? null;
    }
    
    protected function isLoggedIn()
    {
        return isset($_SESSION['admin']) && !empty($_SESSION['admin']['id']);
    }
    
    protected function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
            exit;
        }
    }
    
    protected function requireRole($roles)
    {
        $this->requireLogin();
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        $userRole = $_SESSION['admin']['role'] ?? '';
        
        if (!in_array($userRole, $roles)) {
            $this->redirect('dashboard');
            exit;
        }
    }
}