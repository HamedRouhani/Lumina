<?php
// app/controllers/CustomerController.php
class CustomerController extends Controller
{
    private $customerModel;
    
    public function __construct()
    {
        // اگر کلاس parent سازنده دارد، فراخوانی کن
        if (method_exists(parent::class, '__construct')) {
            parent::__construct();
        }
        $this->customerModel = new CustomerModel();
    }
    
    // نمایش صفحه مدیریت مشتریان
    public function index()
    {
        // بررسی دسترسی
        $admin = $this->getCurrentAdmin();
        if (!in_array($admin['role'], ['super_admin', 'admin'])) {
            $this->redirect('dashboard');
            return;
        }
        
        $this->view('admin.customers', [
            'title' => 'مدیریت مشتریان'
        ]);
    }
    
    // دریافت داده‌های مشتریان برای AJAX
    public function getData()
    {
        // بررسی دسترسی
        $admin = $this->getCurrentAdmin();
        if (!in_array($admin['role'], ['super_admin', 'admin'])) {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
            return;
        }
        
        try {
            $customers = $this->customerModel->getAllCustomers();
            $this->json([
                'success' => true,
                'customers' => $customers,
                'count' => count($customers)
            ]);
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    // دریافت یک مشتری
    public function show($id)
    {
        $admin = $this->getCurrentAdmin();
        if (!in_array($admin['role'], ['super_admin', 'admin'])) {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
            return;
        }
        
        try {
            $customer = $this->customerModel->getCustomerById($id);
            
            if (!$customer) {
                $this->json(['success' => false, 'error' => 'مشتری یافت نشد'], 404);
                return;
            }
            
            $stats = $this->customerModel->getCustomerStats($id);
            
            $serviceModel = new ServiceModel();
            $services = $serviceModel->getServicesByCustomer($id);
            
            $this->json([
                'success' => true,
                'customer' => $customer,
                'services' => $services,
                'stats' => $stats
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // ایجاد مشتری جدید
    public function create()
    {
        $admin = $this->getCurrentAdmin();
        if (!in_array($admin['role'], ['super_admin', 'admin'])) {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
            return;
        }
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['full_name'])) {
                $this->json(['success' => false, 'error' => 'نام کامل الزامی است'], 400);
                return;
            }
            
            $customer = $this->customerModel->createCustomer($data);
            
            $this->json([
                'success' => true,
                'message' => 'مشتری با موفقیت ایجاد شد',
                'customer' => $customer
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // به‌روزرسانی مشتری
    public function update($id)
    {
        $admin = $this->getCurrentAdmin();
        if (!in_array($admin['role'], ['super_admin', 'admin'])) {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
            return;
        }
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $existing = $this->customerModel->getCustomerById($id);
            if (!$existing) {
                $this->json(['success' => false, 'error' => 'مشتری یافت نشد'], 404);
                return;
            }
            
            $result = $this->customerModel->updateCustomer($id, $data);
            
            if ($result) {
                $this->json([
                    'success' => true,
                    'message' => 'مشتری با موفقیت به‌روزرسانی شد'
                ]);
            } else {
                $this->json(['success' => false, 'error' => 'هیچ تغییری اعمال نشد'], 400);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // حذف مشتری
    public function delete($id)
    {
        $admin = $this->getCurrentAdmin();
        if (!in_array($admin['role'], ['super_admin', 'admin'])) {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
            return;
        }
        
        try {
            $customer = $this->customerModel->getCustomerById($id);
            if (!$customer) {
                $this->json(['success' => false, 'error' => 'مشتری یافت نشد'], 404);
                return;
            }
            
            // اگر فعال است، غیرفعال کن
            if ($customer['is_active'] == 1) {
                $result = $this->customerModel->deleteCustomer($id); // این تابع الآن غیرفعال می‌کند
                $message = 'مشتری با موفقیت غیرفعال شد';
            } else {
                // اگر غیرفعال است، فعال کن
                $result = $this->customerModel->activateCustomer($id);
                $message = 'مشتری با موفقیت فعال شد';
            }
            
            if ($result) {
                $this->json([
                    'success' => true,
                    'message' => $message,
                    'is_active' => ($customer['is_active'] == 1) ? 0 : 1
                ]);
            } else {
                $this->json(['success' => false, 'error' => 'عملیات ناموفق بود'], 500);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}