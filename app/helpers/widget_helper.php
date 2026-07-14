<?php
// app/helpers/widget_helper.php

/**
 * تولید کدهای ویجت با تنظیمات کامل
 * 
 * @param array $service اطلاعات سرویس
 * @param array $settings تنظیمات ویجت (primaryColor, buttonColor, floatingPosition, iframeWidth, iframeHeight, iframePosition)
 * @param string $baseUrl آدرس پایه سایت
 * @return array ['floatingCode' => string, 'iframeCode' => string, 'widgetUrl' => string, 'settings' => array]
 */
function generateWidgetCodes($service, $settings = [], $baseUrl = '')
{
    if (empty($baseUrl)) {
        $baseUrl = rtrim($_ENV['APP_URL'] ?? 'https://lifyai.com/mylumina', '/');
    }
    
    // دریافت اطلاعات سرویس
    $serviceCode = $service['service_code'] ?? '';
    $serviceTitle = $service['title'] ?? 'پشتیبانی آنلاین';
    $serviceWelcome = $service['welcome_message'] ?? 'سلام! چطور می‌توانم به شما کمک کنم؟';
    
    // تنظیمات پیش‌فرض
    $primaryColor = $settings['primaryColor'] ?? '#667eea';
    $buttonColor = $settings['buttonColor'] ?? '#28a745';
    $floatingPosition = $settings['floatingPosition'] ?? 'bottom-right';
    $iframeWidth = $settings['iframeWidth'] ?? '500px';
    $iframeHeight = $settings['iframeHeight'] ?? '550';
    $iframePosition = $settings['iframePosition'] ?? 'center';
    $title = $settings['title'] ?? $serviceTitle;
    $welcomeMessage = $settings['welcomeMessage'] ?? $serviceWelcome;
    
    // ============================================================
    // ساخت URL با رنگ‌های کامل (با #)
    // ============================================================
    $primaryColorUrl = urlencode($primaryColor);
    $buttonColorUrl = urlencode($buttonColor);
    $titleEncoded = urlencode($title);
    $welcomeEncoded = urlencode($welcomeMessage);
    
    $widgetUrl = $baseUrl . '/widget-inline?service_code=' . urlencode($serviceCode) . 
                 '&primary_color=' . $primaryColorUrl . 
                 '&button_color=' . $buttonColorUrl . 
                 '&title=' . $titleEncoded . 
                 '&welcome_message=' . $welcomeEncoded;
    
    // ================================================================
    // کد شناور (Floating Widget)
    // ================================================================
    $floatingPosCSS = '';
    $windowPosCSS = '';
    if ($floatingPosition === 'bottom-right') {
        $floatingPosCSS = 'right: 20px; bottom: 20px;';
        $windowPosCSS = 'right: 20px; bottom: 90px;';
    } else if ($floatingPosition === 'bottom-left') {
        $floatingPosCSS = 'left: 20px; bottom: 20px;';
        $windowPosCSS = 'left: 20px; bottom: 90px;';
    }
    
    $floatingCode = "<!-- لومینا - ویجت چت شناور {$title} -->\n";
    $floatingCode .= "<style>\n";
    $floatingCode .= "    .lumina-chat-btn-{$serviceCode} {\n";
    $floatingCode .= "        position: fixed;\n";
    $floatingCode .= "        {$floatingPosCSS}\n";
    $floatingCode .= "        width: 56px;\n";
    $floatingCode .= "        height: 56px;\n";
    $floatingCode .= "        border-radius: 50%;\n";
    $floatingCode .= "        background: linear-gradient(135deg, {$primaryColor} 0%, #764ba2 100%);\n";
    $floatingCode .= "        cursor: pointer;\n";
    $floatingCode .= "        box-shadow: 0 4px 15px rgba(0,0,0,0.2);\n";
    $floatingCode .= "        z-index: 999999;\n";
    $floatingCode .= "        border: none;\n";
    $floatingCode .= "        display: flex;\n";
    $floatingCode .= "        align-items: center;\n";
    $floatingCode .= "        justify-content: center;\n";
    $floatingCode .= "        transition: transform 0.3s ease;\n";
    $floatingCode .= "    }\n";
    $floatingCode .= "    .lumina-chat-btn-{$serviceCode}:hover { transform: scale(1.1); }\n";
    $floatingCode .= "    .lumina-chat-btn-{$serviceCode} svg { width: 28px; height: 28px; fill: white; }\n";
    $floatingCode .= "    .lumina-chat-window-{$serviceCode} {\n";
    $floatingCode .= "        position: fixed;\n";
    $floatingCode .= "        {$windowPosCSS}\n";
    $floatingCode .= "        width: 380px;\n";
    $floatingCode .= "        height: 500px;\n";
    $floatingCode .= "        background: white;\n";
    $floatingCode .= "        border-radius: 16px;\n";
    $floatingCode .= "        box-shadow: 0 10px 40px rgba(0,0,0,0.15);\n";
    $floatingCode .= "        display: none;\n";
    $floatingCode .= "        z-index: 999998;\n";
    $floatingCode .= "        border: none;\n";
    $floatingCode .= "    }\n";
    $floatingCode .= "    @media (max-width: 768px) {\n";
    $floatingCode .= "        .lumina-chat-window-{$serviceCode} { width: calc(100vw - 40px); height: 70vh; }\n";
    $floatingCode .= "    }\n";
    $floatingCode .= "</style>\n";
    $floatingCode .= "<div id=\"luminaChatContainer\">\n";
    $floatingCode .= "    <button class=\"lumina-chat-btn-{$serviceCode}\" id=\"luminaChatBtn\">\n";
    $floatingCode .= "        <svg viewBox=\"0 0 24 24\"><path d=\"M20 2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4V4c0-1.1-.9-2-2-2z\"/></svg>\n";
    $floatingCode .= "    </button>\n";
    $floatingCode .= "    <iframe class=\"lumina-chat-window-{$serviceCode}\" id=\"luminaChatIframe\" \n";
    $floatingCode .= "        src=\"{$widgetUrl}\"\n";
    $floatingCode .= "        title=\"{$title}\">\n";
    $floatingCode .= "    </iframe>\n";
    $floatingCode .= "</div>\n";
    $floatingCode .= "<script>\n";
    $floatingCode .= "(function() {\n";
    $floatingCode .= "    const btn = document.getElementById('luminaChatBtn');\n";
    $floatingCode .= "    const iframe = document.getElementById('luminaChatIframe');\n";
    $floatingCode .= "    let isOpen = false;\n";
    $floatingCode .= "    if (btn) {\n";
    $floatingCode .= "        btn.addEventListener('click', function(e) {\n";
    $floatingCode .= "            e.stopPropagation();\n";
    $floatingCode .= "            if (isOpen) { iframe.style.display = 'none'; isOpen = false; }\n";
    $floatingCode .= "            else { iframe.style.display = 'block'; isOpen = true; }\n";
    $floatingCode .= "        });\n";
    $floatingCode .= "        document.addEventListener('click', function(e) {\n";
    $floatingCode .= "            if (isOpen && btn && iframe && !btn.contains(e.target) && !iframe.contains(e.target)) {\n";
    $floatingCode .= "                iframe.style.display = 'none'; isOpen = false;\n";
    $floatingCode .= "            }\n";
    $floatingCode .= "        });\n";
    $floatingCode .= "    }\n";
    $floatingCode .= "})();\n";
    $floatingCode .= "</script>";
    
    // ================================================================
    // کد iFrame (Inline Widget)
    // ================================================================
    if ($iframePosition === 'center') {
        // ============================================================
        // اصلاح: حذف min-height: 100vh و استفاده از height: auto
        // همچنین اضافه کردن overflow: hidden برای جلوگیری از اسکرول
        // ============================================================
        $iframeCode = "<!-- لومینا - ویجت چت {$title} -->\n";
        $iframeCode .= "<div style=\"display: flex; justify-content: center; align-items: center; height: auto; padding: 20px 15px; background: transparent; width: 100%; box-sizing: border-box; overflow: hidden;\">\n";
        $iframeCode .= "    <div style=\"width: {$iframeWidth}; max-width: 100%; height: {$iframeHeight}px;\">\n";
        $iframeCode .= "        <iframe \n";
        $iframeCode .= "            src=\"{$widgetUrl}\"\n";
        $iframeCode .= "            width=\"100%\"\n";
        $iframeCode .= "            height=\"100%\"\n";
        $iframeCode .= "            frameborder=\"0\"\n";
        $iframeCode .= "            style=\"border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); display: block; overflow: hidden;\"\n";
        $iframeCode .= "            title=\"{$title}\"\n";
        $iframeCode .= "            scrolling=\"no\">\n";
        $iframeCode .= "        </iframe>\n";
        $iframeCode .= "    </div>\n";
        $iframeCode .= "</div>";
    } else {
        $positionStyle = '';
        switch($iframePosition) {
            case 'bottom-right': $positionStyle = 'position: fixed; bottom: 20px; right: 20px;'; break;
            case 'bottom-left': $positionStyle = 'position: fixed; bottom: 20px; left: 20px;'; break;
            case 'top-right': $positionStyle = 'position: fixed; top: 20px; right: 20px;'; break;
            case 'top-left': $positionStyle = 'position: fixed; top: 20px; left: 20px;'; break;
            default: $positionStyle = 'position: relative; margin: 0 auto;';
        }
        
        $iframeCode = "<!-- لومینا - ویجت چت {$title} -->\n";
        $iframeCode .= "<div style=\"{$positionStyle} width: {$iframeWidth}; max-width: 100%; height: {$iframeHeight}px;\">\n";
        $iframeCode .= "    <iframe \n";
        $iframeCode .= "        src=\"{$widgetUrl}\"\n";
        $iframeCode .= "        width=\"100%\"\n";
        $iframeCode .= "        height=\"100%\"\n";
        $iframeCode .= "        frameborder=\"0\"\n";
        $iframeCode .= "        style=\"border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); display: block; overflow: hidden;\"\n";
        $iframeCode .= "        title=\"{$title}\"\n";
        $iframeCode .= "        scrolling=\"no\">\n";
        $iframeCode .= "    </iframe>\n";
        $iframeCode .= "</div>";
    }
    
    return [
        'floatingCode' => $floatingCode,
        'iframeCode' => $iframeCode,
        'widgetUrl' => $widgetUrl,
        'settings' => [
            'primaryColor' => $primaryColor,
            'buttonColor' => $buttonColor,
            'floatingPosition' => $floatingPosition,
            'iframeWidth' => $iframeWidth,
            'iframeHeight' => $iframeHeight,
            'iframePosition' => $iframePosition,
            'title' => $title,
            'welcomeMessage' => $welcomeMessage
        ]
    ];
}