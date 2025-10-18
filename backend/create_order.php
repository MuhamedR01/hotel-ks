
<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db.php';

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception('Invalid JSON data');
    }

    // Validate required fields
    $required = ['customer_name', 'customer_email', 'customer_phone', 'customer_address', 'customer_city', 'customer_country', 'items'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    if (empty($data['items']) || !is_array($data['items'])) {
        throw new Exception('Order must contain at least one item');
    }

    // Generate unique order number
    $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

    // Get values with defaults
    $subtotal = isset($data['subtotal']) ? floatval($data['subtotal']) : 0;
    $shipping_cost = isset($data['shipping_cost']) ? floatval($data['shipping_cost']) : 0;
    $tax = isset($data['tax']) ? floatval($data['tax']) : 0;
    $total_amount = isset($data['total_amount']) ? floatval($data['total_amount']) : 0;

    // Start transaction
    $conn->begin_transaction();

    // Insert order - using correct column names
    $stmt = $conn->prepare("
        INSERT INTO orders (
            order_number, 
            customer_name, 
            customer_email, 
            customer_phone,
            customer_address,
            customer_city,
            customer_country,
            subtotal,
            shipping_cost,
            tax,
            total_amount,
            payment_method,
            payment_status,
            notes,
            status,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, 'processing', NOW())
    ");

    $payment_method = $data['payment_method'] ?? 'cash';
    $notes = $data['notes'] ?? '';

    $stmt->bind_param(
        "sssssssdddsss",
        $order_number,
        $data['customer_name'],
        $data['customer_email'],
        $data['customer_phone'],
        $data['customer_address'],
        $data['customer_city'],
        $data['customer_country'],
        $subtotal,
        $shipping_cost,
        $tax,
        $total_amount,
        $payment_method,
        $notes
    );

    if (!$stmt->execute()) {
        throw new Exception('Failed to create order: ' . $stmt->error);
    }

    $order_id = $conn->insert_id;
    $stmt->close();

    // Insert order items - using correct column names
    $stmt = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, subtotal) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($data['items'] as $item) {
        if (empty($item['product_id']) || empty($item['quantity']) || empty($item['price'])) {
            throw new Exception('Invalid item data');
        }

        $product_name = $item['product_name'] ?? '';
        $product_price = floatval($item['price']);
        $quantity = intval($item['quantity']);
        $item_subtotal = $product_price * $quantity;
        
        $stmt->bind_param(
            "iisdid",
            $order_id,
            $item['product_id'],
            $product_name,
            $product_price,
            $quantity,
            $item_subtotal
        );

        if (!$stmt->execute()) {
            throw new Exception('Failed to add order item: ' . $stmt->error);
        }
    }

    $stmt->close();
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order created successfully',
        'order_id' => $order_id,
        'order_number' => $order_number
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>