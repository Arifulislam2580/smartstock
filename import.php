<?php
/**
 * SmartStock – SQL Import Helper
 * 
 * This script provides an interface to import SQL files into your database.
 * Use this if phpMyAdmin is not available.
 * 
 * Company: Laobaan Bangladesh LTD.
 */

// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', 3306);
define('DB_NAME', 'products_ordering_db');

$message = '';
$messageType = '';

// Try to connect
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);

if ($conn->connect_error) {
    $message = "❌ Cannot connect to MySQL: " . $conn->connect_error . ". Check your credentials in this file.";
    $messageType = 'error';
} else {
    // Create database if not exists
    if (!$conn->select_db(DB_NAME)) {
        if ($conn->query("CREATE DATABASE " . DB_NAME)) {
            $conn->select_db(DB_NAME);
            $message = "Created database: " . DB_NAME;
            $messageType = 'success';
        }
    }
    
    // Handle file upload and import
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['sqlfile'])) {
        $file = $_FILES['sqlfile'];
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            $content = file_get_contents($file['tmp_name']);
            
            // Split queries by semicolon
            $queries = array_filter(array_map('trim', preg_split('/;/', $content)));
            
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            
            foreach ($queries as $query) {
                if (!empty($query)) {
                    if ($conn->query($query)) {
                        $successCount++;
                    } else {
                        $errorCount++;
                        $errors[] = $conn->error;
                    }
                }
            }
            
            if ($errorCount === 0) {
                $message = "✓ Successfully imported $successCount SQL statements!";
                $messageType = 'success';
            } else {
                $message = "⚠️ Imported $successCount statements but had $errorCount errors. Check the messages below.";
                $messageType = 'warning';
            }
        } else {
            $message = "❌ Error uploading file";
            $messageType = 'error';
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>SQL Import | SmartStock Setup</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 20px; }
        h2 { color: #555; margin-top: 30px; margin-bottom: 15px; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; border-left: 4px solid; }
        .success { background: #d4edda; color: #155724; border-color: #28a745; }
        .error { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border-color: #ffeaa7; }
        .info { background: #d1ecf1; color: #0c5460; border-color: #bee5eb; }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        input[type="file"], input[type="text"], textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: monospace; font-size: 14px; }
        textarea { min-height: 300px; resize: vertical; }
        
        button { background: #007bff; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: 600; }
        button:hover { background: #0056b3; }
        
        .instructions { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .instructions ol { margin-left: 20px; }
        .instructions li { margin-bottom: 10px; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        
        .status { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .status-item { margin: 10px 0; }
        .status-ok { color: #28a745; }
        .status-error { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 SmartStock – SQL Import Helper</h1>
        
        <div style="text-align: center; font-size: 12px; color: #666; margin-bottom: 20px;">
            by Laobaan Bangladesh LTD.
        </div>
        
        <?php if ($message): ?>
            <div class="alert <?php echo htmlspecialchars($messageType); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="status">
            <h2>Database Status</h2>
            <?php if ($conn->connect_error): ?>
                <div class="status-item status-error">❌ MySQL Error: <?php echo htmlspecialchars($conn->connect_error); ?></div>
            <?php else: ?>
                <div class="status-item status-ok">✓ Connected to MySQL</div>
                <div class="status-item">Database: <code><?php echo DB_NAME; ?></code></div>
                
                <?php
                if ($conn->select_db(DB_NAME)) {
                    $result = $conn->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");
                    $tableCount = $result->fetch_assoc()['count'];
                    echo "<div class='status-item'>";
                    if ($tableCount > 0) {
                        echo "✓ Tables found: " . intval($tableCount);
                    } else {
                        echo "⚠️ No tables found (not imported yet)";
                    }
                    echo "</div>";
                }
                ?>
            <?php endif; ?>
        </div>
        
        <h2>Method 1: Upload SQL File</h2>
        <div class="instructions">
            <ol>
                <li>Select the SQL file (<code>sql schema.txt</code> or <code>test sql.txt</code>)</li>
                <li>Click "Import File"</li>
                <li>Wait for the import to complete</li>
            </ol>
        </div>
        
        <?php if (!$conn->connect_error): ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="sqlfile">Select SQL File:</label>
                <input type="file" id="sqlfile" name="sqlfile" accept=".sql,.txt" required>
            </div>
            <button type="submit">Import File</button>
        </form>
        <?php endif; ?>
        
        <h2>Method 2: Paste SQL Content</h2>
        <div class="instructions">
            <ol>
                <li>Open <code>sql schema.txt</code> in a text editor</li>
                <li>Copy all the content</li>
                <li>Paste it into the textarea below</li>
                <li>Click "Execute SQL"</li>
            </ol>
        </div>
        
        <?php if (!$conn->connect_error): ?>
        <form method="POST">
            <div class="form-group">
                <label for="sqlcontent">SQL Content:</label>
                <textarea id="sqlcontent" name="sqlcontent" placeholder="Paste your SQL code here..."></textarea>
            </div>
            <button type="submit" name="execute_sql">Execute SQL</button>
        </form>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['execute_sql']) && !empty($_POST['sqlcontent'])) {
            $content = $_POST['sqlcontent'];
            $queries = array_filter(array_map('trim', preg_split('/;/', $content)));
            
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            
            foreach ($queries as $query) {
                if (!empty($query)) {
                    if ($conn->query($query)) {
                        $successCount++;
                    } else {
                        $errorCount++;
                        $errors[] = $conn->error;
                    }
                }
            }
            
            echo "<div class='alert " . ($errorCount === 0 ? 'success' : 'warning') . "'>";
            echo "✓ Executed $successCount statements";
            if ($errorCount > 0) {
                echo "<br>⚠️ Errors: $errorCount<br>";
                foreach ($errors as $error) {
                    if (!empty($error)) {
                        echo "- " . htmlspecialchars($error) . "<br>";
                    }
                }
            }
            echo "</div>";
        }
        ?>
        <?php endif; ?>
        
        <h2>Method 3: phpMyAdmin (Recommended)</h2>
        <div class="instructions">
            <ol>
                <li>Open phpMyAdmin in your browser (<code>http://localhost/phpmyadmin</code>)</li>
                <li>Create database: <code><?php echo DB_NAME; ?></code> (if not exists)</li>
                <li>Select the database</li>
                <li>Click "Import" tab</li>
                <li>Choose <code>sql schema.txt</code> file</li>
                <li>Click "Go" button</li>
                <li>Repeat steps 4-6 for <code>test sql.txt</code> (for sample data, if available)</li>
            </ol>
        </div>
        
        <h2>Next Steps</h2>
        <div class="status">
            <div class="status-item">1. Import <code>sql schema.txt</code> to create tables</div>
            <div class="status-item">2. Import <code>test sql.txt</code> for sample data</div>
            <div class="status-item">3. Go to <a href="index.php">index.php</a> to access the application</div>
            <div class="status-item">4. Admin login: <strong>admin</strong> / <strong>admin123</strong></div>
            <div class="status-item">5. <strong>IMPORTANT:</strong> Change default admin password!</div>
        </div>
        
        <h2>Configuration</h2>
        <div class="instructions">
            <p>Database credentials are defined at the top of this file:</p>
            <code>
DB_HOST = <?php echo DB_HOST; ?><br>
DB_USER = <?php echo DB_USER; ?><br>
DB_NAME = <?php echo DB_NAME; ?><br>
            </code>
            <p style="margin-top: 10px;">If these are incorrect, edit this file to match your MySQL setup.</p>
        </div>
    </div>
</body>
</html>
