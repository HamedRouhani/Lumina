<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'لومینا - پنل مدیریت'; ?></title>
    
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Vazirmatn', 'Tahoma', sans-serif;
            background: #f5f6fa;
            overflow-x: hidden;
        }
        
        /* ==================== ساختار اصلی ==================== */
        .app {
            display: flex;
            min-height: 100vh;
        }
        
        /* ==================== منوی کناری ==================== */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            transition: all 0.3s ease;
            position: fixed;
            right: 0;
            top: 0;
            bottom: 0;
            z-index: 1000;
            overflow-y: auto;
            box-shadow: -2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header .logo {
            font-size: 1.8rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .sidebar-header .logo i {
            font-size: 2rem;
        }
        
        .sidebar-header .version {
            font-size: 12px;
            opacity: 0.6;
            margin-top: 8px;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-section {
            margin-bottom: 20px;
        }
        
        .menu-section-title {
            padding: 10px 20px;
            font-size: 12px;
            color: rgba(255,255,255,0.5);
            letter-spacing: 1px;
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-right: 3px solid transparent;
        }
        
        .menu-item:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            border-right-color: #667eea;
        }
        
        .menu-item.active {
            background: rgba(102,126,234,0.2);
            color: white;
            border-right-color: #667eea;
        }
        
        .menu-item i {
            width: 24px;
            font-size: 1.2rem;
        }
        
        .menu-item span {
            font-size: 14px;
        }
        
        /* ==================== محتوای اصلی ==================== */
        .main-content {
            flex: 1;
            margin-right: 280px;
            transition: all 0.3s ease;
        }
        
        /* ==================== هدر ==================== */
        .top-header {
            background: white;
            padding: 15px 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #333;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-name {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #333;
        }
        
        .user-name i {
            font-size: 1.2rem;
            color: #667eea;
        }
        
        .logout-btn {
            background: #e17055;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Vazirmatn', sans-serif;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .logout-btn:hover {
            background: #d63031;
        }
        
        /* ==================== محتوای صفحه ==================== */
        .page-content {
            padding: 25px;
        }
        
        /* ==================== کارت‌ها ==================== */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* ==================== هشدارها ==================== */
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        /* ==================== ریسپانسیو ==================== */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(100%);
                width: 260px;
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-right: 0;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .top-header {
                padding: 12px 15px;
            }
            
            .page-content {
                padding: 15px;
            }
        }
        
        /* اسکرول بار منو */
        .sidebar::-webkit-scrollbar {
            width: 5px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="app">
        <!-- منوی کناری -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-robot"></i>
                    <span>لومینا</span>
                </div>
                <div class="version">نسخه 1.0</div>
            </div>
            
            <nav class="sidebar-menu" id="sidebarMenu">
                <!-- منو توسط جاوااسکریپت پر می‌شود -->
            </nav>
        </aside>
        
        <!-- محتوای اصلی -->
        <main class="main-content">
            <div class="top-header">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="user-info">
                    <div class="user-name">
                        <i class="fas fa-user-circle"></i>
                        <span id="userFullName"><?php echo escape($_SESSION['admin']['full_name'] ?? $_SESSION['admin']['username'] ?? 'کاربر'); ?></span>
                    </div>
                    <button class="logout-btn" onclick="logout()">
                        <i class="fas fa-sign-out-alt"></i>
                        خروج
                    </button>
                </div>
            </div>
            
            <div class="page-content">
                <?php echo $content ?? ''; ?>
            </div>
        </main>
    </div>
    
    <script>
        // ==================== منوی کناری ====================
        const userRole = '<?php echo $_SESSION['admin']['role'] ?? 'moderator'; ?>';
        const customerId = '<?php echo $_SESSION['admin']['customer_id'] ?? ''; ?>';
        
        // تعریف منوها بر اساس نقش
        const menus = {
            // منوهای عمومی (همه کاربران)
            common: [
                { url: '/mylumina/dashboard', icon: 'fas fa-tachometer-alt', title: 'داشبورد', roles: ['super_admin', 'admin', 'moderator'] }
            ],
            
            // منوهای مدیران (super_admin و admin)
            admin: [
                { url: '/mylumina/admin/customers', icon: 'fas fa-building', title: 'مدیریت مشتریان', roles: ['super_admin', 'admin'] },
                { url: '/mylumina/admin/users', icon: 'fas fa-users', title: 'مدیریت کاربران', roles: ['super_admin', 'admin'] },
                { url: '/mylumina/admin/services', icon: 'fas fa-cogs', title: 'مدیریت سرویس‌ها', roles: ['super_admin', 'admin'] },
                { url: '/mylumina/admin/subscription-plans', icon: 'fas fa-file-contract', title: 'طرح‌های اشتراک', roles: ['super_admin', 'admin'] }
            ],
            
            // منوهای ناظران (moderator)
            moderator: [
                { url: '/mylumina/moderator/services', icon: 'fas fa-cogs', title: 'سرویس‌های من', roles: ['moderator'] },
                { url: '/mylumina/moderator/widget-code', icon: 'fas fa-code', title: 'کد ویجت', roles: ['moderator'] },
                { url: '/mylumina/moderator/usage-report', icon: 'fas fa-chart-bar', title: 'گزارش استفاده', roles: ['moderator'] },
            ]
        };
        
        // تابع برای دریافت منوهای قابل نمایش
        function getVisibleMenus() {
            let visibleMenus = [];
            
            // اضافه کردن منوهای عمومی
            menus.common.forEach(menu => {
                if (menu.roles.includes(userRole)) {
                    visibleMenus.push(menu);
                }
            });
            
            // اضافه کردن منوهای ادمین
            if (userRole === 'super_admin' || userRole === 'admin') {
                menus.admin.forEach(menu => {
                    if (menu.roles.includes(userRole)) {
                        visibleMenus.push(menu);
                    }
                });
            }
            
            // اضافه کردن منوهای ناظر
            if (userRole === 'moderator') {
                menus.moderator.forEach(menu => {
                    if (menu.roles.includes(userRole)) {
                        visibleMenus.push(menu);
                    }
                });
            }
            
            return visibleMenus;
        }
        
        // رندر منو
        function renderMenu() {
            const container = document.getElementById('sidebarMenu');
            const visibleMenus = getVisibleMenus();
            const currentPath = window.location.pathname;
            
            let html = '';
            
            // گروه‌بندی منوها
            const dashboardMenus = visibleMenus.filter(m => m.title === 'داشبورد');
            const managementMenus = visibleMenus.filter(m => 
                ['مدیریت مشتریان', 'مدیریت کاربران', 'مدیریت سرویس‌ها', 'طرح‌های اشتراک', 'سرویس‌های من'].includes(m.title)
            );
            const widgetMenus = visibleMenus.filter(m => 
                ['کد ویجت', 'گزارش استفاده', 'اشتراک‌های من'].includes(m.title)
            );
            
            // بخش داشبورد
            if (dashboardMenus.length > 0) {
                html += `<div class="menu-section">`;
                html += `<div class="menu-section-title"><i class="fas fa-chart-pie"></i> اصلی</div>`;
                dashboardMenus.forEach(menu => {
                    const isActive = currentPath.includes(menu.url.replace('/mylumina', ''));
                    html += `
                        <a href="${menu.url}" class="menu-item ${isActive ? 'active' : ''}">
                            <i class="${menu.icon}"></i>
                            <span>${menu.title}</span>
                        </a>
                    `;
                });
                html += `</div>`;
            }
            
            // بخش مدیریت
            if (managementMenus.length > 0) {
                html += `<div class="menu-section">`;
                html += `<div class="menu-section-title"><i class="fas fa-tools"></i> مدیریت</div>`;
                managementMenus.forEach(menu => {
                    const isActive = currentPath.includes(menu.url.replace('/mylumina', ''));
                    html += `
                        <a href="${menu.url}" class="menu-item ${isActive ? 'active' : ''}">
                            <i class="${menu.icon}"></i>
                            <span>${menu.title}</span>
                        </a>
                    `;
                });
                html += `</div>`;
            }
            
            // بخش ویجت و گزارشات
            if (widgetMenus.length > 0) {
                html += `<div class="menu-section">`;
                html += `<div class="menu-section-title"><i class="fas fa-chart-line"></i> ویجت و گزارشات</div>`;
                widgetMenus.forEach(menu => {
                    const isActive = currentPath.includes(menu.url.replace('/mylumina', ''));
                    html += `
                        <a href="${menu.url}" class="menu-item ${isActive ? 'active' : ''}">
                            <i class="${menu.icon}"></i>
                            <span>${menu.title}</span>
                        </a>
                    `;
                });
                html += `</div>`;
            }
            
            container.innerHTML = html;
        }
        
        // ==================== توابع عمومی ====================
        function logout() {
            if (confirm('آیا از خروج از سیستم اطمینان دارید؟')) {
                window.location.href = '/mylumina/logout';
            }
        }
        
        // تنظیم منوی همبرگری برای موبایل
        function setupMobileMenu() {
            const toggleBtn = document.getElementById('menuToggle');
            const sidebar = document.getElementById('sidebar');
            
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('open');
                });
            }
            
            // بستن منو با کلیک خارج
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                        sidebar.classList.remove('open');
                    }
                }
            });
        }
        
        // ==================== راه‌اندازی ====================
        document.addEventListener('DOMContentLoaded', function() {
            renderMenu();
            setupMobileMenu();
        });
    </script>
</body>
</html>