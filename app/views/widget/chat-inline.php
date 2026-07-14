<?php
// app/views/widget/chat-inline.php - ویجت iFrame
header('Content-Type: text/html; charset=utf-8');

$serviceCode = $serviceCode ?? ($_GET['service_code'] ?? '');
$primaryColor = $primaryColor ?? ($_GET['primary_color'] ?? '#667eea');
$buttonColor = $buttonColor ?? ($_GET['button_color'] ?? $primaryColor);
$title = $title ?? ($_GET['title'] ?? 'پشتیبانی آنلاین');
$welcomeMessage = $welcomeMessage ?? 'سلام! چطور می‌توانم به شما کمک کنم؟';
$apiBaseUrl = rtrim($_ENV['APP_URL'] ?? 'https://lifyai.com/mylumina', '/');

// استفاده از json_encode برای escape کامل
$serviceCodeJson = json_encode($serviceCode);
$welcomeMessageJson = json_encode($welcomeMessage);
$apiBaseUrlJson = json_encode($apiBaseUrl);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Vazirmatn', sans-serif; background: white; height: 100vh; overflow: hidden; }
        .chat-container { display: flex; flex-direction: column; height: 100vh; }
        .chat-header {
            background: linear-gradient(135deg, <?php echo htmlspecialchars($primaryColor); ?> 0%, #764ba2 100%);
            color: white; padding: 15px; text-align: center; font-weight: bold; flex-shrink: 0;
        }
        .chat-messages { flex: 1; overflow-y: auto; padding: 15px; display: flex; flex-direction: column; gap: 12px; background: #f8fafc; }
        .message { display: flex; flex-direction: column; max-width: 85%; }
        .message.user { align-self: flex-end; align-items: flex-end; }
        .message.assistant { align-self: flex-start; align-items: flex-start; }
        .message-content { padding: 10px 14px; border-radius: 18px; font-size: 14px; line-height: 1.5; }
        .message.user .message-content {
            background: linear-gradient(135deg, <?php echo htmlspecialchars($buttonColor); ?> 0%, #764ba2 100%);
            color: white; border-bottom-right-radius: 4px;
        }
        .message.assistant .message-content {
            background: white; color: #334155; border: 1px solid #e2e8f0; border-bottom-left-radius: 4px;
        }
        .message-time { font-size: 10px; color: #94a3b8; margin-top: 4px; }
        .streaming-cursor { display: inline-block; width: 2px; height: 18px; background: <?php echo htmlspecialchars($primaryColor); ?>; margin-right: 2px; animation: blink 1s infinite; vertical-align: middle; }
        @keyframes blink { 0%,50% { opacity: 1; } 51%,100% { opacity: 0; } }
        .typing-indicator { display: flex; align-items: center; gap: 8px; padding: 10px 14px; background: white; border: 1px solid #e2e8f0; border-radius: 18px; }
        .typing-dots { display: flex; gap: 4px; }
        .typing-dots span { width: 6px; height: 6px; border-radius: 50%; background: <?php echo htmlspecialchars($primaryColor); ?>; animation: typingBounce 1.4s infinite; }
        .typing-dots span:nth-child(1) { animation-delay: -0.32s; }
        .typing-dots span:nth-child(2) { animation-delay: -0.16s; }
        @keyframes typingBounce { 0%,80%,100% { transform: scale(0.8); opacity: 0.5; } 40% { transform: scale(1); opacity: 1; } }
        .chat-input-container { padding: 15px; border-top: 1px solid #e2e8f0; background: white; flex-shrink: 0; }
        .input-wrapper { display: flex; gap: 10px; }
        .chat-input { flex: 1; padding: 12px; border: 1px solid #e2e8f0; border-radius: 25px; outline: none; font-family: 'Vazirmatn', sans-serif; font-size: 14px; resize: none; }
        .chat-input:focus { border-color: <?php echo htmlspecialchars($primaryColor); ?>; }
        .send-button {
            background: linear-gradient(135deg, <?php echo htmlspecialchars($buttonColor); ?> 0%, #764ba2 100%);
            color: white; border: none; border-radius: 25px; padding: 12px 20px; cursor: pointer; font-family: 'Vazirmatn', sans-serif;
        }
        .send-button:disabled { opacity: 0.6; cursor: not-allowed; }
        .chat-messages::-webkit-scrollbar { width: 5px; }
        .chat-messages::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 5px; }
        .chat-messages::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">🤖 <?php echo htmlspecialchars($title); ?></div>
        <div class="chat-messages" id="chatMessages">
            <div class="message assistant">
                <div class="message-content"><?php echo htmlspecialchars($welcomeMessage); ?></div>
                <div class="message-time" id="welcomeTime"></div>
            </div>
        </div>
        <div class="chat-input-container">
            <div class="input-wrapper">
                <textarea class="chat-input" id="chatInput" placeholder="پیام خود را بنویسید..." rows="1"></textarea>
                <button class="send-button" id="sendButton">ارسال</button>
            </div>
        </div>
    </div>
    
    <script>
        // ============================================================
        // متغیرها - با json_encode برای امنیت کامل
        // ============================================================
        var serviceCode = <?php echo $serviceCodeJson; ?>;
        var welcomeMessageText = <?php echo $welcomeMessageJson; ?>;
        var apiBaseUrl = <?php echo $apiBaseUrlJson; ?>;
        
        console.log('✅ Chat Widget Init - serviceCode:', serviceCode);
        console.log('✅ welcomeMessage:', welcomeMessageText);
        console.log('✅ apiBaseUrl:', apiBaseUrl);
        
        var visitorId = localStorage.getItem('chat_vid');
        if (!visitorId) {
            visitorId = 'v_' + Date.now() + '_' + Math.random().toString(36).substring(2, 10);
            localStorage.setItem('chat_vid', visitorId);
        }
        
        var widgetId = localStorage.getItem('chat_widget_id');
        if (!widgetId) {
            widgetId = 'widget_' + Date.now();
            localStorage.setItem('chat_widget_id', widgetId);
        }
        
        var isStreaming = false;
        var currentStreamElement = null;
        var fullResponse = '';
        
        // ============================================================
        // توابع کمکی
        // ============================================================
        function escapeHtml(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function formatTime() {
            return new Date().toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' });
        }
        
        function scrollToBottom(force) {
            var container = document.getElementById('chatMessages');
            if (container) {
                setTimeout(function() {
                    // فقط وقتی پیام‌های واقعی وجود دارد اسکرول کن
                    var messages = container.querySelectorAll('.message:not([style*="display: none"])');
                    if (messages.length > 1 || force === true) {
                        container.scrollTop = container.scrollHeight;
                    }
                }, 50);
            }
        }
        
        function addMessage(content, isUser) {
            var container = document.getElementById('chatMessages');
            var welcomeMsg = container.querySelector('.message.assistant:first-child');
            if (welcomeMsg && welcomeMsg.querySelector('.message-content').innerText === welcomeMessageText) {
                welcomeMsg.style.display = 'none';
            }
            var messageDiv = document.createElement('div');
            messageDiv.className = 'message ' + (isUser ? 'user' : 'assistant');
            messageDiv.innerHTML = '<div class="message-content">' + escapeHtml(content) + '</div><div class="message-time">' + formatTime() + '</div>';
            container.appendChild(messageDiv);
            scrollToBottom();
        }
        
        function showTyping() {
            var container = document.getElementById('chatMessages');
            var typingDiv = document.createElement('div');
            typingDiv.className = 'message assistant';
            typingDiv.id = 'typing-indicator';
            typingDiv.innerHTML = '<div class="typing-indicator"><div class="typing-dots"><span></span><span></span><span></span></div></div>';
            container.appendChild(typingDiv);
            scrollToBottom();
        }
        
        function hideTyping() {
            var typing = document.getElementById('typing-indicator');
            if (typing) typing.remove();
        }
        
        function createStreamMessage() {
            var container = document.getElementById('chatMessages');
            var messageDiv = document.createElement('div');
            messageDiv.className = 'message assistant';
            messageDiv.innerHTML = '<div class="message-content"><span class="streaming-text"></span><span class="streaming-cursor"></span></div><div class="message-time">' + formatTime() + '</div>';
            container.appendChild(messageDiv);
            scrollToBottom();
            return messageDiv.querySelector('.message-content');
        }
        
        function updateStreamMessage(element, content) {
            if (element) {
                var textSpan = element.querySelector('.streaming-text');
                if (textSpan) {
                    textSpan.innerHTML = escapeHtml(content);
                } else {
                    element.innerHTML = escapeHtml(content) + '<span class="streaming-cursor"></span>';
                }
                scrollToBottom();
            }
        }
        
        function finalizeStreamMessage(element, content) {
            if (element) {
                element.innerHTML = escapeHtml(content);
                var cursor = element.querySelector('.streaming-cursor');
                if (cursor) cursor.remove();
                scrollToBottom();
            }
        }
        
        // ============================================================
        // تابع اصلی ارسال پیام
        // ============================================================
        function sendMessage() {
            console.log('===== sendMessage called =====');
            
            if (isStreaming) {
                console.log('Already streaming');
                return;
            }
            
            var input = document.getElementById('chatInput');
            var message = input.value.trim();
            
            if (!message) {
                console.log('Empty message');
                return;
            }
            
            console.log('Sending message:', message);
            addMessage(message, true);
            input.value = '';
            input.style.height = 'auto';
            
            isStreaming = true;
            var sendBtn = document.getElementById('sendButton');
            sendBtn.disabled = true;
            showTyping();
            
            var apiUrl = apiBaseUrl + '/api/widgets/chat-stream';
            console.log('API URL:', apiUrl);
            
            fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message: message,
                    service_code: serviceCode,
                    visitor_id: visitorId,
                    widget_id: widgetId
                })
            })
            .then(function(response) {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.body.getReader();
            })
            .then(function(reader) {
                hideTyping();
                currentStreamElement = createStreamMessage();
                fullResponse = '';
                
                var decoder = new TextDecoder();
                var buffer = '';
                
                function readStream() {
                    return reader.read().then(function(result) {
                        if (result.done) {
                            finalizeStreamMessage(currentStreamElement, fullResponse);
                            console.log('Message completed');
                            return;
                        }
                        
                        buffer += decoder.decode(result.value, { stream: true });
                        var lines = buffer.split('\n');
                        buffer = lines.pop() || '';
                        
                        for (var i = 0; i < lines.length; i++) {
                            var trimmedLine = lines[i].trim();
                            if (trimmedLine && trimmedLine.startsWith('data: ')) {
                                var jsonStr = trimmedLine.slice(6);
                                if (jsonStr === '[DONE]') continue;
                                
                                try {
                                    var data = JSON.parse(jsonStr);
                                    if (data.error) {
                                        hideTyping();
                                        if (currentStreamElement) {
                                            var parentDiv = currentStreamElement.closest('.message');
                                            if (parentDiv) parentDiv.remove();
                                        }
                                        addMessage('❌ ' + data.error, false);
                                        isStreaming = false;
                                        document.getElementById('sendButton').disabled = false;
                                        return;
                                    }
                                    if (data.content) {
                                        fullResponse += data.content;
                                        updateStreamMessage(currentStreamElement, fullResponse);
                                    }
                                } catch (e) {
                                    console.error('Parse error:', e);
                                }
                            }
                        }
                        
                        return readStream();
                    });
                }
                
                return readStream();
            })
            .catch(function(error) {
                console.error('Send error:', error);
                hideTyping();
                if (currentStreamElement) {
                    var parentDiv = currentStreamElement.closest('.message');
                    if (parentDiv) parentDiv.remove();
                }
                addMessage('❌ خطا در ارتباط با سرور. لطفاً دوباره تلاش کنید.', false);
            })
            .finally(function() {
                isStreaming = false;
                document.getElementById('sendButton').disabled = false;
                document.getElementById('chatInput').focus();
                currentStreamElement = null;
            });
        }
        
        // ============================================================
        // راه‌اندازی
        // ============================================================
        function initChat() {
            console.log('Initializing chat...');
            document.getElementById('welcomeTime').textContent = formatTime();
            
            var chatInput = document.getElementById('chatInput');
            var sendButton = document.getElementById('sendButton');
            
            if (sendButton) {
                sendButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    sendMessage();
                });
            }
            
            if (chatInput) {
                chatInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        sendMessage();
                    }
                });
                
                chatInput.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 100) + 'px';
                });
                
                // ❌ حذف شد: chatInput.focus();
                // این خط باعث اسکرول خودکار صفحه در زمان لود ویجت می‌شد
            }
            
            // ✅ اضافه شد: listener برای دریافت پیام focus از parent
            window.addEventListener('message', function(event) {
                if (event.data && event.data.type === 'focus') {
                    if (chatInput) {
                        chatInput.focus();
                    }
                }
            });
            
            console.log('✅ Chat initialized successfully');
        }
        
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            initChat();
        } else {
            document.addEventListener('DOMContentLoaded', initChat);
        }
    </script>
</body>
</html>