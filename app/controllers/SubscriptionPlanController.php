<?php
// app/controllers/SubscriptionPlanController.php
class SubscriptionPlanController extends Controller
{
    private $planModel;
    private $subscriptionModel;
    private $serviceModel;
    
    public function __construct()
    {
        // فراخوانی سازنده parent اگر وجود دارد
        if (method_exists(get_parent_class($this), '__construct')) {
            parent::__construct();
        }
        
        $this->planModel = new SubscriptionPlanModel();
        $this->subscriptionModel = new ServiceSubscriptionModel();
        $this->serviceModel = new ServiceModel();
    }
    
    // نمایش صفحه مدیریت طرح‌های اشتراک
    public function index()
    {
        $admin = $this->getCurrentAdmin();
        if (!in_array($admin['role'], ['super_admin', 'admin'])) {
            $this->redirect('dashboard');
            return;
        }
        
        $this->view('admin.subscription-plans', [
            'title' => 'مدیریت طرح‌های اشتراک'
        ]);
    }
    
    // دریافت داده‌های طرح‌ها برای AJAX
    public function getData()
    {
        $admin = $this->getCurrentAdmin();
        if (!in_array($admin['role'], ['super_admin', 'admin'])) {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
            return;
        }
        
        try {
            $plans = $this->planModel->getAllPlans();
            
            // فیلتر کردن plan_id = 0 (بدون اشتراک) برای نمایش
            $plans = array_filter($plans, function($plan) {
                return $plan['id'] != 0;
            });
            $plans = array_values($plans);
            
            $this->json([
                'success' => true,
                'plans' => $plans,
                'count' => count($plans)
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // دریافت یک طرح
    public function show($id)
    {
        $admin = $this->getCurrentAdmin();
        if (!in_array($admin['role'], ['super_admin', 'admin'])) {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
            return;
        }
        
        try {
            $plan = $this->planModel->getPlanById($id);
            
            if (!$plan) {
                $this->json(['success' => false, 'error' => 'طرح یافت نشد'], 404);
                return;
            }
            
            $this->json([
                'success' => true,
                'plan' => $plan
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // ایجاد طرح جدید
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
            if (empty($data['name'])) {
                $this->json(['success' => false, 'error' => 'نام طرح الزامی است'], 400);
                return;
            }
            
            $plan = $this->planModel->createPlan($data);
            
            $this->json([
                'success' => true,
                'message' => 'طرح اشتراک با موفقیت ایجاد شد',
                'plan' => $plan
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // به‌روزرسانی طرح
    public function update($id)
    {
        $admin = $this->getCurrentAdmin();
        if (!in_array($admin['role'], ['super_admin', 'admin'])) {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
            return;
        }
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $existing = $this->planModel->getPlanById($id);
            if (!$existing) {
                $this->json(['success' => false, 'error' => 'طرح یافت نشد'], 404);
                return;
            }
            
            // طرح پیش‌فرض (id=0) قابل ویرایش نیست
            if ($id == 0) {
                $this->json(['success' => false, 'error' => 'طرح پیش‌فرض قابل ویرایش نیست'], 400);
                return;
            }
            
            $result = $this->planModel->updatePlan($id, $data);
            
            if ($result) {
                $this->json([
                    'success' => true,
                    'message' => 'طرح اشتراک با موفقیت به‌روزرسانی شد'
                ]);
            } else {
                $this->json(['success' => false, 'error' => 'هیچ تغییری اعمال نشد'], 400);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // حذف طرح
    public function delete($id)
    {
        $admin = $this->getCurrentAdmin();
        if (!in_array($admin['role'], ['super_admin', 'admin'])) {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
            return;
        }
        
        try {
            // طرح پیش‌فرض (id=0) قابل حذف نیست
            if ($id == 0) {
                $this->json(['success' => false, 'error' => 'طرح پیش‌فرض قابل حذف نیست'], 400);
                return;
            }
            
            $result = $this->planModel->deletePlan($id);
            
            if ($result) {
                $this->json([
                    'success' => true,
                    'message' => 'طرح اشتراک با موفقیت حذف شد'
                ]);
            } else {
                $this->json(['success' => false, 'error' => 'طرح یافت نشد'], 404);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // تخصیص اشتراک به سرویس
    public function assignToService()
    {
        $admin = $this->getCurrentAdmin();
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['service_id'])) {
                $this->json(['success' => false, 'error' => 'شناسه سرویس الزامی است'], 400);
                return;
            }
            if (!isset($data['plan_id'])) {
                $this->json(['success' => false, 'error' => 'شناسه طرح الزامی است'], 400);
                return;
            }
            
            $service = $this->serviceModel->getServiceById($data['service_id']);
            if (!$service) {
                $this->json(['success' => false, 'error' => 'سرویس یافت نشد'], 404);
                return;
            }
            
            // بررسی دسترسی
            if ($admin['role'] === 'moderator') {
                if ($service['customer_id'] != $admin['customer_id']) {
                    $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
                    return;
                }
            }
            
            // اگر plan_id = 0، اشتراک را غیرفعال کن
            if ($data['plan_id'] == 0) {
                $this->subscriptionModel->deactivateSubscription($data['service_id']);
                $message = 'اشتراک سرویس با موفقیت غیرفعال شد';
            } else {
                $plan = $this->planModel->getPlanById($data['plan_id']);
                if (!$plan) {
                    $this->json(['success' => false, 'error' => 'طرح اشتراک یافت نشد'], 404);
                    return;
                }
                
                $this->subscriptionModel->updateSubscription($data['service_id'], $data['plan_id']);
                $message = 'اشتراک با موفقیت به سرویس تخصیص داده شد';
            }
            
            $this->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // دریافت اشتراک یک سرویس
    public function getServiceSubscription($serviceId)
    {
        $admin = $this->getCurrentAdmin();
        
        try {
            $service = $this->serviceModel->getServiceById($serviceId);
            if (!$service) {
                $this->json(['success' => false, 'error' => 'سرویس یافت نشد'], 404);
                return;
            }
            
            // بررسی دسترسی
            if ($admin['role'] === 'moderator') {
                if ($service['customer_id'] != $admin['customer_id']) {
                    $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
                    return;
                }
            }
            
            $subscription = $this->subscriptionModel->getSubscriptionByServiceId($serviceId);
            
            $this->json([
                'success' => true,
                'subscription' => $subscription,
                'service' => [
                    'id' => $service['id'],
                    'title' => $service['title'],
                    'service_code' => $service['service_code']
                ]
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // تمدید اشتراک
    public function renewSubscription()
    {
        $this->requireRole(['super_admin', 'admin']);
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['service_id'])) {
                $this->json(['success' => false, 'error' => 'شناسه سرویس الزامی است'], 400);
                return;
            }
            
            $result = $this->subscriptionModel->renewSubscription($data['service_id']);
            
            if ($result) {
                $this->json([
                    'success' => true,
                    'message' => 'اشتراک با موفقیت تمدید شد'
                ]);
            } else {
                $this->json(['success' => false, 'error' => 'خطا در تمدید اشتراک'], 500);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // دریافت اشتراک‌های کاربر جاری (برای moderator)
    public function mySubscriptions()
    {
        $admin = $this->getCurrentAdmin();
        
        if ($admin['role'] !== 'moderator') {
            $this->json(['success' => false, 'error' => 'این بخش فقط برای کاربران ناظر است'], 403);
            return;
        }
        
        if (!$admin['customer_id']) {
            $this->json(['success' => true, 'subscriptions' => [], 'message' => 'شما مشتری مرتبط ندارید']);
            return;
        }
        
        try {
            $subscriptions = $this->subscriptionModel->getSubscriptionsByCustomer($admin['customer_id']);
            
            $this->json([
                'success' => true,
                'subscriptions' => $subscriptions,
                'count' => count($subscriptions),
                'customer_id' => $admin['customer_id']
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}