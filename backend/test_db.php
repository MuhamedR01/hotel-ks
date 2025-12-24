<?php
/**
 * Simple DB connection tester. Open in browser at:
 * http://localhost/hotel-ks/backend/test_db.php
 * It will include backend/init.php which loads config.local.php on localhost.
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/init.php';

echo "<pre>";
try {
    // Show which config was loaded (from init.php logs) and effective constants
    $loaded = [];
    $loaded['DB_HOST'] = defined('DB_HOST') ? DB_HOST : '(not defined)';
    $loaded['DB_USER'] = defined('DB_USER') ? DB_USER : '(not defined)';
    $loaded['DB_NAME'] = defined('DB_NAME') ? DB_NAME : '(not defined)';
    echo "Effective DB config (password hidden):\n";
    foreach ($loaded as $k => $v) {
        echo sprintf("%s = %s\n", $k, $v);
    }

    // Attempt to connect
    $conn = db_connect();
    echo "\nConnection successful! MySQL server version: " . $conn->server_info . "\n";
    $conn->close();
} catch (Exception $e) {
    echo "\nConnection failed: " . $e->getMessage() . "\n";
    // If mysqli_sql_exception, show underlying message
    if ($e instanceof mysqli_sql_exception || stripos($e->getMessage(), 'Access denied') !== false) {
        echo "\nPossible causes:\n - username/password mismatch\n - user not allowed from this host (check user@'localhost')\n - DB name incorrect\n";
    }
}
echo "</pre>";

?>
