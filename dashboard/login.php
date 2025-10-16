<?php
session_start();

// Set session timeout (30 minutes)
$timeout_duration = 1800;

// Check if session has timed out
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    session_start();
    header('Location: login.php?timeout=1');
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
    header('Location: index.php');
    exit();
}

require_once '../backend/db.php';

$err = '';
$success = '';

// Check for timeout message
if (isset($_GET['timeout']) && $_GET['timeout'] == '1') {
    $err = 'Sesioni juaj ka skaduar. Ju lutem kyçuni përsëri.';
}

// Check for logout message
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    $success = 'Jeni çkyçur me sukses.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Debug: Uncomment these lines temporarily to see what's happening
    // error_log("Login attempt - Username: " . $username);
    // error_log("Password length: " . strlen($password));

    if (empty($username) || empty($password)) {
        $err = 'Ju lutem plotësoni të gjitha fushat.';
    } else {
        // Check if admins table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'admins'");
        
        if ($table_check->num_rows == 0) {
            // Create admins table with all required columns
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
                // Insert default admin
                $default_password = password_hash('admin123', PASSWORD_DEFAULT);
                $insert_admin = "INSERT INTO admins (username, password, name, email, role) 
                               VALUES ('admin', ?, 'Administrator', 'admin@hotelks.com', 'admin')";
                $stmt = $conn->prepare($insert_admin);
                $stmt->bind_param("s", $default_password);
                $stmt->execute();
                
                // Debug
                error_log("Admin table created and default admin inserted");
            }
        } else {
            // Check if table has all required columns and add missing ones
            $columns_check = $conn->query("SHOW COLUMNS FROM admins");
            $existing_columns = [];
            while ($col = $columns_check->fetch_assoc()) {
                $existing_columns[] = $col['Field'];
            }

            // Add missing columns
            if (!in_array('name', $existing_columns)) {
                $conn->query("ALTER TABLE admins ADD COLUMN name VARCHAR(100) NOT NULL DEFAULT 'Administrator' AFTER password");
            }
            if (!in_array('email', $existing_columns)) {
                $conn->query("ALTER TABLE admins ADD COLUMN email VARCHAR(100) UNIQUE AFTER name");
                $conn->query("UPDATE admins SET email = CONCAT(username, '@hotelks.com') WHERE email IS NULL OR email = ''");
            }
            if (!in_array('role', $existing_columns)) {
                $conn->query("ALTER TABLE admins ADD COLUMN role VARCHAR(20) DEFAULT 'admin' AFTER email");
            }
            if (!in_array('last_login', $existing_columns)) {
                $conn->query("ALTER TABLE admins ADD COLUMN last_login TIMESTAMP NULL AFTER created_at");
            }

            // Check if default admin exists
            $check_admin = $conn->query("SELECT id FROM admins WHERE username = 'admin'");
            if ($check_admin->num_rows == 0) {
                $default_password = password_hash('admin123', PASSWORD_DEFAULT);
                $insert_admin = "INSERT INTO admins (username, password, name, email, role) 
                               VALUES ('admin', ?, 'Administrator', 'admin@hotelks.com', 'admin')";
                $stmt = $conn->prepare($insert_admin);
                $stmt->bind_param("s", $default_password);
                $stmt->execute();
                
                error_log("Default admin created");
            }
        }

        // Query admin from database
        $stmt = $conn->prepare("SELECT id, username, password, name, email, role FROM admins WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Debug
        error_log("Query executed. Rows found: " . $result->num_rows);

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            // Debug - Remove after testing
            error_log("Admin found: " . $admin['username']);
            error_log("Stored password hash: " . substr($admin['password'], 0, 20) . "...");
            error_log("Attempting to verify password");
            
            // Verify password
            if (password_verify($password, $admin['password'])) {
                // Password is correct, set session variables
                $_SESSION['admin_logged'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = $admin['role'] ?? 'admin';
                $_SESSION['last_activity'] = time();

                error_log("Login successful for: " . $admin['username']);

                // Update last login
                $update_stmt = $conn->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
                $update_stmt->bind_param("i", $admin['id']);
                $update_stmt->execute();

                // Redirect to dashboard
                header('Location: index.php');
                exit();
            } else {
                error_log("Password verification failed for user: " . $username);
                error_log("Input password length: " . strlen($password));
                $err = 'Emri i përdoruesit ose fjalëkalimi është i gabuar.';
            }
        } else {
            error_log("No admin found with username/email: " . $username);
            $err = 'Emri i përdoruesit ose fjalëkalimi është i gabuar.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Hotel KS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="bg-blue-600 text-white w-16 h-16 rounded-full flex items-center justify-center font-bold text-2xl mx-auto mb-4">
                    KS
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Admin Panel</h1>
                <p class="text-gray-600 mt-2">Kyçuni për të vazhduar</p>
            </div>

            <!-- Success Message -->
            <?php if ($success): ?>
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-start">
                    <i class="fas fa-check-circle mt-0.5 mr-2"></i>
                    <span class="text-sm"><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if ($err): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-lg flex items-start">
                    <i class="fas fa-exclamation-circle mt-0.5 mr-2"></i>
                    <span class="text-sm"><?= htmlspecialchars($err) ?></span>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="" class="space-y-6">
                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-1"></i> Emri i Përdoruesit ose Email
                    </label>
                    <input 
                        type="text" 
                        id="username"
                        name="username" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="admin"
                        autocomplete="username"
                    />
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-1"></i> Fjalëkalimi
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password"
                            name="password" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all pr-12"
                            placeholder="••••••••"
                            autocomplete="current-password"
                        />
                        <button 
                            type="button"
                            onclick="togglePassword()"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                        >
                            <i id="password-icon" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <span class="ml-2 text-sm text-gray-700">Më mbaj mend</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                >
                    <i class="fas fa-sign-in-alt mr-2"></i> Kyçu
                </button>
            </form>

            <!-- Default Credentials Info -->
            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>Kredencialet e paracaktuara:</strong><br>
                    <span class="ml-5">Përdoruesi: <code class="bg-blue-100 px-2 py-0.5 rounded">admin</code></span><br>
                    <span class="ml-5">Fjalëkalimi: <code class="bg-blue-100 px-2 py-0.5 rounded">admin123</code></span>
                </p>
            </div>

            <!-- Back to Site -->
            <div class="mt-6 text-center">
                <a href="../frontend/public/index.php" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i> Kthehu në faqen kryesore
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        }

        // Auto-hide success/error messages after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.bg-green-50, .bg-red-50');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>