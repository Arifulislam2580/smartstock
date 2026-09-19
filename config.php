<?php
/**
 * SmartStock – Product Management System
 * Database Configuration
 * 
 * Company: Laobaan Bangladesh LTD.
 * 
 * Central configuration file for database connections
 */

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'products_ordering_db');
define('DB_PORT', 3306);

// Create MySQLi connection using OOP
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Error: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Error reporting for development
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>
