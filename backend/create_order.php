
<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

ob_start();

try {
    include 'db.php';
    session_start();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }

    // Validate required fields
    $requiredFields = ['customer_name', 'customer_email', 'customer_phone', 'customer_address', 'customer_city', 'customer_country', 'total_amount', 'items'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
            throw new Exception("Field '$field' is required");
        }
    }

    if (!is_array($data['items']) || count($data['items']) === 0) {
        throw new Exception('Order must contain at least one item');
    }

    // Start transaction
    $conn->begin_transaction();

    // Get user ID if logged in
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    // Generate unique order number
    $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

    // Prepare order insert
    $stmt = $conn->prepare("
        INSERT INTO orders (
            user_id, 
            order_number, 
            customer_name, 
            customer_email, 
            customer_phone, 
            customer_address, 
            customer_city, 
            customer_country, 
            total_amount, 
            payment_method, 
            payment_status, 
            notes, 
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    $paymentMethod = isset($data['payment_method']) ? $data['payment_method'] : 'cash';
    $paymentStatus = 'pending';
    $notes = isset($data['notes']) ? trim($data['notes']) : '';
    $status = 'pending';

    $stmt->bind_param(
        "isssssssdssss",
        $userId,
        $orderNumber,
        $data['customer_name'],
        $data['customer_email'],
        $data['customer_phone'],
        $data['customer_address'],
        $data['customer_city'],
        $data['customer_country'],
        $data['total_amount'],
        $paymentMethod,
        $paymentStatus,
        $notes,
        $status
    );

    if (!$stmt->execute()) {
        throw new Exception('Failed to create order: ' . $stmt->error);
    }

    $orderId = $stmt->insert_id;
    $stmt->close();

    // Insert order items - adjusted to match actual table structure
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception('Database prepare error for order items: ' . $conn->error);
    }

    foreach ($data['items'] as $item) {
        if (!isset($item['product_id']) || !isset($item['quantity'])) {
            throw new Exception('Invalid item data');
        }

        $stmt->bind_param(
            "iii",
            $orderId,
            $item['product_id'],
            $item['quantity']
        );

        if (!$stmt->execute()) {
            throw new Exception('Failed to add order item: ' . $stmt->error);
        }
    }

    $stmt->close();

    // Commit transaction
    $conn->commit();
    $conn->close();

    ob_end_clean();

    echo json_encode([
        'success' => true,
        'message' => 'Order created successfully',
        'order_number' => $orderNumber,
        'order_id' => $orderId
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
        $conn->close();
    }
    
    ob_end_clean();
    http_response_code(400);
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>