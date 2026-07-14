<?php
$title = 'سرویس‌های من';
ob_start();
?>

<style>
    .page-header {
        margin-bottom: 25px;
    }
    
    .page-header h2 {
        color: #333;
        margin-bottom: 8px;
    }
    
    .page-header p {
        color: #6c757d;
    }
    
    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 25px;
    }
    
    .service-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .service-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    }
    
    .service-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
    }
    
    .service-title {
        font-size: 1.2rem;
        font-weight: bold;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .service-status {
        font-size: 12px;
        padding: 4px 10px;
        border-radius: 20px;
        background: rgba(255,255,255,0.2);
    }
    
    .service-code {
        font-size: 12px;
        opacity: 0.8;
        font-family: monospace;
        word-break: break-all;
    }
    
    .service-body {
        padding: 20px;
    }
    
    .service-info {
        margin-bottom: 20px;
    }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e9ecef;
    }
    
    .info-label {
        color: #6c757d;
        font-size: 13px;
    }
    
    .info-value {
        font-weight: 500;
        color: #333;
        word-break: break-all;
        text-align: left;
    }
    
    .subscription-box {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 15px;
        margin-top: 15px;
    }
    
    .subscription-title {
        font-weight: bold;
        margin-bottom: 10px;
        color: #495057;
    }
    
    .status-active {
        background: #d4edda;
        color: #155724;
        padding: 8px 12px;
        border-radius: 8px;
    }
    
    .status-expired {
        background: #f8d7da;
        color: #721c24;
        padding: 8px 12px;
        border-radius: 8px;
    }
    
    .status-warning {
        background: #fff3cd;
        color: #856404;
        padding: 8px 12px;
        border-radius: 8px;
    }
    
    .progress-bar {
        height: 6px;
        background: #e9ecef;
        border-radius: 3px;
        overflow: hidden;
        margin: 10px 0;
    }
    
    .progress-fill {
        height: 100%;
        background: #28a745;
        border-radius: 3px;
        transition: width 0.3s;
    }
    
    .btn-code {
        width: 100%;
        background: #28a745;
        color: white;
        border: none;
        padding: 10px;
        border-radius: 8px;
        cursor: pointer;
        font-family: 'Vazirmatn', sans-serif;
        font-size: 14px;
        transition: all 0.3s;
        margin-top: 15px;
    }
    
    .btn-code:hover {
        background: #218838;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px;
        background: white;
        border-radius: 15px;
        grid-column: 1/-1;
    }
    
    .empty-state i {
        font-size: 60px;
        margin-bottom: 20px;
        color: #dee2e6;
    }
    
    .empty-state h3 {
        margin-bottom: 10px;
        color: #495057;
    }
    
    .empty-state p {
        color: #6c757d;
    }
    
    .loading {
        text-align: center;
        padding: 60px;
        grid-column: 1/-1;
    }
    
    .loading i {
        font-size: 2rem;
        color: #667eea;
        margin-bottom: 15px;
    }
    
    /* مودال */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 10000;
        display: none;
        justify-content: center;
        align-items: center;
    }
    
    .modal-container {
        background: white;
        border-radius: 15px;
        width: 90%;
        max-width: 700px;
        max-height: 80vh;
        overflow-y: auto;
        animation: slideIn 0.3s ease;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
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
    
    .modal-body {
        padding: 20px;
    }
    
    .code-preview {
        background: #1e1e1e;
        color: #d4d4d4;
        padding: 15px;
        border-radius: 10px;
        font-family: monospace;
        font-size: 13px;
        overflow-x: auto;
        white-space: pre-wrap;
        word-break: break-all;
    }
    
    .info-box {
        background: #e7f3ff;
        padding: 12px;
        border-radius: 8px;
        border-right: 4px solid #2196F3;
        margin-top: 15px;
    }
    
    @media (max-width: 768px) {
        .services-grid {
            grid-template-columns: 1fr;
        }
        
        .info-row {
            flex-direction: column;
            gap: 5px;
        }
        
        .info-value {
            text-align: right;
        }
    }
</style>

<div class="page-header">
    <h2><i class="fas fa-cogs"></i> سرویس‌های من</h2>
    <p>لیست سرویس‌های فعال شما به همراه اطلاعات اشتراک و کد نصب ویجت</p>
</div>

<div id="servicesContainer" class="services-grid">
    <div class="loading">
        <i class="fas fa-spinner fa-spin"></i>
        <p>در حال بارگذاری سرویس‌ها...</p>
    </div>
</div>

<!-- مودال نمایش کد ویجت -->
<div id="widgetModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3><i class="fas fa-code"></i> کد نصب ویجت</h3>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-body">
            <div id="modalServiceTitle" style="margin-bottom: 15px; font-weight: bold;"></div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 10px; font-weight: bold;">📱 کد دکمه شناور (Floating Widget):</label>
                <div class="code-preview" id="floatingCodePreview"></div>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 10px; font-weight: bold;">🖥️ کد iFrame (Inline Widget):</label>
                <div class="code-preview" id="iframeCodePreview"></div>
            </div>
            
            <div style="margin-top: 15px; display: flex; gap: 10px; justify-content: flex-end;">
                <button onclick="copyCode('floating')" class="btn-code" style="width: auto; background: #28a745; margin-top: 0;">
                    <i class="fas fa-copy"></i> کپی کد شناور
                </button>
                <button onclick="copyCode('iframe')" class="btn-code" style="width: auto; background: #17a2b8; margin-top: 0;">
                    <i class="fas fa-copy"></i> کپی کد iFrame
                </button>
                <button onclick="closeModal()" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer;">
                    بستن
                </button>
            </div>
            <div class="info-box">
                <i class="fas fa-lightbulb"></i>
                <strong>راهنمای نصب:</strong><br>
                - کد را در انتهای تگ &lt;body&gt; سایت خود قرار دهید.<br>
                - ویجت به صورت خودکار در گوشه صفحه نمایش داده می‌شود.<br>
                - برای تغییر رنگ و موقعیت، به بخش مدیریت سرویس‌ها مراجعه کنید.
            </div>
        </div>
    </div>
</div>

<script>
var baseUrl = window.location.origin;
var currentService = null;
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
    if (type === undefined) type = 'error';
    var alertDiv = document.createElement('div');
    alertDiv.innerHTML = message;
    var bgColor = type === 'error' ? '#f8d7da' : '#d4edda';
    var textColor = type === 'error' ? '#721c24' : '#155724';
    alertDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; background: ' + bgColor + '; color: ' + textColor + '; padding: 12px 20px; border-radius: 8px; z-index: 10001; box-shadow: 0 2px 10px rgba(0,0,0,0.1);';
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
    
    console.log('Widget URL:', widgetUrl);
    console.log('Settings:', settings);
    
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
        iframeCode += '<div style="display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; background: transparent;">\n';
        iframeCode += '    <div style="width: ' + iframeWidth + '; max-width: 100%;">\n';
        iframeCode += '        <iframe \n';
        iframeCode += '            src="' + widgetUrl + '"\n';
        iframeCode += '            width="100%"\n';
        iframeCode += '            height="' + iframeHeight + '"\n';
        iframeCode += '            frameborder="0"\n';
        iframeCode += '            style="border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); display: block;"\n';
        iframeCode += '            title="' + title + '">\n';
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
        iframeCode += '<div style="' + positionStyle + ' width: ' + iframeWidth + '; max-width: 100%;">\n';
        iframeCode += '    <iframe \n';
        iframeCode += '        src="' + widgetUrl + '"\n';
        iframeCode += '        width="100%"\n';
        iframeCode += '        height="' + iframeHeight + '"\n';
        iframeCode += '        frameborder="0"\n';
        iframeCode += '        style="border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); display: block;"\n';
        iframeCode += '        title="' + title + '">\n';
        iframeCode += '    </iframe>\n';
        iframeCode += '</div>';
    }
    
    return { floatingCode: floatingCode, iframeCode: iframeCode };
}

