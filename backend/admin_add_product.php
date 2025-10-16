<?php
// dashboard or admin API endpoint for adding product (expects form POST)
include 'db.php';
session_start();
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: /dashboard/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $description = $_POST['description'] ?? '';
    // For simplicity we accept image as URL or leave null
    $image = $_POST['image'] ?? '';

    $stmt = $conn->prepare("INSERT INTO products (name, price, description, image) VALUES (?,?,?,?)");
    $stmt->bind_param("sdss", $name, $price, $description, $image);
    if ($stmt->execute()) {
        header('Location: /dashboard/index.php?msg=added');
        exit();
    } else {
        echo "Error inserting product";
    }
}
?>
