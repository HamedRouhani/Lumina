<?php
$title = 'گزارش استفاده کاربران';
ob_start();
?>

<style>
    .page-header { margin-bottom: 25px; }
    
    .stats-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 25px;
    }
    
    .stat-box {
        background: white;
        padding: 15px 20px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        text-align: center;
    }
    
    .stat-box .number {
        font-size: 28px;
        font-weight: bold;
        color: #667eea;
    }
    
    .stat-box .label {
        color: #6c757d;
        font-size: 14px;
    }
    
    .table-container {
        background: white;
        border-radius: 12px;
        overflow-x: auto;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .table-container table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table-container th,
    .table-container td {
        padding: 12px 15px;
        text-align: right;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
    }
    
    .table-container th {
        background: #f8f9fa;
        font-weight: bold;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .table-container tr:hover {
        background: #f8f9fa;
    }
    
    .btn-view-chats {
        background: #667eea;
        color: white;
        border: none;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 13px;
        cursor: pointer;
        transition: 0.2s;
    }
    
    .btn-view-chats:hover {
        background: #5566c9;
    }
    
    .btn-export-excel {
        background: #28a745;
        color: white;
        border: none;
        padding: 10px 22px;
        border-radius: 8px;
        font-size: 14px;
        cursor: pointer;
        transition: 0.2s;
    }
    
    .btn-export-excel:hover {
        background: #218838;
    }
    
    .btn-export-user-excel {
        background: #17a2b8;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        cursor: pointer;
        transition: 0.2s;
    }
    
    .btn-export-user-excel:hover {
        background: #138496;
    }
    
    .toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .badge {
        display: inline-block;
        background: #e9ecef;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 14px;
        color: #495057;
    }
    
    .badge-active {
        background: #d4edda;
        color: #155724;
    }
    
    .badge-inactive {
        background: #f8d7da;
        color: #721c24;
    }
    
    .loading-spinner {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }
    
    .search-box {
        margin-bottom: 20px;
    }
    
    .search-input {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-family: 'Vazirmatn', sans-serif;
        font-size: 14px;
    }
    
    .search-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
    }
    
    /* Modal */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 10000;
        justify-content: center;
        align-items: center;
    }
    
    .modal-box {
        background: white;
        border-radius: 16px;
        width: 92%;
        max-width: 1100px;
        max-height: 90vh;
        overflow-y: auto;
        padding: 25px;
        position: relative;
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
    
    .modal-close {
        position: sticky;
        top: 0;
        float: left;
        font-size: 28px;
        cursor: pointer;
        background: none;
        border: none;
        color: #333;
        z-index: 10;
        padding: 0 5px;
    }
    
    .modal-title {
        font-size: 1.3rem;
        font-weight: bold;
        margin-bottom: 20px;
        padding-left: 40px;
    }
    
    .modal-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px;
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
    }
    
    .modal-stat {
        text-align: center;
    }
    
    .modal-stat .num {
        font-size: 20px;
        font-weight: bold;
        color: #667eea;
    }
    
    .modal-stat .lbl {
        font-size: 12px;
        color: #6c757d;
    }
    
    .modal-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    
    .modal-table th,
    .modal-table td {
        padding: 10px 12px;
        text-align: right;
        border-bottom: 1px solid #e9ecef;
        vertical-align: top;
    }
    
    .modal-table th {
        background: #f1f3f5;
        position: sticky;
        top: 0;
        z-index: 5;
    }
    
    .modal-table td {
        max-width: 300px;
        word-wrap: break-word;
        word-break: break-word;
    }
    
    .message-preview {
        max-height: 80px;
        overflow-y: auto;
        font-size: 13px;
        line-height: 1.5;
        white-space: pre-wrap;
    }
    
    .modal-loading {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }
    
    .no-data {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        padding: 15px 0;
        flex-wrap: wrap;
    }
    
    .page-btn {
        padding: 6px 14px;
        border: 1px solid #dee2e6;
        background: white;
        border-radius: 6px;
        cursor: pointer;
        font-family: 'Vazirmatn', sans-serif;
        transition: all 0.3s;
    }
    
    .page-btn:hover:not(:disabled) {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }
    
    .page-btn.active {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }
    
    .page-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .page-info {
        padding: 6px 12px;
        color: #6c757d;
        font-size: 13px;
    }
    
    .per-page-select {
        padding: 6px 12px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        font-family: 'Vazirmatn', sans-serif;
        background: white;
        cursor: pointer;
    }
    
    .export-loading {
        display: none;
        text-align: center;
        padding: 10px;
        color: #6c757d;
    }
    
    @media (max-width: 768px) {
        .modal-box {
            width: 98%;
            padding: 15px;
        }
        .modal-table {
            font-size: 12px;
        }
        .modal-table th,
        .modal-table td {
            padding: 6px 8px;
        }
        .table-container {
            font-size: 13px;
        }
        .table-container th,
        .table-container td {
            padding: 8px 10px;
        }
        .stats-summary {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<div class="page-header">
    <h2><i class="fas fa-users"></i> گزارش استفاده کاربران</h2>
    <p>لیست کاربرانی که از ویجت استفاده کرده‌اند به همراه تعداد چت‌ها و آخرین فعالیت</p>
</div>

<!-- آمار کلی -->
<div class="stats-summary" id="statsSummary">
    <div class="stat-box">
        <div class="number" id="totalUsers">0</div>
        <div class="label">کل کاربران</div>
    </div>
    <div class="stat-box">
        <div class="number" id="totalChats">0</div>
        <div class="label">کل چت‌ها</div>
    </div>
    <div class="stat-box">
        <div class="number" id="avgChats">0</div>
        <div class="label">میانگین چت به ازای هر کاربر</div>
    </div>
    <div class="stat-box">
        <div class="number" id="activeUsers">0</div>
        <div class="label">کاربران فعال (حداقل ۱ چت)</div>
    </div>
</div>

<div class="toolbar">
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button class="btn-export-excel" onclick="exportFullExcel()">
            <i class="fas fa-file-excel"></i> خروجی کامل همه کاربران
        </button>
        <span id="exportLoading" class="export-loading">
            <i class="fas fa-spinner fa-spin"></i> در حال تولید خروجی...
        </span>
    </div>
    <span class="badge" id="totalUsersBadge">در حال بارگذاری...</span>
</div>

<div class="search-box">
    <input type="text" class="search-input" id="searchInput" placeholder="🔍 جستجو بر اساس شناسه کاربر، Session ID یا Widget ID...">
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th style="width:50px;">#</th>
                <th>شناسه کاربر</th>
                <th>Session ID</th>
                <th style="width:80px;">تعداد چت‌ها</th>
                <th style="width:140px;">آخرین فعالیت</th>
                <th style="width:100px;">روزهای فعالیت</th>
                <th style="width:80px;">وضعیت</th>
                <th style="width:180px;">عملیات</th>
            </tr>
        </thead>
        <tbody id="usersTableBody">
            <tr>
                <td colspan="8" class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i> در حال بارگذاری داده‌ها...
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Modal برای نمایش چت‌های کاربر -->
<div id="chatModal" class="modal-overlay">
    <div class="modal-box">
        <button class="modal-close" onclick="closeChatModal()">&times;</button>
        <div class="modal-title" id="modalTitle">چت‌های کاربر</div>
        
        <!-- آمار کاربر در مودال -->
        <div class="modal-stats" id="modalStats">
            <div class="modal-stat">
                <div class="num" id="mTotalChats">0</div>
                <div class="lbl">کل چت‌ها</div>
            </div>
            <div class="modal-stat">
                <div class="num" id="mActiveDays">0</div>
                <div class="lbl">روزهای فعالیت</div>
            </div>
            <div class="modal-stat">
                <div class="num" id="mFirstChat">-</div>
                <div class="lbl">اولین چت</div>
            </div>
            <div class="modal-stat">
                <div class="num" id="mLastChat">-</div>
                <div class="lbl">آخرین چت</div>
            </div>
        </div>
        
        <div style="display: flex; justify-content: flex-end; margin-bottom: 15px;">
            <button class="btn-export-user-excel" onclick="exportUserExcel()">
                <i class="fas fa-file-excel"></i> خروجی چت‌های این کاربر
            </button>
        </div>
        
        <div id="modalContent">
            <div class="modal-loading"><i class="fas fa-spinner fa-spin"></i> در حال بارگذاری چت‌ها...</div>
        </div>
        
        <!-- صفحه‌بندی چت‌ها -->
        <div class="pagination" id="chatPagination"></div>
    </div>
</div>

<script>
// ============================================================
// متغیرها
// ============================================================
let usersData = [];
let filteredUsers = [];
let currentPage = 1;
let perPage = 10;
let currentUserId = null;
let chatPaginationData = {};

// ============================================================
// دریافت لیست کاربران
// ============================================================
async function fetchUserList() {
    try {
        const response = await fetch('/mylumina/api/moderator/users-list');
        const result = await response.json();
        
        if (result.success) {
            usersData = result.users;
            filteredUsers = [...usersData];
            updateStats();
            renderTable(filteredUsers, 1);
            document.getElementById('totalUsersBadge').textContent = 'تعداد کل کاربران: ' + usersData.length;
        } else {
            showError(result.error || 'خطا در دریافت لیست کاربران');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('خطا در ارتباط با سرور');
    }
}

// ============================================================
// به‌روزرسانی آمار
// ============================================================
function updateStats() {
    const total = usersData.length;
    let totalChats = 0;
    let activeUsers = 0;
    
    usersData.forEach(u => {
        totalChats += u.chat_count || 0;
        if (u.chat_count > 0) activeUsers++;
    });
    
    document.getElementById('totalUsers').textContent = total;
    document.getElementById('totalChats').textContent = totalChats.toLocaleString('fa-IR');
    document.getElementById('avgChats').textContent = total > 0 ? (totalChats / total).toFixed(1) : '0';
    document.getElementById('activeUsers').textContent = activeUsers;
}

// ============================================================
// رندر جدول کاربران
// ============================================================
function renderTable(users, page) {
    const tbody = document.getElementById('usersTableBody');
    const start = (page - 1) * perPage;
    const end = Math.min(start + perPage, users.length);
    const pageUsers = users.slice(start, end);
    
    if (!users || users.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="no-data">
                    <i class="fas fa-info-circle"></i> هیچ کاربری تاکنون از ویجت استفاده نکرده است.
                </td>
            </tr>
        `;
        renderTablePagination(users, page);
        return;
    }
    
    let rows = '';
    pageUsers.forEach((user, index) => {
        const statusHtml = user.is_active 
            ? '<span class="badge badge-active">فعال</span>' 
            : '<span class="badge badge-inactive">غیرفعال</span>';
        
        const userIdentifier = user.session_id ? 
            user.session_id.substring(0, 12) + '...' : 
            'کاربر #' + user.user_id;
        
        rows += `
            <tr>
                <td>${start + index + 1}</td>
                <td>
                    <strong>کاربر #${user.user_id}</strong>
                    <br><small style="color: #6c757d;">${escapeHtml(userIdentifier)}</small>
                </td>
                <td><code style="font-size: 11px;">${escapeHtml(user.session_id || '-')}</code></td>
                <td><strong>${formatNumber(user.chat_count)}</strong></td>
                <td>${formatDateTime(user.last_activity)}</td>
                <td>${user.active_days || 0} روز</td>
                <td>${statusHtml}</td>
                <td>
                    <button class="btn-view-chats" onclick="openChatModal(${user.user_id})" ${user.chat_count == 0 ? 'disabled' : ''}>
                        <i class="fas fa-comments"></i> مشاهده چت‌ها (${user.chat_count})
                    </button>
                    ${user.chat_count > 0 ? `
                    <button class="btn-export-user-excel" onclick="exportSingleUserExcel(${user.user_id})" style="margin-top: 5px;">
                        <i class="fas fa-file-excel"></i> اکسل
                    </button>
                    ` : ''}
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = rows;
    renderTablePagination(users, page);
}

// ============================================================
// صفحه‌بندی جدول اصلی
// ============================================================
function renderTablePagination(users, page) {
    const container = document.getElementById('tablePagination');
    if (!container) {
        // ایجاد صفحه‌بندی اگر وجود ندارد
        const tableContainer = document.querySelector('.table-container');
        const paginationDiv = document.createElement('div');
        paginationDiv.className = 'pagination';
        paginationDiv.id = 'tablePagination';
        tableContainer.appendChild(paginationDiv);
    }
    
    const totalPages = Math.ceil(users.length / perPage);
    const container2 = document.getElementById('tablePagination');
    
    if (totalPages <= 1) {
        container2.innerHTML = '';
        return;
    }
    
    let html = `
        <button class="page-btn" onclick="goToPage(${page - 1})" ${page <= 1 ? 'disabled' : ''}>
            <i class="fas fa-chevron-right"></i>
        </button>
    `;
    
    for (let i = 1; i <= totalPages; i++) {
        if (i === page) {
            html += `<button class="page-btn active">${i}</button>`;
        } else if (i <= 3 || i > totalPages - 2 || Math.abs(i - page) <= 1) {
            html += `<button class="page-btn" onclick="goToPage(${i})">${i}</button>`;
        } else if (i === 4 && page > 5) {
            html += `<span class="page-info">...</span>`;
        } else if (i === totalPages - 3 && page < totalPages - 4) {
            html += `<span class="page-info">...</span>`;
        }
    }
    
    html += `
        <button class="page-btn" onclick="goToPage(${page + 1})" ${page >= totalPages ? 'disabled' : ''}>
            <i class="fas fa-chevron-left"></i>
        </button>
        <span class="page-info">${page} از ${totalPages}</span>
    `;
    
    container2.innerHTML = html;
}

function goToPage(page) {
    const totalPages = Math.ceil(filteredUsers.length / perPage);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderTable(filteredUsers, page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ============================================================
// جستجو
// ============================================================
document.getElementById('searchInput').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase().trim();
    if (!term) {
        filteredUsers = [...usersData];
    } else {
        filteredUsers = usersData.filter(user => {
            const idStr = '#' + user.user_id;
            const session = user.session_id || '';
            const widget = user.widget_id || '';
            return idStr.includes(term) || 
                   session.toLowerCase().includes(term) || 
                   widget.toLowerCase().includes(term);
        });
    }
    currentPage = 1;
    renderTable(filteredUsers, 1);
});

// ============================================================
// باز کردن مودال چت‌ها
// ============================================================
let currentChatPage = 1;
const chatPerPage = 20;

function openChatModal(userId) {
    const modal = document.getElementById('chatModal');
    const content = document.getElementById('modalContent');
    const title = document.getElementById('modalTitle');
    
    currentUserId = userId;
    currentChatPage = 1;
    
    modal.style.display = 'flex';
    content.innerHTML = '<div class="modal-loading"><i class="fas fa-spinner fa-spin"></i> در حال بارگذاری چت‌ها...</div>';
    
    const user = usersData.find(u => u.user_id == userId);
    title.textContent = 'چت‌های کاربر #' + userId + (user ? ' (' + (user.session_id || '') + ')' : '');
    
    loadUserChats(userId, 1);
}

function loadUserChats(userId, page) {
    const content = document.getElementById('modalContent');
    const statsDiv = document.getElementById('modalStats');
    
    fetch(`/mylumina/api/moderator/user-chats?user_id=${userId}&page=${page}&limit=${chatPerPage}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // به‌روزرسانی آمار
                document.getElementById('mTotalChats').textContent = data.stats.total_chats || 0;
                document.getElementById('mActiveDays').textContent = data.stats.active_days || 0;
                document.getElementById('mFirstChat').textContent = data.stats.first_chat ? formatDateTime(data.stats.first_chat) : '-';
                document.getElementById('mLastChat').textContent = data.stats.last_chat ? formatDateTime(data.stats.last_chat) : '-';
                
                chatPaginationData = {
                    current_page: data.page,
                    total_pages: data.total_pages,
                    total: data.total
                };
                
                if (data.chats && data.chats.length > 0) {
                    let rows = data.chats.map(chat => `
                        <tr>
                            <td>${chat.id}</td>
                            <td>
                                <div class="message-preview">${escapeHtml(chat.chat_user || '-')}</div>
                            </td>
                            <td>
                                <div class="message-preview">${escapeHtml(chat.chat_bot || '-')}</div>
                            </td>
                            <td>${chat.message_type || 'text'}</td>
                            <td style="white-space:nowrap;">${formatDateTime(chat.created_at)}</td>
                        </tr>
                    `).join('');

                    content.innerHTML = `
                        <table class="modal-table">
                            <thead>
                                <tr>
                                    <th style="width:60px;">#</th>
                                    <th>پیام کاربر</th>
                                    <th>پاسخ ربات</th>
                                    <th style="width:80px;">نوع</th>
                                    <th style="width:180px;">زمان</th>
                                </tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                    `;
                } else {
                    content.innerHTML = '<div class="no-data">این کاربر هیچ چتی ندارد.</div>';
                }
                
                renderChatPagination();
            } else {
                content.innerHTML = '<div class="no-data" style="color:red;">خطا در بارگذاری چت‌ها: ' + (data.error || '') + '</div>';
            }
        })
        .catch(err => {
            console.error('Error:', err);
            content.innerHTML = '<div class="no-data" style="color:red;">خطا در بارگذاری چت‌ها</div>';
        });
}

function renderChatPagination() {
    const container = document.getElementById('chatPagination');
    const { current_page, total_pages, total } = chatPaginationData;
    
    if (total_pages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = `
        <button class="page-btn" onclick="goToChatPage(${current_page - 1})" ${current_page <= 1 ? 'disabled' : ''}>
            <i class="fas fa-chevron-right"></i>
        </button>
    `;
    
    for (let i = 1; i <= total_pages; i++) {
        if (i === current_page) {
            html += `<button class="page-btn active">${i}</button>`;
        } else if (i <= 3 || i > total_pages - 2 || Math.abs(i - current_page) <= 1) {
            html += `<button class="page-btn" onclick="goToChatPage(${i})">${i}</button>`;
        } else if (i === 4 && current_page > 5) {
            html += `<span class="page-info">...</span>`;
        } else if (i === total_pages - 3 && current_page < total_pages - 4) {
            html += `<span class="page-info">...</span>`;
        }
    }
    
    html += `
        <button class="page-btn" onclick="goToChatPage(${current_page + 1})" ${current_page >= total_pages ? 'disabled' : ''}>
            <i class="fas fa-chevron-left"></i>
        </button>
        <span class="page-info">${current_page} از ${total_pages} (${total} چت)</span>
    `;
    
    container.innerHTML = html;
}

function goToChatPage(page) {
    const { total_pages } = chatPaginationData;
    if (page < 1 || page > total_pages) return;
    currentChatPage = page;
    loadUserChats(currentUserId, page);
}

// ============================================================
// خروجی اکسل
// ============================================================
async function exportFullExcel() {
    if (!usersData || usersData.length === 0) {
        alert('داده‌ای برای خروجی وجود ندارد.');
        return;
    }

    const loadingEl = document.getElementById('exportLoading');
    loadingEl.style.display = 'block';

    try {
        const allChats = [];
        
        for (const user of usersData) {
            if (user.chat_count > 0) {
                try {
                    const response = await fetch(`/mylumina/api/moderator/user-chats?user_id=${user.user_id}&limit=9999`);
                    const result = await response.json();
                    
                    if (result.success && result.chats) {
                        result.chats.forEach(chat => {
                            allChats.push({
                                'شناسه کاربر': '#' + user.user_id,
                                'Session ID': user.session_id || '-',
                                'شناسه چت': chat.id,
                                'پیام کاربر': chat.chat_user || '-',
                                'پاسخ ربات': chat.chat_bot || '-',
                                'نوع پیام': chat.message_type || 'text',
                                'زمان چت': formatDateTime(chat.created_at)
                            });
                        });
                    }
                } catch (e) {
                    console.error('Error fetching chats for user:', user.user_id, e);
                }
            }
        }

        loadingEl.style.display = 'none';

        if (allChats.length === 0) {
            alert('هیچ چتی برای خروجی وجود ندارد.');
            return;
        }

        // تولید فایل اکسل
        if (typeof XLSX === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js';
            script.onload = function() {
                generateExcel(allChats);
            };
            document.head.appendChild(script);
        } else {
            generateExcel(allChats);
        }

    } catch (error) {
        loadingEl.style.display = 'none';
        console.error('Error:', error);
        showError('خطا در تولید خروجی اکسل');
    }
}

function generateExcel(rows) {
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.json_to_sheet(rows);
    ws['!cols'] = [
        { wch: 15 },
        { wch: 25 },
        { wch: 12 },
        { wch: 50 },
        { wch: 50 },
        { wch: 15 },
        { wch: 25 }
    ];
    XLSX.utils.book_append_sheet(wb, ws, 'تمامی چت‌ها');
    
    // برگه خلاصه
    const summaryRows = usersData.map((user, index) => ({
        'ردیف': index + 1,
        'شناسه کاربر': '#' + user.user_id,
        'Session ID': user.session_id || '-',
        'تعداد چت‌ها': user.chat_count || 0,
        'روزهای فعالیت': user.active_days || 0,
        'آخرین فعالیت': formatDateTime(user.last_activity),
        'وضعیت': user.is_active ? 'فعال' : 'غیرفعال'
    }));
    const wsSummary = XLSX.utils.json_to_sheet(summaryRows);
    wsSummary['!cols'] = [
        { wch: 8 },
        { wch: 15 },
        { wch: 25 },
        { wch: 15 },
        { wch: 15 },
        { wch: 25 },
        { wch: 12 }
    ];
    XLSX.utils.book_append_sheet(wb, wsSummary, 'خلاصه کاربران');
    
    const fileName = `گزارش_کامل_چت‌ها_${new Date().toLocaleDateString('fa-IR')}.xlsx`;
    XLSX.writeFile(wb, fileName);
    
    showSuccess(`خروجی اکسل با ${rows.length} چت از ${usersData.filter(u => u.chat_count > 0).length} کاربر تولید شد.`);
}

// ============================================================
// خروجی اکسل یک کاربر
// ============================================================
function exportSingleUserExcel(userId) {
    window.location.href = `/mylumina/api/moderator/user-chats?user_id=${userId}&format=excel`;
}

// ============================================================
// توابع کمکی
// ============================================================
function formatNumber(num) {
    if (num === null || num === undefined) return '0';
    return Number(num).toLocaleString('fa-IR');
}

function formatDateTime(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return '-';
    return date.toLocaleDateString('fa-IR') + ' ' + date.toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' });
}

function escapeHtml(text) {
    if (!text) return '-';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showError(message) {
    const alertDiv = document.createElement('div');
    alertDiv.innerHTML = '⚠️ ' + message;
    alertDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #f8d7da; color: #721c24; padding: 14px 24px; border-radius: 8px; z-index: 99999; box-shadow: 0 4px 12px rgba(0,0,0,0.15); max-width: 400px;';
    document.body.appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 5000);
}

function showSuccess(message) {
    const alertDiv = document.createElement('div');
    alertDiv.innerHTML = '✅ ' + message;
    alertDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #d4edda; color: #155724; padding: 14px 24px; border-radius: 8px; z-index: 99999; box-shadow: 0 4px 12px rgba(0,0,0,0.15); max-width: 400px;';
    document.body.appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 5000);
}

// ============================================================
// بستن مودال
// ============================================================
function closeChatModal() {
    document.getElementById('chatModal').style.display = 'none';
}

document.getElementById('chatModal').addEventListener('click', function(e) {
    if (e.target === this) closeChatModal();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeChatModal();
});

// ============================================================
// بارگذاری اولیه
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    fetchUserList();
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>