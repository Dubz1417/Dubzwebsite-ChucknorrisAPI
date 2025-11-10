<?php
        
function initSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', 1); 
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.cookie_lifetime', 0); 
        ini_set('session.gc_maxlifetime', 1800); 
        ini_set('session.use_strict_mode', 1); 
        
        session_start();
        
      
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } elseif (time() - $_SESSION['created'] > 1200) { 
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
        
        
        if (!isset($_SESSION['user_ip'])) {
            $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
        } elseif ($_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
            
            session_unset();
            session_destroy();
            session_start();
            logSecurityEvent('session_hijack_attempt', 'IP mismatch detected');
        }
        
        
        if (!isset($_SESSION['user_agent'])) {
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        } elseif ($_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
           
            session_unset();
            session_destroy();
            session_start();
            logSecurityEvent('session_hijack_attempt', 'User agent mismatch detected');
        }
    }
}


function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    
   
    if (time() - $_SESSION['csrf_token_time'] > 3600) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    
    return $_SESSION['csrf_token'];
}


function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        logSecurityEvent('csrf_token_failure', 'Invalid CSRF token');
        return false;
    }
    return true;
}


function sanitizeInput($data, $maxLength = 255) {
    if (is_array($data)) {
        return array_map(function($item) use ($maxLength) {
            return sanitizeInput($item, $maxLength);
        }, $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    
    
    if (strlen($data) > $maxLength) {
        $data = substr($data, 0, $maxLength);
    }
    
    
    $data = str_replace(chr(0), '', $data);
    
    
    $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    return $data;
}


function validateEmail($email) {
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
   
    if (strlen($email) > 254) { 
        return false;
    }
    
   
    $disposableDomains = ['tempmail.com', 'guerrillamail.com', 'mailinator.com', '10minutemail.com'];
    $domain = substr(strrchr($email, "@"), 1);
    if (in_array(strtolower($domain), $disposableDomains)) {
        return false;
    }
    
    return true;
}


function validatePhone($phone) {
    $phone = preg_replace('/[\s\-]/', '', $phone);
    return preg_match('/^(\+63|0)?[0-9]{10}$/', $phone);
}


function checkLoginAttempts($username) {
    $ip = $_SERVER['REMOTE_ADDR'];
    
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }
    if (!isset($_SESSION['ip_attempts'])) {
        $_SESSION['ip_attempts'] = [];
    }
    
    $now = time();
    $username_key = hash('sha256', $username); 
    $ip_key = hash('sha256', $ip);
    

    if (isset($_SESSION['login_attempts'][$username_key])) {
        $_SESSION['login_attempts'][$username_key] = array_filter(
            $_SESSION['login_attempts'][$username_key], 
            function($timestamp) use ($now) {
                return ($now - $timestamp) < 900; 
            }
        );
    }
    
    if (isset($_SESSION['ip_attempts'][$ip_key])) {
        $_SESSION['ip_attempts'][$ip_key] = array_filter(
            $_SESSION['ip_attempts'][$ip_key], 
            function($timestamp) use ($now) {
                return ($now - $timestamp) < 900;
            }
        );
    }
    
   
    $username_attempts = isset($_SESSION['login_attempts'][$username_key]) ? 
                        count($_SESSION['login_attempts'][$username_key]) : 0;
    
    
    $ip_attempts = isset($_SESSION['ip_attempts'][$ip_key]) ? 
                   count($_SESSION['ip_attempts'][$ip_key]) : 0;
    
   
    if ($username_attempts >= 5) {
        logSecurityEvent('brute_force_attempt', "Username: $username, Attempts: $username_attempts");
        return false;
    }
    
    if ($ip_attempts >= 10) { 
        logSecurityEvent('brute_force_attempt', "IP: $ip, Attempts: $ip_attempts");
        return false;
    }
    
    return true;
}


function recordLoginAttempt($username) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $username_key = hash('sha256', $username);
    $ip_key = hash('sha256', $ip);
    
    if (!isset($_SESSION['login_attempts'][$username_key])) {
        $_SESSION['login_attempts'][$username_key] = [];
    }
    if (!isset($_SESSION['ip_attempts'][$ip_key])) {
        $_SESSION['ip_attempts'][$ip_key] = [];
    }
    
    $_SESSION['login_attempts'][$username_key][] = time();
    $_SESSION['ip_attempts'][$ip_key][] = time();
    
    logSecurityEvent('failed_login', "Username: $username, IP: $ip");
}


function clearLoginAttempts($username) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $username_key = hash('sha256', $username);
    $ip_key = hash('sha256', $ip);
    
    unset($_SESSION['login_attempts'][$username_key]);
    unset($_SESSION['ip_attempts'][$ip_key]);
    
    logSecurityEvent('successful_login', "Username: $username, IP: $ip");
}


