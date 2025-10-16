<?php
// Disable error display, log them instead
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start output buffering
ob_start();

// Set headers first
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    exit(0);
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    include 'db.php';

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

    // Hash password
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (email, password, name, phone, address, city, country) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception('Database prepare error');
    }

    $stmt->bind_param("sssssss", $email, $hash, $name, $phone, $address, $city, $country);

    if (!$stmt->execute()) {
        throw new Exception('Failed to create user');
    }

    $userId = $stmt->insert_id;
    $stmt->close();
    $conn->close();

    // Clear any output and send response
    ob_end_clean();
    
    echo json_encode([
        'success' => true,
        'message' => 'Regjistrimi u krye me sukses',
        'user' => [
            'id' => $userId,
            'email' => $email,
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'country' => $country
        ]
    ]);

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