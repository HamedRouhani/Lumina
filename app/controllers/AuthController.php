<?php
// app/controllers/AuthController.php
class AuthController extends Controller
{
    public function login()
    {
        // اگر قبلاً لاگین کرده، به دشبورد برو
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard');
        }
        
        $this->view('auth.login');
    }
    
    public function doLogin()
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $_SESSION['login_error'] = 'نام کاربری و رمز عبور الزامی است';
            $this->redirect('login');
            return;
        }
        
        $db = Database::getInstance();
        
        // رمز عبور به صورت base64 ذخیره شده
        $passwordHash = base64_encode($password);
        
        $admin = $db->fetch(
            "SELECT * FROM Admins WHERE username = ? AND password_hash = ? AND is_active = 1",
            [$username, $passwordHash]
        );
        
        if ($admin) {
            $_SESSION['admin'] = [
                'id' => $admin['id'],
                'username' => $admin['username'],
                'full_name' => $admin['full_name'],
                'role' => $admin['role'],
                'customer_id' => $admin['customer_id']
            ];
            
            // به‌روزرسانی last_login
            $db->execute(
                "UPDATE Admins SET last_login = NOW() WHERE id = ?",
                [$admin['id']]
            );
            
            $this->redirect('dashboard');
        } else {
            $_SESSION['login_error'] = 'نام کاربری یا رمز عبور اشتباه است';
            $this->redirect('login');
        }
    }
    
    public function logout()
    {
        session_destroy();
        $this->redirect('login');
    }
    
    public function check()
    {
        if ($this->isLoggedIn()) {
            $this->json(['success' => true, 'admin' => $_SESSION['admin']]);
        } else {
            $this->json(['success' => false, 'error' => 'Not logged in'], 401);
        }
    }
}
