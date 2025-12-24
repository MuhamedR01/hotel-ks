<?php
// db.php kept for backwards compatibility. Prefer using init.php and db_connect().
// This file will attempt to load config.php if present.
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
} else if (file_exists(__DIR__ . '/config.example.php')) {
    require_once __DIR__ . '/config.example.php';
}

// Default connection values (development fallback)
$host = defined('DB_HOST') ? DB_HOST : 'localhost';
$user = defined('DB_USER') ? DB_USER : 'root';
$pass = defined('DB_PASS') ? DB_PASS : '';
$db   = defined('DB_NAME') ? DB_NAME : 'ecommerce_db';

if (defined('ENABLE_STRICT_MYSQLI') && ENABLE_STRICT_MYSQLI) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
}

try {
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        error_log('Connection failed: ' . $conn->connect_error);
        throw new Exception('Database connection failed');
    }
    $conn->set_charset('utf8mb4');
} catch (Exception $e) {
    // Log and rethrow so calling code can handle gracefully
    error_log('DB connection error: ' . $e->getMessage());
    throw $e;
}
?>
