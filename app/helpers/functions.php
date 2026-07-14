<?php
// app/helpers/functions.php

// اطمینان از وجود ROOT_PATH
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}

function loadEnv($path)
{
    if (!file_exists($path)) return;
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) continue;
        
        $key = trim($parts[0]);
        $value = trim($parts[1]);
        
        // حذف quotation marks
        $value = trim($value, '"\'');
        
        if (!isset($_ENV[$key])) {
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}

function dd($data)
{
    echo '<pre style="direction: ltr; text-align: left; background: #f4f4f4; padding: 15px; border-radius: 8px; margin: 20px; font-family: monospace;">';
    var_dump($data);
    echo '</pre>';
    die();
}

function escape($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function formatPersianDate($timestamp, $format = 'Y/m/d H:i')
{
    if (!$timestamp) return '-';
    
    // استفاده از تابع jdate اگر موجود باشد، در غیر این صورت از date استفاده کن
    if (function_exists('jdate')) {
        return jdate($format, strtotime($timestamp));
    }
    
    return date($format, strtotime($timestamp));
}

function getRoleName($role)
{
    $roles = [
        'super_admin' => 'مدیر کل',
        'admin' => 'مدیر',
        'moderator' => 'ناظر'
    ];
    return $roles[$role] ?? $role;
}

function getChannelName($channel)
{
    $channels = [
        'webapp' => 'وب اپلیکیشن',
        'telegram' => 'تلگرام',
        'instagram' => 'اینستاگرام',
        'whatsapp' => 'واتساپ'
    ];
    return $channels[$channel] ?? $channel;
}

function generateRandomString($length = 10)
{
    return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
}

function generateCustomerCode()
{
    $timestamp = base_convert(time(), 10, 36);
    $random = generateRandomString(6);
    return substr("cust_{$timestamp}_{$random}", 0, 32);
}

function generateServiceCode()
{
    $timestamp = base_convert(time(), 10, 36);
    $random = generateRandomString(4);
    return substr("svc_{$timestamp}_{$random}", 0, 32);
}