<?php
include '../../../conn.php';
require_once __DIR__ . '/../../../includes/security.php';
Security::init_secure_session('ADMIN_SESSION');
header('Content-Type: application/json');

// Handle file upload
$imagePath = '';
if (isset($_FILES["Upload"]) && $_FILES["Upload"]["error"] === UPLOAD_ERR_OK) {
  $uploadResult = Security::store_uploaded_image($_FILES["Upload"], __DIR__ . "/../../../assets/images/uploads/", 'product_');
  if (!$uploadResult['success']) {
    http_response_code(500);
    echo json_encode(['error' => $uploadResult['error']]);
    exit();
  }
  $imagePath = $uploadResult['filename'];
}

// Get POST data
$name = $_POST['productName'] ?? '';
$color = $_POST['color'] ?? '';
$categoryName = $_POST['category'] ?? '';
$brand = $_POST['brand'] ?? '';
$price = $_POST['productPrice'] ?? 0;
$stock = $_POST['stock'] ?? 0;
$description = $_POST['description'] ?? '';

if ($name !== "" && $color !== "" && $categoryName !== "" && $brand !== "" && $price !== "" && $stock !== "" && $description !== "" && $imagePath !== "") {

  $selectCategorySql = "SELECT id FROM categories WHERE name = ?";
  $stmtCategory = $conn->prepare($selectCategorySql);
  $stmtCategory->bind_param("s", $categoryName);
  $stmtCategory->execute();
  $resultCategory = $stmtCategory->get_result();

  if ($resultCategory->num_rows === 0) {
    echo json_encode(['error' => 'Failed to get category id']);
    exit();
  }

  $categoryData = $resultCategory->fetch_assoc();
  $categoryId = $categoryData['id'];
  $stmtCategory->close();

  $sql = "INSERT INTO total_products (name, color, category_id, brand, price, stock, description, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssssdiss", $name, $color, $categoryId, $brand, $price, $stock, $description, $imagePath);

  if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Product added successfully']);
  } else {
    echo json_encode(['error' => 'Failed to add product: ' . $stmt->error]);
  }
  $stmt->close();
} else {
  echo json_encode(['error' => 'Please fill in all required fields']);
}

$conn->close();
exit();