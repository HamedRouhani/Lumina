<?php
// app/views/widget/chat-widget.php - ویجت شناور
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Vazirmatn', sans-serif; background: transparent; }
        
        .chat-widget-container {
            position: fixed;
            <?php echo $position === 'bottom-right' ? 'right: 20px;' : 'left: 20px;'; ?>
            bottom: 20px;
            z-index: 10000;
        }
        
        .chat-widget-button {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, <?php echo $primaryColor; ?> 0%, #764ba2 100%);
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(102,126,234,0.4);
            transition: all 0.3s;
        }
        
        .chat-widget-button:hover { transform: scale(1.1); }
        
        .chat-widget-window {
            position: absolute;
            <?php echo $position === 'bottom-right' ? 'right: 0;' : 'left: 0;'; ?>
            bottom: 70px;
            width: 380px;
            max-width: 85vw;
            height: 520px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            display: none;
            flex-direction: column;
            overflow: hidden;
        }
        
        .chat-widget-header {
            background: linear-gradient(135deg, <?php echo $primaryColor; ?> 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .chat-widget-title { font-weight: bold; }
        
        .chat-widget-close {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
        }
        
        .chat-widget-close:hover { background: rgba(255,255,255,0.3); }
        
        .chat-widget-messages {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 12px;
            background: #f8fafc;
        }
        
        .message { display: flex; flex-direction: column; max-width: 85%; }
        .message.user { align-self: flex-end; align-items: flex-end; }
        .message.assistant { align-self: flex-start; align-items: flex-start; }
        
        .message-content {
            padding: 10px 14px;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .message.user .message-content {
            background: linear-gradient(135deg, <?php echo $primaryColor; ?> 0%, #764ba2 100%);
            color: white;
            border-bottom-right-radius: 4px;
        }
        
        .message.assistant .message-content {
            background: white;
            color: #334155;
            border: 1px solid #e2e8f0;
            border-bottom-left-radius: 4px;
        }
        
        .message-time { font-size: 10px; color: #94a3b8; margin-top: 4px; }
        
        .streaming-cursor {
            display: inline-block;
            width: 2px;
            height: 18px;
            background: <?php echo $primaryColor; ?>;
            margin-right: 2px;
            animation: blink 1s infinite;
            vertical-align: middle;
        }
        
        @keyframes blink { 0%,50% { opacity: 1; } 51%,100% { opacity: 0; } }
        
        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
        }
        
        .typing-dots { display: flex; gap: 4px; }
        .typing-dots span {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: <?php echo $primaryColor; ?>;
            animation: typingBounce 1.4s infinite;
        }
        
        .typing-dots span:nth-child(1) { animation-delay: -0.32s; }
        .typing-dots span:nth-child(2) { animation-delay: -0.16s; }
        
        @keyframes typingBounce {
            0%,80%,100% { transform: scale(0.8); opacity: 0.5; }
            40% { transform: scale(1); opacity: 1; }
        }
        
        .chat-widget-input-container {
            padding: 15px;
            border-top: 1px solid #e2e8f0;
            background: white;
        }
        
        .input-wrapper { display: flex; gap: 10px; }
        
        .chat-widget-input {
            flex: 1;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 25px;
            outline: none;
            font-family: 'Vazirmatn', sans-serif;
            font-size: 14px;
            resize: none;
        }
        
        .chat-widget-input:focus { border-color: <?php echo $primaryColor; ?>; }
        
        .send-button {
            background: linear-gradient(135deg, <?php echo $primaryColor; ?> 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 12px 20px;
            cursor: pointer;
            font-family: 'Vazirmatn', sans-serif;
        }
        
        .send-button:disabled { opacity: 0.6; cursor: not-allowed; }
        
        .chat-widget-messages::-webkit-scrollbar { width: 5px; }
        .chat-widget-messages::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 5px; }
        .chat-widget-messages::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 5px; }
        
        @media (max-width: 768px) {
            .chat-widget-window { width: calc(100vw - 40px); height: 70vh; }
        }
    </style>
</head>
<body>
    <div class="chat-widget-container">
        <div class="chat-widget-button" id="chatButton">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="white">
                <path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
            </svg>
        </div>
        
        <div class="chat-widget-window" id="chatWindow">
            <div class="chat-widget-header">
                <div class="chat-widget-title"><?php echo htmlspecialchars($title); ?></div>
                <button class="chat-widget-close" id="closeButton">×</button>
            </div>
            
            <div class="chat-widget-messages" id="chatMessages">
                <div class="message assistant">
                    <div class="message-content"><?php echo htmlspecialchars($welcomeMessage); ?></div>
                    <div class="message-time" id="welcomeTime"></div>
                </div>
            </div>
            
            <div class="chat-widget-input-container">
                <div class="input-wrapper">
                    <textarea class="chat-widget-input" id="chatInput" placeholder="پیام خود را بنویسید..." rows="1"></textarea>
                    <button class="send-button" id="sendButton">ارسال</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const serviceCode = '<?php echo addslashes($serviceCode); ?>';
        const apiBaseUrl = '<?php echo $apiBaseUrl; ?>';
        const primaryColor = '<?php echo $primaryColor; ?>';
        const autoOpen = <?php echo $autoOpen ? 'true' : 'false'; ?>;
        
        let visitorId = localStorage.getItem('chat_vid');
        if (!visitorId) {
            visitorId = 'v_' + Date.now() + '_' + Math.random().toString(36).substring(2, 10);
            localStorage.setItem('chat_vid', visitorId);
        }
        
        let widgetId = localStorage.getItem('chat_widget_id');
        if (!widgetId) {
            widgetId = 'widget_' + Date.now();
            localStorage.setItem('chat_widget_id', widgetId);
        }
        
        let isStreaming = false;
        let currentStreamElement = null;
        let fullResponse = '';
        let isOpen = false;
        
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function formatTime() {
            return new Date().toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' });
        }
        
        function scrollToBottom() {
            const container = document.getElementById('chatMessages');
            if (container) setTimeout(() => container.scrollTop = container.scrollHeight, 50);
        }
        
        function addMessage(content, isUser) {
            const container = document.getElementById('chatMessages');
            const welcomeMsg = container.querySelector('.message.assistant:first-child');
            if (welcomeMsg && welcomeMsg.querySelector('.message-content').innerText === '<?php echo addslashes($welcomeMessage); ?>') {
                welcomeMsg.style.display = 'none';
            }
            
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user' : 'assistant'}`;
            messageDiv.innerHTML = `<div class="message-content">${escapeHtml(content)}</div><div class="message-time">${formatTime()}</div>`;
            container.appendChild(messageDiv);
            scrollToBottom();
        }
        
        function showTyping() {
            const container = document.getElementById('chatMessages');
            const typingDiv = document.createElement('div');
            typingDiv.className = 'message assistant';
            typingDiv.id = 'typing-indicator';
            typingDiv.innerHTML = `<div class="typing-indicator"><div class="typing-dots"><span></span><span></span><span></span></div><span>در حال تایپ...</span></div>`;
            container.appendChild(typingDiv);
            scrollToBottom();
        }
        
        function hideTyping() {
            const typing = document.getElementById('typing-indicator');
            if (typing) typing.remove();
        }
        
        function createStreamMessage() {
            const container = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message assistant';
            messageDiv.innerHTML = `<div class="message-content"><span></span><span class="streaming-cursor"></span></div><div class="message-time">${formatTime()}</div>`;
            container.appendChild(messageDiv);
            scrollToBottom();
            return messageDiv.querySelector('.message-content');
        }
        
        function updateStreamMessage(element, content) {
            if (element) {
                element.innerHTML = escapeHtml(content) + '<span class="streaming-cursor"></span>';
                scrollToBottom();
            }
        }
        
        function finalizeStreamMessage(element, content) {
            if (element) {
                element.innerHTML = escapeHtml(content);
                const cursor = element.querySelector('.streaming-cursor');
                if (cursor) cursor.remove();
                scrollToBottom();
            }
        }
        
        async function sendMessage() {
        if (isStreaming) return;
        
        const input = document.getElementById('chatInput');
        const message = input.value.trim();
        if (!message) return;
        
        addMessage(message, true);
        input.value = '';
        input.style.height = 'auto';
        
        isStreaming = true;
        document.getElementById('sendButton').disabled = true;
        showTyping();
        
        let reader = null;
        
        try {
            const response = await fetch(`${apiBaseUrl}/api/widgets/chat-stream`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message: message,
                    service_code: serviceCode,
                    visitor_id: visitorId,
                    widget_id: widgetId
                })
            });
            
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`خطا در ارتباط با سرور (${response.status}): ${errorText.substring(0, 100)}`);
            }
            
            hideTyping();
            currentStreamElement = createStreamMessage();
            fullResponse = '';
            
            reader = response.body.getReader();
            const decoder = new TextDecoder();
            let buffer = '';
            
            while (true) {
                const { done, value } = await reader.read();
                if (done) break;
                
                buffer += decoder.decode(value, { stream: true });
                const lines = buffer.split('\n');
                buffer = lines.pop() || '';
                
                for (const line of lines) {
                    if (line.trim() && line.startsWith('data: ')) {
                        try {
                            const data = JSON.parse(line.slice(6));
                            
                            // نمایش خطاهای سرور
                            if (data.error) {
                                hideTyping();
                                if (currentStreamElement) {
                                    const parentDiv = currentStreamElement.closest('.message');
                                    if (parentDiv) parentDiv.remove();
                                }
                                addMessage('❌ ' + data.error, false);
                                isStreaming = false;
                                document.getElementById('sendButton').disabled = false;
                                if (reader) await reader.cancel();
                                return;
                            }
                            
                            if (data.content) {
                                fullResponse += data.content;
                                updateStreamMessage(currentStreamElement, fullResponse);
                            } else if (data.complete) {
                                // پایان استریم
                                break;
                            }
                        } catch (e) {
                            console.error('Parse error:', e);
                        }
                    }
                }
            }
            
            finalizeStreamMessage(currentStreamElement, fullResponse);
            
        } catch (error) {
            console.error('Error:', error);
            hideTyping();
            if (currentStreamElement) {
                const parentDiv = currentStreamElement.closest('.message');
                if (parentDiv) parentDiv.remove();
            }
            addMessage('❌ ' + error.message, false);
        } finally {
            isStreaming = false;
            document.getElementById('sendButton').disabled = false;
            input.focus();
            currentStreamElement = null;
            if (reader) try { await reader.cancel(); } catch(e) {}
        }
    }
        
        function openChat() {
            if (isOpen) return;
            document.getElementById('chatWindow').style.display = 'flex';
            document.getElementById('chatButton').style.transform = 'scale(0.9)';
            isOpen = true;
            setTimeout(() => document.getElementById('chatInput').focus(), 300);
        }
        
        function closeChat() {
            if (isStreaming && !confirm('در حال دریافت پاسخ هستید. آیا مطمئنید؟')) return;
            document.getElementById('chatWindow').style.display = 'none';
            document.getElementById('chatButton').style.transform = 'scale(1)';
            isOpen = false;
        }
        
        function toggleChat() { isOpen ? closeChat() : openChat(); }
        
        document.getElementById('welcomeTime').textContent = formatTime();
        document.getElementById('chatButton').addEventListener('click', toggleChat);
        document.getElementById('closeButton').addEventListener('click', closeChat);
        document.getElementById('sendButton').addEventListener('click', sendMessage);
        
        const chatInput = document.getElementById('chatInput');
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
        chatInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });
        
        if (autoOpen) setTimeout(openChat, 2000);
        
        console.log('✅ Chat widget initialized');
    </script>
</body>
</html>