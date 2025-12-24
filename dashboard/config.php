<?php
// Dashboard configuration
define('DASHBOARD_TITLE', 'Hotel KS - Admin Dashboard');
define('ITEMS_PER_PAGE', 10);
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes

// Base paths - update these when deploying to production
define('BACKEND_URL', 'https://hotel-ks.com/backend');
define('DASHBOARD_URL', 'https://dashboard.hotel-ks.com');

// Enable temporary debug on dashboard to show PHP errors (set to false in production)
if (!defined('DASHBOARD_DEBUG')) define('DASHBOARD_DEBUG', true);

// Dashboard cookie domain for subdomain deployment (leading dot allows sharing across subdomains)
define('DASHBOARD_COOKIE_DOMAIN', '.hotel-ks.com');

// Session configuration - must be set BEFORE session_start()
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 1); // Use secure cookies in production (HTTPS)
}

// Timezone
date_default_timezone_set('Europe/Tirane');

// Helper function to format date
function format_date($date) {
    return date('d/m/Y H:i', strtotime($date));
}

// Helper function to get order status badge
function get_status_badge($status) {
    $badges = [
        'pending' => '<span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Në pritje</span>',
        'processing' => '<span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">Në përpunim</span>',
        'completed' => '<span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Përfunduar</span>',
        'cancelled' => '<span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Anuluar</span>',
    ];
    return $badges[$status] ?? $status;
}

// Helper function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>