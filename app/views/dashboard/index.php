<?php
$title = 'داشبورد';
ob_start();
?>

<style>
    .welcome-section {
        background: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .welcome-section h2 {
        color: #333;
        margin-bottom: 10px;
    }
    
    .welcome-section p {
        color: #6c757d;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border-left: 4px solid #667eea;
        transition: transform 0.3s;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
    }
    
    .stat-icon {
        font-size: 2.5rem;
        color: #667eea;
        margin-bottom: 15px;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 14px;
        color: #6c757d;
    }
    
    .chart-container {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .chart-title {
        font-size: 1.2rem;
        font-weight: bold;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .chart-title-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .chart-wrapper {
        height: 300px;
        position: relative;
    }
    
    .activities-container {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 30px;
    }
    
    .activities-header {
        padding: 20px 20px 0 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .activities-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .activities-table th,
    .activities-table td {
        padding: 12px 15px;
        text-align: right;
        border-bottom: 1px solid #e9ecef;
        vertical-align: top;
    }
    
    .activities-table th {
        background: #f8f9fa;
        font-weight: bold;
    }
    
    .activities-table tr:hover {
        background: #f8f9fa;
    }
    
    .message-preview {
        max-width: 300px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        cursor: pointer;
        color: #667eea;
    }
    
    .message-preview:hover {
        text-decoration: underline;
    }
    
    .message-full {
        display: none;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 10px;
        margin-top: 10px;
        white-space: pre-wrap;
        word-break: break-word;
        line-height: 1.6;
    }
    
    .message-full.show {
        display: block;
    }
    
    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        background: #667eea;
        color: white;
    }
    
    .btn-sm:hover {
        background: #5a67d8;
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        padding: 20px;
        flex-wrap: wrap;
    }
    
    .page-btn {
        padding: 8px 15px;
        border: 1px solid #dee2e6;
        background: white;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
        font-family: 'Vazirmatn', sans-serif;
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
        padding: 8px 15px;
        color: #6c757d;
    }
    
    .per-page-select {
        padding: 8px 12px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        font-family: 'Vazirmatn', sans-serif;
        background: white;
        cursor: pointer;
    }
    
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
    
    .modal-content {
        background: white;
        border-radius: 15px;
        width: 90%;
        max-width: 800px;
        max-height: 85vh;
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
        z-index: 1;
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #6c757d;
    }
    
    .detail-section {
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .detail-label {
        font-weight: bold;
        color: #667eea;
        margin-bottom: 5px;
    }
    
    .detail-value {
        color: #333;
        line-height: 1.6;
        white-space: pre-wrap;
        word-break: break-word;
    }
    
    .refresh-btn {
        background: none;
        border: none;
        color: #667eea;
        cursor: pointer;
        font-size: 16px;
        transition: transform 0.3s;
        padding: 5px;
    }
    
    .refresh-btn:hover {
        transform: rotate(180deg);
    }
    
    .loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,0.8);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }
    
    .loading-spinner {
        width: 50px;
        height: 50px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
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
    
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .stat-card {
            padding: 15px;
        }
        
        .stat-number {
            font-size: 1.5rem;
        }
        
        .activities-table {
            display: block;
            overflow-x: auto;
        }
        
        .message-preview {
            max-width: 150px;
        }
        
        .chart-title {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="welcome-section">
    <h2>
        <i class="fas fa-hand-wave"></i>
        خوش آمدید، <span id="adminName"><?php echo escape($admin['full_name'] ?? $admin['username']); ?></span>
    </h2>
    <p id="adminInfo">
        نقش: <?php echo $admin['role'] === 'super_admin' ? 'مدیر کل' : ($admin['role'] === 'admin' ? 'مدیر' : 'ناظر'); ?>
        <?php if ($admin['customer_id']): ?>
            | شناسه مشتری: <?php echo escape($admin['customer_id']); ?>
        <?php endif; ?>
    </p>
</div>

<!-- آمارها -->
<div class="stats-grid" id="statsGrid">
    <!-- آمارها توسط JavaScript پر می‌شوند -->
</div>

<!-- نمودار چت‌های هفتگی -->
<div class="chart-container" id="chartContainer" style="display: none;">
    <div class="chart-title">
        <div class="chart-title-left">
            <i class="fas fa-chart-line"></i>
            <span>آمار چت‌های هفت روز اخیر</span>
        </div>
        <button class="refresh-btn" onclick="refreshStats()" title="بروزرسانی">
            <i class="fas fa-sync-alt"></i>
        </button>
    </div>
    <div class="chart-wrapper">
        <canvas id="chatChart"></canvas>
    </div>
</div>

<!-- فعالیت‌های اخیر -->
<div class="activities-container" id="activitiesContainer" style="display: none;">
    <div class="activities-header">
        <div class="chart-title-left">
            <i class="fas fa-history"></i>
            <span>آخرین فعالیت‌ها</span>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <select id="perPageSelect" class="per-page-select" onchange="changePerPage()">
                <option value="10">۱۰ در هر صفحه</option>
                <option value="20" selected>۲۰ در هر صفحه</option>
                <option value="50">۵۰ در هر صفحه</option>
            </select>
            <button class="refresh-btn" onclick="loadActivities()" title="بروزرسانی">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>
    <div style="overflow-x: auto;">
        <table class="activities-table">
            <thead>
                <tr>
                    <th>زمان</th>
                    <th>سرویس</th>
                    <th>پیام کاربر</th>
                    <th>پاسخ ربات</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody id="activitiesTableBody">
                <tr><td colspan="5" style="text-align: center;">در حال بارگذاری...</td></tr>
            </tbody>
        </table>
    </div>
    
    <!-- صفحه‌بندی -->
    <div class="pagination" id="pagination">
        <!-- صفحه‌بندی توسط JavaScript پر می‌شود -->
    </div>
</div>

<!-- مودال نمایش جزئیات -->
<div id="detailModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-info-circle"></i> جزئیات فعالیت</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- جزئیات اینجا بارگذاری می‌شود -->
        </div>
    </div>
</div>

<!-- لودینگ -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let chatChart = null;
let currentPage = 1;
let perPage = 20;
let totalPages = 1;

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
    return date.toLocaleDateString('fa-IR') + ' ' + date.toLocaleTimeString('fa-IR');
}

function formatNumber(num) {
    if (num === undefined || num === null) return '0';
    return num.toLocaleString('fa-IR');
}

function showLoading(show) {
    document.getElementById('loadingOverlay').style.display = show ? 'flex' : 'none';
}

function showError(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger';
    alertDiv.innerHTML = message;
    alertDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; padding: 12px; border-radius: 8px; background: #f8d7da; color: #721c24; z-index: 10001;';
    document.body.appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 5000);
}

// ==================== بارگذاری آمار ====================
async function loadStats() {
    showLoading(true);
    
    try {
        const response = await fetch('/mylumina/api/dashboard/stats');
        const data = await response.json();
        
        if (data.success) {
            renderStats(data.stats);
            
            if (data.stats.chat_stats && data.stats.chat_stats.dates && data.stats.chat_stats.dates.length > 0) {
                renderChart(data.stats.chat_stats);
                document.getElementById('chartContainer').style.display = 'block';
            }
        }
    } catch (error) {
        console.error('Error loading stats:', error);
        showError('خطا در بارگذاری آمار');
    } finally {
        showLoading(false);
    }
}

function renderStats(stats) {
    const container = document.getElementById('statsGrid');
    
    let cards = [];
    
    if (stats.total_customers !== undefined) {
        cards = [
            { icon: 'fa-building', number: stats.total_customers || 0, label: 'مشتریان', color: '#667eea' },
            { icon: 'fa-cogs', number: stats.total_services || 0, label: 'سرویس‌ها', color: '#28a745' },
            { icon: 'fa-users', number: stats.total_users || 0, label: 'کاربران سیستم', color: '#17a2b8' },
            { icon: 'fa-comments', number: stats.total_chats || 0, label: 'کل چت‌ها', color: '#ffc107' },
            { icon: 'fa-calendar-day', number: stats.today_chats || 0, label: 'چت‌های امروز', color: '#fd7e14' }
        ];
    } else {
        cards = [
            { icon: 'fa-cogs', number: stats.total_services || 0, label: 'سرویس‌های شما', color: '#667eea' },
            { icon: 'fa-comments', number: stats.total_chats || 0, label: 'کل چت‌ها', color: '#28a745' },
            { icon: 'fa-calendar-day', number: stats.today_chats || 0, label: 'چت‌های امروز', color: '#ffc107' }
        ];
    }
    
    container.innerHTML = cards.map(card => `
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas ${card.icon}" style="color: ${card.color}"></i>
            </div>
            <div class="stat-number">${formatNumber(card.number)}</div>
            <div class="stat-label">${card.label}</div>
        </div>
    `).join('');
}

function renderChart(chatStats) {
    const ctx = document.getElementById('chatChart').getContext('2d');
    
    if (chatChart) {
        chatChart.destroy();
    }
    
    chatChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chatStats.dates,
            datasets: [{
                label: 'تعداد چت‌ها',
                data: chatStats.counts,
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderColor: '#667eea',
                borderWidth: 2,
                pointBackgroundColor: '#667eea',
                pointBorderColor: '#fff',
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    rtl: true,
                    labels: { font: { family: 'Vazirmatn' } }
                },
                tooltip: {
                    rtl: true,
                    callbacks: {
                        label: function(context) {
                            return `تعداد: ${context.raw.toLocaleString('fa-IR')}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return value.toLocaleString('fa-IR');
                        }
                    }
                }
            }
        }
    });
}

// ==================== بارگذاری فعالیت‌ها با صفحه‌بندی ====================
async function loadActivities() {
    showLoading(true);
    
    try {
        const response = await fetch(`/mylumina/api/dashboard/activities?page=${currentPage}&limit=${perPage}`);
        const data = await response.json();
        
        if (data.success) {
            renderActivities(data.activities);
            renderPagination(data.pagination);
            document.getElementById('activitiesContainer').style.display = 'block';
        }
    } catch (error) {
        console.error('Error loading activities:', error);
        showError('خطا در بارگذاری فعالیت‌ها');
    } finally {
        showLoading(false);
    }
}

function renderActivities(activities) {
    const tbody = document.getElementById('activitiesTableBody');
    
    if (!activities || activities.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">هیچ فعالیتی یافت نشد</td></tr>';
        return;
    }
    
    tbody.innerHTML = activities.map(activity => `
        <tr>
            <td style="white-space: nowrap;">${formatDate(activity.created_at)}</td>
            <td>${escapeHtml(activity.service_title || activity.service_code || '-')}</td>
            <td>
                <div class="message-preview" onclick="toggleMessage('user-${activity.id}')">
                    ${escapeHtml((activity.chat_user || '-').substring(0, 80))}${activity.chat_user && activity.chat_user.length > 80 ? '...' : ''}
                </div>
                <div id="user-${activity.id}" class="message-full">
                    ${escapeHtml(activity.chat_user || 'بدون پیام')}
                </div>
            </td>
            <td>
                <div class="message-preview" onclick="toggleMessage('bot-${activity.id}')">
                    ${escapeHtml((activity.chat_bot || '-').substring(0, 80))}${activity.chat_bot && activity.chat_bot.length > 80 ? '...' : ''}
                </div>
                <div id="bot-${activity.id}" class="message-full">
                    ${escapeHtml(activity.chat_bot || 'بدون پاسخ')}
                </div>
            </td>
            <td>
                <button class="btn-sm" onclick="showDetail(${activity.id})">
                    <i class="fas fa-eye"></i> مشاهده کامل
                </button>
            </td>
        </tr>
    `).join('');
}

function renderPagination(pagination) {
    const container = document.getElementById('pagination');
    
    if (!pagination || pagination.total_pages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '';
    
    // دکمه قبلی
    html += `<button class="page-btn" onclick="goToPage(${pagination.current_page - 1})" ${!pagination.has_prev ? 'disabled' : ''}>
                <i class="fas fa-chevron-right"></i> قبلی
            </button>`;
    
    // صفحه‌ها
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);
    
    if (startPage > 1) {
        html += `<button class="page-btn" onclick="goToPage(1)">1</button>`;
        if (startPage > 2) html += `<span class="page-info">...</span>`;
    }
    
    for (let i = startPage; i <= endPage; i++) {
        html += `<button class="page-btn ${i === pagination.current_page ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
    }
    
    if (endPage < pagination.total_pages) {
        if (endPage < pagination.total_pages - 1) html += `<span class="page-info">...</span>`;
        html += `<button class="page-btn" onclick="goToPage(${pagination.total_pages})">${pagination.total_pages}</button>`;
    }
    
    // دکمه بعدی
    html += `<button class="page-btn" onclick="goToPage(${pagination.current_page + 1})" ${!pagination.has_next ? 'disabled' : ''}>
                بعدی <i class="fas fa-chevron-left"></i>
            </button>`;
    
    // اطلاعات
    html += `<span class="page-info">
                نمایش ${((pagination.current_page - 1) * pagination.per_page) + 1} - 
                ${Math.min(pagination.current_page * pagination.per_page, pagination.total_records)} 
                از ${formatNumber(pagination.total_records)}
            </span>`;
    
    container.innerHTML = html;
}

function goToPage(page) {
    currentPage = page;
    loadActivities();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function changePerPage() {
    perPage = parseInt(document.getElementById('perPageSelect').value);
    currentPage = 1;
    loadActivities();
}

function toggleMessage(id) {
    const element = document.getElementById(id);
    if (element) {
        element.classList.toggle('show');
    }
}

// ==================== مشاهده جزئیات کامل ====================
async function showDetail(id) {
    showLoading(true);
    
    try {
        const response = await fetch(`/mylumina/api/dashboard/activities/${id}`);
        const data = await response.json();
        
        if (data.success) {
            renderModal(data.activity);
            document.getElementById('detailModal').style.display = 'flex';
        } else {
            showError(data.error || 'خطا در دریافت جزئیات');
        }
    } catch (error) {
        console.error('Error loading detail:', error);
        showError('خطا در دریافت جزئیات');
    } finally {
        showLoading(false);
    }
}

function renderModal(activity) {
    const modalBody = document.getElementById('modalBody');
    
    modalBody.innerHTML = `
        <div class="detail-section">
            <div class="detail-label"><i class="fas fa-calendar"></i> زمان</div>
            <div class="detail-value">${formatDate(activity.created_at)}</div>
        </div>
        
        <div class="detail-section">
            <div class="detail-label"><i class="fas fa-cogs"></i> اطلاعات سرویس</div>
            <div class="detail-value">
                <strong>عنوان:</strong> ${escapeHtml(activity.service_title || '-')}<br>
                <strong>کد سرویس:</strong> <code>${escapeHtml(activity.service_code || '-')}</code><br>
                <strong>آدرس:</strong> ${escapeHtml(activity.service_url || '-')}<br>
                <strong>مشتری:</strong> ${escapeHtml(activity.company_name || activity.customer_name || '-')}
            </div>
        </div>
        
        <div class="detail-section">
            <div class="detail-label"><i class="fas fa-user"></i> پیام کاربر</div>
            <div class="detail-value" style="background: #f0f4f8; padding: 15px; border-radius: 10px; border-right: 4px solid #667eea;">
                ${escapeHtml(activity.chat_user || 'بدون پیام')}
            </div>
        </div>
        
        <div class="detail-section">
            <div class="detail-label"><i class="fas fa-robot"></i> پاسخ ربات</div>
            <div class="detail-value" style="background: #e9ecef; padding: 15px; border-radius: 10px; border-right: 4px solid #28a745;">
                ${escapeHtml(activity.chat_bot || 'بدون پاسخ')}
            </div>
        </div>
        
        <div class="detail-section">
            <div class="detail-label"><i class="fas fa-info-circle"></i> اطلاعات تکمیلی</div>
            <div class="detail-value">
                <strong>شناسه چت:</strong> ${activity.id}<br>
                <strong>شناسه جلسه:</strong> <code>${escapeHtml(activity.session_id || '-')}</code><br>
                <strong>شناسه ویجت:</strong> <code>${escapeHtml(activity.widget_id || '-')}</code>
            </div>
        </div>
    `;
}

function closeModal() {
    document.getElementById('detailModal').style.display = 'none';
}

// بستن مودال با کلیک خارج
document.getElementById('detailModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// بستن با کلید ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});

function refreshStats() {
    loadStats();
    loadActivities();
}

// بروزرسانی خودکار هر 30 ثانیه
setInterval(() => {
    refreshStats();
}, 30000);

// ==================== راه‌اندازی ====================
loadStats();
loadActivities();
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>