async function loadServices() {
    var container = document.getElementById('servicesContainer');
    
    try {
        var response = await fetch('/mylumina/api/moderator/services');
        var data = await response.json();
        
        if (data.success) {
            renderServices(data.services);
        } else {
            container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>خطا در بارگذاری</h3><p>' + (data.error || 'خطا در دریافت اطلاعات') + '</p></div>';
        }
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = '<div class="empty-state"><i class="fas fa-wifi"></i><h3>خطا در ارتباط با سرور</h3><p>لطفاً صفحه را دوباره بارگذاری کنید.</p><button onclick="loadServices()" style="margin-top: 15px; padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 8px; cursor: pointer;">تلاش مجدد</button></div>';
    }
}

function renderServices(services) {
    var container = document.getElementById('servicesContainer');
    
    if (!services || services.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-inbox"></i><h3>هیچ سرویسی یافت نشد</h3><p>شما هنوز هیچ سرویسی ندارید. لطفاً با پشتیبانی تماس بگیرید.</p></div>';
        return;
    }
    
    var html = '';
    for (var i = 0; i < services.length; i++) {
        var service = services[i];
        var statusClass = 'status-active';
        var statusText = 'فعال';
        var remainingHtml = '';
        
        if (!service.plan_name || service.plan_name === 'بدون اشتراک') {
            statusClass = 'status-warning';
            statusText = 'بدون اشتراک';
            remainingHtml = '<span>⚠️ بدون محدودیت (نامحدود)</span>';
        } else if (service.is_active == 0) {
            statusClass = 'status-expired';
            statusText = 'غیرفعال';
            remainingHtml = '<span>❌ اشتراک غیرفعال است</span>';
        } else if (service.chat_limit > 0 && service.chat_count >= service.chat_limit) {
            statusClass = 'status-expired';
            statusText = 'تمام شده';
            remainingHtml = '<span>📊 چت‌های باقی‌مانده: 0</span>';
        } else {
            if (service.chat_limit > 0) {
                var percent = (service.chat_count / service.chat_limit) * 100;
                remainingHtml = '<div>📊 چت‌های استفاده شده: ' + service.chat_count + ' از ' + service.chat_limit + '</div><div class="progress-bar"><div class="progress-fill" style="width: ' + percent + '%"></div></div>';
            }
            if (service.days_remaining && service.days_remaining !== 'نامحدود') {
                remainingHtml += '<div style="margin-top: 8px;">⏰ ' + service.days_remaining + ' روز باقی مانده</div>';
            }
        }
        
        html += '<div class="service-card">';
        html += '    <div class="service-header">';
        html += '        <div class="service-title">' + escapeHtml(service.title || 'سرویس بدون نام') + '<span class="service-status">' + statusText + '</span></div>';
        html += '        <div class="service-code">کد: ' + escapeHtml(service.service_code) + '</div>';
        html += '    </div>';
        html += '    <div class="service-body">';
        html += '        <div class="service-info">';
        html += '            <div class="info-row"><span class="info-label"><i class="fas fa-globe"></i> دامنه</span><span class="info-value">' + escapeHtml(service.url) + '</span></div>';
        html += '            <div class="info-row"><span class="info-label"><i class="fas fa-tag"></i> طرح اشتراک</span><span class="info-value">' + escapeHtml(service.plan_name || 'بدون اشتراک') + '</span></div>';
        html += '            <div class="info-row"><span class="info-label"><i class="fas fa-calendar"></i> تاریخ ایجاد</span><span class="info-value">' + formatDate(service.created_at) + '</span></div>';
        html += '        </div>';
        html += '        <div class="subscription-box ' + statusClass + '">';
        html += '            <div class="subscription-title">وضعیت اشتراک</div>';
        html += '            ' + remainingHtml;
        html += '        </div>';
        html += '        <button class="btn-code" onclick="showWidgetCode(' + service.id + ')">';
        html += '            <i class="fas fa-code"></i> دریافت کد نصب ویجت';
        html += '        </button>';
        html += '    </div>';
        html += '</div>';
    }
    
    container.innerHTML = html;
}

