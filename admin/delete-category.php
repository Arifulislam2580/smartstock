<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin-login.php");
    exit;
}

require_once("includes/db.php");

$id = intval($_GET['id'] ?? 0);
if (!$id) { die("Category not found"); }

// Get category image to delete from server
$stmt = $conn->prepare("SELECT image_name FROM tbl_category WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();

// Delete from DB
$sql = "DELETE FROM tbl_category WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

// Delete image from server
if (!empty($category['image_name']) && file_exists("../assets/images/" . $category['image_name'])) {
    unlink("../assets/images/" . $category['image_name']);
}

header("Location: manage-categories.php");
exit;
?>
