<?php
// Disable error display
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start output buffering
ob_start();

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    exit(0);
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

try {
    include 'db.php';
    session_start();

    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }

    if (!isset($data['email']) || !isset($data['password'])) {
        throw new Exception('Email dhe fjalëkalimi janë të detyrueshëm');
    }

    $email = trim($data['email']);
    $password = $data['password'];

    // Get user with all fields
    $stmt = $conn->prepare("SELECT id, password, name, email, phone, address, city, country, unique_id FROM users WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Database error');
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_logged'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_unique_id'] = $user['unique_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            
            ob_end_clean();

            echo json_encode([
                'success' => true,
                'message' => 'Kyçja u krye me sukses!',
                'user' => [
                    'id' => $user['id'],
                    'unique_id' => $user['unique_id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'phone' => $user['phone'],
                    'address' => $user['address'],
                    'city' => $user['city'],
                    'country' => $user['country']
                ]
            ]);
            exit();
        }
    }

    throw new Exception('Email ose fjalëkalimi është i gabuar');

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->close();
    }
    
    ob_end_clean();
    http_response_code(401);
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

exit();
?>