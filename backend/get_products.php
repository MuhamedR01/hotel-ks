<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

include 'db.php';

// Get limit parameter if provided
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : null;

// Build query
$query = "SELECT id, name, description, price, image, category, stock, is_active 
          FROM products 
          WHERE is_active = 1 
          ORDER BY created_at DESC";

if ($limit) {
    $query .= " LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
} else {
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$res = $stmt->get_result();
$items = [];

while ($row = $res->fetch_assoc()) {
    // Ensure image path is absolute
    if (!empty($row['image']) && !filter_var($row['image'], FILTER_VALIDATE_URL)) {
        $row['image'] = 'http://localhost/hotel-ks/' . ltrim($row['image'], '/');
    }
    $items[] = $row;
}

echo json_encode($items);
$stmt->close();
$conn->close();
?>