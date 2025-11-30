<?php
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db.php';

try {
    // Get optional query parameters
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : null;
    $exclude = isset($_GET['exclude']) ? intval($_GET['exclude']) : null;
    
    // Check if image_type column exists
    $column_check = $conn->query("SHOW COLUMNS FROM products LIKE 'image_type'");
    $has_image_type = $column_check && $column_check->num_rows > 0;
    
    // Check if image_mime_type column exists
    $mime_check = $conn->query("SHOW COLUMNS FROM products LIKE 'image_mime_type'");
    $has_mime_type = $mime_check && $mime_check->num_rows > 0;
    
    // Build the base query
    $query = "SELECT id, name, description, price, image, stock, created_at";
    
    if ($has_image_type) {
        $query .= ", image_type";
    } else if ($has_mime_type) {
        $query .= ", image_mime_type as image_type";
    }
    
    $query .= " FROM products WHERE 1=1";
    
    // Add exclude filter if provided
    if ($exclude) {
        $query .= " AND id != ?";
    }
    
    $query .= " ORDER BY created_at DESC";
    
    // Add limit if provided
    if ($limit) {
        $query .= " LIMIT ?";
    }
    
    // Prepare and execute the statement
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    // Bind parameters based on what filters are active
    $params = [];
    $types = "";
    
    if ($exclude) {
        $params[] = $exclude;
        $types .= "i";
    }
    
    if ($limit) {
        $params[] = $limit;
        $types .= "i";
    }
    
    // Bind parameters if any exist
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception("Failed to get result: " . $stmt->error);
    }
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        // Convert BLOB image to base64 if it exists
        if (!empty($row['image'])) {
            // Check if image is already a path (string starting with 'uploads/')
            if (is_string($row['image']) && strpos($row['image'], 'uploads/') === 0) {
                // It's a file path, construct full URL
                $row['image'] = 'http://localhost/hotel-ks/' . $row['image'];
            } else {
                // It's BLOB data, convert to base64
                $imageType = isset($row['image_type']) ? $row['image_type'] : 'image/jpeg';
                $base64 = base64_encode($row['image']);
                $row['image'] = "data:{$imageType};base64,{$base64}";
            }
        } else {
            // Provide placeholder image
            $row['image'] = 'https://via.placeholder.com/400x300?text=No+Image';
        }
        
        // Remove image_type from response as it's no longer needed
        if (isset($row['image_type'])) {
            unset($row['image_type']);
        }
        
        // Ensure numeric fields are properly typed
        $row['id'] = (int)$row['id'];
        $row['price'] = (float)$row['price'];
        $row['stock'] = isset($row['stock']) ? (int)$row['stock'] : 0;
        
        $products[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'products' => $products
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'A server error occurred.',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>