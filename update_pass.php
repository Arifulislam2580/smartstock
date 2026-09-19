<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'products_ordering_db', 3306);
$hash = password_hash('admin123', PASSWORD_BCRYPT);
$conn->query("UPDATE tbl_admin SET password='$hash' WHERE username='admin'");
echo 'Password updated.';
?>