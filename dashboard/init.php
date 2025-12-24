<?php
// Dashboard init for subdomain deployments.
// Place this file in the dashboard/ folder. It sets secure session cookie parameters
// and starts the session. You can override COOKIE_DOMAIN in dashboard/config.php

// Enable error reporting on local/dev hosts to help diagnose startup errors.
$hostForDebug = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
$serverAddr = $_SERVER['SERVER_ADDR'] ?? '';

// If the request originates from localhost (127.0.0.1 or ::1) or you're using a hosts file
// mapping (e.g., dashboard.hotel-ks.com -> 127.0.0.1), enable display errors for debugging.
if (in_array($remoteAddr, ['127.0.0.1', '::1']) || in_array($serverAddr, ['127.0.0.1', '::1'])) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    // Also allow explicit debug opt-in via DASHBOARD_DEBUG constant in config.php
    if (defined('DASHBOARD_DEBUG') && DASHBOARD_DEBUG) {
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL);
    } else {
        ini_set('display_errors', '0');
    }
}

// Load optional dashboard-specific config
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

// Determine cookie domain: prefer DASHBOARD_COOKIE_DOMAIN, otherwise derive base domain.
if (defined('DASHBOARD_COOKIE_DOMAIN') && !empty(DASHBOARD_COOKIE_DOMAIN)) {
    $cookieDomain = DASHBOARD_COOKIE_DOMAIN;
} else {
    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
    // If host is like "dashboard.hotel-ks.com" produce ".hotel-ks.com"
    $parts = explode('.', $host);
    if (count($parts) >= 3) {
        // drop the first subdomain (e.g., dashboard) and use the rest
        array_shift($parts);
        $cookieDomain = '.' . implode('.', $parts);
    } elseif (count($parts) === 2) {
        // host is like example.com -> use .example.com
        $cookieDomain = '.' . implode('.', $parts);
    } else {
        // For localhost or single-label hosts, use host (no leading dot)
        $cookieDomain = ($host === 'localhost' || $host === '127.0.0.1') ? $host : ('.' . $host);
    }
}

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443;

// enforce secure cookie flags in production
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
// Only force secure cookies if HTTPS detected; allows local testing over HTTP
ini_set('session.cookie_secure', $secure ? 1 : 0);

// Use samesite Lax to allow top-level navigation but restrict CSRF-prone requests.
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => $cookieDomain,
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Provide a helper to get $conn via backend/db_connect if backend/init.php exists
if (!function_exists('dashboard_db_connect')) {
    function dashboard_db_connect() {
        // prefer backend/init.php's db_connect
        if (file_exists(__DIR__ . '/../backend/init.php')) {
            require_once __DIR__ . '/../backend/init.php';
            return db_connect();
        }
        // fallback to old db.php
        require_once __DIR__ . '/../backend/db.php';
        global $conn;
        return $conn;
    }
}

?>
