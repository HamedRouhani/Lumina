<?php
$title = 'مدیریت طرح‌های اشتراک';
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
    .btn-sm { padding: 5px 10px; font-size: 12px; margin: 2px; }
    
    .plans-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .plan-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .plan-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    .plan-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        text-align: center;
    }
    
    .plan-name {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .plan-body {
        padding: 20px;
    }
    
    .plan-detail {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #e9ecef;
    }
    
    .plan-detail:last-child {
        border-bottom: none;
    }
    
    .detail-label {
        color: #6c757d;
        font-size: 14px;
    }
    
    .detail-value {
        font-weight: bold;
        color: #333;
    }
    
    .plan-actions {
        padding: 15px 20px;
        border-top: 1px solid #e9ecef;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
    
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
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
    }
    
    .modal-header {
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
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
    
    .empty-state {
        text-align: center;
        padding: 60px;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 48px;
        margin-bottom: 15px;
        color: #dee2e6;
    }
    
    .info-box {
        background: #e7f3ff;
        padding: 15px;
        border-radius: 8px;
        border-right: 4px solid #2196F3;
        margin-bottom: 20px;
    }
</style>

<div class="page-header">
    <h2>مدیریت طرح‌های اشتراک</h2>
    <button class="btn btn-primary" onclick="openPlanModal()">
        <i class="fas fa-plus"></i> طرح جدید
    </button>
</div>

<div class="info-box">
    <i class="fas fa-info-circle"></i>
    <strong>راهنما:</strong>
    <ul style="margin-top: 10px; margin-right: 20px;">
        <li>طرح‌های اشتراک تعیین می‌کنند که هر سرویس چند چت می‌تواند داشته باشد و تا چه مدت معتبر است.</li>
        <li>مقدار <strong>0</strong> در محدودیت چت یا مدت زمان به معنای <strong>نامحدود</strong> است.</li>
        <li>پس از ایجاد طرح، می‌توانید آن را در صفحه مدیریت سرویس‌ها به سرویس مورد نظر تخصیص دهید.</li>
    </ul>
</div>

<div class="card">
    <div class="card-header">
        <h3>لیست طرح‌های اشتراک</h3>
        <span id="totalCount">0 طرح</span>
    </div>
    <div class="card-body">
        <div id="plansContainer" class="plans-grid">
            <!-- طرح‌ها اینجا نمایش داده می‌شوند -->
        </div>
        <div id="emptyState" class="empty-state" style="display: none;">
            <i class="fas fa-file-contract"></i>
            <h3>هیچ طرح اشتراکی یافت نشد</h3>
            <p>برای شروع، اولین طرح اشتراک خود را ایجاد کنید.</p>
            <button class="btn btn-primary" onclick="openPlanModal()">
                <i class="fas fa-plus"></i> ایجاد طرح اول
            </button>
        </div>
    </div>
</div>

<!-- Modal ایجاد/ویرایش طرح -->
<div id="planModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">ایجاد طرح اشتراک جدید</h3>
            <button onclick="closePlanModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">×</button>
        </div>
        <div class="modal-body">
            <form id="planForm">
                <input type="hidden" id="planId">
                
                <div class="form-group">
                    <label class="form-label">نام طرح *</label>
                    <input type="text" class="form-control" id="plan_name" required placeholder="مثال: برنز ماهانه">
                </div>
                
                <div class="form-group">
                    <label class="form-label">محدودیت تعداد چت</label>
                    <input type="number" class="form-control" id="chat_limit" value="0" min="0">
                    <small>عدد 0 به معنی نامحدود</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">مدت زمان اعتبار (روز)</label>
                    <input type="number" class="form-control" id="duration_days" value="0" min="0">
                    <small>عدد 0 به معنی نامحدود</small>
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-warning" onclick="closePlanModal()">انصراف</button>
                    <button type="submit" class="btn btn-primary">ذخیره طرح</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let plans = [];

// ==================== توابع کمکی ====================
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
    alertDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; padding: 12px; border-radius: 8px;';
    if (type === 'success') alertDiv.style.background = '#d4edda';
    else if (type === 'danger') alertDiv.style.background = '#f8d7da';
    document.body.appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 3000);
}

function formatNumber(num) {
    if (num === null || num === undefined) return '-';
    return num.toLocaleString('fa-IR');
}

