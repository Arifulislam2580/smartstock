<?php
/**
 * SmartStock – CLI Database Setup Script
 * Run this from command line: php setup_db.php
 */

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'products_ordering_db');
define('DB_PORT', 3306);

mysqli_report(MYSQLI_REPORT_OFF);

// Connect to MySQL (without selecting DB)
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);

if ($conn->connect_error) {
    die("MySQL Connection Failed: " . $conn->connect_error . "\n");
}

echo "Connected to MySQL.\n";

// Create database if not exists
if (!$conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME)) {
    die("Failed to create database: " . $conn->error . "\n");
}

echo "Database '" . DB_NAME . "' ready.\n";

// Select database
$conn->select_db(DB_NAME);

// Import schema
$schemaFile = __DIR__ . '/sql schema.txt';
if (!file_exists($schemaFile)) {
    die("Schema file not found: $schemaFile\n");
}

$schemaContent = file_get_contents($schemaFile);
$queries = array_filter(array_map('trim', preg_split('/;/', $schemaContent)));

echo "Importing schema...\n";
foreach ($queries as $query) {
    if (!empty($query) && !preg_match('/^--/', $query)) {  // Skip comments
        if (!$conn->query($query)) {
            $error = $conn->error;
            $safeMessage = stripos($error, 'already exists') !== false || stripos($error, 'Duplicate entry') !== false
                ? 'skipped because it already exists'
                : 'Schema Error: ' . $error;

            if (stripos($error, 'already exists') !== false || stripos($error, 'Duplicate entry') !== false) {
                echo "Skipping existing object: " . substr($query, 0, 80) . "...\n";
                continue;
            }

            echo "Schema Error: " . $error . " (Query: " . substr($query, 0, 50) . "...)\n";
        }
    }
}

echo "Schema imported.\n";

// Import test data
$testFile = __DIR__ . '/test sql.txt';
if (file_exists($testFile)) {
    $testContent = file_get_contents($testFile);
    $queries = array_filter(array_map('trim', preg_split('/;/', $testContent)));

    echo "Importing test data...\n";
    foreach ($queries as $query) {
        if (!empty($query) && !preg_match('/^--/', $query)) {  // Skip comments
            if (!$conn->query($query)) {
                $error = $conn->error;
                if (stripos($error, 'Duplicate entry') !== false || stripos($error, 'already exists') !== false) {
                    echo "Skipping duplicate sample data: " . substr($query, 0, 80) . "...\n";
                    continue;
                }
                echo "Test Data Error: " . $error . " (Query: " . substr($query, 0, 50) . "...)\n";
            }
        }
    }
    echo "Test data imported.\n";
} else {
    echo "Test data file not found: $testFile\n";
    echo "If you want sample data, create 'test sql.txt' or import data manually later.\n";
}

echo "Setup complete! Admin login: admin / admin123\n";

$conn->close();
?>