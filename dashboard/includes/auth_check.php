<?php
// File: c:\xampp\htdocs\hotel-ks\dashboard\includes\auth_check.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: login.php?timeout=1');
    exit();
}

// Set admin info for use in pages
$admin_id = $_SESSION['admin_id'] ?? 0;
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$admin_email = $_SESSION['admin_email'] ?? '';
$admin_username = $_SESSION['admin_username'] ?? '';

// Check for session timeout (30 minutes)
$timeout_duration = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Function to check if user has permission (for future role-based access)
function hasPermission($permission) {
    // For now, all admins have all permissions
    // In future, you can implement role-based permissions
    return true;
}

// Function to log admin activity (optional)
function logActivity($action, $details = '') {
    global $conn, $admin_id;
    
    if (isset($conn) && $admin_id > 0) {
        $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $stmt->bind_param("isss", $admin_id, $action, $details, $ip);
        $stmt->execute();
    }
}

// CSRF Token Generation and Validation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function generateCSRFToken() {
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>