function validatePassword($password) {
    $errors = [];
    
    
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters';
    }
    
    
    if (strlen($password) > 128) {
        $errors[] = 'Password must be less than 128 characters';
    }
    
   
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }
    
   
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }
    
   
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }
    
    
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Password must contain at least one special character (!@#$%^&*)';
    }
    
    
    $commonPasswords = ['password', 'password123', '12345678', 'qwerty123', 'admin123'];
    if (in_array(strtolower($password), $commonPasswords)) {
        $errors[] = 'This password is too common. Please choose a stronger password';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

function validateFileUpload($file, $maxSize = 5242880) { 
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
  
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'error' => 'No file uploaded'];
    }
    
   
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload error'];
    }
 
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File too large (max 5MB)'];
    }
    
    
    if ($file['size'] < 100) {
        return ['success' => false, 'error' => 'File too small'];
    }
    
   
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        return ['success' => false, 'error' => 'Invalid file type. Only images allowed'];
    }
    
  
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_extensions)) {
        return ['success' => false, 'error' => 'Invalid file extension'];
    }
    
   
    $filename = $file['name'];
    if (substr_count($filename, '.') > 1) {
        return ['success' => false, 'error' => 'Invalid filename'];
    }
    
    
    if (function_exists('getimagesize')) {
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['success' => false, 'error' => 'Invalid image file'];
        }
    }
    
    return ['success' => true];
}


function setSecurityHeaders() {
   
    header('X-Frame-Options: DENY');
    
   
    header('X-XSS-Protection: 1; mode=block');
    
   
    header('X-Content-Type-Options: nosniff');
    
    
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
   
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    
   
    header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
    
    
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://api.chucknorris.io https://fonts.googleapis.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https: blob:; connect-src 'self' https://api.chucknorris.io; frame-ancestors 'none'; base-uri 'self'; form-action 'self';");
}


function logSecurityEvent($event_type, $details = '') {
    $log_file = __DIR__ . '/security_logs.txt';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $timestamp = date('Y-m-d H:i:s');
    $username = $_SESSION['username'] ?? 'guest';
    
    $log_entry = sprintf(
        "[%s] %s | User: %s | IP: %s | Details: %s | UA: %s\n",
        $timestamp,
        strtoupper($event_type),
        $username,
        $ip,
        $details,
        substr($user_agent, 0, 100)
    );
    
   
    @file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}


function detectAttackPatterns($input) {
    
    $sql_patterns = [
        '/(\bSELECT\b|\bUNION\b|\bDROP\b|\bDELETE\b|\bINSERT\b|\bUPDATE\b)/i',
        '/(\bOR\b\s+\d+\s*=\s*\d+)/i',
        '/(\'|\"|--|\#|\/\*|\*\/)/i'
    ];
    
   
    $xss_patterns = [
        '/<script[^>]*>.*?<\/script>/is',
        '/on\w+\s*=\s*["\']?[^"\']*["\']?/i',
        '/javascript:/i',
        '/<iframe/i'
    ];
    
   
    $path_patterns = [
        '/\.\.\//',
        '/\.\.\\\\/',
        '/\0/'
    ];
    
    
    foreach ($sql_patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            logSecurityEvent('sql_injection_attempt', "Pattern matched: $pattern");
            return 'SQL_INJECTION';
        }
    }
    
    foreach ($xss_patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            logSecurityEvent('xss_attempt', "Pattern matched: $pattern");
            return 'XSS';
        }
    }
    
    foreach ($path_patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            logSecurityEvent('path_traversal_attempt', "Pattern matched: $pattern");
            return 'PATH_TRAVERSAL';
        }
    }
    
    return false;
}


function validateRequestOrigin() {
    $allowed_origins = [
        $_SERVER['HTTP_HOST'] ?? '',
        'localhost',
        '127.0.0.1'
    ];
    
    $origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
    
    if (!empty($origin)) {
        $origin_host = parse_url($origin, PHP_URL_HOST);
        if (!in_array($origin_host, $allowed_origins)) {
            logSecurityEvent('invalid_origin', "Origin: $origin");
            return false;
        }
    }
    
    return true;
}


function checkRateLimit($action = 'general', $max_requests = 60, $time_window = 60) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $key = "rate_limit_{$action}_" . hash('sha256', $ip);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'count' => 1,
            'start_time' => time()
        ];
        return true;
    }
    
    $elapsed = time() - $_SESSION[$key]['start_time'];
    
  
    if ($elapsed > $time_window) {
        $_SESSION[$key] = [
            'count' => 1,
            'start_time' => time()
        ];
        return true;
    }
    
   
    $_SESSION[$key]['count']++;
    
    
    if ($_SESSION[$key]['count'] > $max_requests) {
        logSecurityEvent('rate_limit_exceeded', "Action: $action, IP: $ip, Requests: {$_SESSION[$key]['count']}");
        return false;
    }
    
    return true;
}


function sanitizeFilename($filename) {
   
    $filename = basename($filename);
    
   
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    
   
    if (strlen($filename) > 200) {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $filename = substr($filename, 0, 195) . '.' . $extension;
    }
    
    return $filename;
}
?>
