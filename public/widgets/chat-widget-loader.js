// chat-widget-loader.js - لودر اصلی ویجت
(function() {
    // جلوگیری از بارگذاری چندباره
    if (window.__chatWidgetLoaded) return;
    window.__chatWidgetLoaded = true;
    
    console.log('Chat Widget Loader - Starting...');
    
    // تنظیمات پیش‌فرض
    const config = window.ChatWidgetConfig || {};
    const serviceCode = config.serviceCode;
    
    if (!serviceCode) {
        console.error('Chat Widget: serviceCode is required');
        return;
    }
    
    console.log('Chat Widget Config:', config);
    
    // تعیین مسیر پایه - اصلاح شده
    const baseUrl = window.location.origin + '/mylumina';
    
    // اضافه کردن استایل‌های پایه
    const style = document.createElement('style');
    style.textContent = `
        .chat-widget-container-iframe {
            position: fixed;
            ${config.position === 'bottom-right' ? 'right: 20px;' : 'left: 20px;'}
            bottom: 20px;
            z-index: 999999;
            transition: all 0.3s ease;
        }
        
        .chat-widget-button-iframe {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, ${config.primaryColor || '#667eea'} 0%, #764ba2 100%);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
        }
        
        .chat-widget-button-iframe:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0,0,0,0.25);
        }
        
        .chat-widget-button-iframe svg {
            width: 28px;
            height: 28px;
            fill: white;
        }
        
        .chat-widget-window-iframe {
            position: fixed;
            ${config.position === 'bottom-right' ? 'right: 20px;' : 'left: 20px;'}
            bottom: 90px;
            width: 380px;
            height: 550px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            display: none;
            z-index: 999998;
            border: none;
            overflow: hidden;
        }
        
        @media (max-width: 768px) {
            .chat-widget-window-iframe {
                width: calc(100vw - 40px);
                height: 70vh;
                ${config.position === 'bottom-right' ? 'right: 20px;' : 'left: 20px;'}
                bottom: 90px;
            }
        }
        
        @keyframes chatWidgetFadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .chat-widget-window-iframe.show {
            display: block;
            animation: chatWidgetFadeIn 0.3s ease;
        }
        
        .chat-widget-badge-iframe {
            position: absolute;
            top: -5px;
            ${config.position === 'bottom-right' ? 'right: -5px;' : 'left: -5px;'}
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            display: none;
        }
    `;
    document.head.appendChild(style);
    
    // ایجاد دکمه ویجت
    const container = document.createElement('div');
    container.className = 'chat-widget-container-iframe';
    container.innerHTML = `
        <div class="chat-widget-button-iframe" id="chatWidgetButton">
            <svg viewBox="0 0 24 24" width="28" height="28" fill="white">
                <path d="M20 2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4V4c0-1.1-.9-2-2-2z"/>
            </svg>
            <div class="chat-widget-badge-iframe" id="chatWidgetBadge">1</div>
        </div>
        <iframe class="chat-widget-window-iframe" id="chatWidgetIframe" 
                src="${baseUrl}/widget-inline?service_code=${encodeURIComponent(serviceCode)}&primary_color=${encodeURIComponent(config.primaryColor || '#667eea')}&title=${encodeURIComponent(config.title || 'پشتیبانی آنلاین')}&welcome_message=${encodeURIComponent(config.welcomeMessage || 'سلام! چطور می‌توانم به شما کمک کنم؟')}"
                title="Chat Widget">
        </iframe>
    `;
    document.body.appendChild(container);
    
    // المنت‌ها
    const button = document.getElementById('chatWidgetButton');
    const iframe = document.getElementById('chatWidgetIframe');
    const badge = document.getElementById('chatWidgetBadge');
    
    let isOpen = false;
    let hasUnread = false;
    
    // باز کردن ویجت
    function openWidget() {
        iframe.classList.add('show');
        button.style.transform = 'scale(0.95)';
        isOpen = true;
        
        if (hasUnread) {
            badge.style.display = 'none';
            hasUnread = false;
        }
        
        setTimeout(() => {
            iframe.contentWindow.postMessage({ type: 'focus' }, '*');
        }, 300);
    }
    
    // بستن ویجت
    function closeWidget() {
        iframe.classList.remove('show');
        button.style.transform = 'scale(1)';
        isOpen = false;
    }
    
    // تغییر وضعیت
    function toggleWidget() {
        if (isOpen) {
            closeWidget();
        } else {
            openWidget();
        }
    }
    
    // دریافت پیام از iframe
    window.addEventListener('message', function(event) {
        if (event.data && event.data.type === 'newMessage' && !isOpen) {
            hasUnread = true;
            badge.style.display = 'flex';
        }
        
        if (event.data && event.data.type === 'close') {
            closeWidget();
        }
    });
    
    // رویداد کلیک روی دکمه
    button.addEventListener('click', toggleWidget);
    
    // باز کردن خودکار
    if (config.autoOpen) {
        const delay = config.autoOpenDelay || 3000;
        setTimeout(() => {
            if (!isOpen) {
                openWidget();
                setTimeout(() => {
                    if (isOpen) {
                        closeWidget();
                    }
                }, 30000);
            }
        }, delay);
    }
    
    console.log('✅ Chat Widget loaded successfully');
})();