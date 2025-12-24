
<?php
require_once 'init.php';
// Use db_connect() to get a connection
// $conn will be created later where needed

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception('Invalid JSON data');
    }

    // Validate required fields (email is optional)
    $required = ['customer_name', 'customer_phone', 'customer_address', 'customer_city', 'customer_country', 'items'];
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
    // Compute subtotal from items server-side to prevent tampering
    $subtotal = 0;
    foreach ($data['items'] as $item) {
        $subtotal += floatval($item['price']) * intval($item['quantity']);
    }

    // Determine shipping by country
    // Normalize country text to be more forgiving (mirrors frontend normalizeCountry)
    $rawCountry = $data['customer_country'] ?? '';
    function normalize_country_for_shipping($str) {
        $s = trim((string)$str);
        if ($s === '') return '';
        // Lowercase using multibyte
        $s = mb_strtolower($s, 'UTF-8');
        // Try to transliterate diacritics to ASCII
        $trans = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        if ($trans !== false && $trans !== null) {
            $s = $trans;
        }
        // Remove any non alphanumeric characters, keep spaces
        $s = preg_replace('/[^a-z0-9\s]/', ' ', $s);
        // Collapse spaces
        $s = preg_replace('/\s+/', ' ', $s);
        return trim($s);
    }

    $normalizedCountry = normalize_country_for_shipping($rawCountry);

    if (strpos($normalizedCountry, 'kosov') !== false) {
        $shipping_cost = 2.0; // always charge shipping for Kosovo
    } elseif (strpos($normalizedCountry, 'alban') !== false || strpos($normalizedCountry, 'north macedonia') !== false || strpos($normalizedCountry, 'maced') !== false || strpos($normalizedCountry, 'maqed') !== false) {
        $shipping_cost = 5.0;
    } else {
        $shipping_cost = 5.0;
    }

    // No tax applies
    $tax = 0.0;
    $total_amount = $subtotal + $shipping_cost;

    // Start transaction
    $conn = db_connect();
    $conn->begin_transaction();

    // Insert order - using correct column names
    $stmt = $conn->prepare("INSERT INTO orders (
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
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, 'processing', NOW())");
    $payment_method = $data['payment_method'] ?? 'cash';
    $notes = $data['notes'] ?? '';

    $customer_email = $data['customer_email'] ?? '';

    $stmt->bind_param(
        "sssssssddddss",
        $order_number,
        $data['customer_name'],
        $customer_email,
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

    
        // Insert order items - include size if the column exists
        // Detect whether order_items table has a 'size' or 'selected_size' column
        $colCheck = $conn->query("SHOW COLUMNS FROM order_items LIKE 'size'");
        $has_size_col = $colCheck && $colCheck->num_rows > 0;
        $colCheck2 = $conn->query("SHOW COLUMNS FROM order_items LIKE 'selected_size'");
        $has_selected_size_col = $colCheck2 && $colCheck2->num_rows > 0;

        if ($has_size_col) {
            $insertSql = "INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, subtotal, size) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertSql);
        } elseif ($has_selected_size_col) {
            $insertSql = "INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, subtotal, selected_size) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertSql);
        } else {
            $insertSql = "INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertSql);
        }

        if (!$stmt) {
            throw new Exception('Failed to prepare order items statement: ' . $conn->error);
        }

        foreach ($data['items'] as $item) {
            if (empty($item['product_id']) || !isset($item['quantity']) || !isset($item['price'])) {
                throw new Exception('Invalid item data');
            }

            $product_name = $item['product_name'] ?? '';
            $product_price = floatval($item['price']);
            $quantity = intval($item['quantity']);
            $item_subtotal = $product_price * $quantity;

            if ($has_size_col) {
                $sizeVal = isset($item['selected_size']) ? $item['selected_size'] : null;
                $stmt->bind_param(
                    "iisdids",
                    $order_id,
                    $item['product_id'],
                    $product_name,
                    $product_price,
                    $quantity,
                    $item_subtotal,
                    $sizeVal
                );
            } elseif ($has_selected_size_col) {
                $sizeVal = isset($item['selected_size']) ? $item['selected_size'] : null;
                $stmt->bind_param(
                    "iisdids",
                    $order_id,
                    $item['product_id'],
                    $product_name,
                    $product_price,
                    $quantity,
                    $item_subtotal,
                    $sizeVal
                );
            } else {
                $stmt->bind_param(
                    "iisdid",
                    $order_id,
                    $item['product_id'],
                    $product_name,
                    $product_price,
                    $quantity,
                    $item_subtotal
                );
            }

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