// ==================== بارگذاری داده‌ها ====================
async function loadPlans() {
    try {
        const response = await fetch('/mylumina/api/subscription-plans/data');
        const data = await response.json();
        
        if (data.success) {
            plans = data.plans;
            renderPlans(plans);
            document.getElementById('totalCount').textContent = plans.length + ' طرح';
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('خطا در بارگذاری طرح‌ها', 'danger');
    }
}

function renderPlans(plansList) {
    const container = document.getElementById('plansContainer');
    const emptyState = document.getElementById('emptyState');
    
    if (plansList.length === 0) {
        container.innerHTML = '';
        emptyState.style.display = 'block';
        return;
    }
    
    emptyState.style.display = 'none';
    container.innerHTML = '';
    
    plansList.forEach(plan => {
        const chatLimitText = plan.chat_limit == 0 ? 'نامحدود' : formatNumber(plan.chat_limit);
        const durationText = plan.duration_days == 0 ? 'نامحدود' : formatNumber(plan.duration_days) + ' روز';
        
        const card = document.createElement('div');
        card.className = 'plan-card';
        card.innerHTML = `
            <div class="plan-header">
                <div class="plan-name">${escapeHtml(plan.name)}</div>
            </div>
            <div class="plan-body">
                <div class="plan-detail">
                    <span class="detail-label"><i class="fas fa-comments"></i> محدودیت چت</span>
                    <span class="detail-value">${chatLimitText}</span>
                </div>
                <div class="plan-detail">
                    <span class="detail-label"><i class="fas fa-calendar-alt"></i> مدت اعتبار</span>
                    <span class="detail-value">${durationText}</span>
                </div>
                <div class="plan-detail">
                    <span class="detail-label"><i class="fas fa-id-card"></i> شناسه</span>
                    <span class="detail-value">${plan.id}</span>
                </div>
            </div>
            <div class="plan-actions">
                <button class="btn btn-warning btn-sm" onclick="editPlan(${plan.id})">
                    <i class="fas fa-edit"></i> ویرایش
                </button>
                <button class="btn btn-danger btn-sm" onclick="deletePlan(${plan.id})">
                    <i class="fas fa-trash"></i> حذف
                </button>
            </div>
        `;
        container.appendChild(card);
    });
}

// ==================== ایجاد/ویرایش طرح ====================
function openPlanModal() {
    document.getElementById('modalTitle').textContent = 'ایجاد طرح اشتراک جدید';
    document.getElementById('planForm').reset();
    document.getElementById('planId').value = '';
    document.getElementById('chat_limit').value = 0;
    document.getElementById('duration_days').value = 0;
    document.getElementById('planModal').style.display = 'flex';
}

function closePlanModal() {
    document.getElementById('planModal').style.display = 'none';
}

function editPlan(id) {
    const plan = plans.find(p => p.id == id);
    if (!plan) return;
    
    document.getElementById('modalTitle').textContent = 'ویرایش طرح اشتراک';
    document.getElementById('planId').value = plan.id;
    document.getElementById('plan_name').value = plan.name;
    document.getElementById('chat_limit').value = plan.chat_limit;
    document.getElementById('duration_days').value = plan.duration_days;
    document.getElementById('planModal').style.display = 'flex';
}

async function savePlan() {
    const id = document.getElementById('planId').value;
    const data = {
        name: document.getElementById('plan_name').value.trim(),
        chat_limit: parseInt(document.getElementById('chat_limit').value) || 0,
        duration_days: parseInt(document.getElementById('duration_days').value) || 0
    };
    
    if (!data.name) {
        showAlert('نام طرح الزامی است', 'danger');
        return;
    }
    
    const url = id ? `/mylumina/api/subscription-plans/${id}` : '/mylumina/api/subscription-plans';
    const method = id ? 'PUT' : 'POST';
    
    try {
        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            closePlanModal();
            loadPlans();
        } else {
            showAlert(result.error, 'danger');
        }
    } catch (error) {
        showAlert('خطا در ارتباط با سرور', 'danger');
    }
}

// ==================== حذف طرح ====================
async function deletePlan(id) {
    const plan = plans.find(p => p.id == id);
    if (!plan) return;
    
    if (!confirm(`آیا از حذف طرح "${plan.name}" اطمینان دارید؟\n\n⚠️ این عمل غیرقابل بازگشت است.`)) return;
    
    try {
        const response = await fetch(`/mylumina/api/subscription-plans/${id}`, {
            method: 'DELETE'
        });
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            loadPlans();
        } else {
            showAlert(result.error, 'danger');
        }
    } catch (error) {
        showAlert('خطا در ارتباط با سرور', 'danger');
    }
}

// ==================== رویدادها ====================
document.getElementById('planForm').addEventListener('submit', function(e) {
    e.preventDefault();
    savePlan();
});

// ==================== راه‌اندازی اولیه ====================
loadPlans();
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>