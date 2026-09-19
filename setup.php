<?php
/**
 * Setup Script for SmartStock – Product Management System
 * 
 * This script helps you set up the database and verify all requirements.
 * Run this in your browser after uploading the project.
 * 
 * Company: Laobaan Bangladesh LTD.
 */

$config = [
    'host' => '127.0.0.1',
    'user' => 'root',
    'pass' => '',
    'dbname' => 'products_ordering_db',
    'port' => 3306
];

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Setup | SmartStock - Product Management System</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 40px; color: #333; }";
echo ".container { max-width: 800px; margin: 0 auto; }";
echo ".success { color: #27ae60; background: #ecf0f1; padding: 10px; margin: 10px 0; border-left: 4px solid #27ae60; }";
echo ".error { color: #e74c3c; background: #ecf0f1; padding: 10px; margin: 10px 0; border-left: 4px solid #e74c3c; }";
echo ".info { color: #3498db; background: #ecf0f1; padding: 10px; margin: 10px 0; border-left: 4px solid #3498db; }";
echo ".warning { color: #f39c12; background: #ecf0f1; padding: 10px; margin: 10px 0; border-left: 4px solid #f39c12; }";
echo "h1 { color: #2c3e50; }";
echo "h2 { border-bottom: 2px solid #3498db; padding-bottom: 10px; }";
echo "code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }";
echo "</style>";
echo "</head><body>";
echo "<div class='container'>";

echo "<h1>🔧 SmartStock – Setup & Verification</h1>";

// Check PHP Version
echo "<h2>1. System Requirements Check</h2>";
$php_version = phpversion();
echo "PHP Version: " . htmlspecialchars($php_version) . " ";
if (version_compare($php_version, '7.4.0', '>=')) {
    echo "<span class='success'>✓ OK</span>";
} else {
    echo "<span class='error'>✗ FAILED - Minimum PHP 7.4 required</span>";
}
echo "<br>";

// Check MySQLi Extension
$mysqli_loaded = extension_loaded('mysqli') ? '✓ OK' : '✗ NOT LOADED';
echo "MySQLi Extension: <span class='" . (extension_loaded('mysqli') ? 'success' : 'error') . "'>" . $mysqli_loaded . "</span><br>";

// Check Database Connection
echo "<h2>2. Database Connection</h2>";
$conn = new mysqli($config['host'], $config['user'], $config['pass'], '', $config['port']);

if ($conn->connect_error) {
    echo "<div class='error'>✗ Connection Failed: " . htmlspecialchars($conn->connect_error) . "</div>";
    echo "<div class='info'>Update credentials in setup.php: <code>\$config = [...]</code></div>";
} else {
    echo "<div class='success'>✓ Connected to MySQL</div>";
    
    // Check if database exists
    echo "<h2>3. Database Setup</h2>";
    $db_exists = $conn->select_db($config['dbname']);
    
    if (!$db_exists) {
        echo "<div class='warning'>Database '" . htmlspecialchars($config['dbname']) . "' not found. Creating...</div>";
        if ($conn->query("CREATE DATABASE IF NOT EXISTS " . $config['dbname'])) {
            echo "<div class='success'>✓ Database created</div>";
            $conn->select_db($config['dbname']);
        } else {
            echo "<div class='error'>✗ Failed to create database: " . htmlspecialchars($conn->error) . "</div>";
        }
    } else {
        echo "<div class='success'>✓ Database exists</div>";
    }
    
    // Check tables
    if ($conn->select_db($config['dbname'])) {
        $tables_check = $conn->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = '" . $config['dbname'] . "'");
        $tables_count = $tables_check->fetch_assoc()['count'];
        
        echo "<h2>4. Database Tables</h2>";
        
        if ($tables_count === 0) {
            echo "<div class='info'>No tables found. You need to import the SQL schema.</div>";
            echo "<h3>To Import SQL Schema:</h3>";
            echo "<ol>";
            echo "<li>Open phpMyAdmin</li>";
            echo "<li>Select database: " . htmlspecialchars($config['dbname']) . "</li>";
            echo "<li>Go to 'Import' tab</li>";
            echo "<li>Upload <code>sql schema.txt</code></li>";
            echo "<li>Click 'Go'</li>";
            echo "</ol>";
        } else {
            echo "<div class='success'>✓ Found " . $tables_count . " tables</div>";
            
            // List tables
            $tables_result = $conn->query("SHOW TABLES");
            echo "<h3>Tables:</h3>";
            echo "<ul>";
            while ($table = $tables_result->fetch_row()) {
                echo "<li>" . htmlspecialchars($table[0]) . "</li>";
            }
            echo "</ul>";
            
            // Check admin user
            $admin_check = $conn->query("SELECT COUNT(*) as count FROM tbl_admin");
            if ($admin_check) {
                $admin_count = $admin_check->fetch_assoc()['count'];
                echo "<div class='info'>Admin users found: " . intval($admin_count) . "</div>";
                
                if ($admin_count === 0) {
                    echo "<div class='warning'>⚠ No admin user found. You need to import sample data from <code>test sql.txt</code> or create an admin record manually.</div>";
                }
            }
        }
    }
}

echo "<h2>5. File Permissions</h2>";
$assets_path = __DIR__ . '/assets/images';
if (is_writable($assets_path)) {
    echo "<div class='success'>✓ assets/images is writable</div>";
} else {
    echo "<div class='warning'>⚠ assets/images may not be writable. Images may not upload.</div>";
}

echo "<h2>6. Next Steps</h2>";
echo "<ol>";
echo "<li>Import SQL schema from <code>sql schema.txt</code> using phpMyAdmin</li>";
echo "<li>Import sample data from <code>test sql.txt</code></li>";
echo "<li>Update database credentials in <code>admin/includes/db.php</code> (if different from defaults)</li>";
echo "<li>Visit <a href='index.php'>http://localhost/products_ordering/index.php</a></li>";
echo "<li>Admin login: <code>admin</code> / <code>admin123</code></li>";
echo "</ol>";

echo "<h2>Troubleshooting</h2>";
echo "<div class='info'>";
echo "<strong>If you see connection errors:</strong><br>";
echo "Edit the \$config array at the top of this file with your MySQL credentials:<br>";
echo "<code>\$config = [<br>";
echo "&nbsp;&nbsp;'host' => '127.0.0.1',<br>";
echo "&nbsp;&nbsp;'user' => 'root',<br>";
echo "&nbsp;&nbsp;'pass' => 'your_password',<br>";
echo "&nbsp;&nbsp;'dbname' => 'products_ordering_db'<br>";
echo "];</code>";
echo "</div>";

$conn->close();
echo "</div>";
echo "</body></html>";
?>
