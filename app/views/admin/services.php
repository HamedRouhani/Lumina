<?php
$title = 'مدیریت سرویس‌ها';
ob_start();
?>

<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-family: 'Vazirmatn', sans-serif;
        transition: all 0.3s;
    }
    
    .btn-primary { background: #667eea; color: white; }
    .btn-primary:hover { background: #5a67d8; }
    .btn-warning { background: #ffc107; color: #212529; }
    .btn-warning:hover { background: #e0a800; }
    .btn-danger { background: #dc3545; color: white; }
    .btn-danger:hover { background: #c82333; }
    .btn-success { background: #28a745; color: white; }
    .btn-success:hover { background: #218838; }
    .btn-info { background: #17a2b8; color: white; }
    .btn-info:hover { background: #138496; }
    .btn-sm { padding: 5px 10px; font-size: 12px; }
    
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table th, .table td {
        padding: 12px 15px;
        text-align: right;
        border-bottom: 1px solid #e9ecef;
    }
    
    .table th {
        background: #f8f9fa;
        font-weight: bold;
    }
    
    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
    }
    
    .badge-success { background: #d4edda; color: #155724; }
    .badge-danger { background: #f8d7da; color: #721c24; }
    .badge-warning { background: #fff3cd; color: #856404; }
    .badge-info { background: #d1ecf1; color: #0c5460; }
    
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }
    
    .modal-content {
        background: white;
        border-radius: 10px;
        width: 90%;
        max-width: 650px;
        max-height: 90vh;
        overflow-y: auto;
    }
    
    .modal-header {
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        background: white;
    }
    
    .modal-body { padding: 20px; }
    
    .form-group { margin-bottom: 15px; }
    .form-label { display: block; margin-bottom: 5px; font-weight: bold; }
    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-family: 'Vazirmatn', sans-serif;
    }
    textarea.form-control { resize: vertical; min-height: 80px; }
    
    .search-box { margin-bottom: 20px; }
    .search-input {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-family: 'Vazirmatn', sans-serif;
    }
    
    .tab-container {
        display: flex;
        border-bottom: 1px solid #e9ecef;
        margin-bottom: 20px;
        gap: 5px;
    }
    
    .tab {
        padding: 10px 20px;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        transition: all 0.3s;
    }
    
    .tab.active {
        border-bottom-color: #667eea;
        color: #667eea;
        font-weight: bold;
    }
    
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    
    .widget-code-preview {
        background: #1e1e1e;
        color: #d4d4d4;
        padding: 15px;
        border-radius: 6px;
        font-family: monospace;
        font-size: 13px;
        overflow-x: auto;
        white-space: pre-wrap;
        word-break: break-all;
        max-height: 400px;
    }
    
    .info-box {
        background: #e7f3ff;
        padding: 12px;
        border-radius: 6px;
        border-right: 4px solid #2196F3;
        margin: 15px 0;
    }
    
    .card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .card-header {
        padding: 15px 20px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .card-body {
        padding: 20px;
        overflow-x: auto;
    }
    
    .action-buttons {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }
    
    code {
        background: #f8f9fa;
        padding: 2px 6px;
        border-radius: 4px;
        font-family: monospace;
        font-size: 12px;
    }
    
    .color-preview {
        display: inline-block;
        width: 30px;
        height: 30px;
        border-radius: 6px;
        margin-right: 10px;
        vertical-align: middle;
        border: 1px solid #ddd;
    }
    
    .subscription-preview {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 8px;
        margin-top: 10px;
    }
    
    @media (max-width: 768px) {
        .table th, .table td {
            padding: 8px;
            font-size: 12px;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .btn-sm {
            width: 100%;
            margin: 2px 0;
        }
    }
</style>

<div class="page-header">
    <h2><i class="fas fa-server"></i> مدیریت سرویس‌ها</h2>
    <button class="btn btn-primary" onclick="openModal()">
        <i class="fas fa-plus"></i> سرویس جدید
    </button>
</div>

<div class="search-box">
    <input type="text" class="search-input" id="searchInput" placeholder="🔍 جستجو بر اساس عنوان، دامنه یا کد سرویس...">
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-list"></i> لیست سرویس‌ها</h3>
        <span id="totalCount" style="color: #6c757d;">0 سرویس</span>
    </div>
    <div class="card-body">
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>کد سرویس</th>
                        <th>عنوان</th>
                        <th>دامنه</th>
                        <th>مشتری</th>
                        <th>Assistant ID</th>
                        <th>وضعیت</th>
                        <th>تاریخ ایجاد</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody id="servicesTableBody">
                    <tr><td colspan="8" style="text-align: center;">در حال بارگذاری...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal ایجاد/ویرایش سرویس -->
<div id="serviceModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">ایجاد سرویس جدید</h3>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-body">
            <form id="serviceForm">
                <input type="hidden" id="serviceId">
                
                <div class="tab-container">
                    <div class="tab active" onclick="switchTab('basic')">اطلاعات پایه</div>
                    <div class="tab" onclick="switchTab('widget')">تنظیمات ویجت</div>
                    <div class="tab" onclick="switchTab('subscription')">اشتراک</div>
                </div>
                
                <div id="tab-basic" class="tab-content active">
                    <div class="form-group">
                        <label class="form-label">مشتری <span style="color: red;">*</span></label>
                        <select class="form-control" id="customer_id" required>
                            <option value="">انتخاب مشتری</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">عنوان سرویس</label>
                        <input type="text" class="form-control" id="title" placeholder="مثال: پشتیبانی فروشگاه">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">دامنه سایت <span style="color: red;">*</span></label>
                        <input type="text" class="form-control" id="url" placeholder="example.com" required>
                        <small>بدون http:// و www وارد کنید</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Assistant ID (OpenAI) <span style="color: red;">*</span></label>
                        <input type="text" class="form-control" id="assistant_ai" placeholder="asst_xxxxx" required>
                        <small>شناسه دستیار OpenAI که در پنل OpenAI ایجاد کرده‌اید</small>
                    </div>
                </div>
                
                <div id="tab-widget" class="tab-content">
                    <div class="form-group">
                        <label class="form-label">پیام خوشامدگویی</label>
                        <textarea class="form-control" id="welcome_message" rows="3" placeholder="سلام! به سرویس ما خوش آمدید. چطور می‌تونم کمک کنم؟"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">رنگ اصلی ویجت</label>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="color" class="form-control" id="primary_color" value="#667eea" style="width: 80px; padding: 5px;">
                            <span id="colorPreview" class="color-preview" style="background-color: #667eea;"></span>
                            <span id="colorValue">#667eea</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">موقعیت ویجت</label>
                        <select class="form-control" id="position">
                            <option value="bottom-right">پایین راست</option>
                            <option value="bottom-left">پایین چپ</option>
                        </select>
                    </div>
                </div>
                
                <div id="tab-subscription" class="tab-content">
                    <div class="form-group">
                        <label class="form-label">طرح اشتراک</label>
                        <select class="form-control" id="plan_id">
                            <option value="0">بدون اشتراک (نامحدود)</option>
                        </select>
                        <small>انتخاب طرح اشتراک برای این سرویس</small>
                    </div>
                    
                    <div id="subscriptionPreview" class="subscription-preview" style="display: none;">
                        <i class="fas fa-info-circle"></i> <span id="previewText"></span>
                    </div>
                    
                    <div class="info-box">
                        <i class="fas fa-lightbulb"></i>
                        <strong>توضیحات:</strong>
                        <ul style="margin-top: 10px; margin-right: 20px;">
                            <li>بدون اشتراک: سرویس بدون محدودیت کار می‌کند</li>
                            <li>طرح‌های محدود: پس از اتمام تعداد چت یا زمان، سرویس متوقف می‌شود</li>
                            <li>می‌توانید بعداً از بخش مدیریت اشتراک‌ها تغییر دهید</li>
                        </ul>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-warning" onclick="closeModal()">انصراف</button>
                    <button type="submit" class="btn btn-primary">ذخیره سرویس</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal کد ویجت -->
<div id="widgetCodeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-code"></i> کد نصب ویجت</h3>
            <button onclick="closeWidgetModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">عنوان سرویس:</label>
                <input type="text" class="form-control" id="widgetServiceTitle" readonly>
            </div>
            <div class="form-group">
                <label class="form-label">📱 کد دکمه شناور (Floating Widget):</label>
                <div class="widget-code-preview" id="floatingCodePreview"></div>
            </div>
            <div class="form-group">
                <label class="form-label">🖥️ کد iFrame (Inline Widget):</label>
                <div class="widget-code-preview" id="iframeCodePreview"></div>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end; flex-wrap: wrap;">
                <button class="btn btn-success" onclick="copyWidgetCode('floating')">
                    <i class="fas fa-copy"></i> کپی کد شناور
                </button>
                <button class="btn btn-info" onclick="copyWidgetCode('iframe')">
                    <i class="fas fa-copy"></i> کپی کد iFrame
                </button>
                <button class="btn btn-warning" onclick="closeWidgetModal()">بستن</button>
            </div>
            <div class="info-box" style="margin-top: 15px;">
                <i class="fas fa-lightbulb"></i>
                <strong>راهنما:</strong> این کد را در انتهای تگ &lt;body&gt; سایت خود قرار دهید.
            </div>
        </div>
    </div>
</div>

<script>
var baseUrl = window.location.origin;
var services = [];
var customers = [];
var plans = [];
var currentFloatingCode = '';
var currentIframeCode = '';

function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return '-';
    var date = new Date(dateString);
    return date.toLocaleDateString('fa-IR');
}

function showAlert(message, type) {
    var alertDiv = document.createElement('div');
    var bgColor = type === 'success' ? '#d4edda' : '#f8d7da';
    var textColor = type === 'success' ? '#155724' : '#721c24';
    alertDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; background: ' + bgColor + '; color: ' + textColor + '; padding: 12px 20px; border-radius: 8px; z-index: 10001; box-shadow: 0 2px 10px rgba(0,0,0,0.1);';
    alertDiv.innerHTML = message;
    document.body.appendChild(alertDiv);
    setTimeout(function() { alertDiv.remove(); }, 3000);
}

// ================================================================
// تابع تولید کد ویجت با تنظیمات کامل
// ================================================================
function generateWidgetCodes(service) {
    var serviceCode = service.service_code;
    
    // دریافت تنظیمات از دیتابیس
    var settings = {};
    if (service.widget_settings) {
        try {
            settings = JSON.parse(service.widget_settings);
        } catch(e) {
            console.log('Error parsing widget_settings:', e);
        }
    }
    
    // تنظیمات پیش‌فرض
    var primaryColor = settings.primaryColor || '#667eea';
    var buttonColor = settings.buttonColor || '#28a745';
    var floatingPosition = settings.floatingPosition || 'bottom-right';
    var iframeWidth = settings.iframeWidth || '500px';
    var iframeHeight = settings.iframeHeight || '550';
    var iframePosition = settings.iframePosition || 'center';
    var title = service.title || 'پشتیبانی آنلاین';
    var welcomeMessage = service.welcome_message || 'سلام! چطور می‌توانم به شما کمک کنم؟';
    
    // ============================================================
    // اصلاح: رنگ‌ها با # در URL ارسال شوند
    // ============================================================
    var primaryColorUrl = encodeURIComponent(primaryColor);
    var buttonColorUrl = encodeURIComponent(buttonColor);
    var titleEncoded = encodeURIComponent(title);
    var welcomeEncoded = encodeURIComponent(welcomeMessage);
    
    // ساخت URL پایه ویجت
    var widgetUrl = baseUrl + '/mylumina/widget-inline?service_code=' + encodeURIComponent(serviceCode) + 
                    '&primary_color=' + primaryColorUrl + 
                    '&button_color=' + buttonColorUrl + 
                    '&title=' + titleEncoded + 
                    '&welcome_message=' + welcomeEncoded;
    
    // ================================================================
    // کد شناور (Floating Widget)
    // ================================================================
    var floatingPosCSS = '';
    var windowPosCSS = '';
    if (floatingPosition === 'bottom-right') {
        floatingPosCSS = 'right: 20px; bottom: 20px;';
        windowPosCSS = 'right: 20px; bottom: 90px;';
    } else if (floatingPosition === 'bottom-left') {
        floatingPosCSS = 'left: 20px; bottom: 20px;';
        windowPosCSS = 'left: 20px; bottom: 90px;';
    }
    
    var floatingCode = '<!-- لومینا - ویجت چت شناور ' + title + ' -->\n';
    floatingCode += '<style>\n';
    floatingCode += '    .lumina-chat-btn-' + serviceCode + ' {\n';
    floatingCode += '        position: fixed;\n';
    floatingCode += '        ' + floatingPosCSS + '\n';
    floatingCode += '        width: 56px;\n';
    floatingCode += '        height: 56px;\n';
    floatingCode += '        border-radius: 50%;\n';
    floatingCode += '        background: linear-gradient(135deg, ' + primaryColor + ' 0%, #764ba2 100%);\n';
    floatingCode += '        cursor: pointer;\n';
    floatingCode += '        box-shadow: 0 4px 15px rgba(0,0,0,0.2);\n';
    floatingCode += '        z-index: 999999;\n';
    floatingCode += '        border: none;\n';
    floatingCode += '        display: flex;\n';
    floatingCode += '        align-items: center;\n';
    floatingCode += '        justify-content: center;\n';
    floatingCode += '        transition: transform 0.3s ease;\n';
    floatingCode += '    }\n';
    floatingCode += '    .lumina-chat-btn-' + serviceCode + ':hover { transform: scale(1.1); }\n';
    floatingCode += '    .lumina-chat-btn-' + serviceCode + ' svg { width: 28px; height: 28px; fill: white; }\n';
    floatingCode += '    .lumina-chat-window-' + serviceCode + ' {\n';
    floatingCode += '        position: fixed;\n';
    floatingCode += '        ' + windowPosCSS + '\n';
    floatingCode += '        width: 380px;\n';
    floatingCode += '        height: 500px;\n';
    floatingCode += '        background: white;\n';
    floatingCode += '        border-radius: 16px;\n';
    floatingCode += '        box-shadow: 0 10px 40px rgba(0,0,0,0.15);\n';
    floatingCode += '        display: none;\n';
    floatingCode += '        z-index: 999998;\n';
    floatingCode += '        border: none;\n';
    floatingCode += '    }\n';
    floatingCode += '    @media (max-width: 768px) {\n';
    floatingCode += '        .lumina-chat-window-' + serviceCode + ' { width: calc(100vw - 40px); height: 70vh; }\n';
    floatingCode += '    }\n';
    floatingCode += '</style>\n';
    floatingCode += '<div id="luminaChatContainer">\n';
    floatingCode += '    <button class="lumina-chat-btn-' + serviceCode + '" id="luminaChatBtn">\n';
    floatingCode += '        <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4V4c0-1.1-.9-2-2-2z"/></svg>\n';
    floatingCode += '    </button>\n';
    floatingCode += '    <iframe class="lumina-chat-window-' + serviceCode + '" id="luminaChatIframe" \n';
    floatingCode += '        src="' + widgetUrl + '"\n';
    floatingCode += '        title="' + title + '">\n';
    floatingCode += '    </iframe>\n';
    floatingCode += '</div>\n';
    floatingCode += '<script>\n';
    floatingCode += '(function() {\n';
    floatingCode += '    const btn = document.getElementById("luminaChatBtn");\n';
    floatingCode += '    const iframe = document.getElementById("luminaChatIframe");\n';
    floatingCode += '    let isOpen = false;\n';
    floatingCode += '    if (btn) {\n';
    floatingCode += '        btn.addEventListener("click", function(e) {\n';
    floatingCode += '            e.stopPropagation();\n';
    floatingCode += '            if (isOpen) { iframe.style.display = "none"; isOpen = false; }\n';
    floatingCode += '            else { iframe.style.display = "block"; isOpen = true; }\n';
    floatingCode += '        });\n';
    floatingCode += '        document.addEventListener("click", function(e) {\n';
    floatingCode += '            if (isOpen && btn && iframe && !btn.contains(e.target) && !iframe.contains(e.target)) {\n';
    floatingCode += '                iframe.style.display = "none"; isOpen = false;\n';
    floatingCode += '            }\n';
    floatingCode += '        });\n';
    floatingCode += '    }\n';
    floatingCode += '})();\n';
    floatingCode += '<\/script>';
    
    // ================================================================
    // کد iFrame (Inline Widget)
    // ================================================================
    var iframeCode = '';
    
    if (iframePosition === 'center') {
        iframeCode = '<!-- لومینا - ویجت چت ' + title + ' -->\n';
        iframeCode += '<div style="display: flex; justify-content: center; align-items: center; height: auto; padding: 20px 15px; background: transparent; width: 100%; box-sizing: border-box; overflow: hidden;">\n';
        iframeCode += '    <div style="width: ' + iframeWidth + '; max-width: 100%; height: ' + iframeHeight + 'px;">\n';
        iframeCode += '        <iframe \n';
        iframeCode += '            src="' + widgetUrl + '"\n';
        iframeCode += '            width="100%"\n';
        iframeCode += '            height="100%"\n';
        iframeCode += '            frameborder="0"\n';
        iframeCode += '            style="border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); display: block; overflow: hidden;"\n';
        iframeCode += '            title="' + title + '"\n';
        iframeCode += '            scrolling="no">\n';
        iframeCode += '        </iframe>\n';
        iframeCode += '    </div>\n';
        iframeCode += '</div>';
    } else {
        var positionStyle = '';
        switch(iframePosition) {
            case 'bottom-right': positionStyle = 'position: fixed; bottom: 20px; right: 20px;'; break;
            case 'bottom-left': positionStyle = 'position: fixed; bottom: 20px; left: 20px;'; break;
            case 'top-right': positionStyle = 'position: fixed; top: 20px; right: 20px;'; break;
            case 'top-left': positionStyle = 'position: fixed; top: 20px; left: 20px;'; break;
            default: positionStyle = 'position: relative; margin: 0 auto;';
        }
        
        iframeCode = '<!-- لومینا - ویجت چت ' + title + ' -->\n';
        iframeCode += '<div style="' + positionStyle + ' width: ' + iframeWidth + '; max-width: 100%; height: ' + iframeHeight + 'px;">\n';
        iframeCode += '    <iframe \n';
        iframeCode += '        src="' + widgetUrl + '"\n';
        iframeCode += '        width="100%"\n';
        iframeCode += '        height="100%"\n';
        iframeCode += '        frameborder="0"\n';
        iframeCode += '        style="border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); display: block; overflow: hidden;"\n';
        iframeCode += '        title="' + title + '"\n';
        iframeCode += '        scrolling="no">\n';
        iframeCode += '    </iframe>\n';
        iframeCode += '</div>';
    }
    
    return { floatingCode: floatingCode, iframeCode: iframeCode };
}

async function loadCustomers() {
    try {
        var response = await fetch('/mylumina/api/services/customers');
        var data = await response.json();
        if (data.success) {
            customers = data.customers;
            var select = document.getElementById('customer_id');
            select.innerHTML = '<option value="">انتخاب مشتری</option>';
            for (var i = 0; i < customers.length; i++) {
                var c = customers[i];
                var option = document.createElement('option');
                option.value = c.id;
                option.textContent = (c.company_name || c.full_name) + ' (' + c.customer_code + ')';
                select.appendChild(option);
            }
        }
    } catch (error) {
        console.error('Error loading customers:', error);
    }
}

async function loadPlans() {
    try {
        var response = await fetch('/mylumina/api/subscription-plans/data');
        var data = await response.json();
        if (data.success) {
            plans = data.plans;
            var select = document.getElementById('plan_id');
            select.innerHTML = '<option value="0">بدون اشتراک (نامحدود)</option>';
            for (var i = 0; i < plans.length; i++) {
                var p = plans[i];
                var option = document.createElement('option');
                option.value = p.id;
                var limitText = p.chat_limit > 0 ? p.chat_limit + ' چت' : 'نامحدود';
                var daysText = p.duration_days > 0 ? ' - ' + p.duration_days + ' روز' : '';
                option.textContent = p.name + ' (' + limitText + daysText + ')';
                select.appendChild(option);
            }
        }
    } catch (error) {
        console.error('Error loading plans:', error);
    }
}

async function loadServices() {
    try {
        var response = await fetch('/mylumina/api/services/data');
        var data = await response.json();
        if (data.success) {
            services = data.services;
            renderServices(services);
            document.getElementById('totalCount').textContent = services.length + ' سرویس';
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('خطا در بارگذاری سرویس‌ها', 'danger');
    }
}

function renderServices(servicesList) {
    var tbody = document.getElementById('servicesTableBody');
    
    if (servicesList.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">هیچ سرویسی یافت نشد</td></tr>';
        return;
    }
    
    tbody.innerHTML = '';
    
    for (var i = 0; i < servicesList.length; i++) {
        var service = servicesList[i];
        var statusText = service.is_active ? 'فعال' : 'غیرفعال';
        var statusClass = service.is_active ? 'badge-success' : 'badge-danger';
        
        var row = document.createElement('tr');
        row.innerHTML = `
            <td><code style="font-size: 11px;">${escapeHtml(service.service_code)}</code></td>
            <td><strong>${escapeHtml(service.title || '-')}</strong></td>
            <td>${escapeHtml(service.url)}</td>
            <td>${escapeHtml(service.company_name || service.customer_name || '-')}</td>
            <td><code style="font-size: 11px;">${escapeHtml(service.assistant_ai ? service.assistant_ai.substring(0, 20) + '...' : '-')}</code></td>
            <td><span class="badge ${statusClass}">${statusText}</span></td>
            <td>${formatDate(service.created_at)}</td>
            <td class="action-buttons">
                <button class="btn btn-info btn-sm" onclick="showWidgetCode(${service.id})" title="کد ویجت">
                    <i class="fas fa-code"></i>
                </button>
                <button class="btn btn-warning btn-sm" onclick="editService(${service.id})" title="ویرایش">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn ${service.is_active ? 'btn-danger' : 'btn-success'} btn-sm" onclick="toggleServiceStatus(${service.id}, ${service.is_active})" title="${service.is_active ? 'غیرفعال' : 'فعال'} کردن">
                    <i class="fas ${service.is_active ? 'fa-ban' : 'fa-check'}"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    }
}

async function toggleServiceStatus(id, isCurrentlyActive) {
    var action = isCurrentlyActive ? 'غیرفعال' : 'فعال';
    if (!confirm('آیا از ' + action + ' کردن این سرویس اطمینان دارید؟')) return;
    
    try {
        var response = await fetch('/mylumina/api/services/' + id + '/toggle', { method: 'POST' });
        var result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            loadServices();
        } else {
            showAlert(result.error, 'danger');
        }
    } catch (error) {
        showAlert('خطا در ارتباط با سرور', 'danger');
    }
}

function openModal() {
    document.getElementById('modalTitle').textContent = 'ایجاد سرویس جدید';
    document.getElementById('serviceForm').reset();
    document.getElementById('serviceId').value = '';
    document.getElementById('primary_color').value = '#667eea';
    document.getElementById('position').value = 'bottom-right';
    document.getElementById('plan_id').value = '0';
    document.getElementById('colorPreview').style.backgroundColor = '#667eea';
    document.getElementById('colorValue').textContent = '#667eea';
    document.getElementById('subscriptionPreview').style.display = 'none';
    switchTab('basic');
    document.getElementById('serviceModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('serviceModal').style.display = 'none';
}

async function editService(id) {
    var service = services.find(function(s) { return s.id == id; });
    if (!service) return;
    
    document.getElementById('modalTitle').textContent = 'ویرایش سرویس';
    document.getElementById('serviceId').value = service.id;
    document.getElementById('customer_id').value = service.customer_id;
    document.getElementById('title').value = service.title || '';
    document.getElementById('url').value = service.url || '';
    document.getElementById('assistant_ai').value = service.assistant_ai || '';
    document.getElementById('welcome_message').value = service.welcome_message || '';
    
    if (service.widget_settings) {
        try {
            var settings = JSON.parse(service.widget_settings);
            if (settings.primaryColor) {
                document.getElementById('primary_color').value = settings.primaryColor;
                document.getElementById('colorPreview').style.backgroundColor = settings.primaryColor;
                document.getElementById('colorValue').textContent = settings.primaryColor;
            }
            if (settings.position) document.getElementById('position').value = settings.position;
        } catch(e) {}
    }
    
    // دریافت اشتراک فعلی
    try {
        var subResponse = await fetch('/mylumina/api/subscription-plans/service/' + id);
        var subData = await subResponse.json();
        if (subData.success && subData.subscription && subData.subscription.plan_id) {
            document.getElementById('plan_id').value = subData.subscription.plan_id;
            
            var preview = document.getElementById('subscriptionPreview');
            var previewText = document.getElementById('previewText');
            preview.style.display = 'block';
            previewText.innerHTML = 'اشتراک فعلی: ' + (subData.subscription.plan_name || 'بدون اشتراک') + ' - ' + (subData.subscription.chat_count || 0) + ' چت استفاده شده';
        } else {
            document.getElementById('plan_id').value = '0';
            document.getElementById('subscriptionPreview').style.display = 'none';
        }
    } catch(e) {
        document.getElementById('plan_id').value = '0';
    }
    
    switchTab('basic');
    document.getElementById('serviceModal').style.display = 'flex';
}

async function saveService() {
    var id = document.getElementById('serviceId').value;
    
    var widgetSettings = {
        primaryColor: document.getElementById('primary_color').value,
        position: document.getElementById('position').value
    };
    
    var data = {
        customer_id: document.getElementById('customer_id').value,
        title: document.getElementById('title').value,
        url: document.getElementById('url').value,
        assistant_ai: document.getElementById('assistant_ai').value,
        welcome_message: document.getElementById('welcome_message').value,
        widget_settings: JSON.stringify(widgetSettings),
        plan_id: document.getElementById('plan_id').value || 0
    };
    
    if (!data.customer_id) {
        showAlert('لطفاً مشتری را انتخاب کنید', 'danger');
        return;
    }
    if (!data.url) {
        showAlert('لطفاً دامنه سایت را وارد کنید', 'danger');
        return;
    }
    if (!data.assistant_ai) {
        showAlert('لطفاً Assistant ID را وارد کنید', 'danger');
        return;
    }
    
    var url = id ? '/mylumina/api/services/' + id : '/mylumina/api/services';
    var method = id ? 'PUT' : 'POST';
    
    try {
        var response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        var result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            closeModal();
            loadServices();
        } else {
            showAlert(result.error, 'danger');
        }
    } catch (error) {
        showAlert('خطا در ارتباط با سرور', 'danger');
    }
}

async function showWidgetCode(serviceId) {
    var service = services.find(function(s) { return s.id == serviceId; });
    if (!service) {
        showAlert('سرویس یافت نشد', 'danger');
        return;
    }
    
    // ============================================================
    // اصلاح: استفاده از تابع generateWidgetCodes با تنظیمات کامل
    // ============================================================
    var codes = generateWidgetCodes(service);
    currentFloatingCode = codes.floatingCode;
    currentIframeCode = codes.iframeCode;
    
    document.getElementById('widgetServiceTitle').value = service.title || 'سرویس بدون نام';
    document.getElementById('floatingCodePreview').textContent = currentFloatingCode;
    document.getElementById('iframeCodePreview').textContent = currentIframeCode;
    document.getElementById('widgetCodeModal').style.display = 'flex';
}

function closeWidgetModal() {
    document.getElementById('widgetCodeModal').style.display = 'none';
}

function copyWidgetCode(type) {
    var code = type === 'floating' ? currentFloatingCode : currentIframeCode;
    var typeName = type === 'floating' ? 'دکمه شناور' : 'iFrame';
    
    if (!code) return;
    
    navigator.clipboard.writeText(code).then(function() {
        showAlert('✅ کد ' + typeName + ' با موفقیت کپی شد!', 'success');
    }).catch(function() {
        showAlert('❌ خطا در کپی کردن کد', 'danger');
    });
}

function switchTab(tabName) {
    var tabs = document.querySelectorAll('.tab');
    for (var i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove('active');
    }
    var contents = document.querySelectorAll('.tab-content');
    for (var i = 0; i < contents.length; i++) {
        contents[i].classList.remove('active');
    }
    
    if (tabName === 'basic') {
        tabs[0].classList.add('active');
        document.getElementById('tab-basic').classList.add('active');
    } else if (tabName === 'widget') {
        tabs[1].classList.add('active');
        document.getElementById('tab-widget').classList.add('active');
    } else {
        tabs[2].classList.add('active');
        document.getElementById('tab-subscription').classList.add('active');
    }
}

var colorInput = document.getElementById('primary_color');
if (colorInput) {
    colorInput.addEventListener('input', function() {
        document.getElementById('colorPreview').style.backgroundColor = this.value;
        document.getElementById('colorValue').textContent = this.value;
    });
}

document.getElementById('searchInput').addEventListener('input', function(e) {
    var term = e.target.value.toLowerCase();
    var filtered = services.filter(function(s) {
        return (s.title && s.title.toLowerCase().includes(term)) ||
               (s.url && s.url.toLowerCase().includes(term)) ||
               (s.service_code && s.service_code.toLowerCase().includes(term)) ||
               (s.company_name && s.company_name.toLowerCase().includes(term));
    });
    renderServices(filtered);
});

document.getElementById('serviceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    saveService();
});

loadCustomers();
loadPlans();
loadServices();
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>