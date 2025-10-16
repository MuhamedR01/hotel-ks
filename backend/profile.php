<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

ob_start();

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    exit(0);
}

include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Not authenticated"]);
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare("SELECT id, email, name, phone, address, city, country FROM users WHERE id = ?");
    if (!$stmt) {
        ob_end_clean();
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Database error: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $stmt->close();
        $conn->close();
        ob_end_clean();
        
        echo json_encode([
            "success" => true,
            "user" => [
                "id" => $user['id'],
                "email" => $user['email'],
                "name" => $user['name'] ?? '',
                "phone" => $user['phone'] ?? '',
                "address" => $user['address'] ?? '',
                "city" => $user['city'] ?? '',
                "country" => $user['country'] ?? ''
            ]
        ]);
    } else {
        $stmt->close();
        $conn->close();
        ob_end_clean();
        
        http_response_code(404);
        echo json_encode(["success" => false, "error" => "User not found"]);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!$data) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Invalid data"]);
        exit();
    }

    $name = isset($data['name']) ? trim($data['name']) : '';
    $phone = isset($data['phone']) ? trim($data['phone']) : '';
    $address = isset($data['address']) ? trim($data['address']) : '';
    $city = isset($data['city']) ? trim($data['city']) : '';
    $country = isset($data['country']) ? trim($data['country']) : '';

    $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ?, city = ?, country = ? WHERE id = ?");
    if (!$stmt) {
        ob_end_clean();
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Database error"]);
        exit();
    }

    $stmt->bind_param("sssssi", $name, $phone, $address, $city, $country, $userId);

    if ($stmt->execute()) {
        $stmt->close();
        
        // Get updated user data
        $stmt = $conn->prepare("SELECT id, email, name, phone, address, city, country FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        $stmt->close();
        $conn->close();
        ob_end_clean();

        echo json_encode([
            "success" => true,
            "message" => "Profile updated successfully",
            "user" => [
                "id" => $user['id'],
                "email" => $user['email'],
                "name" => $user['name'] ?? '',
                "phone" => $user['phone'] ?? '',
                "address" => $user['address'] ?? '',
                "city" => $user['city'] ?? '',
                "country" => $user['country'] ?? ''
            ]
        ]);
    } else {
        $stmt->close();
        $conn->close();
        ob_end_clean();
        
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Failed to update profile"]);
    }
    exit();
}

ob_end_clean();
http_response_code(405);
echo json_encode(["success" => false, "error" => "Method not allowed"]);
exit();
?>