<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include 'db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
  echo json_encode(null);
  exit();
}
$stmt = $conn->prepare("SELECT id, name, description, price, image FROM products WHERE id = ?");
$stmt->bind_param("i",$id);
$stmt->execute();
$res = $stmt->get_result();
$item = $res->fetch_assoc();
echo json_encode($item);
?>
