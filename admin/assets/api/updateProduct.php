<?php
include '../../../conn.php';
require_once __DIR__ . '/../../../includes/security.php';
Security::init_secure_session('ADMIN_SESSION');
header('Content-Type: application/json');

// Get product ID to update
$productId = $_POST['productId'] ?? '';

if ($productId === '') {
  echo json_encode(['error' => 'Product ID is required for update']);
  exit();
}

// Handle file upload
$imagePath = '';
$imageUpdated = false;
if (isset($_FILES["Upload"]) && $_FILES["Upload"]["error"] === UPLOAD_ERR_OK) {
  $uploadResult = Security::store_uploaded_image($_FILES["Upload"], __DIR__ . "/../../../assets/images/uploads/", 'product_');
  if (!$uploadResult['success']) {
    http_response_code(500);
    echo json_encode(['error' => $uploadResult['error']]);
    exit();
  }
  $imagePath = $uploadResult['filename'];
  $imageUpdated = true;
}

// Get POST data
$name = $_POST['productName'] ?? '';
$color = $_POST['color'] ?? '';
$categoryName = $_POST['category'] ?? '';
$brand = $_POST['brand'] ?? '';
$price = $_POST['productPrice'] ?? 0;
$stock = $_POST['stock'] ?? 0;
$description = $_POST['description'] ?? '';

if ($name !== "" && $color !== "" && $categoryName !== "" && $brand !== "" && $price !== "" && $stock !== "" && $description !== "") {

  // Get category ID
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

  if ($imageUpdated) {
    // Update with image
    $sql = "UPDATE total_products SET name=?, color=?, category_id=?, brand=?, price=?, stock=?, description=?, image=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssdissi", $name, $color, $categoryId, $brand, $price, $stock, $description, $imagePath, $productId);
  } else {
    // Update without changing image
    $sql = "UPDATE total_products SET name=?, color=?, category_id=?, brand=?, price=?, stock=?, description=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssdisi", $name, $color, $categoryId, $brand, $price, $stock, $description, $productId);
  }

  if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
  } else {
    echo json_encode(['error' => 'Failed to update product: ' . $stmt->error]);
  }
  $stmt->close();
} else {
  echo json_encode(['error' => 'Please fill in all required fields']);
}

$conn->close();
