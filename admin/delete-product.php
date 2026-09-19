<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin-login.php");
    exit;
}

require_once("includes/db.php");
//include("includes/navbar.php");

$id = intval($_GET['id'] ?? 0);
if (!$id) { die("Product not found"); }

// Get product image to delete from server
$stmt = $conn->prepare("SELECT image_name FROM tbl_product WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

// Delete from DB
$sql = "DELETE FROM tbl_product WHERE product_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

// Delete image from server
if (!empty($product['image_name']) && file_exists("../assets/images/" . $product['image_name'])) {
    unlink("../assets/images/" . $product['image_name']);
}

header("Location: manage-products.php");
exit;
?>

