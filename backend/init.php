<?php
// Initialization: load config, enable strict mysqli errors if configured
// and set secure CORS headers for API endpoints.

// Load config: allow a local override when running on localhost (XAMPP/dev).
$host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
$remote = $_SERVER['REMOTE_ADDR'] ?? '';
$serverAddr = $_SERVER['SERVER_ADDR'] ?? '';

// If running locally and a `config.local.php` exists, prefer it and do NOT load `config.php` to
// avoid duplicate constant definitions. Otherwise load the main config or the example.
$localConfig = __DIR__ . '/config.local.php';
$mainConfig = __DIR__ . '/config.php';
$exampleConfig = __DIR__ . '/config.example.php';

$isLocalRequest = stripos($host, 'localhost') !== false || in_array($remote, ['127.0.0.1', '::1']) || in_array($serverAddr, ['127.0.0.1', '::1']);

if ($isLocalRequest && file_exists($localConfig)) {
    require_once $localConfig;
    // Debug: log that local config was loaded and the effective DB constants (no password)
    error_log('backend/init.php: loaded config.local.php');
    error_log('backend/init.php: DB_HOST=' . (defined('DB_HOST') ? DB_HOST : ''));
    error_log('backend/init.php: DB_USER=' . (defined('DB_USER') ? DB_USER : ''));
    error_log('backend/init.php: DB_NAME=' . (defined('DB_NAME') ? DB_NAME : ''));
} elseif (file_exists($mainConfig)) {
    require_once $mainConfig;
    error_log('backend/init.php: loaded config.php');
    error_log('backend/init.php: DB_HOST=' . (defined('DB_HOST') ? DB_HOST : ''));
    error_log('backend/init.php: DB_USER=' . (defined('DB_USER') ? DB_USER : ''));
    error_log('backend/init.php: DB_NAME=' . (defined('DB_NAME') ? DB_NAME : ''));
} elseif (file_exists($exampleConfig)) {
    require_once $exampleConfig;
    error_log('backend/init.php: loaded config.example.php');
    error_log('backend/init.php: DB_HOST=' . (defined('DB_HOST') ? DB_HOST : ''));
    error_log('backend/init.php: DB_USER=' . (defined('DB_USER') ? DB_USER : ''));
    error_log('backend/init.php: DB_NAME=' . (defined('DB_NAME') ? DB_NAME : ''));
}

// Enable strict mysqli reporting for development/production if configured
if (defined('ENABLE_STRICT_MYSQLI') && ENABLE_STRICT_MYSQLI) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
}

// Secure CORS: allow only configured origin, allow credentials for cookies/sessions
if (defined('ALLOWED_ORIGIN') && !empty(ALLOWED_ORIGIN)) {
    header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGIN);
} else {
    // By default, disallow cross-origin; change ALLOWED_ORIGIN in config.php for dev/prod
    header('Access-Control-Allow-Origin: ');
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
// Note: don't force Content-Type here — some includes are HTML dashboard pages.

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection helper using config constants
function db_connect() {
    // DB constants: DB_HOST, DB_USER, DB_PASS, DB_NAME
    // Order of precedence: config.php constants -> environment variables -> sensible defaults
    $host = defined('DB_HOST') ? DB_HOST : (getenv('DB_HOST') ?: 'localhost');
    $user = defined('DB_USER') ? DB_USER : (getenv('DB_USER') ?: 'root');
    $pass = defined('DB_PASS') ? DB_PASS : (getenv('DB_PASS') ?: '');
    $db   = defined('DB_NAME') ? DB_NAME : (getenv('DB_NAME') ?: '');
    $port = defined('DB_PORT') ? DB_PORT : (getenv('DB_PORT') ?: null);

    // Some hosts provide host:port in DB_HOST (e.g. mysql.hostinger.com:3306)
    if (strpos($host, ':') !== false) {
        [$h, $p] = explode(':', $host, 2);
        if (is_numeric($p)) {
            $host = $h;
            $port = (int)$p;
        }
    }

    // Quick sanity check for placeholder config values to provide a clear error
    $placeholderPatterns = ['your_', 'changeme', 'replace_me', 'example'];
    // Ensure DB name is explicitly set to avoid accidental connections
    if (empty($db)) {
        throw new Exception(
            "Database not configured. Please set DB_NAME in backend/config.php (or the DB_NAME env var) to your database name."
        );
    }

    foreach ([$user, $db] as $val) {
        foreach ($placeholderPatterns as $p) {
            if (stripos($val, $p) !== false) {
                throw new Exception(
                    "Database not configured. Please create `backend/config.php` from `backend/config.example.php` and set valid DB_HOST/DB_USER/DB_PASS/DB_NAME values."
                );
            }
        }
    }

    // Attempt connection and catch mysqli exceptions to avoid uncaught fatals
    try {
        // Use port when provided
        if (!empty($port)) {
            $conn = new mysqli($host, $user, $pass, $db, (int)$port);
        } else {
            $conn = new mysqli($host, $user, $pass, $db);
        }
    } catch (mysqli_sql_exception $e) {
        error_log('DB Connection failed (mysqli_sql_exception): ' . $e->getMessage());
        throw new Exception('Database connection failed. Please verify backend/config.php or environment variables and database server.');
    }

    if ($conn->connect_error) {
        error_log('DB Connection failed: ' . $conn->connect_error);
        throw new Exception('Database connection failed. Please verify backend/config.php and database server.');
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}

?>
