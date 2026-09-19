<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin-login.php");
    exit;
}

require_once("includes/db.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $original_price = !empty($_POST['original_price']) ? floatval($_POST['original_price']) : null;
    $stock_quantity = max(0, intval($_POST['stock_quantity']));
    $category_id = intval($_POST['category_id']);
    $supplier_id = !empty($_POST['supplier_id']) ? intval($_POST['supplier_id']) : null;
    $featured = trim($_POST['featured']);
    $active = trim($_POST['active']);
    $image_source = trim($_POST['image_source']);

    $image_name = "";
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxFileSize = 2 * 1024 * 1024;

    // Handle online image URL
    if ($image_source === 'online' && !empty($_POST['image_url'])) {
        $image_url = trim($_POST['image_url']);
        if (filter_var($image_url, FILTER_VALIDATE_URL)) {
            $image_name = $image_url;
        } else {
            echo "<script>alert('Invalid image URL format!'); window.history.back();</script>";
            exit;
        }
    }
    // Handle file upload
    else if ($image_source === 'upload' && !empty($_FILES['image']['name'])) {
        $fileName = basename($_FILES['image']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $imageInfo = @getimagesize($_FILES['image']['tmp_name']);

        if (!in_array($fileExt, $allowedExtensions) || !$imageInfo) {
            echo "<script>alert('Invalid image file. Allowed formats: jpg, jpeg, png, gif, webp.'); window.history.back();</script>";
            exit;
        }
        if ($_FILES['image']['size'] > $maxFileSize) {
            echo "<script>alert('Image file size must be 2MB or less.'); window.history.back();</script>";
            exit;
        }

        $image_name = time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
        $target = "../assets/images/" . $image_name;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image_name = "";
        }
    }

    $sql = "INSERT INTO tbl_product (title, description, price, original_price, stock_quantity, image_name, category_id, supplier_id, featured, active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ssddisiiss", $title, $description, $price, $original_price, $stock_quantity, $image_name, $category_id, $supplier_id, $featured, $active);

    if ($stmt->execute()) {
        header("Location: manage-products.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

$categories = $conn->query("SELECT * FROM tbl_category WHERE active='Yes'");
$hasSupplierTable = $conn->query("SHOW TABLES LIKE 'tbl_supplier'");
if ($hasSupplierTable && $hasSupplierTable->num_rows > 0) {
    $suppliers = $conn->query("SELECT * FROM tbl_supplier WHERE active='Yes' ORDER BY name ASC");
} else {
    $suppliers = false;
    $conn->query("CREATE TABLE IF NOT EXISTS tbl_supplier (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        contact_person VARCHAR(150),
        phone VARCHAR(30),
        email VARCHAR(150),
        address TEXT,
        active VARCHAR(10) DEFAULT 'Yes',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Product | SmartStock Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #ffffff, #e3f2fd);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            animation: fadeIn 1s ease-in;
        }

        h2 {
            color: #007bff;
            margin-bottom: 30px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
            animation: slideDown 0.8s ease-out;
        }

        form {
            background: white;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
            animation: slideUp 0.8s ease-out;
        }

        label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-weight: 600;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"],
        textarea,
        select {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            background: linear-gradient(to right, #fff, #f9f9f9);
            transition: all 0.3s ease;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
        }

        input:focus,
        textarea:focus,
        select:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0,123,255,0.5);
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #ff5722, #ffc107, #2196f3);
            border: none;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: linear-gradient(45deg, #e65100, #ffca28, #1976d2);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <h2>Add New Product</h2>
    <form method="post" enctype="multipart/form-data">
        <label>Title:</label>
        <input type="text" name="title" required><br>

        <label>Description:</label>
        <textarea name="description"></textarea><br>

        <label>Price:</label>
        <input type="number" step="0.01" name="price" required><br>

        <label>Original Price:</label>
        <input type="number" step="0.01" name="original_price"><br>

        <label>Stock Quantity:</label>
        <input type="number" min="0" name="stock_quantity" value="0"><br>

        <label>Category:</label>
        <select name="category_id" required>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($cat['id'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($cat['title'], ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endwhile; ?>
        </select><br>

        <label>Image Source:</label>
        <div style="margin-bottom: 20px;">
            <label style="display: inline-block; margin-right: 20px;">
                <input type="radio" name="image_source" value="upload" checked> Upload File
            </label>
            <label style="display: inline-block;">
                <input type="radio" name="image_source" value="online"> Online URL
            </label>
        </div>

        <div id="upload-section">
            <label>Upload Image:</label>
            <input type="file" name="image"><br>
        </div>

        <div id="online-section" style="display:none;">
            <label>Image URL:</label>
            <input type="text" name="image_url" placeholder="https://example.com/image.jpg"><br>
        </div>

        <label>Supplier:</label>
        <select name="supplier_id">
            <option value="">-- Select Supplier --</option>
            <?php if ($suppliers && $suppliers->num_rows > 0): ?>
                <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($supplier['id'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($supplier['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endwhile; ?>
            <?php else: ?>
                <option value="">No suppliers available. Add one first.</option>
            <?php endif; ?>
        </select><br>

        <label>Featured:</label>
        <select name="featured">
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select><br>

        <label>Active:</label>
        <select name="active">
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select><br>

        <button type="submit">Add New Product</button>
    </form>
    
    <script>
        const imageSourceRadios = document.querySelectorAll('input[name="image_source"]');
        const uploadSection = document.getElementById('upload-section');
        const onlineSection = document.getElementById('online-section');

        imageSourceRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'upload') {
                    uploadSection.style.display = 'block';
                    onlineSection.style.display = 'none';
                } else {
                    uploadSection.style.display = 'none';
                    onlineSection.style.display = 'block';
                }
            });
        });
    </script>
</body>
</html>

