<?php
require_once __DIR__ . '/init.php';

// Use db helper and start session
$conn = db_connect();
session_start();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['items']) || !isset($data['total'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid order data']);
    exit;
}

// Get order details
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$total = floatval($data['total']);
$customer_name = $data['customer_name'] ?? 'Guest';
$customer_email = $data['customer_email'] ?? '';
$customer_phone = $data['customer_phone'] ?? '';
$shipping_address = $data['shipping_address'] ?? '';
$shipping_city = $data['shipping_city'] ?? '';
$shipping_country = $data['shipping_country'] ?? '';
$shipping_postal_code = $data['shipping_postal_code'] ?? '';
$payment_method = $data['payment_method'] ?? 'cash';
$notes = $data['notes'] ?? '';

// Generate unique order number
$order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

// Insert order with status 'processing'
$stmt = $conn->prepare("INSERT INTO orders (
    order_number, 
    user_id, 
    customer_name, 
    customer_email, 
    customer_phone, 
    shipping_address, 
    shipping_city, 
    shipping_country, 
    shipping_postal_code, 
    total_amount, 
    status, 
    payment_method, 
    payment_status, 
    notes
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'processing', ?, 'pending', ?)");

$stmt->bind_param(
    "sissssssdsss",
    $order_number,
    $user_id,
    $customer_name,
    $customer_email,
    $customer_phone,
    $shipping_address,
    $shipping_city,
    $shipping_country,
    $shipping_postal_code,
    $total,
    $payment_method,
    $notes
);

if ($stmt->execute()) {
    $order_id = $stmt->insert_id;
    
    // Insert order items
    $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    
    $update_stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");

    foreach ($data['items'] as $item) {
        $product_id = intval($item['id']);
        $quantity = intval($item['quantity']);
        $price = floatval($item['price']);
        
        $item_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
        $item_stmt->execute();

        // Update product stock safely with prepared statement
        if ($update_stmt) {
            $update_stmt->bind_param("iii", $quantity, $product_id, $quantity);
            $update_stmt->execute();
        }
    }
    
    $item_stmt->close();
    if ($update_stmt) $update_stmt->close();
    
    echo json_encode([
        'success' => true, 
        'order_id' => $order_id,
        'order_number' => $order_number,
        'message' => 'Order created successfully with processing status'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Could not create order: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>