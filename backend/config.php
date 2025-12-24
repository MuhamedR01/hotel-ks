<?php
// Production config for Hostinger. Edit these values on the server.
// IMPORTANT: Do NOT commit production credentials to source control.

// MySQL / MariaDB
// Update the password below with the production DB password on the server.
define('DB_HOST', 'localhost');
define('DB_USER', '');
define('DB_PASS', '');
define('DB_NAME', '');

// CORS: set to the production frontend origin (update if needed)
define('ALLOWED_ORIGIN', 'https://hotel-ks.com');

// Enable strict mysqli error reporting in production
define('ENABLE_STRICT_MYSQLI', true);

// Optional: set a setup token to protect one-time setup scripts (if used)
// define('SETUP_TOKEN', 'changeme');

?>
