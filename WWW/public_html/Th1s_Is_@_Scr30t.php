<?php
define('SECRET_KEY', 'thisIsASecretKey123!');

function hmac_sha256($data, $key) {
    return hash_hmac('sha256', $data, $key);
}

if (!isset($_COOKIE['auth_token'])) {
    header('Location: index.html');
    exit();
}

$token_json = base64_decode($_COOKIE['auth_token']);
$data = json_decode($token_json, true);

if (!$data || !isset($data['username'], $data['timestamp'], $data['signature'])) {
    header('Location: index.html');
    exit();
}

// 验证签名
$expected_signature = hmac_sha256($data['username'] . $data['timestamp'], SECRET_KEY);

if (!hash_equals($expected_signature, $data['signature'])) {
    header('Location: index.html');
    exit();
}

// 可选：判断 token 时间有效期，比如5分钟以内
if ($data['timestamp'] < (time()*1000 - 5*60*1000)) {
    header('Location: index.html');
    exit();
}
require_once __DIR__ . '/vendor/autoload.php';

$smarty = new Smarty();
$smarty->setTemplateDir(sys_get_temp_dir());
$smarty->setCompileDir(sys_get_temp_dir());

// 获取 IP（可控）
$ip = $_SERVER['REMOTE_ADDR'];
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}

// 构造 Smarty 模板
$template = <<<TPL
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IP Information</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #333;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
            animation: fadeIn 0.8s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 2.2rem;
            background: linear-gradient(90deg, #3498db, #9b59b6);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .subtitle {
            color: #7f8c8d;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        
        .ip-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin: 20px 0;
            border-left: 5px solid #3498db;
            transition: transform 0.3s ease;
        }
        
        .ip-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .ip-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            display: block;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .ip-value {
            font-size: 1.4rem;
            color: #34495e;
            font-family: 'Courier New', monospace;
            word-break: break-all;
        }
        
        .footer {
            margin-top: 30px;
            color: #95a5a6;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hello There!</h1>
        <p class="subtitle">We've detected your connection details</p>
        
        <div class="ip-card">
            <span class="ip-label">Your IP Address</span>
            <span class="ip-value">{$ip}</span>
        </div>
        
        <div class="footer">
            Connection information updated in real-time
        </div>
    </div>
</body>
</html>
TPL;

// 写入模板文件
file_put_contents(sys_get_temp_dir() . '/ip.tpl', $template);

// 渲染并输出
$smarty->display('ip.tpl');