<?php
// app/controllers/ServiceController.php

class ServiceController extends Controller
{
    private $serviceModel;
    private $customerModel;
    private $subscriptionModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->serviceModel = new ServiceModel();
        $this->customerModel = new CustomerModel();
        $this->subscriptionModel = new ServiceSubscriptionModel();
    }
    
    // ==================== صفحات ====================
    
    public function index()
    {
        $this->requireRole(['super_admin', 'admin']);
        $this->view('admin.services', ['title' => 'مدیریت سرویس‌ها']);
    }
    
    // ==================== APIها ====================
    
    public function getData()
    {
        $this->requireRole(['super_admin', 'admin']);
        
        try {
            $services = $this->serviceModel->getAllServices();
            
            $formattedServices = array_map(function($service) {
                $subscription = $this->subscriptionModel->getSubscriptionByServiceId($service['id']);
                
                return [
                    'id' => $service['id'],
                    'service_code' => $service['service_code'],
                    'title' => $service['title'],
                    'url' => $service['url'],
                    'customer_id' => $service['customer_id'],
                    'company_name' => $service['company_name'] ?? $service['customer_name'],
                    'assistant_ai' => $service['assistant_id'],
                    'is_active' => $service['is_active'],
                    'created_at' => $service['created_at'],
                    'widget_settings' => $service['widget_settings'],
                    'welcome_message' => $service['welcome_message'],
                    'subscription' => $subscription
                ];
            }, $services);
            
            $this->json([
                'success' => true,
                'services' => $formattedServices,
                'count' => count($formattedServices)
            ]);
        } catch (Exception $e) {
            error_log("getData error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    public function getCustomers()
    {
        $this->requireRole(['super_admin', 'admin']);
        
        try {
            $customers = $this->customerModel->getAllCustomers();
            $this->json(['success' => true, 'customers' => $customers]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    public function show($id)
    {
        $this->requireLogin();
        
        try {
            $service = $this->serviceModel->getServiceById($id);
            
            if (!$service) {
                $this->json(['success' => false, 'error' => 'سرویس یافت نشد'], 404);
                return;
            }
            
            $admin = $this->getCurrentAdmin();
            if ($admin['role'] === 'moderator' && $service['customer_id'] != $admin['customer_id']) {
                $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
                return;
            }
            
            $subscription = $this->subscriptionModel->getSubscriptionByServiceId($id);
            $stats = $this->serviceModel->getServiceStats($id);
            $chats = $this->serviceModel->getServiceChats($id, 50);
            
            $this->json([
                'success' => true,
                'service' => $service,
                'subscription' => $subscription,
                'stats' => $stats,
                'recent_chats' => $chats
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // ==================== ایجاد سرویس جدید ====================
    
    public function create()
    {
        $this->requireRole(['super_admin', 'admin']);
        
        try {
            $input = file_get_contents('php://input');
            error_log("ServiceController::create - Input: " . $input);
            
            $data = json_decode($input, true);
            
            if (!$data) {
                $this->json(['success' => false, 'error' => 'داده‌های ارسالی معتبر نیست'], 400);
                return;
            }
            
            // اعتبارسنجی
            $errors = array();
            if (empty($data['customer_id'])) {
                $errors[] = 'مشتری الزامی است';
            }
            if (empty($data['url'])) {
                $errors[] = 'دامنه الزامی است';
            }
            if (empty($data['assistant_ai'])) {
                $errors[] = 'Assistant ID الزامی است';
            }
            
            if (!empty($errors)) {
                $this->json(['success' => false, 'error' => implode(' - ', $errors)], 400);
                return;
            }
            
            // نرمال‌سازی URL
            $normalizedUrl = $this->normalizeUrl($data['url']);
            
            // آماده‌سازی داده‌ها برای مدل
            $serviceData = array(
                'customer_id' => $data['customer_id'],
                'title' => $data['title'] ?? null,
                'url' => $normalizedUrl,
                'assistant_id' => $data['assistant_ai'],
                'welcome_message' => $data['welcome_message'] ?? null,
                'channel' => 'webapp',
                'is_active' => 1
            );
            
            // تنظیمات ویجت
            if (isset($data['widget_settings'])) {
                $widgetSettings = $data['widget_settings'];
                if (is_string($widgetSettings)) {
                    $serviceData['widget_settings'] = $widgetSettings;
                } else {
                    $serviceData['widget_settings'] = json_encode($widgetSettings);
                }
            } else {
                $serviceData['widget_settings'] = json_encode(array(
                    'primaryColor' => '#667eea',
                    'position' => 'bottom-right'
                ));
            }
            
            // ایجاد سرویس
            $serviceId = $this->serviceModel->createService($serviceData);
            
            if ($serviceId) {
                // تخصیص اشتراک
                $planId = isset($data['plan_id']) ? (int)$data['plan_id'] : 0;
                if ($planId > 0) {
                    $this->subscriptionModel->updateSubscription($serviceId, $planId);
                    error_log("ServiceController::create - Subscription assigned: plan_id=$planId for service_id=$serviceId");
                } else {
                    $this->subscriptionModel->updateSubscription($serviceId, 0);
                }
                
                $newService = $this->serviceModel->getServiceById($serviceId);
                $subscription = $this->subscriptionModel->getSubscriptionByServiceId($serviceId);
                
                $this->json(array(
                    'success' => true,
                    'message' => 'سرویس با موفقیت ایجاد شد',
                    'service' => $newService,
                    'subscription' => $subscription
                ));
            } else {
                $this->json(['success' => false, 'error' => 'خطا در ایجاد سرویس'], 500);
            }
            
        } catch (Exception $e) {
            error_log("ServiceController::create - Exception: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // ==================== به‌روزرسانی سرویس ====================
    
    public function update($id)
    {
        $this->requireRole(['super_admin', 'admin']);
        
        try {
            $input = file_get_contents('php://input');
            error_log("ServiceController::update - Input: " . $input);
            
            $data = json_decode($input, true);
            
            if (!$data) {
                $this->json(['success' => false, 'error' => 'داده‌های ارسالی معتبر نیست'], 400);
                return;
            }
            
            $existing = $this->serviceModel->getServiceById($id);
            if (!$existing) {
                $this->json(['success' => false, 'error' => 'سرویس یافت نشد'], 404);
                return;
            }
            
            // آماده‌سازی داده‌ها برای به‌روزرسانی
            $updateData = array();
            
            if (isset($data['title'])) {
                $updateData['title'] = $data['title'];
            }
            
            if (isset($data['url'])) {
                $updateData['url'] = $this->normalizeUrl($data['url']);
            }
            
            if (isset($data['assistant_ai'])) {
                $updateData['assistant_id'] = $data['assistant_ai'];
            }
            
            if (isset($data['welcome_message'])) {
                $updateData['welcome_message'] = $data['welcome_message'];
            }
            
            if (isset($data['widget_settings'])) {
                $widgetSettings = $data['widget_settings'];
                if (is_string($widgetSettings)) {
                    $updateData['widget_settings'] = $widgetSettings;
                } else {
                    $updateData['widget_settings'] = json_encode($widgetSettings);
                }
            }
            
            // به‌روزرسانی سرویس
            $result = false;
            if (!empty($updateData)) {
                $result = $this->serviceModel->updateService($id, $updateData);
            }
            
            // به‌روزرسانی اشتراک
            $subscriptionUpdated = false;
            if (isset($data['plan_id'])) {
                $planId = (int)$data['plan_id'];
                if ($planId > 0) {
                    $this->subscriptionModel->updateSubscription($id, $planId);
                    $subscriptionUpdated = true;
                    error_log("ServiceController::update - Subscription updated: plan_id=$planId for service_id=$id");
                } else {
                    $this->subscriptionModel->deactivateSubscription($id);
                    $subscriptionUpdated = true;
                }
            }
            
            if ($result || $subscriptionUpdated) {
                $this->json(array(
                    'success' => true,
                    'message' => 'سرویس با موفقیت به‌روزرسانی شد'
                ));
            } else {
                $this->json(['success' => false, 'error' => 'هیچ تغییری اعمال نشد'], 400);
            }
            
        } catch (Exception $e) {
            error_log("ServiceController::update - Exception: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // ==================== تغییر وضعیت سرویس ====================
    
    public function toggleStatus($id)
    {
        $this->requireRole(['super_admin', 'admin']);
        
        try {
            $service = $this->serviceModel->getServiceById($id);
            
            if (!$service) {
                $this->json(['success' => false, 'error' => 'سرویس یافت نشد'], 404);
                return;
            }
            
            if ($service['is_active'] == 1) {
                $result = $this->serviceModel->deleteService($id);
                $message = 'سرویس با موفقیت غیرفعال شد';
                $is_active = 0;
            } else {
                $result = $this->serviceModel->activateService($id);
                $message = 'سرویس با موفقیت فعال شد';
                $is_active = 1;
            }
            
            if ($result) {
                $this->json(array(
                    'success' => true,
                    'message' => $message,
                    'is_active' => $is_active
                ));
            } else {
                $this->json(['success' => false, 'error' => 'عملیات ناموفق بود'], 500);
            }
            
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // ==================== دریافت کد ویجت ====================
    
    public function getWidgetCode($id)
    {
        $this->requireLogin();
        
        try {
            $service = $this->serviceModel->getServiceById($id);
            
            if (!$service) {
                $this->json(['success' => false, 'error' => 'سرویس یافت نشد'], 404);
                return;
            }
            
            $admin = $this->getCurrentAdmin();
            if ($admin['role'] === 'moderator' && $service['customer_id'] != $admin['customer_id']) {
                $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز'], 403);
                return;
            }
            
            $widgetCode = $this->generateWidgetCode($service);
            
            $this->json(array(
                'success' => true,
                'widget_code' => $widgetCode,
                'service' => array(
                    'id' => $service['id'],
                    'service_code' => $service['service_code'],
                    'title' => $service['title'],
                    'url' => $service['url'],
                    'widget_settings' => $service['widget_settings']
                )
            ));
            
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // ==================== تغییر مشتری سرویس ====================
    
    public function changeCustomer($id)
    {
        $this->requireRole(['super_admin', 'admin']);
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['customer_id'])) {
                $this->json(['success' => false, 'error' => 'مشتری جدید الزامی است'], 400);
                return;
            }
            
            $result = $this->serviceModel->changeCustomer($id, $data['customer_id']);
            
            if ($result) {
                $this->subscriptionModel->deactivateSubscription($id);
                
                $this->json(array(
                    'success' => true,
                    'message' => 'مشتری سرویس با موفقیت تغییر یافت'
                ));
            } else {
                $this->json(['success' => false, 'error' => 'خطا در تغییر مشتری'], 500);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    // ==================== توابع کمکی ====================
    
    private function normalizeUrl($url)
    {
        $url = trim($url);
        $url = strtolower($url);
        
        $url = preg_replace('#^https?://#', '', $url);
        $url = preg_replace('#^www\.#', '', $url);
        $url = parse_url($url, PHP_URL_HOST) ?: $url;
        $url = explode('/', $url)[0];
        
        return $url;
    }
    
    private function generateWidgetCode($service)
    {
        $widgetSettings = array();
        if (!empty($service['widget_settings'])) {
            $widgetSettings = json_decode($service['widget_settings'], true) ?? array();
        }
        
        $primaryColor = $widgetSettings['primaryColor'] ?? '#667eea';
        $position = $widgetSettings['position'] ?? 'bottom-right';
        $welcomeMessage = $service['welcome_message'] ?? "سلام! به {$service['title']} خوش آمدید. چطور می‌تونم کمک کنم؟";
        
        $baseUrl = 'https://lifyai.com';
        $serviceCode = $service['service_code'];
        $title = addslashes($service['title'] ?? 'پشتیبانی آنلاین');
        $welcomeMessageEncoded = addslashes($welcomeMessage);
        
        $html = <<<HTML
<!-- لومینا - ویجت چت هوشمند {$title} -->
<div id="lumina-chat-widget-{$serviceCode}" style="position: fixed; {$position}: 20px; bottom: 20px; z-index: 999999;">
    <button id="lumina-chat-btn-{$serviceCode}" style="
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, {$primaryColor} 0%, #764ba2 100%);
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.3s ease;
    ">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="white">
            <path d="M20 2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4V4c0-1.1-.9-2-2-2z"/>
        </svg>
    </button>
    <iframe id="lumina-chat-iframe-{$serviceCode}" 
        src="{$baseUrl}/mylumina/widget-inline?service_code={$serviceCode}&primary_color={$primaryColor}&title=" . urlencode($title) . "&welcome_message=" . urlencode($welcomeMessageEncoded) . "
        style="
            position: fixed;
            {$position}: 20px;
            bottom: 90px;
            width: 380px;
            height: 550px;
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            display: none;
            z-index: 999998;
            background: white;
        "
        title="{$title}">
    </iframe>
</div>
<script>
(function() {
    var btn = document.getElementById('lumina-chat-btn-{$serviceCode}');
    var iframe = document.getElementById('lumina-chat-iframe-{$serviceCode}');
    var isOpen = false;
    
    if (btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (isOpen) {
                iframe.style.display = 'none';
                isOpen = false;
            } else {
                iframe.style.display = 'block';
                isOpen = true;
            }
        });
        
        btn.addEventListener('mouseenter', function() { btn.style.transform = 'scale(1.1)'; });
        btn.addEventListener('mouseleave', function() { btn.style.transform = 'scale(1)'; });
    }
    
    document.addEventListener('click', function(e) {
        if (isOpen && btn && iframe && !btn.contains(e.target) && !iframe.contains(e.target)) {
            iframe.style.display = 'none';
            isOpen = false;
        }
    });
})();
<\/script>
HTML;
        
        return $html;
    }
}
?>