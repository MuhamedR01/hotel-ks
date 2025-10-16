<?php
require_once '../backend/db.php';

echo "<h2>Database Check</h2>";

// Check connection
echo "<p>✓ Database connected</p>";

// Check admins table
$result = $conn->query("SELECT * FROM admins WHERE username = 'admin'");

if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    echo "<h3>Admin User Details:</h3>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    foreach ($admin as $key => $value) {
        if ($key === 'password') {
            echo "<tr><td>$key</td><td>" . substr($value, 0, 30) . "... (length: " . strlen($value) . ")</td></tr>";
        } else {
            echo "<tr><td>$key</td><td>$value</td></tr>";
        }
    }
    echo "</table>";
    
    // Test password
    echo "<h3>Password Test:</h3>";
    $test_password = 'admin123';
    $verify = password_verify($test_password, $admin['password']);
    echo "<p>Testing password 'admin123': " . ($verify ? "<span style='color: green;'>✓ SUCCESS</span>" : "<span style='color: red;'>✗ FAILED</span>") . "</p>";
    
    if (!$verify) {
        echo "<p style='color: red;'>The password in the database is not properly hashed or doesn't match 'admin123'.</p>";
        echo "<p><a href='reset_admin.php'>Click here to reset the admin password</a></p>";
    }
} else {
    echo "<p style='color: red;'>No admin user found in database!</p>";
    echo "<p><a href='reset_admin.php'>Click here to create admin user</a></p>";
}
?>