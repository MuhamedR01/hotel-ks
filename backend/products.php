<?php
require_once 'init.php';

$conn = db_connect();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Get all products or single product
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
            
            if ($product) {
                echo json_encode(["success" => true, "data" => $product]);
            } else {
                http_response_code(404);
                echo json_encode(["success" => false, "error" => "Product not found"]);
            }
        } else {
            $result = $conn->query("SELECT * FROM products ORDER BY id DESC");
            $products = [];
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
            echo json_encode(["success" => true, "data" => $products]);
        }
        break;
        
    case 'POST':
        // Add new product
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['name']) || !isset($data['price'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "error" => "Name and price are required"]);
            exit();
        }
        
        $name = $data['name'];
        $price = floatval($data['price']);
        $description = $data['description'] ?? '';
        $image = $data['image'] ?? '';
        
        $stmt = $conn->prepare("INSERT INTO products (name, price, description, image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdss", $name, $price, $description, $image);
        
        if ($stmt->execute()) {
            echo json_encode([
                "success" => true, 
                "message" => "Product added successfully",
                "id" => $conn->insert_id
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "Failed to add product"]);
        }
        break;
        
    case 'PUT':
        // Update product
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "error" => "Product ID is required"]);
            exit();
        }
        
        $id = intval($data['id']);
        $name = $data['name'];
        $price = floatval($data['price']);
        $description = $data['description'] ?? '';
        $image = $data['image'] ?? '';
        
        $stmt = $conn->prepare("UPDATE products SET name=?, price=?, description=?, image=? WHERE id=?");
        $stmt->bind_param("sdssi", $name, $price, $description, $image, $id);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Product updated successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "Failed to update product"]);
        }
        break;
        
    case 'DELETE':
        // Delete product
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Product deleted successfully"]);
            } else {
                http_response_code(500);
                echo json_encode(["success" => false, "error" => "Failed to delete product"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "error" => "Product ID is required"]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(["success" => false, "error" => "Method not allowed"]);
        break;
}
?>