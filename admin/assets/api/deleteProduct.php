<?php
include "../../../conn.php";

$redirect = "../../add-product.php";

if (!$conn) {
  header("Location: {$redirect}?status=db_error");
  exit;
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
  header("Location: {$redirect}?status=invalid_id");
  exit;
}

$sql = $conn->prepare("UPDATE total_products SET is_deleted = NOW() WHERE id = ?");
$sql->bind_param("i", $product_id);
$sql->execute();

$status = $sql->affected_rows > 0 ? 'deleted' : 'not_found';

$sql->close();
$conn->close();

header("Location: {$redirect}?status={$status}");
exit;
