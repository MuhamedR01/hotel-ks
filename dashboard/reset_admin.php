<?php
require_once __DIR__ . '/../backend/init.php';

$conn = db_connect();

echo "<h2>Admin Password Reset</h2>";

// Protect this script: allow only when an admin is logged OR when a one-time SETUP_TOKEN is provided via GET
if (session_status() === PHP_SESSION_NONE) session_start();
$allowed = false;
if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
    $allowed = true;
} elseif (defined('SETUP_TOKEN') && !empty(SETUP_TOKEN) && isset($_GET['token']) && hash_equals(SETUP_TOKEN, $_GET['token'])) {
    $allowed = true;
}

if (!$allowed) {
    echo "<p style='color: red;'>This script is protected. Log in as admin or provide a valid setup token.</p>";
    exit;
}

// Check if admins table exists
$table_check = $conn->query("SHOW TABLES LIKE 'admins'");

if ($table_check->num_rows == 0) {
    echo "<p style='color: red;'>Admins table does not exist. Creating it now...</p>";
    
    $create_table = "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL DEFAULT 'Administrator',
        email VARCHAR(100) UNIQUE NOT NULL,
        role VARCHAR(20) DEFAULT 'admin',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL
    )";
    
    if ($conn->query($create_table)) {
        echo "<p style='color: green;'>Table created successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error creating table: " . $conn->error . "</p>";
        exit;
    }
}

// Delete existing admin if exists
$conn->query("DELETE FROM admins WHERE username = 'admin'");

// Create new admin with hashed password
$username = 'admin';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$name = 'Administrator';
$email = 'admin@hotelks.com';
$role = 'admin';

$stmt = $conn->prepare("INSERT INTO admins (username, password, name, email, role) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $username, $hashed_password, $name, $email, $role);

if ($stmt->execute()) {
    echo "<p style='color: green;'>✓ Admin user created successfully!</p>";
    echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<strong>Login Credentials:</strong><br>";
    echo "Username: <code style='background: #fff; padding: 2px 8px; border-radius: 3px;'>admin</code><br>";
    echo "Password: <code style='background: #fff; padding: 2px 8px; border-radius: 3px;'>admin123</code>";
    echo "</div>";
    
    // Verify the password
    $verify_stmt = $conn->prepare("SELECT password FROM admins WHERE username = ?");
    $verify_stmt->bind_param("s", $username);
    $verify_stmt->execute();
    $result = $verify_stmt->get_result();
    $admin = $result->fetch_assoc();
    
    if (password_verify($password, $admin['password'])) {
        echo "<p style='color: green;'>✓ Password verification test: PASSED</p>";
    } else {
        echo "<p style='color: red;'>✗ Password verification test: FAILED</p>";
    }
    
    echo "<p style='margin-top: 20px;'><a href='login.php' style='background: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
} else {
    echo "<p style='color: red;'>Error creating admin: " . $stmt->error . "</p>";
}

$stmt->close();
$conn->close();
?>