<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin-login.php");
    exit;
}

require_once('includes/db.php');
//include("includes/navbar.php");

$id = intval($_GET['id'] ?? 0);
if ($id) {
    $sql = "DELETE FROM tbl_admin WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: manage-admin.php");
exit;
?>

