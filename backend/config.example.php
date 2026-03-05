<?php
// Configuration template - COPY this file to config.php and fill in your values
// IMPORTANT: Never commit config.php with real credentials!

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');
define('DB_NAME', 'your_database_name');

// CORS Configuration  
define('ALLOWED_ORIGIN', 'https://your-domain.com');

// Security Settings
define('ENABLE_STRICT_MYSQLI', true);

// Optional: Setup token for one-time scripts (generate a random string)
// define('SETUP_TOKEN', 'your-random-token-here');
?>
