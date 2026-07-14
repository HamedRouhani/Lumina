<?php
// app/controllers/AdminController.php
class AdminController extends Controller
{
    private $adminModel;
    private $customerModel;
    
    public function __construct()
    {
        if (method_exists(parent::class, '__construct')) {
            parent::__construct();
        }
        $this->adminModel = new AdminModel();
        $this->customerModel = new CustomerModel();
    }
    
    // نمایش صفحه مدیریت کاربران
    public function index()
    {
        $admin = $this->getCurrentAdmin();
        if (!in_array($admin['role'], ['super_admin', 'admin'])) {
            $this->redirect('dashboard');
            return;
        }
        
        $this->view('admin.users', [
            'title' => 'مدیریت کاربران'
        ]);
    }
    
    // دریافت داده‌های کاربران برای AJAX
    public function getData()
    {
        $admin = $this->getCurrentAdmin();
        if (!in_array($admin['role'], ['super_admin', 'admin'])) {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
            return;
        }
        
        try {
            $users = $this->adminModel->getAllAdmins();
            
            // فیلتر بر اساس دسترسی
            if ($admin['role'] === 'admin') {
                // مدیران نمی‌توانند مدیران کل را ببینند
                $users = array_filter($users, function($user) {
                    return $user['role'] !== 'super_admin';
                });
                $users = array_values($users);
            }
            
            $this->json([
                'success' => true,
                'users' => $users,
                'count' => count($users),
                'current_user_id' => $admin['id'],
                'current_user_role' => $admin['role']
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // دریافت یک کاربر
    public function show($id)
    {
        $admin = $this->getCurrentAdmin();
        
        try {
            $user = $this->adminModel->getAdminById($id);
            
            if (!$user) {
                $this->json(['success' => false, 'error' => 'کاربر یافت نشد'], 404);
                return;
            }
            
            // بررسی دسترسی
            if ($admin['role'] === 'admin' && $user['role'] === 'super_admin') {
                $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
                return;
            }
            
            $this->json([
                'success' => true,
                'user' => $user
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // ایجاد کاربر جدید
    public function create()
    {
        $admin = $this->getCurrentAdmin();
        if (!in_array($admin['role'], ['super_admin', 'admin'])) {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
            return;
        }
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // اعتبارسنجی
            if (empty($data['username'])) {
                $this->json(['success' => false, 'error' => 'نام کاربری الزامی است'], 400);
                return;
            }
            if (empty($data['password'])) {
                $this->json(['success' => false, 'error' => 'رمز عبور الزامی است'], 400);
                return;
            }
            if (strlen($data['password']) < 6) {
                $this->json(['success' => false, 'error' => 'رمز عبور باید حداقل ۶ کاراکتر باشد'], 400);
                return;
            }
            if (empty($data['full_name'])) {
                $this->json(['success' => false, 'error' => 'نام کامل الزامی است'], 400);
                return;
            }
            
            // بررسی تکراری نبودن نام کاربری
            $existing = $this->adminModel->getAdminByUsername($data['username']);
            if ($existing) {
                $this->json(['success' => false, 'error' => 'این نام کاربری قبلاً ثبت شده است'], 400);
                return;
            }
            
            // مدیران نمی‌توانند کاربر با نقش مدیر کل ایجاد کنند
            if ($admin['role'] === 'admin' && ($data['role'] ?? 'moderator') === 'super_admin') {
                $this->json(['success' => false, 'error' => 'شما نمی‌توانید مدیر کل ایجاد کنید'], 403);
                return;
            }
            
            $user = $this->adminModel->createAdmin($data);
            
            $this->json([
                'success' => true,
                'message' => 'کاربر با موفقیت ایجاد شد',
                'user' => $user
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // به‌روزرسانی کاربر
    public function update($id)
    {
        $admin = $this->getCurrentAdmin();
        if (!in_array($admin['role'], ['super_admin', 'admin'])) {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
            return;
        }
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $existing = $this->adminModel->getAdminById($id);
            if (!$existing) {
                $this->json(['success' => false, 'error' => 'کاربر یافت نشد'], 404);
                return;
            }
            
            // کاربر نمی‌تواند نقش خودش را تغییر دهد
            if ($id == $admin['id'] && isset($data['role']) && $data['role'] !== $existing['role']) {
                $this->json(['success' => false, 'error' => 'شما نمی‌توانید نقش خود را تغییر دهید'], 403);
                return;
            }
            
            // مدیران نمی‌توانند نقش مدیران کل را تغییر دهند
            if ($admin['role'] === 'admin' && $existing['role'] === 'super_admin') {
                $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
                return;
            }
            
            $result = $this->adminModel->updateAdmin($id, $data);
            
            if ($result) {
                $this->json([
                    'success' => true,
                    'message' => 'کاربر با موفقیت به‌روزرسانی شد'
                ]);
            } else {
                $this->json(['success' => false, 'error' => 'هیچ تغییری اعمال نشد'], 400);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // تغییر وضعیت کاربر (فعال/غیرفعال)
    public function toggleStatus($id)
    {
        $admin = $this->getCurrentAdmin();
        if (!in_array($admin['role'], ['super_admin', 'admin'])) {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
            return;
        }
        
        try {
            $user = $this->adminModel->getAdminById($id);
            
            if (!$user) {
                $this->json(['success' => false, 'error' => 'کاربر یافت نشد'], 404);
                return;
            }
            
            // کاربر نمی‌تواند خودش را غیرفعال کند
            if ($id == $admin['id']) {
                $this->json(['success' => false, 'error' => 'شما نمی‌توانید وضعیت حساب خود را تغییر دهید'], 403);
                return;
            }
            
            // مدیران نمی‌توانند وضعیت مدیران کل را تغییر دهند
            if ($admin['role'] === 'admin' && $user['role'] === 'super_admin') {
                $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
                return;
            }
            
            if ($user['is_active'] == 1) {
                $result = $this->adminModel->deleteAdmin($id);
                $message = 'کاربر با موفقیت غیرفعال شد';
                $is_active = 0;
            } else {
                $result = $this->adminModel->activateAdmin($id);
                $message = 'کاربر با موفقیت فعال شد';
                $is_active = 1;
            }
            
            if ($result) {
                $this->json([
                    'success' => true,
                    'message' => $message,
                    'is_active' => $is_active
                ]);
            } else {
                $this->json(['success' => false, 'error' => 'عملیات ناموفق بود'], 500);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // تغییر رمز عبور
    public function changePassword($id)
    {
        $admin = $this->getCurrentAdmin();
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['new_password'])) {
                $this->json(['success' => false, 'error' => 'رمز عبور جدید الزامی است'], 400);
                return;
            }
            if (strlen($data['new_password']) < 6) {
                $this->json(['success' => false, 'error' => 'رمز عبور باید حداقل ۶ کاراکتر باشد'], 400);
                return;
            }
            
            $user = $this->adminModel->getAdminById($id);
            if (!$user) {
                $this->json(['success' => false, 'error' => 'کاربر یافت نشد'], 404);
                return;
            }
            
            // اگر کاربر رمز خودش را تغییر می‌دهد، رمز فعلی را بررسی کن
            if ($id == $admin['id']) {
                if (empty($data['current_password'])) {
                    $this->json(['success' => false, 'error' => 'رمز عبور فعلی الزامی است'], 400);
                    return;
                }
                
                $isValid = $this->adminModel->verifyPassword($data['current_password'], $user['password_hash']);
                if (!$isValid) {
                    $this->json(['success' => false, 'error' => 'رمز عبور فعلی اشتباه است'], 400);
                    return;
                }
            } else {
                // بررسی دسترسی برای تغییر رمز دیگران
                if (!in_array($admin['role'], ['super_admin', 'admin'])) {
                    $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
                    return;
                }
                
                // مدیران نمی‌توانند رمز مدیران کل را تغییر دهند
                if ($admin['role'] === 'admin' && $user['role'] === 'super_admin') {
                    $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
                    return;
                }
            }
            
            $result = $this->adminModel->changePassword($id, $data['new_password']);
            
            if ($result) {
                $this->json([
                    'success' => true,
                    'message' => 'رمز عبور با موفقیت تغییر یافت'
                ]);
            } else {
                $this->json(['success' => false, 'error' => 'خطا در تغییر رمز عبور'], 500);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // دریافت لیست مشتریان برای select
    public function getCustomers()
    {
        $admin = $this->getCurrentAdmin();
        
        try {
            if ($admin['role'] === 'super_admin') {
                $customers = $this->customerModel->getAllCustomers();
            } elseif ($admin['role'] === 'admin') {
                // مدیران فقط مشتریان خود را می‌بینند
                $customers = $this->customerModel->getCustomerById($admin['customer_id']);
                $customers = $customers ? [$customers] : [];
            } else {
                $customers = [];
            }
            
            $this->json([
                'success' => true,
                'customers' => $customers
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // آمار دشبورد
    public function stats()
    {
        $admin = $this->getCurrentAdmin();
        
        try {
            $totalUsers = $this->adminModel->countActive();
            $totalCustomers = $this->customerModel->count();
            $totalServices = (new ServiceModel())->count();
            
            $this->json([
                'success' => true,
                'stats' => [
                    'total_users' => $totalUsers,
                    'total_customers' => $totalCustomers,
                    'total_services' => $totalServices,
                    'admin_role' => $admin['role']
                ]
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}