async function showWidgetCode(serviceId) {
    try {
        var response = await fetch('/mylumina/api/moderator/widget-code/' + serviceId);
        var data = await response.json();
        
        if (data.success) {
            currentService = data.service;
            
            // ============================================================
            // اصلاح: اطمینان از وجود widget_settings در data.service
            // ============================================================
            // اگر widget_settings در response وجود ندارد، از مقدار پیش‌فرض استفاده کن
            if (!data.service.widget_settings) {
                data.service.widget_settings = JSON.stringify({
                    primaryColor: '#667eea',
                    buttonColor: '#28a745',
                    floatingPosition: 'bottom-right',
                    iframeWidth: '500px',
                    iframeHeight: '550',
                    iframePosition: 'center'
                });
            }
            
            var codes = generateWidgetCodes(data.service);
            currentFloatingCode = codes.floatingCode;
            currentIframeCode = codes.iframeCode;
            
            document.getElementById('modalServiceTitle').innerHTML = '<i class="fas fa-robot"></i> سرویس: ' + escapeHtml(data.service.title || 'بدون نام');
            document.getElementById('floatingCodePreview').textContent = currentFloatingCode;
            document.getElementById('iframeCodePreview').textContent = currentIframeCode;
            document.getElementById('widgetModal').style.display = 'flex';
        } else {
            showAlert(data.error || 'خطا در دریافت کد ویجت');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('خطا در دریافت کد ویجت');
    }
}

function copyCode(type) {
    var code = type === 'floating' ? currentFloatingCode : currentIframeCode;
    var typeName = type === 'floating' ? 'دکمه شناور' : 'iFrame';
    
    if (!code) return;
    
    navigator.clipboard.writeText(code).then(function() {
        showAlert('✅ کد ' + typeName + ' با موفقیت کپی شد!', 'success');
    }).catch(function() {
        showAlert('❌ خطا در کپی کردن کد');
    });
}

function closeModal() {
    document.getElementById('widgetModal').style.display = 'none';
    currentService = null;
    currentFloatingCode = '';
    currentIframeCode = '';
}

document.getElementById('widgetModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

loadServices();
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>