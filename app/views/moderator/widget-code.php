<?php
// views/moderator/widget-code.php
require_once __DIR__ . '/../../helpers/widget_helper.php';

$title = 'تنظیمات ویجت';
ob_start();
?>

<style>
    .settings-container { max-width: 800px; margin: 0 auto; }
    .service-selector { margin-bottom: 30px; }
    .settings-card { background: white; border-radius: 16px; padding: 25px; margin-bottom: 30px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
    .settings-card h3 { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #e9ecef; color: #333; }
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; margin-bottom: 8px; font-weight: bold; color: #495057; }
    .form-control { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 8px; font-family: 'Vazirmatn', sans-serif; font-size: 14px; }
    .form-control:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
    .row-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .row-3col { display: grid; grid-template-columns: 1fr 0.5fr 1fr; gap: 10px; align-items: end; }
    .color-preview { display: inline-block; width: 40px; height: 40px; border-radius: 8px; margin-left: 10px; vertical-align: middle; border: 1px solid #ddd; }
    .btn-save { background: #28a745; color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-family: 'Vazirmatn', sans-serif; font-size: 16px; transition: background 0.3s; }
    .btn-save:hover { background: #218838; }
    .code-preview { background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 10px; font-family: monospace; font-size: 13px; overflow-x: auto; white-space: pre-wrap; word-break: break-all; margin-top: 15px; }
    .btn-copy { background: #17a2b8; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; margin-top: 10px; font-family: 'Vazirmatn', sans-serif; }
    .btn-copy:hover { background: #138496; }
    .alert { padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; }
    .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .loading { text-align: center; padding: 50px; }
    @media (max-width: 768px) { 
        .row-2col { grid-template-columns: 1fr; gap: 15px; }
        .row-3col { grid-template-columns: 1fr; gap: 10px; }
    }
</style>

<div class="settings-container">
    <div class="page-header">
        <h2><i class="fas fa-sliders-h"></i> تنظیمات ظاهری ویجت</h2>
        <p>تنظیمات رنگ، عنوان، پیام خوشامدگویی و موقعیت ویجت چت</p>
    </div>

    <div class="service-selector">
        <label class="form-label">انتخاب سرویس</label>
        <select id="serviceSelect" class="form-control">
            <option value="">در حال بارگذاری...</option>
        </select>
    </div>

    <div id="settingsPanel" style="display: none;">
        <form id="settingsForm">
            <input type="hidden" id="serviceId">

            <div class="settings-card">
                <h3><i class="fas fa-palette"></i> تنظیمات عمومی</h3>
                <div class="form-group">
                    <label class="form-label">عنوان ویجت</label>
                    <input type="text" class="form-control" id="widgetTitle" placeholder="مثال: پشتیبانی آنلاین لومینا">
                </div>
                <div class="form-group">
                    <label class="form-label">متن خوشامدگویی</label>
                    <textarea class="form-control" id="welcomeMessage" rows="3" placeholder="سلام! چطور می‌توانم به شما کمک کنم؟"></textarea>
                </div>
                <div class="row-2col">
                    <div class="form-group">
                        <label class="form-label">رنگ اصلی ویجت</label>
                        <div>
                            <input type="color" id="primaryColor" style="width: 60px; height: 40px; border: 1px solid #ddd; border-radius: 8px;">
                            <span id="primaryColorValue" class="color-preview"></span>
                            <span id="primaryColorText" style="margin-right: 10px;">#667eea</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">رنگ دکمه ارسال</label>
                        <div>
                            <input type="color" id="buttonColor" style="width: 60px; height: 40px; border: 1px solid #ddd; border-radius: 8px;">
                            <span id="buttonColorValue" class="color-preview"></span>
                            <span id="buttonColorText" style="margin-right: 10px;">#28a745</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-card">
                <h3><i class="fas fa-comment-dots"></i> تنظیمات حالت شناور (Floating Widget)</h3>
                <div class="form-group">
                    <label class="form-label">موقعیت دکمه شناور</label>
                    <select class="form-control" id="floatingPosition">
                        <option value="bottom-right">پایین راست</option>
                        <option value="bottom-left">پایین چپ</option>
                    </select>
                </div>
            </div>

            <div class="settings-card">
                <h3><i class="fas fa-window-maximize"></i> تنظیمات حالت iFrame</h3>
                
                <!-- عرض با انتخاب واحد -->
                <div class="form-group">
                    <label class="form-label">عرض</label>
                    <div class="row-3col">
                        <input type="number" class="form-control" id="iframeWidth" value="500" min="100" max="1200" step="10">
                        <select class="form-control" id="iframeWidthUnit">
                            <option value="px">پیکسل (px)</option>
                            <option value="%">درصد (%)</option>
                        </select>
                        <span style="color: #6c757d; font-size: 12px; padding-top: 8px;">عدد بین 100 تا 1200</span>
                    </div>
                </div>
                
                <!-- ارتفاع -->
                <div class="form-group">
                    <label class="form-label">ارتفاع (پیکسل)</label>
                    <div class="row-3col">
                        <input type="number" class="form-control" id="iframeHeight" value="550" min="200" max="900" step="10">
                        <span style="color: #6c757d; font-size: 12px; padding-top: 8px;">پیکسل (px)</span>
                        <span style="color: #6c757d; font-size: 12px; padding-top: 8px;">عدد بین 200 تا 900</span>
                    </div>
                </div>
                
                <!-- موقعیت -->
                <div class="form-group">
                    <label class="form-label">موقعیت iFrame در صفحه</label>
                    <select class="form-control" id="iframePosition">
                        <option value="center">وسط صفحه</option>
                        <option value="bottom-right">پایین راست</option>
                        <option value="bottom-left">پایین چپ</option>
                        <option value="top-right">بالا راست</option>
                        <option value="top-left">بالا چپ</option>
                    </select>
                </div>
            </div>

            <div style="text-align: left; margin: 20px 0;">
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> ذخیره تنظیمات</button>
            </div>
        </form>

        <div class="settings-card">
            <h3><i class="fas fa-code"></i> کد نصب ویجت</h3>
            <div class="form-group">
                <label class="form-label">📱 کد دکمه شناور (Floating Widget)</label>
                <div class="code-preview" id="floatingCodePreview"></div>
                <button class="btn-copy" onclick="copyCode('floating')">کپی کد شناور</button>
            </div>
            <div class="form-group">
                <label class="form-label">🖥️ کد iFrame (Inline Widget)</label>
                <div class="code-preview" id="iframeCodePreview"></div>
                <button class="btn-copy" onclick="copyCode('iframe')">کپی کد iFrame</button>
            </div>
            <div class="alert alert-success">
                <i class="fas fa-lightbulb"></i>
                <strong>راهنما:</strong> این کد را در انتهای تگ &lt;body&gt; سایت خود قرار دهید.
            </div>
        </div>
    </div>
</div>

<script>
const baseUrl = window.location.origin;
let services = [];
let currentService = null;
let currentFloatingCode = '';
let currentIframeCode = '';

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = message;
    alertDiv.style.cssText = `position: fixed; top: 20px; right: 20px; z-index: 10001; min-width: 300px;`;
    document.body.appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 3000);
}

async function loadServices() {
    try {
        const response = await fetch('/mylumina/api/moderator/services');
        const data = await response.json();
        if (data.success && data.services) {
            services = data.services;
            renderServiceSelect();
            if (services.length > 0) loadServiceSettings(services[0].id);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('خطا در بارگذاری سرویس‌ها', 'danger');
    }
}

function renderServiceSelect() {
    const select = document.getElementById('serviceSelect');
    if (services.length === 0) {
        select.innerHTML = '<option value="">هیچ سرویسی یافت نشد</option>';
        return;
    }
    select.innerHTML = services.map(s => `<option value="${s.id}">${escapeHtml(s.title || 'سرویس بدون نام')} (${s.service_code})</option>`).join('');
    select.onchange = () => { if (select.value) loadServiceSettings(parseInt(select.value)); };
}

async function loadServiceSettings(serviceId) {
    const service = services.find(s => s.id == serviceId);
    if (!service) return;
    currentService = service;
    document.getElementById('serviceId').value = service.id;
    
    let settings = {};
    if (service.widget_settings) {
        try { settings = JSON.parse(service.widget_settings); } catch(e) {}
    }
    
    document.getElementById('widgetTitle').value = service.title || '';
    document.getElementById('welcomeMessage').value = service.welcome_message || 'سلام! چطور می‌توانم به شما کمک کنم؟';
    
    const primaryColor = settings.primaryColor || '#667eea';
    document.getElementById('primaryColor').value = primaryColor;
    document.getElementById('primaryColorValue').style.backgroundColor = primaryColor;
    document.getElementById('primaryColorText').textContent = primaryColor;
    
    const buttonColor = settings.buttonColor || '#28a745';
    document.getElementById('buttonColor').value = buttonColor;
    document.getElementById('buttonColorValue').style.backgroundColor = buttonColor;
    document.getElementById('buttonColorText').textContent = buttonColor;
    
    document.getElementById('floatingPosition').value = settings.floatingPosition || 'bottom-right';
    
    // بارگذاری عرض با جداسازی عدد و واحد
    let iframeWidth = settings.iframeWidth || '500px';
    let widthValue = parseInt(iframeWidth) || 500;
    let widthUnit = iframeWidth.includes('%') ? '%' : 'px';
    document.getElementById('iframeWidth').value = widthValue;
    document.getElementById('iframeWidthUnit').value = widthUnit;
    
    document.getElementById('iframeHeight').value = settings.iframeHeight || '550';
    document.getElementById('iframePosition').value = settings.iframePosition || 'center';
    
    document.getElementById('settingsPanel').style.display = 'block';
    updateCodePreview();
}

function getCurrentSettings() {
    // ترکیب عدد و واحد برای عرض
    let widthValue = document.getElementById('iframeWidth').value;
    let widthUnit = document.getElementById('iframeWidthUnit').value;
    let iframeWidth = widthValue + widthUnit;
    
    return {
        title: document.getElementById('widgetTitle').value,
        welcomeMessage: document.getElementById('welcomeMessage').value,
        primaryColor: document.getElementById('primaryColor').value,
        buttonColor: document.getElementById('buttonColor').value,
        floatingPosition: document.getElementById('floatingPosition').value,
        iframeWidth: iframeWidth,
        iframeHeight: document.getElementById('iframeHeight').value,
        iframePosition: document.getElementById('iframePosition').value
    };
}

function updateCodePreview() {
    if (!currentService) return;
    
    const settings = getCurrentSettings();
    
    // تنظیمات ویجت
    const widgetSettings = {
        primaryColor: settings.primaryColor || '#667eea',
        buttonColor: settings.buttonColor || '#28a745',
        floatingPosition: settings.floatingPosition || 'bottom-right',
        iframeWidth: settings.iframeWidth || '500px',
        iframeHeight: settings.iframeHeight || '550',
        iframePosition: settings.iframePosition || 'center'
    };
    
    // ارسال به سرور برای دریافت کد ویجت
    fetch('/mylumina/api/moderator/generate-widget-code', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            service_id: currentService.id,
            settings: widgetSettings
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentFloatingCode = data.floating_code;
            currentIframeCode = data.iframe_code;
            document.getElementById('floatingCodePreview').textContent = currentFloatingCode;
            document.getElementById('iframeCodePreview').textContent = currentIframeCode;
        }
    })
    .catch(error => console.error('Error:', error));
}

async function saveSettings() {
    const serviceId = document.getElementById('serviceId').value;
    if (!serviceId) { showAlert('لطفاً ابتدا یک سرویس انتخاب کنید', 'danger'); return; }
    
    const settings = getCurrentSettings();
    
    const dataToSend = {
        service_id: parseInt(serviceId),
        settings: {
            title: settings.title,
            welcome_message: settings.welcomeMessage,
            primaryColor: settings.primaryColor,
            buttonColor: settings.buttonColor,
            floatingPosition: settings.floatingPosition,
            iframeWidth: settings.iframeWidth,
            iframeHeight: settings.iframeHeight,
            iframePosition: settings.iframePosition
        }
    };
    
    console.log('Saving settings:', dataToSend);
    
    try {
        const response = await fetch('/mylumina/api/moderator/widget-settings', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dataToSend)
        });
        const data = await response.json();
        if (data.success) {
            showAlert('تنظیمات با موفقیت ذخیره شد', 'success');
            const service = services.find(s => s.id == serviceId);
            if (service) {
                service.title = settings.title;
                service.welcome_message = settings.welcomeMessage;
                service.widget_settings = JSON.stringify({
                    primaryColor: settings.primaryColor,
                    buttonColor: settings.buttonColor,
                    floatingPosition: settings.floatingPosition,
                    iframeWidth: settings.iframeWidth,
                    iframeHeight: settings.iframeHeight,
                    iframePosition: settings.iframePosition
                });
            }
            updateCodePreview();
        } else {
            showAlert(data.error || 'خطا در ذخیره تنظیمات', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('خطا در ارتباط با سرور', 'danger');
    }
}

function copyCode(type) {
    const code = type === 'floating' ? currentFloatingCode : currentIframeCode;
    const typeName = type === 'floating' ? 'دکمه شناور' : 'iFrame';
    navigator.clipboard.writeText(code).then(() => showAlert(`✅ کد ${typeName} با موفقیت کپی شد!`, 'success'))
        .catch(() => showAlert('❌ خطا در کپی کردن کد', 'danger'));
}

document.getElementById('primaryColor').addEventListener('input', function(e) {
    const val = e.target.value;
    document.getElementById('primaryColorValue').style.backgroundColor = val;
    document.getElementById('primaryColorText').textContent = val;
    updateCodePreview();
});

document.getElementById('buttonColor').addEventListener('input', function(e) {
    const val = e.target.value;
    document.getElementById('buttonColorValue').style.backgroundColor = val;
    document.getElementById('buttonColorText').textContent = val;
    updateCodePreview();
});

// رویدادهای تغییر برای بروزرسانی زنده
['widgetTitle', 'welcomeMessage', 'floatingPosition', 'iframeWidth', 'iframeWidthUnit', 'iframeHeight', 'iframePosition'].forEach(id => {
    const el = document.getElementById(id);
    if (el) { 
        el.addEventListener('input', () => updateCodePreview()); 
        el.addEventListener('change', () => updateCodePreview()); 
    }
});

document.getElementById('settingsForm').addEventListener('submit', function(e) { e.preventDefault(); saveSettings(); });
loadServices();
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>