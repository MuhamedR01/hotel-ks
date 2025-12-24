<?php
// Local development database config for XAMPP/localhost.
// Copy/rename this file or edit values to match your local MySQL setup.
// This file is optional and will only be loaded when running on localhost.

// Example values for XAMPP on Windows
if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'hotel_ks');

// Allow multiple local origins for dev tooling (optional)
if (!defined('ALLOWED_ORIGIN')) define('ALLOWED_ORIGIN', 'http://localhost:5173');

?>
