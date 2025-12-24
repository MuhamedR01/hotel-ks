<?php
require_once 'init.php';

// Ensure we return JSON only and suppress direct HTML error output that breaks JSON parsing on the client.
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
ob_start();

try {
    // Connect to DB inside the try so exceptions are caught and returned as JSON
    $conn = db_connect();
    // Get optional query parameters
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : null;
    $exclude = isset($_GET['exclude']) ? intval($_GET['exclude']) : null;
    
    // Check if image_type column exists
    $column_check = $conn->query("SHOW COLUMNS FROM products LIKE 'image_type'");
    $has_image_type = $column_check && $column_check->num_rows > 0;
    
    // Check if image_mime_type column exists
    $mime_check = $conn->query("SHOW COLUMNS FROM products LIKE 'image_mime_type'");
    $has_mime_type = $mime_check && $mime_check->num_rows > 0;
    
    // Check for availability column (preferred) or fallback to stock if present
    $avail_check = $conn->query("SHOW COLUMNS FROM products LIKE 'available'");
    $has_available = $avail_check && $avail_check->num_rows > 0;
    $stock_check = $conn->query("SHOW COLUMNS FROM products LIKE 'stock'");
    $has_stock = $stock_check && $stock_check->num_rows > 0;
    // Check for has_sizes column
    $sizes_check = $conn->query("SHOW COLUMNS FROM products LIKE 'has_sizes'");
    $has_has_sizes = $sizes_check && $sizes_check->num_rows > 0;

    // Build the base query
    $query = "SELECT id, name, description, price, image, created_at";
    if ($has_available) {
        $query .= ", available";
    } else if ($has_stock) {
        // if available column doesn't exist, include stock (legacy)
        $query .= ", stock";
    }

    // Include has_sizes flag if present
    if ($has_has_sizes) {
        $query .= ", has_sizes";
    }
    
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
            // Provide inline SVG placeholder (offline-safe)
            $svg = '%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22300%22%3E%3Crect fill=%22%23ddd%22 width=%22400%22 height=%22300%22/%3E%3Ctext fill=%22%23999%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3ENo Image%3C/text%3E%3C/svg%3E';
            $row['image'] = 'data:image/svg+xml,' . $svg;
        }
        
        // Remove image_type from response as it's no longer needed
        if (isset($row['image_type'])) {
            unset($row['image_type']);
        }
        
        // Ensure numeric fields are properly typed
        $row['id'] = (int)$row['id'];
        $row['price'] = (float)$row['price'];
        // Normalize availability: prefer explicit `available` column; otherwise infer from stock when present; default to available
        if (isset($row['available'])) {
            $row['available'] = (int)$row['available'];
        } else if (isset($row['stock'])) {
            $row['available'] = ((int)$row['stock']) > 0 ? 1 : 0;
            unset($row['stock']);
        } else {
            $row['available'] = 1;
        }

        // Normalize has_sizes flag to boolean when present
        if (isset($row['has_sizes'])) {
            $row['has_sizes'] = (int)$row['has_sizes'] ? 1 : 0;
        }
        
        $products[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    // Return success response (clear any buffered output first)
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'products' => $products
    ]);
    
} catch (Exception $e) {
    // Clean any buffered output (warnings, notices) to ensure a valid JSON response
    if (ob_get_length() !== false) ob_end_clean();
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