<?php
$title = 'مدیریت کاربران';
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
    .btn-sm { padding: 5px 10px; font-size: 12px; margin: 2px; }
    
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
    .badge-primary { background: #cce5ff; color: #004085; }
    
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
    select.form-control { cursor: pointer; }
    
    .search-box { margin-bottom: 20px; }
    .search-input {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-family: 'Vazirmatn', sans-serif;
    }
    
    .filter-box {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    
    .filter-box label {
        display: flex;
        align-items: center;
        gap: 5px;
        cursor: pointer;
    }
</style>

<div class="page-header">
    <h2>مدیریت کاربران سیستم</h2>
    <button class="btn btn-primary" onclick="openUserModal()">
        <i class="fas fa-plus"></i> کاربر جدید
    </button>
</div>

<div class="search-box">
    <input type="text" class="search-input" id="searchInput" placeholder="جستجو بر اساس نام کاربری، نام کامل یا ایمیل...">
</div>

<div class="filter-box">
    <label>
        <input type="radio" name="roleFilter" value="all" checked> همه
    </label>
    <label>
        <input type="radio" name="roleFilter" value="super_admin"> مدیر کل
    </label>
    <label>
        <input type="radio" name="roleFilter" value="admin"> مدیر
    </label>
    <label>
        <input type="radio" name="roleFilter" value="moderator"> ناظر
    </label>
    <label>
        <input type="radio" name="statusFilter" value="all" checked> همه وضعیت‌ها
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
        <h3>لیست کاربران</h3>
        <span id="totalCount">0 کاربر</span>
    </div>
    <div class="card-body">
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>شناسه</th>
                        <th>نام کاربری</th>
                        <th>نام کامل</th>
                        <th>ایمیل</th>
                        <th>نقش</th>
                        <th>مشتری</th>
                        <th>وضعیت</th>
                        <th>تاریخ ایجاد</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <tr><td colspan="9" style="text-align: center;">در حال بارگذاری...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal ایجاد/ویرایش کاربر -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">ایجاد کاربر جدید</h3>
            <button onclick="closeUserModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">×</button>
        </div>
        <div class="modal-body">
            <form id="userForm">
                <input type="hidden" id="userId">
                
                <div class="form-group">
                    <label class="form-label">نام کاربری *</label>
                    <input type="text" class="form-control" id="username" required>
                </div>
                
                <div class="form-group" id="passwordGroup">
                    <label class="form-label">رمز عبور *</label>
                    <input type="password" class="form-control" id="password" autocomplete="new-password">
                    <small>حداقل ۶ کاراکتر - در ویرایش خالی بگذارید تا بدون تغییر بماند</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">نام کامل *</label>
                    <input type="text" class="form-control" id="full_name" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">ایمیل</label>
                    <input type="email" class="form-control" id="email">
                </div>
                
                <div class="form-group">
                    <label class="form-label">نقش *</label>
                    <select class="form-control" id="role">
                        <option value="moderator">ناظر</option>
                        <option value="admin">مدیر</option>
                        <option value="super_admin">مدیر کل</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">مشتری مرتبط</label>
                    <select class="form-control" id="customer_id">
                        <option value="">بدون مشتری</option>
                    </select>
                    <small>اختیاری - فقط برای کاربران ناظر</small>
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-warning" onclick="closeUserModal()">انصراف</button>
                    <button type="submit" class="btn btn-primary">ذخیره کاربر</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal تغییر رمز عبور -->
<div id="passwordModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>تغییر رمز عبور</h3>
            <button onclick="closePasswordModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">×</button>
        </div>
        <div class="modal-body">
            <form id="passwordForm">
                <input type="hidden" id="passwordUserId">
                
                <div class="form-group" id="currentPasswordGroup">
                    <label class="form-label">رمز عبور فعلی *</label>
                    <input type="password" class="form-control" id="current_password">
                </div>
                
                <div class="form-group">
                    <label class="form-label">رمز عبور جدید *</label>
                    <input type="password" class="form-control" id="new_password" required>
                    <small>حداقل ۶ کاراکتر</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">تکرار رمز عبور جدید *</label>
                    <input type="password" class="form-control" id="confirm_password" required>
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-warning" onclick="closePasswordModal()">انصراف</button>
                    <button type="submit" class="btn btn-primary">تغییر رمز</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let users = [];
let customers = [];
let currentUserId = null;
let currentUserRole = null;

// ==================== توابع کمکی ====================
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('fa-IR');
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

function getRoleName(role) {
    const roles = {
        'super_admin': 'مدیر کل',
        'admin': 'مدیر',
        'moderator': 'ناظر'
    };
    return roles[role] || role;
}

function getRoleBadgeClass(role) {
    const classes = {
        'super_admin': 'badge-danger',
        'admin': 'badge-warning',
        'moderator': 'badge-success'
    };
    return classes[role] || 'badge-info';
}

// ==================== بارگذاری داده‌ها ====================
async function loadCustomers() {
    try {
        const response = await fetch('/mylumina/api/admin/customers');
        const data = await response.json();
        if (data.success) {
            customers = data.customers;
            const select = document.getElementById('customer_id');
            select.innerHTML = '<option value="">بدون مشتری</option>';
            customers.forEach(c => {
                const option = document.createElement('option');
                option.value = c.id;
                option.textContent = `${c.company_name || c.full_name} (${c.customer_code})`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading customers:', error);
    }
}

async function loadUsers() {
    try {
        const response = await fetch('/mylumina/api/admin/users/data');
        const data = await response.json();
        if (data.success) {
            users = data.users;
            currentUserId = data.current_user_id;
            currentUserRole = data.current_user_role;
            renderUsers(users);
            document.getElementById('totalCount').textContent = users.length + ' کاربر';
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('خطا در بارگذاری کاربران', 'danger');
    }
}

function renderUsers(usersList) {
    const tbody = document.getElementById('usersTableBody');
    
    if (usersList.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align: center;">هیچ کاربری یافت نشد</td></tr>';
        return;
    }
    
    tbody.innerHTML = '';
    
    usersList.forEach(user => {
        const statusText = user.is_active ? 'فعال' : 'غیرفعال';
        const statusClass = user.is_active ? 'badge-success' : 'badge-danger';
        const actionBtnClass = user.is_active ? 'btn-danger' : 'btn-success';
        const actionBtnIcon = user.is_active ? 'fa-ban' : 'fa-check';
        const actionBtnText = user.is_active ? 'غیرفعال' : 'فعال';
        
        // غیرفعال کردن دکمه تغییر وضعیت برای کاربر جاری
        const isCurrentUser = (user.id == currentUserId);
        const disableToggle = isCurrentUser ? 'disabled' : '';
        
        // بررسی دسترسی برای نمایش دکمه‌ها
        const canEdit = (currentUserRole === 'super_admin') || 
                       (currentUserRole === 'admin' && user.role !== 'super_admin');
        const canChangePassword = true;
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${user.id}</td>
            <td><strong>${escapeHtml(user.username)}</strong></td>
            <td>${escapeHtml(user.full_name)}</td>
            <td>${escapeHtml(user.email || '-')}</td>
            <td><span class="badge ${getRoleBadgeClass(user.role)}">${getRoleName(user.role)}</span></td>
            <td>${escapeHtml(user.company_name || user.customer_full_name || '-')}</td>
            <td><span class="badge ${statusClass}">${statusText}</span></td>
            <td>${formatDate(user.created_at)}</td>
            <td>
                <button class="btn btn-info btn-sm" onclick="changePassword(${user.id})" title="تغییر رمز">
                    <i class="fas fa-key"></i>
                </button>
                ${canEdit ? `
                <button class="btn btn-warning btn-sm" onclick="editUser(${user.id})" title="ویرایش">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn ${actionBtnClass} btn-sm" onclick="toggleUserStatus(${user.id}, ${user.is_active})" title="${actionBtnText} کردن" ${disableToggle}>
                    <i class="fas ${actionBtnIcon}"></i>
                </button>
                ` : ''}
            </td>
        `;
        tbody.appendChild(row);
    });
}

// ==================== فیلترها ====================
function filterUsers() {
    const roleFilter = document.querySelector('input[name="roleFilter"]:checked').value;
    const statusFilter = document.querySelector('input[name="statusFilter"]:checked').value;
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    
    let filtered = [...users];
    
    if (roleFilter !== 'all') {
        filtered = filtered.filter(u => u.role === roleFilter);
    }
    
    if (statusFilter !== 'all') {
        filtered = filtered.filter(u => u.is_active == (statusFilter === 'active' ? 1 : 0));
    }
    
    if (searchTerm) {
        filtered = filtered.filter(u => 
            u.username.toLowerCase().includes(searchTerm) ||
            u.full_name.toLowerCase().includes(searchTerm) ||
            (u.email && u.email.toLowerCase().includes(searchTerm))
        );
    }
    
    renderUsers(filtered);
}

document.querySelectorAll('input[name="roleFilter"], input[name="statusFilter"]').forEach(radio => {
    radio.addEventListener('change', filterUsers);
});

document.getElementById('searchInput').addEventListener('input', filterUsers);

// ==================== ایجاد/ویرایش کاربر ====================
function openUserModal() {
    document.getElementById('modalTitle').textContent = 'ایجاد کاربر جدید';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('passwordGroup').style.display = 'block';
    document.getElementById('password').required = true;
    document.getElementById('userModal').style.display = 'flex';
    
    // محدود کردن نقش‌ها بر اساس دسترسی
    const roleSelect = document.getElementById('role');
    if (currentUserRole === 'admin') {
        roleSelect.innerHTML = '<option value="moderator">ناظر</option>';
    } else {
        roleSelect.innerHTML = `
            <option value="moderator">ناظر</option>
            <option value="admin">مدیر</option>
            <option value="super_admin">مدیر کل</option>
        `;
    }
}

function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
}

function editUser(id) {
    const user = users.find(u => u.id == id);
    if (!user) return;
    
    document.getElementById('modalTitle').textContent = 'ویرایش کاربر';
    document.getElementById('userId').value = user.id;
    document.getElementById('username').value = user.username;
    document.getElementById('full_name').value = user.full_name;
    document.getElementById('email').value = user.email || '';
    document.getElementById('role').value = user.role;
    document.getElementById('customer_id').value = user.customer_id || '';
    document.getElementById('password').value = '';
    document.getElementById('password').required = false;
    document.getElementById('passwordGroup').style.display = 'block';
    
    // در ویرایش، رمز عبور الزامی نیست
    const passwordField = document.getElementById('password');
    passwordField.required = false;
    passwordField.placeholder = 'برای عدم تغییر، خالی بگذارید';
    
    document.getElementById('userModal').style.display = 'flex';
}

async function saveUser() {
    const id = document.getElementById('userId').value;
    const data = {
        username: document.getElementById('username').value.trim(),
        full_name: document.getElementById('full_name').value.trim(),
        email: document.getElementById('email').value.trim(),
        role: document.getElementById('role').value,
        customer_id: document.getElementById('customer_id').value || null
    };
    
    const password = document.getElementById('password').value;
    if (password) {
        if (password.length < 6) {
            showAlert('رمز عبور باید حداقل ۶ کاراکتر باشد', 'danger');
            return;
        }
        data.password = password;
    }
    
    if (!data.username) {
        showAlert('نام کاربری الزامی است', 'danger');
        return;
    }
    if (!data.full_name) {
        showAlert('نام کامل الزامی است', 'danger');
        return;
    }
    if (!id && !password) {
        showAlert('رمز عبور برای کاربر جدید الزامی است', 'danger');
        return;
    }
    
    const url = id ? `/mylumina/api/admin/users/${id}` : '/mylumina/api/admin/users';
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
            closeUserModal();
            loadUsers();
        } else {
            showAlert(result.error, 'danger');
        }
    } catch (error) {
        showAlert('خطا در ارتباط با سرور', 'danger');
    }
}

// ==================== تغییر وضعیت کاربر ====================
async function toggleUserStatus(id, isCurrentlyActive) {
    const action = isCurrentlyActive ? 'غیرفعال' : 'فعال';
    if (!confirm(`آیا از ${action} کردن این کاربر اطمینان دارید؟`)) return;
    
    try {
        const response = await fetch(`/mylumina/api/admin/users/${id}/toggle`, {
            method: 'POST'
        });
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            loadUsers();
        } else {
            showAlert(result.error, 'danger');
        }
    } catch (error) {
        showAlert('خطا در ارتباط با سرور', 'danger');
    }
}

// ==================== تغییر رمز عبور ====================
function changePassword(userId) {
    const user = users.find(u => u.id == userId);
    if (!user) return;
    
    document.getElementById('passwordUserId').value = userId;
    document.getElementById('passwordForm').reset();
    
    // اگر کاربر رمز خودش را تغییر می‌دهد، فیلد رمز فعلی را نشان بده
    const isCurrentUser = (userId == currentUserId);
    document.getElementById('currentPasswordGroup').style.display = isCurrentUser ? 'block' : 'none';
    document.getElementById('current_password').required = isCurrentUser;
    
    document.getElementById('passwordModal').style.display = 'flex';
}

function closePasswordModal() {
    document.getElementById('passwordModal').style.display = 'none';
}

async function savePassword() {
    const userId = document.getElementById('passwordUserId').value;
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const currentPassword = document.getElementById('current_password').value;
    
    if (!newPassword) {
        showAlert('رمز عبور جدید الزامی است', 'danger');
        return;
    }
    if (newPassword.length < 6) {
        showAlert('رمز عبور باید حداقل ۶ کاراکتر باشد', 'danger');
        return;
    }
    if (newPassword !== confirmPassword) {
        showAlert('رمز عبور جدید و تکرار آن مطابقت ندارند', 'danger');
        return;
    }
    
    const isCurrentUser = (userId == currentUserId);
    if (isCurrentUser && !currentPassword) {
        showAlert('رمز عبور فعلی الزامی است', 'danger');
        return;
    }
    
    const data = { new_password: newPassword };
    if (isCurrentUser) {
        data.current_password = currentPassword;
    }
    
    try {
        const response = await fetch(`/mylumina/api/admin/users/${userId}/change-password`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            closePasswordModal();
            
            // اگر کاربر رمز خودش را تغییر داده، لاگاوت کن
            if (isCurrentUser) {
                setTimeout(() => {
                    if (confirm('رمز عبور تغییر کرد. برای اعمال تغییرات باید دوباره وارد شوید. ادامه می‌دهید؟')) {
                        window.location.href = '/mylumina/logout';
                    }
                }, 1000);
            }
        } else {
            showAlert(result.error, 'danger');
        }
    } catch (error) {
        showAlert('خطا در ارتباط با سرور', 'danger');
    }
}

// ==================== رویدادها ====================
document.getElementById('userForm').addEventListener('submit', function(e) {
    e.preventDefault();
    saveUser();
});

document.getElementById('passwordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    savePassword();
});

// ==================== راه‌اندازی اولیه ====================
loadCustomers();
loadUsers();
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>