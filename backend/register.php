<?php
require_once 'init.php';

// Disable error display, log them instead (production)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start output buffering
ob_start();

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $conn = db_connect();

    // Get and parse input
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
    $name = isset($data['name']) ? trim($data['name']) : '';
    $phone = isset($data['phone']) ? trim($data['phone']) : '';
    $address = isset($data['address']) ? trim($data['address']) : '';
    $city = isset($data['city']) ? trim($data['city']) : '';
    $country = isset($data['country']) ? trim($data['country']) : '';

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email nuk është i vlefshëm');
    }

    // Validate password
    if (strlen($password) < 6) {
        throw new Exception('Fjalëkalimi duhet të ketë të paktën 6 karaktere');
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Database prepare error');
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        throw new Exception('Ky email është tashmë i regjistruar');
    }
    $stmt->close();

    // Generate unique 6-digit ID
    do {
        $unique_id = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE unique_id = ?");
        $check_stmt->bind_param("s", $unique_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
    } while ($check_result->num_rows > 0);
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user with unique_id
    $stmt = $conn->prepare("INSERT INTO users (unique_id, name, email, password, phone, address, city, country, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssssss", $unique_id, $name, $email, $hashed_password, $phone, $address, $city, $country);
    
    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        
        // Fetch the complete user data including unique_id
        $user_stmt = $conn->prepare("SELECT id, unique_id, name, email, phone, address, city, country FROM users WHERE id = ?");
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user_data = $user_result->fetch_assoc();
        
        // Clear any output and send response
        ob_end_clean();
        
        echo json_encode([
            'success' => true,
            'message' => 'Regjistrimi u krye me sukses!',
            'user' => $user_data
        ]);
    } else {
        throw new Exception('Failed to create user');
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->close();
    }
    
    ob_end_clean();
    http_response_code(400);
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

exit();