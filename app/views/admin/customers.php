<?php
$title = 'مدیریت مشتریان';
ob_start();
?>

<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-family: 'Vazirmatn', sans-serif;
        transition: all 0.3s;
    }
    
    .btn-primary {
        background: #667eea;
        color: white;
    }
    
    .btn-primary:hover {
        background: #5a67d8;
    }
    
    .btn-warning {
        background: #ffc107;
        color: #212529;
    }
    
    .btn-danger {
        background: #dc3545;
        color: white;
    }
    
    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
    }
    
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table th,
    .table td {
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
    
    .badge-success {
        background: #d4edda;
        color: #155724;
    }
    
    .badge-danger {
        background: #f8d7da;
        color: #721c24;
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
    
    .modal-body {
        padding: 20px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    
    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-family: 'Vazirmatn', sans-serif;
    }
    
    .search-box {
        margin-bottom: 20px;
    }
    
    .search-input {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-family: 'Vazirmatn', sans-serif;
    }
</style>

<div class="page-header">
    <h2>مدیریت مشتریان</h2>
    <button class="btn btn-primary" onclick="openModal()">
        <i class="fas fa-plus"></i> مشتری جدید
    </button>
</div>

<div class="search-box">
    <input type="text" class="search-input" id="searchInput" placeholder="جستجو بر اساس نام، شرکت یا تلفن...">
</div>

<div class="filter-box" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">
    <label>
        <input type="radio" name="statusFilter" value="all" checked> همه
    </label>
    <label>
        <input type="radio" name="statusFilter" value="active"> فعال
    </label>
    <label>
        <input type="radio" name="statusFilter" value="inactive"> غیرفعال
    </label>
</div>

<div class="card">
    <div class="card-header">
        <h3>لیست مشتریان</h3>
        <span id="totalCount">0 مشتری</span>
    </div>
    <div class="card-body">
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>کد مشتری</th>
                        <th>نام کامل</th>
                        <th>شرکت</th>
                        <th>تلفن</th>
                        <th>سرویس‌ها</th>
                        <th>وضعیت</th>
                        <th>تاریخ ایجاد</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody id="customersTableBody">
                    <tr><td colspan="8" style="text-align: center;">در حال بارگذاری...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal ایجاد/ویرایش مشتری -->
<div id="customerModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">ایجاد مشتری جدید</h3>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">×</button>
        </div>
        <div class="modal-body">
            <form id="customerForm">
                <input type="hidden" id="customerId">
                
                <div class="form-group">
                    <label class="form-label">نام کامل *</label>
                    <input type="text" class="form-control" id="full_name" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">نام شرکت</label>
                    <input type="text" class="form-control" id="company_name">
                </div>
                
                <div class="form-group">
                    <label class="form-label">شماره تلفن</label>
                    <input type="tel" class="form-control" id="phone">
                </div>
                
                <div class="form-group">
                    <label class="form-label">آدرس</label>
                    <textarea class="form-control" id="address" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="is_active" checked> فعال
                    </label>
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-warning" onclick="closeModal()">انصراف</button>
                    <button type="submit" class="btn btn-primary">ذخیره</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let customers = [];
let token = '<?php echo $_SESSION['admin']['id'] ?? ''; ?>';

function getToken() {
    // در اینجا می‌توانید توکن را از localStorage یا session بگیرید
    return '';
}

async function loadCustomers() {
    try {
        const response = await fetch('/mylumina/api/customers/data');
        const data = await response.json();
        
        if (data.success) {
            customers = data.customers;
            renderCustomers(customers);
            document.getElementById('totalCount').textContent = customers.length + ' مشتری';
        } else {
            showAlert(data.error, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('خطا در بارگذاری مشتریان', 'danger');
    }
}

function renderCustomers(customersList) {
    const tbody = document.getElementById('customersTableBody');
    
    if (customersList.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">هیچ مشتری یافت نشد</td></tr>';
        return;
    }
    
    tbody.innerHTML = '';
    
    customersList.forEach(customer => {
        const row = document.createElement('tr');
        
        // تعیین وضعیت دکمه بر اساس is_active
        const statusText = customer.is_active ? 'فعال' : 'غیرفعال';
        const statusClass = customer.is_active ? 'badge-success' : 'badge-danger';
        const actionBtnClass = customer.is_active ? 'btn-danger' : 'btn-success';
        const actionBtnIcon = customer.is_active ? 'fa-ban' : 'fa-check';
        const actionBtnText = customer.is_active ? 'غیرفعال' : 'فعال';
        
        row.innerHTML = `
            <td><code>${escapeHtml(customer.customer_code)}</code></td>
            <td>${escapeHtml(customer.full_name)}</td>
            <td>${escapeHtml(customer.company_name || '-')}</td>
            <td>${escapeHtml(customer.phone || '-')}</td>
            <td><span class="badge badge-success">${customer.service_count || 0} سرویس</span></td>
            <td>
                <span class="badge ${statusClass}">
                    ${statusText}
                </span>
            </td>
            <td>${formatDate(customer.created_at)}</td>
            <td>
                <button class="btn btn-warning btn-sm" onclick="editCustomer(${customer.id})" title="ویرایش">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn ${actionBtnClass} btn-sm" onclick="toggleCustomerStatus(${customer.id}, ${customer.is_active})" title="${actionBtnText} کردن">
                    <i class="fas ${actionBtnIcon}"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

async function toggleCustomerStatus(id, isCurrentlyActive) {
    const action = isCurrentlyActive ? 'غیرفعال' : 'فعال';
    if (!confirm(`آیا از ${action} کردن این مشتری اطمینان دارید؟`)) return;
    
    try {
        // از同一个 endpoint DELETE استفاده می‌کنیم اما منطق آن تغییر کرده
        const response = await fetch(`/mylumina/api/customers/${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            loadCustomers(); // بازخوانی لیست
        } else {
            showAlert(result.error, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('خطا در ارتباط با سرور', 'danger');
    }
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('fa-IR');
}

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
    alertDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    document.body.appendChild(alertDiv);
    
    setTimeout(() => alertDiv.remove(), 3000);
}

async function saveCustomer() {
    const id = document.getElementById('customerId').value;
    const data = {
        full_name: document.getElementById('full_name').value.trim(),
        company_name: document.getElementById('company_name').value.trim(),
        phone: document.getElementById('phone').value.trim(),
        address: document.getElementById('address').value.trim(),
        is_active: document.getElementById('is_active').checked
    };
    
    if (!data.full_name) {
        showAlert('نام کامل الزامی است', 'danger');
        return;
    }
    
    const url = id ? `/mylumina/api/customers/${id}` : '/mylumina/api/customers';
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
            closeModal();
            loadCustomers();
        } else {
            showAlert(result.error, 'danger');
        }
    } catch (error) {
        showAlert('خطا در ارتباط با سرور', 'danger');
    }
}

function openModal() {
    document.getElementById('modalTitle').textContent = 'ایجاد مشتری جدید';
    document.getElementById('customerForm').reset();
    document.getElementById('customerId').value = '';
    document.getElementById('is_active').checked = true;
    document.getElementById('customerModal').style.display = 'flex';
}

function editCustomer(id) {
    const customer = customers.find(c => c.id == id);
    if (!customer) return;
    
    document.getElementById('modalTitle').textContent = 'ویرایش مشتری';
    document.getElementById('customerId').value = customer.id;
    document.getElementById('full_name').value = customer.full_name;
    document.getElementById('company_name').value = customer.company_name || '';
    document.getElementById('phone').value = customer.phone || '';
    document.getElementById('address').value = customer.address || '';
    document.getElementById('is_active').checked = customer.is_active;
    document.getElementById('customerModal').style.display = 'flex';
}

async function deleteCustomer(id) {
    if (!confirm('آیا از حذف این مشتری اطمینان دارید؟')) return;
    
    try {
        const response = await fetch(`/mylumina/api/customers/${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            loadCustomers();
        } else {
            showAlert(result.error, 'danger');
        }
    } catch (error) {
        showAlert('خطا در ارتباط با سرور', 'danger');
    }
}

function closeModal() {
    document.getElementById('customerModal').style.display = 'none';
}

// جستجو
document.getElementById('searchInput').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    const filtered = customers.filter(c => 
        c.full_name.toLowerCase().includes(term) ||
        (c.company_name && c.company_name.toLowerCase().includes(term)) ||
        (c.phone && c.phone.includes(term))
    );
    renderCustomers(filtered);
});

// اضافه کردن event listener برای فیلتر وضعیت
document.querySelectorAll('input[name="statusFilter"]').forEach(radio => {
    radio.addEventListener('change', function() {
        filterByStatus(this.value);
    });
});

function filterByStatus(status) {
    let filtered = [...customers];
    
    if (status === 'active') {
        filtered = filtered.filter(c => c.is_active == 1);
    } else if (status === 'inactive') {
        filtered = filtered.filter(c => c.is_active == 0);
    }
    
    renderCustomers(filtered);
}

// رویداد فرم
document.getElementById('customerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    saveCustomer();
});

// بارگذاری اولیه
loadCustomers();
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>