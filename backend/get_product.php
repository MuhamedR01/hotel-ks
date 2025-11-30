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
    if (!isset($_GET['id'])) {
        throw new Exception('Product ID is required');
    }
    
    $id = intval($_GET['id']);
    
    // First, check which columns exist
    $columns_result = $conn->query("SHOW COLUMNS FROM products");
    $existing_columns = [];
    while ($col = $columns_result->fetch_assoc()) {
        $existing_columns[] = $col['Field'];
    }
    
    // Build the query dynamically based on existing columns
    $select_fields = ['id', 'name', 'description', 'price', 'stock'];
    
    // Add optional fields if they exist
    $optional_fields = [
        'category', 'is_active', 'featured', 'rating', 'reviews', 
        'created_at', 'updated_at', 'has_sizes', 'sizes',
        'image', 'image_name', 'image_size', 'image_type',
        'image_2', 'image_2_name', 'image_2_size', 'image_2_type',
        'image_3', 'image_3_name', 'image_3_size', 'image_3_type',
        'image_4', 'image_4_name', 'image_4_size', 'image_4_type',
        'image_5', 'image_5_name', 'image_5_size', 'image_5_type'
    ];
    
    foreach ($optional_fields as $field) {
        if (in_array($field, $existing_columns)) {
            $select_fields[] = $field;
        }
    }
    
    $query = "SELECT " . implode(', ', $select_fields) . " FROM products WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception("Failed to get result: " . $stmt->error);
    }
    
    if ($result->num_rows === 0) {
        throw new Exception('Product not found');
    }
    
    $product = $result->fetch_assoc();
    
    // Convert all BLOB images to base64
    $images = [];
    for ($i = 1; $i <= 5; $i++) {
        $imageField = $i === 1 ? 'image' : "image_$i";
        $imageTypeField = $i === 1 ? 'image_type' : "image_{$i}_type";
        
        if (isset($product[$imageField]) && !empty($product[$imageField])) {
            $imageType = isset($product[$imageTypeField]) ? $product[$imageTypeField] : 'image/jpeg';
            $base64 = base64_encode($product[$imageField]);
            $images[] = "data:{$imageType};base64,{$base64}";
        }
    }
    
    // If no images found, use placeholder
    if (empty($images)) {
        $images[] = 'https://via.placeholder.com/800x600?text=No+Image';
    }
    
    // Parse sizes JSON if product has sizes
    $sizes = [];
    if (isset($product['has_sizes']) && $product['has_sizes'] && !empty($product['sizes'])) {
        $sizesData = json_decode($product['sizes'], true);
        if (is_array($sizesData)) {
            $sizes = $sizesData;
        }
    }
    
    // Build clean product response
    $productData = [
        'id' => (int)$product['id'],
        'name' => $product['name'],
        'description' => $product['description'],
        'price' => (float)$product['price'],
        'stock' => (int)$product['stock'],
        'category' => isset($product['category']) ? $product['category'] : '',
        'is_active' => isset($product['is_active']) ? (bool)$product['is_active'] : true,
        'featured' => isset($product['featured']) ? (bool)$product['featured'] : false,
        'rating' => isset($product['rating']) && $product['rating'] ? (float)$product['rating'] : 4.5,
        'reviews' => isset($product['reviews']) ? (int)$product['reviews'] : 0,
        'created_at' => isset($product['created_at']) ? $product['created_at'] : null,
        'updated_at' => isset($product['updated_at']) ? $product['updated_at'] : null,
        'has_sizes' => isset($product['has_sizes']) ? (bool)$product['has_sizes'] : false,
        'sizes' => $sizes,
        'images' => $images,
        'image' => $images[0], // First image for backward compatibility
        'features' => [
            'Cilësi e lartë',
            'Garanci 1 vit',
            'Transport falas për porosi mbi 50€'
        ],
        'specifications' => [
            'Material' => 'Premium',
            'Origjina' => 'Kosovë',
            'Garancia' => '1 vit'
        ]
    ];
    
    // Get related products (same category if exists, excluding current product)
    $related_select = ['id', 'name', 'price'];
    if (in_array('category', $existing_columns)) {
        $related_select[] = 'category';
    }
    if (in_array('image', $existing_columns)) {
        $related_select[] = 'image';
    }
    if (in_array('image_type', $existing_columns)) {
        $related_select[] = 'image_type';
    }
    if (in_array('image_2', $existing_columns)) {
        $related_select[] = 'image_2';
    }
    if (in_array('image_2_type', $existing_columns)) {
        $related_select[] = 'image_2_type';
    }
    
    $related_query = "SELECT " . implode(', ', $related_select) . " FROM products WHERE id != ?";
    
    // Add is_active filter if column exists
    if (in_array('is_active', $existing_columns)) {
        $related_query .= " AND is_active = 1";
    }
    
    // Add category filter if product has category and column exists
    $has_category = isset($product['category']) && !empty($product['category']) && in_array('category', $existing_columns);
    if ($has_category) {
        $related_query .= " AND category = ?";
    }
    
    $related_query .= " ORDER BY RAND() LIMIT 4";
    
    $related_stmt = $conn->prepare($related_query);
    
    if ($related_stmt) {
        if ($has_category) {
            $related_stmt->bind_param("is", $id, $product['category']);
        } else {
            $related_stmt->bind_param("i", $id);
        }
        
        $related_stmt->execute();
        $related_result = $related_stmt->get_result();
        
        $related_products = [];
        while ($related_row = $related_result->fetch_assoc()) {
            // Get first available image
            $relatedImage = null;
            if (isset($related_row['image']) && !empty($related_row['image'])) {
                $imageType = isset($related_row['image_type']) ? $related_row['image_type'] : 'image/jpeg';
                $base64 = base64_encode($related_row['image']);
                $relatedImage = "data:{$imageType};base64,{$base64}";
            } elseif (isset($related_row['image_2']) && !empty($related_row['image_2'])) {
                $imageType = isset($related_row['image_2_type']) ? $related_row['image_2_type'] : 'image/jpeg';
                $base64 = base64_encode($related_row['image_2']);
                $relatedImage = "data:{$imageType};base64,{$base64}";
            } else {
                $relatedImage = 'https://via.placeholder.com/400x300?text=No+Image';
            }
            
            $related_products[] = [
                'id' => (int)$related_row['id'],
                'name' => $related_row['name'],
                'price' => (float)$related_row['price'],
                'category' => isset($related_row['category']) ? $related_row['category'] : '',
                'image' => $relatedImage
            ];
        }
        
        $related_stmt->close();
    } else {
        $related_products = [];
    }
    
    // Build final response
    $response = [
        'success' => true,
        'product' => $productData,
        'related_products' => $related_products
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>