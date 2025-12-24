<?php
// Production config template for Hostinger
// COPY THIS FILE to backend/config.php on the server and fill the real values.
// Do NOT commit real credentials to version control.

// MySQL / MariaDB
define('DB_HOST', 'localhost');               // usually 'localhost' on Hostinger
define('DB_USER', 'your_prod_db_user');       // replace with production DB user
define('DB_PASS', 'your_prod_db_password');   // replace with production DB password
define('DB_NAME', 'your_prod_db_name');       // replace with production DB name

// CORS origin for frontend (used by backend/init.php)
// Set to the exact origin where your SPA will be served (including https://)
define('ALLOWED_ORIGIN', 'https://hotel-ks.com');

// Enable strict mysqli error reporting (recommended in production)
define('ENABLE_STRICT_MYSQLI', true);

// Optional: one-time setup protection token
// define('SETUP_TOKEN', 'replace-with-random-token');

?>